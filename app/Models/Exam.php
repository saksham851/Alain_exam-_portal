<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Question;

class Exam extends Model
{
    protected $fillable = [
        'category_id',
        'exam_code',
        'name',
        'description',
        'certification_type',
        'duration_minutes',
        'status',
        'is_active',
        'cloned_from_id',
        'exam_standard_id',
        'total_questions',
        'passing_score_overall',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => 0,
    ];

    public function category()
    {
        return $this->belongsTo(ExamCategory::class, 'category_id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function studentExams()
    {
        return $this->hasMany(StudentExam::class);
    }
    
    public function getDurationAttribute()
    {
        return $this->duration_minutes;
    }

    public function clonedFrom()
    {
        return $this->belongsTo(Exam::class, 'cloned_from_id');
    }

    public function examStandard()
    {
        return $this->belongsTo(ExamStandard::class, 'exam_standard_id');
    }

    public function categoryPassingScores()
    {
        return $this->hasMany(ExamCategoryPassingScore::class);
    }
    
    // Custom helper to get all questions in this exam
    public function getAllQuestions()
    {
        return Question::where('questions.status', 1)
            ->whereHas('visit', function($q) {
                $q->where('status', 1)
                  ->whereHas('caseStudy', function($q) {
                      $q->where('status', 1)
                        ->whereHas('section', function($q) {
                            $q->where('exam_id', $this->id)
                              ->where('status', 1);
                        });
                  });
            });
    }

    /**
     * Validate if exam meets the standard requirements
     */
    public function validateStandardCompliance()
    {
        if (!$this->exam_standard_id) {
            return [
                'valid' => true, 
                'errors' => [], 
                'content_areas' => [],
                'total_questions' => 0,
                'uncategorized_count' => 0
            ];
        }

        $standard = $this->examStandard()->with('categories.contentAreas')->first();
        if (!$standard) {
             return ['valid' => false, 'errors' => ['Exam Standard not found'], 'content_areas' => []];
        }

        $errors = [];
        $contentAreasData = [];
        
        // Load all active questions in this exam with their tags and relationship chain
        $questions = $this->getAllQuestions()->with(['tags', 'visit.caseStudy'])->get();
        $totalQuestionsCount = $questions->count();

        // Calculate Uncategorized Questions
        $standardCategoryIds = $standard->categories->pluck('id')->toArray();
        $uncategorizedCount = 0;
        $uncategorizedQuestions = [];
        foreach ($questions as $q) {
            $hasValidTag = $q->tags->whereIn('score_category_id', $standardCategoryIds)->isNotEmpty();
            if (!$hasValidTag) {
                $uncategorizedCount++;
                $uncategorizedQuestions[] = [
                    'id'   => $q->id,
                    'text' => strip_tags($q->question_text),
                ];
            }
        }

        // Calculate Uncategorized Questions Grouped by Category
        $uncategorizedByCategory = [];
        foreach ($standard->categories as $cat) {
            $uncategorizedByCategory[$cat->id] = [];
            $catAreaIds = $cat->contentAreas->pluck('id')->toArray();
            
            foreach ($questions as $q) {
                $hasTagInCat = $q->tags->whereIn('content_area_id', $catAreaIds)->isNotEmpty();
                if (!$hasTagInCat) {
                    $uncategorizedByCategory[$cat->id][] = [
                        'id' => $q->id,
                        'text' => strip_tags($q->question_text),
                        'tags_count' => $q->tags->count()
                    ];
                }
            }
        }

        if ($questions->isEmpty()) {
            foreach ($standard->categories as $category) {
                foreach ($category->contentAreas as $area) {
                    $contentAreasData[] = [
                        'id' => $area->id,
                        'name' => $area->name,
                        'category' => $category->name,
                        'category_id' => $category->id,
                        'required' => $area->max_points,
                        'allowed_points' => $area->max_points,
                        'current' => 0,
                        'assigned_points' => 0,
                        'valid' => ($area->max_points <= 0),
                        'section_breakdown' => []
                    ];
                }
            }
            return [
                'valid' => empty(array_filter($contentAreasData, fn($a) => !$a['valid'])),
                'errors' => ['No questions added to exam'],
                'content_areas' => $contentAreasData,
                'sections' => [],
                'total_questions' => 0,
                'uncategorized_count' => 0,
                'uncategorized_questions' => [],
                'uncategorized_by_category' => []
            ];
        }

        $examSections = $this->sections()->where('status', 1)->orderBy('order_no')->get();

        foreach ($standard->categories as $category) {
            foreach ($category->contentAreas as $area) {
                
                $maxPoints = $area->max_points; 
                $assignedPoints = 0; 
                $sectionBreakdown = [];

                foreach ($examSections as $sec) {
                    $sectionBreakdown[$sec->id] = 0;
                }

                foreach ($questions as $q) {
                    if ($q->tags->contains('content_area_id', $area->id)) {
                        $points = $q->max_question_points;
                        $assignedPoints += $points;
                        
                        // Map to section via eager-loaded visit->caseStudy chain
                        $secId = $q->visit?->caseStudy?->section_id ?? null;
                        if ($secId && isset($sectionBreakdown[$secId])) {
                            $sectionBreakdown[$secId] += $points;
                        }
                    }
                }

                $isValid = ($assignedPoints >= $maxPoints);
                if (!$isValid) {
                     $errors[] = "{$category->name} - {$area->name}: Required {$maxPoints}, Found {$assignedPoints}.";
                }

                $contentAreasData[] = [
                    'id' => $area->id,
                    'name' => $area->name,
                    'category' => $category->name,
                    'category_id' => $category->id,
                    'required' => $maxPoints,
                    'allowed_points' => $maxPoints, // For show.blade.php compatibility
                    'current' => $assignedPoints,
                    'assigned_points' => $assignedPoints, // For show.blade.php compatibility
                    'percentage' => $area->percentage,
                    'valid' => $isValid,
                    'section_breakdown' => $sectionBreakdown
                ];
            }
        }

        // DYNAMIC GUIDANCE: List missing points for ALL content areas across ALL categories
        $guidance = [];
        foreach ($contentAreasData as $area) {
            if ($area['required'] > $area['current']) {
                $need = $area['required'] - $area['current'];
                $guidance[] = [
                    'count' => $need,
                    'category' => $area['category'],
                    'area_name' => $area['name'],
                    'message' => "Add {$need} points for '{$area['name']}' in '{$area['category']}'"
                ];
            }
        }

        $grandTotalRequired = 0;
        $grandTotalAchieved = 0;
        $categorySummaries = [];

        foreach ($contentAreasData as $area) {
            $grandTotalRequired += $area['required'];
            $grandTotalAchieved += $area['assigned_points'];
            
            $catName = $area['category'];
            if (!isset($categorySummaries[$catName])) {
                $categorySummaries[$catName] = ['required' => 0, 'achieved' => 0];
            }
            $categorySummaries[$catName]['required'] += $area['required'];
            $categorySummaries[$catName]['achieved'] += $area['assigned_points'];
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'compliance_guidance' => $guidance,
            'content_areas' => $contentAreasData,
            'sections' => $examSections->map(fn($s) => ['id' => $s->id, 'name' => $s->title]),
            'categories' => $standard->categories->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'content_areas' => $cat->contentAreas->map(fn($a) => [
                    'id' => $a->id, 
                    'name' => $a->name, 
                    'max_points' => $a->max_points
                ])
            ]),
            'total_questions' => $totalQuestionsCount,
            'total_exam_points' => $grandTotalAchieved,
            'grand_total_required' => $grandTotalRequired,
            'grand_total_achieved' => $grandTotalAchieved,
            'category_summaries' => $categorySummaries,
            'uncategorized_count' => $uncategorizedCount,
            'uncategorized_questions' => $uncategorizedQuestions,
            'uncategorized_by_category' => $uncategorizedByCategory
        ];
    }
}
