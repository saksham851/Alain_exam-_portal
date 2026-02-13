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
        return Question::whereHas('visit.caseStudy.section', function($q) {
            $q->where('exam_id', $this->id);
        })->where('status', 1);
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
        
        // Load all active questions in this exam with their tags
        $questions = $this->getAllQuestions()->with(['tags'])->get();
        $totalQuestionsCount = $questions->count();

        // Calculate Uncategorized Questions
        $standardCategoryIds = $standard->categories->pluck('id')->toArray();
        $uncategorizedCount = 0;

        foreach ($questions as $q) {
            $hasValidTag = $q->tags->whereIn('score_category_id', $standardCategoryIds)->isNotEmpty();
            if (!$hasValidTag) {
                $uncategorizedCount++;
            }
        }

        if ($questions->isEmpty()) {
            foreach ($standard->categories as $category) {
                foreach ($category->contentAreas as $area) {
                    $contentAreasData[] = [
                        'id' => $area->id,
                        'name' => $area->name,
                        'category' => $category->name,
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
                'uncategorized_count' => 0
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
                        
                        // Map to section
                        $secId = $q->caseStudy->section_id ?? null;
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

        // MISSING ANALYSIS: Find pairs of missing points
        $guidance = [];
        $cat1 = $standard->categories->where('category_number', 1)->first();
        $cat2 = $standard->categories->where('category_number', 2)->first();

        if ($cat1 && $cat2) {
            $cat1Gaps = collect($contentAreasData)->where('category', $cat1->name)->filter(fn($a) => $a['required'] > $a['current'])->values();
            $cat2Gaps = collect($contentAreasData)->where('category', $cat2->name)->filter(fn($a) => $a['required'] > $a['current'])->values();
            
            $tempCat1Gaps = $cat1Gaps->map(fn($a) => ['id' => $a['id'], 'name' => $a['name'], 'need' => $a['required'] - $a['current']])->toArray();
            $tempCat2Gaps = $cat2Gaps->map(fn($a) => ['id' => $a['id'], 'name' => $a['name'], 'need' => $a['required'] - $a['current']])->toArray();

            $i = 0; $j = 0;
            while ($i < count($tempCat1Gaps) && $j < count($tempCat2Gaps)) {
                $take = min($tempCat1Gaps[$i]['need'], $tempCat2Gaps[$j]['need']);
                if ($take > 0) {
                    $guidance[] = [
                        'count' => $take,
                        'cat1_area' => $tempCat1Gaps[$i]['name'],
                        'cat2_area' => $tempCat2Gaps[$j]['name']
                    ];
                    $tempCat1Gaps[$i]['need'] -= $take;
                    $tempCat2Gaps[$j]['need'] -= $take;
                }
                if ($tempCat1Gaps[$i]['need'] <= 0) $i++;
                if (isset($tempCat2Gaps[$j]) && $tempCat2Gaps[$j]['need'] <= 0) $j++;
            }

            // Remaining Gaps (if any category needs more than the other)
            while ($i < count($tempCat1Gaps)) {
                if ($tempCat1Gaps[$i]['need'] > 0) {
                    $guidance[] = ['count' => $tempCat1Gaps[$i]['need'], 'cat1_area' => $tempCat1Gaps[$i]['name'], 'cat2_area' => null];
                }
                $i++;
            }
            while ($j < count($tempCat2Gaps)) {
                if ($tempCat2Gaps[$j]['need'] > 0) {
                    $guidance[] = ['count' => $tempCat2Gaps[$j]['need'], 'cat1_area' => null, 'cat2_area' => $tempCat2Gaps[$j]['name']];
                }
                $j++;
            }
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
            'uncategorized_count' => $uncategorizedCount
        ];
    }
}
