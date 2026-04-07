<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\Section;
use App\Models\CaseStudy;
use App\Models\Exam;
use Illuminate\Support\Facades\DB;

class SectionController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $search = $request->get('search');
        $examId = $request->get('exam_id');
        $categoryId = $request->get('category_id');
        $certificationType = $request->get('certification_type');
        $isActive = $request->get('is_active');

        // Base query
        $status = $request->get('status', 'active');
        $query = Section::with([
            'exam.category',
            'caseStudies' => function($q) {
                $q->where('case_studies.status', 1);
            },
            'caseStudies.questions' => function($q) {
                $q->where('questions.status', 1);
            },
            'clonedFrom.exam'
        ]);

        if ($status === 'inactive') {
            $query->where('status', 0);
        } else {
            $query->where('status', 1);
        }

        // Search by section name
        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        // Filter by exam
        if ($examId) {
            $query->where('exam_id', $examId);
        }

        // Filter by category (through exam relationship)
        if ($categoryId) {
            $query->whereHas('exam', function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        // Filter by certification type (through exam relationship)
        if ($certificationType) {
            $query->whereHas('exam', function($q) use ($certificationType) {
                $q->where('certification_type', $certificationType);
            });
        }

        // Filter by exam active/inactive status
        if ($request->filled('is_active')) {
            $query->whereHas('exam', function($q) use ($isActive) {
                $q->where('is_active', $isActive);
            });
        }

        $sections = $query->orderBy('order_no', 'asc')->paginate(15)->withQueryString();

        // Get all exams for filter dropdown
        $exams = Exam::where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Get all categories for filter dropdown
        $categories = \App\Models\ExamCategory::where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Get all unique certification types for filter dropdown
        $certificationTypes = Exam::where('status', 1)
            ->whereNotNull('certification_type')
            ->distinct()
            ->orderBy('certification_type')
            ->pluck('certification_type');

        return view('admin.case_studies.index', [
            'caseStudies' => $sections,
            'exams' => $exams,
            'categories' => $categories,
            'certificationTypes' => $certificationTypes
        ]);
    }

    public function create(Request $request)
    {
        $section = null;
        $exams = Exam::where('status', 1)->with(['examStandard.categories.contentAreas', 'sections'])->get();
        
        $existingSections = collect();
        if ($request->has('exam_id')) {
            $existingSections = Section::where('exam_id', $request->exam_id)
                ->where('status', 1)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return view('admin.case_studies.edit', [
            'caseStudy' => $section, 
            'exams' => $exams,
            'existingSections' => $existingSections
        ]);
    }

    public function store(Request $request)
    {
        // Check if exam is active
        $exam = Exam::find($request->exam_id);
        if ($exam && $exam->is_active == 1) {
            return redirect()->back()->with('error', 'Cannot add section to an active exam. Please deactivate the exam first.');
        }

        // Sanitize input: remove extra spaces from title
        if ($request->has('title')) {
            $request->merge([
                'title' => trim(preg_replace('/\s+/', ' ', $request->title))
            ]);
        }

        $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sections')->where(function ($query) use ($request) {
                    return $query->where('exam_id', $request->exam_id)
                                 ->where('status', 1); // Only check against active sections
                }),
            ],
            'exam_id' => 'required|exists:exams,id',
            'exam_standard_category_id' => 'nullable|exists:exam_standard_categories,id',
            'content' => 'nullable|string',
        ], [
            'title.unique' => 'A section with this name already exists in the selected exam.',
        ]);

        // Create main section
        $section = Section::create([
            'exam_id' => $request->exam_id,
            'exam_standard_category_id' => $request->exam_standard_category_id,
            'title' => $request->title,
            'content' => $request->input('content'),
            'order_no' => Section::where('exam_id', $request->exam_id)->max('order_no') + 1,
            'status' => 1,
        ]);

        if ($request->has('return_url')) {
            return redirect($request->return_url)->with('success', 'Section created successfully!');
        }

        $prefix = auth()->user()->role === 'manager' ? 'manager' : 'admin';
        return redirect()->route($prefix . '.sections.index')
            ->with('section_created_success', true)
            ->with('created_exam_id', $request->exam_id)
            ->with('created_section_id', $section->id);
    }

    public function edit($id)
    {
        $section = Section::with('caseStudies')->find($id);
        if (!$section || $section->status == 0) return back()->with('error', 'Section not found');

        $exams = Exam::where('status', 1)->with(['examStandard.categories.contentAreas', 'sections'])->get();
        return view('admin.case_studies.edit', ['caseStudy' => $section, 'exams' => $exams]);
    }

    public function update(Request $request, $id)
    {
        // Check if exam is active
        $exam = Exam::find($request->exam_id);
        if ($exam && $exam->is_active == 1) {
            return redirect()->back()->with('error', 'Cannot modify section in an active exam. Please deactivate the exam first.');
        }

        // Sanitize input: remove extra spaces from title
        if ($request->has('title')) {
            $request->merge([
                'title' => trim(preg_replace('/\s+/', ' ', $request->title))
            ]);
        }

        $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sections')->where(function ($query) use ($request) {
                    return $query->where('exam_id', $request->exam_id)
                                 ->where('status', 1); // Only check against active sections
                })->ignore($id),
            ],
            'exam_id' => 'required|exists:exams,id',
            'exam_standard_category_id' => 'nullable|exists:exam_standard_categories,id',
            'content' => 'nullable|string',
        ], [
            'title.unique' => 'A section with this name already exists in the selected exam.',
        ]);

        $section = Section::find($id);
        if (!$section || $section->status == 0) return back()->with('error', 'Section not found');

        // Update main section
        $section->update([
            'exam_id' => $request->exam_id,
            'exam_standard_category_id' => $request->exam_standard_category_id,
            'title' => $request->title,
            'content' => $request->input('content'),
        ]);

        if ($request->has('return_url')) {
            return redirect($request->return_url)->with('success', 'Section updated successfully.');
        }

        $prefix = auth()->user()->role === 'manager' ? 'manager' : 'admin';
        return redirect()->route($prefix . '.sections.index')->with('success', 'Section updated successfully.');
    }

    public function ajaxDestroy($id)
    {
        $section = Section::with(['exam', 'caseStudies.visits.questions'])->find($id);
        if (!$section) {
            return response()->json(['success' => false, 'message' => 'Section not found'], 404);
        }

        // Check if exam is active
        if ($section->exam && $section->exam->is_active == 1) {
            return response()->json(['success' => false, 'message' => 'Cannot delete section from an active exam.'], 422);
        }

        $section->update(['status' => 0]); 

        // Cascade soft delete
        foreach ($section->caseStudies as $caseStudy) {
            $caseStudy->update(['status' => 0]);
            foreach ($caseStudy->visits as $visit) {
                $visit->update(['status' => 0]);
                foreach ($visit->questions as $question) {
                    $question->update(['status' => 0]);
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Section deleted successfully.']);
    }

    // AJAX: Get deleted (soft-deleted) sections for an exam
    public function getDeletedSections($examId)
    {
        try {
            $sections = Section::where('exam_id', $examId)
                ->where('status', 0)
                ->orderBy('updated_at', 'desc')
                ->get(['id', 'title', 'updated_at']);

            $formatted = $sections->map(function($s) {
                return [
                    'id'         => $s->id,
                    'title'      => $s->title,
                    'deleted_at' => $s->updated_at->diffForHumans(),
                ];
            });

            return response()->json(['success' => true, 'sections' => $formatted]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'sections' => [], 'error' => $e->getMessage()], 500);
        }
    }

    // AJAX: Restore a soft-deleted section
    public function ajaxRestore($id)
    {
        $section = Section::with('exam')->find($id);
        if (!$section) {
            return response()->json(['success' => false, 'message' => 'Section not found'], 404);
        }

        if ($section->status == 1) {
            return response()->json(['success' => false, 'message' => 'Section is already active.'], 422);
        }

        // Check if exam is active
        if ($section->exam && $section->exam->is_active == 1) {
            return response()->json(['success' => false, 'message' => 'Cannot restore section in an active exam. Please deactivate the exam first.'], 422);
        }

        $section->update(['status' => 1]);
        return response()->json(['success' => true, 'message' => 'Section restored successfully.']);
    }

    public function show($id)
    {
        $section = Section::with(['exam.category', 'caseStudies.questions.options'])->find($id);

        if (!$section) {
            $prefix = auth()->user()->role === 'manager' ? 'manager' : 'admin';
            return redirect()->route($prefix . '.sections.index')->with('error', 'Section not found');
        }

        return view('admin.sections.show', compact('section'));
    }

    public function destroy($id)
    {
        $section = Section::with(['exam', 'caseStudies.visits.questions'])->find($id);
        if (!$section) {
            return back()->with('error', 'Section not found');
        }

        // Check if exam is active
        if ($section->exam && $section->exam->is_active == 1) {
            return back()->with('error', 'Cannot delete section from an active exam. Please deactivate the exam first.');
        }

        $section->update(['status' => 0]); 

        // Cascade soft delete
        foreach ($section->caseStudies as $caseStudy) {
            $caseStudy->update(['status' => 0]);
            foreach ($caseStudy->visits as $visit) {
                $visit->update(['status' => 0]);
                foreach ($visit->questions as $question) {
                    $question->update(['status' => 0]);
                }
            }
        }

        return back()->with('success', 'Section deleted successfully.');
    }

    public function activate($id)
    {
        $section = Section::find($id);
        if (!$section) {
            return back()->with('error', 'Section not found');
        }

        // Check if exam is active
        if ($section->exam && $section->exam->is_active == 1) {
            return back()->with('error', 'Cannot activate section in an active exam. Please deactivate the exam first.');
        }

        $section->update(['status' => 1]); 
        return back()->with('success', 'Section activated successfully.');
    }

    // EXPORT TO CSV
    public function export()
    {
        $sections = Section::where('status', 1)->with('exam')->get();
        
        $filename = 'sections_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($sections) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Exam', 'Title', 'Content', 'Order', 'Created At']);

            foreach ($sections as $s) {
                fputcsv($file, [
                    $s->id,
                    $s->exam->name ?? '',
                    $s->title,
                    strip_tags($s->content),
                    $s->order_no,
                    $s->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // IMPORT FROM CSV  
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'exam_id' => 'required|exists:exams,id'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        
        fgetcsv($handle); // Skip header
        
        $imported = 0;
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) >= 2) {
                Section::create([
                    'exam_id' => $request->exam_id,
                    'title' => $data[0],
                    'content' => $data[1] ?? null,
                    'order_no' => Section::where('exam_id', $request->exam_id)->max('order_no') + 1,
                    'status' => 1,
                ]);
                $imported++;
            }
        }
        fclose($handle);

        return redirect()->route('admin.sections.index')
            ->with('success', "Successfully imported $imported sections!");
    }

    // AJAX: Get sections for an exam
    public function getSections($examId)
    {
        try {
            $exam = Exam::with(['examStandard.categories.contentAreas'])->find($examId);
            
            if (!$exam) {
                 return response()->json([
                     'success' => false,
                     'sections' => [], 
                     'exam' => null, 
                     'compliance' => null
                 ]);
            }

            $sections = Section::where('exam_id', $examId)
                ->where('status', 1)
                ->orderBy('order_no')
                ->get(['id', 'title', 'exam_standard_category_id', 'status']);
            
            $compliance = $exam->validateStandardCompliance();

            $formattedSections = $sections->map(function($s) use ($exam) {
                // Determine Category Name
                $catName = '';
                if ($exam->examStandard && $s->exam_standard_category_id) {
                    $cat = $exam->examStandard->categories->where('id', $s->exam_standard_category_id)->first();
                    if ($cat) $catName = $cat->name;
                }
                
                return [
                    'id' => $s->id,
                    'title' => $s->title,
                    'category_name' => $catName,
                    'exam_is_active' => (int)$exam->is_active,
                    'exam_standard_category_id' => $s->exam_standard_category_id
                ];
            });

            return response()->json([
                'success' => true,
                'sections' => $formattedSections,
                'exam' => [
                    'id' => $exam->id,
                    'name' => $exam->name,
                    'is_active' => (int)$exam->is_active,
                    'total_questions' => $exam->total_questions
                ],
                'compliance' => $compliance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'sections' => []
            ], 500);
        }
    }

    // AJAX: Get section content (case studies, visits, questions)
    public function getSectionContent($sectionId)
    {
        try {
            $section = Section::with([
                'caseStudies' => function($q) {
                    $q->where('status', 1)->orderBy('order_no');
                },
                'caseStudies.visits' => function($q) {
                    $q->where('status', 1)->orderBy('order_no');
                },
                'caseStudies.visits.questions' => function($q) {
                    $q->where('status', 1);
                },
                'caseStudies.visits.questions.options',
                'caseStudies.visits.questions.tags'
            ])->find($sectionId);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Section not found',
                    'case_studies' => []
                ]);
            }

            $formattedCaseStudies = $section->caseStudies->map(function($cs) {
                return [
                    'id' => $cs->id,
                    'title' => $cs->title,
                    'content' => $cs->content,
                    'order_no' => $cs->order_no,
                    'visits' => $cs->visits->map(function($visit) {
                        return [
                            'id' => $visit->id,
                            'title' => $visit->title,
                            'description' => $visit->description,
                            'order_no' => $visit->order_no,
                            'questions' => $visit->questions->map(function($q) {
                                return [
                                    'id' => $q->id,
                                    'question_text' => $q->question_text,
                                    'question_type' => $q->question_type,
                                    'max_question_points' => $q->max_question_points,
                                    'tags_count' => $q->tags ? $q->tags->count() : 0,
                                    'options' => $q->options->map(function($opt) {
                                        return [
                                            'id' => $opt->id,
                                            'option_key' => $opt->option_key,
                                            'option_text' => $opt->option_text,
                                            'is_correct' => $opt->is_correct
                                        ];
                                    })
                                ];
                            })
                        ];
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'case_studies' => $formattedCaseStudies
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'case_studies' => []
            ], 500);
        }
    }

    // CLONE SECTION
    public function clone(Request $request)
    {
        $request->validate([
            'source_section_ids' => 'required|array',
            'source_section_ids.*' => 'exists:sections,id',
            'target_exam_id' => 'required|exists:exams,id',
        ]);

        $targetExam = Exam::findOrFail($request->target_exam_id);

        if ($targetExam->is_active == 1) {
            return redirect()->back()->with('error', 'Cannot clone section into an active exam. Please deactivate the target exam first.');
        }

        // --- CHECK CAPACITY START ---
        $questionsToAdd = 0;
        foreach ($request->source_section_ids as $sectionId) {
             $sourceSection = Section::find($sectionId);
             if($sourceSection) {
                 // Count active questions in this section's case studies
                 $questionsToAdd += \App\Models\Question::whereHas('visit.caseStudy', function($q) use ($sectionId) {
                     $q->where('section_id', $sectionId);
                 })->where('status', 1)->count();
             }
        }

        if ($questionsToAdd > 0 && $targetExam->total_questions) {
            $currentCount = \App\Models\Question::whereHas('visit.caseStudy.section', function($q) use ($targetExam) {
                $q->where('exam_id', $targetExam->id);
            })->where('status', 1)->count();

            if (($currentCount + $questionsToAdd) > $targetExam->total_questions) {
                 return redirect()->back()->with('error', "Cannot clone sections. It would add {$questionsToAdd} questions, exceeding the exam limit of {$targetExam->total_questions} (Current: {$currentCount}).");
            }
        }
        // --- CHECK CAPACITY END ---

        DB::beginTransaction();
        try {
            $clonedCount = 0;
            $lastCreatedSectionId = null;
            
            foreach ($request->source_section_ids as $sectionId) {
                $sourceSection = Section::with(['caseStudies.visits.questions.options', 'caseStudies.visits.questions.tags'])->find($sectionId);
                
                if (!$sourceSection) continue;

                // Clone Section
                $newSection = Section::create([
                    'exam_id' => $targetExam->id,
                    'title' => $sourceSection->title,
                    'content' => $sourceSection->content,
                    'order_no' => Section::where('exam_id', $targetExam->id)->max('order_no') + 1,
                    'status' => 1,
                    'cloned_from_id' => $sourceSection->id, // Track source section
                ]);
                
                $lastCreatedSectionId = $newSection->id;

                // Clone Case Studies
                foreach ($sourceSection->caseStudies as $caseStudy) {
                    $newCaseStudy = \App\Models\CaseStudy::create([
                        'section_id' => $newSection->id,
                        'title' => $caseStudy->title,
                        'content' => $caseStudy->content,
                        'order_no' => $caseStudy->order_no,
                        'status' => $caseStudy->status,
                        'cloned_from_id' => $caseStudy->id,
                        'cloned_from_section_id' => $caseStudy->section_id,
                        'cloned_at' => now(),
                    ]);

                    // Clone Visits and Questions
                    foreach ($caseStudy->visits as $visit) {
                        $newVisit = \App\Models\Visit::create([
                            'case_study_id' => $newCaseStudy->id,
                            'title' => $visit->title,
                            'description' => $visit->description,
                            'order_no' => $visit->order_no,
                            'status' => $visit->status,
                        ]);

                        foreach ($visit->questions as $question) {
                            $newQuestion = \App\Models\Question::create([
                                'visit_id' => $newVisit->id,
                                'question_text' => $question->question_text,
                                'question_type' => $question->question_type,
                                'max_question_points' => $question->max_question_points,
                                'status' => $question->status,
                                'cloned_from_id' => $question->id,
                            ]);

                            // Clone Options
                            foreach ($question->options as $option) {
                                \App\Models\QuestionOption::create([
                                    'question_id' => $newQuestion->id,
                                    'option_key' => $option->option_key,
                                    'option_text' => $option->option_text,
                                    'is_correct' => $option->is_correct,
                                ]);
                            }

                            // Clone Tags
                            foreach ($question->tags as $tag) {
                                \App\Models\QuestionTag::create([
                                    'question_id' => $newQuestion->id,
                                    'score_category_id' => $tag->score_category_id,
                                    'content_area_id' => $tag->content_area_id,
                                ]);
                            }
                        }
                    }
                }
                $clonedCount++;
            }

            DB::commit();

            return redirect()->route('admin.sections.index', ['exam_id' => $targetExam->id])
                ->with('section_created_success', true)
                ->with('created_exam_id', $targetExam->id)
                ->with('created_section_id', $lastCreatedSectionId)
                ->with('success', "$clonedCount sections cloned successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error cloning sections: ' . $e->getMessage());
        }
    }
}
