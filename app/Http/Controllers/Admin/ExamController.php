<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exam;

class ExamController extends Controller
{
    // SHOW ALL EXAMS
    public function index(Request $request)
    {
        // Get filter parameters
        $search = $request->get('search');
        $categoryId = $request->get('category_id');
        $certificationType = $request->get('certification_type');
        $duration = $request->get('duration');
        // Default to 'active' if status is not explicitly 'inactive'
        $status = $request->get('status') === 'inactive' ? 0 : 1;

        // Base query - Filter by status (Active/Inactive)
        $query = Exam::where('status', $status)
            ->with(['category', 'clonedFrom']); // Eager load category and clonedFrom

        // Search by exam name or code
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('exam_code', 'like', '%' . $search . '%');
            });
        }

        // Filter by category
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        // Filter by certification type
        if ($certificationType) {
            $query->where('certification_type', $certificationType);
        }

        // Filter by exact duration
        if ($duration !== null && $duration !== '') {
            $query->where('duration_minutes', $duration);
        }

        // Filter by Active Status (Locked/Unlocked)
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->input('is_active'));
        }

        $exams = $query->orderBy('created_at', 'desc')->paginate(15);

        // Append query parameters to pagination links
        $exams->appends($request->all());

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

        // Auto-generate next exam code for cloning
        $latestExam = Exam::orderBy('id', 'desc')->first();
        $nextCode = 'MH0001';
        if ($latestExam && preg_match('/MH(\d+)/', $latestExam->exam_code, $matches)) {
            $num = intval($matches[1]) + 1;
            $nextCode = 'MH' . str_pad($num, 4, '0', STR_PAD_LEFT);
        }

        // Get all active exams for clone dropdown (available for cloning)
        $allExams = Exam::where('status', 1)
            ->with('category')
            ->orderBy('name')
            ->get();

        return view('admin.exams.index', compact('exams', 'categories', 'certificationTypes', 'nextCode', 'allExams'));
    }

    // ACTIVATE EXAM (RESTORE)
    public function activate($id)
    {
        $exam = Exam::with('sections.caseStudies.questions')->find($id);

        if (!$exam) {
            return redirect()->back()->with('error', 'Exam Not Found');
        }

        // Restore the Exam
        $exam->update(['status' => 1]);

        // Cascade restore: Activate all related Sections
        foreach ($exam->sections as $section) {
            $section->update(['status' => 1]);

            // Activate related Case Studies
            foreach ($section->caseStudies as $caseStudy) {
                $caseStudy->update(['status' => 1]);

                // Activate related Questions
                foreach ($caseStudy->questions as $question) {
                    $question->update(['status' => 1]);
                }
            }
        }

        return redirect()->route('admin.exams.index')
            ->with('success', 'Exam Activated Successfully! All related content has been restored.');
    }

    public function create()
    {
        $exam = null;
        $categories = \App\Models\ExamCategory::where('status', 1)->orderBy('name')->get();
        $examStandards = \App\Models\ExamStandard::with(['categories.contentAreas'])->get();

        // Auto-generate next exam code
        $latestExam = Exam::orderBy('id', 'desc')->first();
        $nextCode = 'MH0001';
        if ($latestExam && preg_match('/MH(\d+)/', $latestExam->exam_code, $matches)) {
            $num = intval($matches[1]) + 1;
            $nextCode = 'MH' . str_pad($num, 4, '0', STR_PAD_LEFT);
        }

        return view('admin.exams.edit', compact('exam', 'categories', 'nextCode', 'examStandards'));
    }

    // SAVE NEW
    public function store(Request $request)
    {
        // Handle new certification type
        if ($request->filled('new_certification_type')) {
            $certificationType = $request->new_certification_type;
        } else {
             $certificationType = $request->certification_type;
        }
        
        // Sanitize certification type
        $certificationType = trim(preg_replace('/\s+/', ' ', $certificationType));

        // Sanitize exam name
        if ($request->has('name')) {
            $request->merge([
                'name' => trim(preg_replace('/\s+/', ' ', $request->name))
            ]);
        }

        // FORCE GENERATE EXAM CODE ON BACKEND TO PREVENT TAMPERING
        $latestExam = Exam::orderBy('id', 'desc')->first();
        $nextCode = 'MH0001';
        if ($latestExam && preg_match('/MH(\d+)/', $latestExam->exam_code, $matches)) {
            $num = intval($matches[1]) + 1;
            $nextCode = 'MH' . str_pad($num, 4, '0', STR_PAD_LEFT);
        }

        // Ensure uniqueness (simple collision check)
        while(Exam::where('exam_code', $nextCode)->exists()) {
             $nextCode = 'MH' . str_pad((intval(substr($nextCode, 2)) + 1), 4, '0', STR_PAD_LEFT);
        }

        // Overwrite user input for exam_code
        $request->merge(['exam_code' => $nextCode]); 
        $request->merge(['certification_type' => $certificationType]);

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'exam_code' => 'required|string|max:50|unique:exams,exam_code',
            'category_id' => 'required|exists:exam_categories,id',
            'certification_type' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'exam_standard_id' => 'nullable|exists:exam_standards,id',
            'passing_score_overall' => 'nullable|integer|min:0',
            'passing_scores.*' => 'nullable|integer|min:0',
            'total_questions' => 'nullable|integer|min:0',
        ], [
            'name.regex' => 'The exam name must only contain letters, numbers, and spaces.',
            'certification_type.regex' => 'The certification type must only contain letters, numbers, and spaces.',
            'duration_minutes.min' => 'The exam duration must be at least 1 minute.',
        ]);

        $exam = Exam::create([
            'name' => $request->name,
            'exam_code' => $request->exam_code, // This is now the verified backend-generated code
            'category_id' => $request->category_id,
            'certification_type' => $certificationType,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes,
            'exam_standard_id' => $request->exam_standard_id,
            'passing_score_overall' => $request->passing_score_overall ?? 65,
            'total_questions' => $request->total_questions,
            'status' => 1,
            'is_active' => 0, // New exams start as inactive
        ]);

        // Process Passing Scores if provided
        if ($request->has('passing_scores') && is_array($request->passing_scores)) {
            foreach ($request->passing_scores as $catId => $score) {
                \App\Models\ExamCategoryPassingScore::create([
                    'exam_id' => $exam->id,
                    'exam_standard_category_id' => $catId,
                    'passing_score' => $score,
                ]);
            }
        }

        return redirect()->route('admin.sections.index')
            ->with('open_add_section_modal', true)
            ->with('new_exam_id', $exam->id)
            ->with('success', 'Exam Created Successfully!');
    }

    // SHOW EXAM
    // SHOW EXAM
    public function show($id)
    {
        $exam = Exam::with(['category', 'sections' => function($q) {
            $q->where('status', 1)->orderBy('order_no');
        }, 'sections.caseStudies' => function($q) {
            $q->where('status', 1)->orderBy('order_no');
        }, 'sections.caseStudies.questions' => function($q) {
            $q->where('status', 1);
        }, 'sections.caseStudies.questions.options'])
        ->findOrFail($id);

        $compliance = $exam->validateStandardCompliance();

        return view('admin.exams.show', compact('exam', 'compliance'));
    }

    // EDIT FORM
    public function edit($id)
    {
        $exam = Exam::with('categoryPassingScores')->find($id);

        if (!$exam) return redirect()->back()->with('error', 'Exam Not Found');

        $categories = \App\Models\ExamCategory::where('status', 1)->orderBy('name')->get();
        $examStandards = \App\Models\ExamStandard::with(['categories.contentAreas'])->get();
        return view('admin.exams.edit', compact('exam', 'categories', 'examStandards'));
    }

    // UPDATE EXAM
    public function update(Request $request, $id)
    {
        $exam = Exam::find($id);
        
        if(!$exam) return redirect()->back()->with('error', 'Exam Not Found');

        // Check if exam is locked and force edit checkbox is not checked
        if ($exam->is_active == 1 && !$request->has('force_edit')) {
            return redirect()->back()->with('error', 'This exam is locked. Check "Force Edit" to edit this active exam.');
        }

        // Handle new certification type
        $certificationType = $request->certification_type;
        if ($request->filled('new_certification_type')) {
            $certificationType = $request->new_certification_type;
        }
        
        // Sanitize certification type
        $certificationType = trim(preg_replace('/\s+/', ' ', $certificationType));

        // Sanitize input: remove extra spaces from name and exam_code
        if ($request->has('name')) {
            $request->merge([
                'name' => trim(preg_replace('/\s+/', ' ', $request->name))
            ]);
        }
        if ($request->has('exam_code')) {
            $request->merge([
                'exam_code' => trim(preg_replace('/\s+/', ' ', $request->exam_code))
            ]);
        }

        $request->merge(['certification_type' => $certificationType]);

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'exam_code' => 'required|string|max:50|unique:exams,exam_code,' . $id,
            'category_id' => 'required|exists:exam_categories,id',
            'certification_type' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'exam_standard_id' => 'nullable|exists:exam_standards,id',
            'passing_score_overall' => 'nullable|integer|min:0',
            'passing_scores.*' => 'nullable|integer|min:0',
            'total_questions' => 'nullable|integer|min:0',
        ], [
            'name.regex' => 'The exam name must only contain letters, numbers, and spaces.',
            'certification_type.regex' => 'The certification type must only contain letters, numbers, and spaces.',
            'duration_minutes.min' => 'The exam duration must be at least 1 minute.',
        ]);

        $exam->update([
            'name' => $request->name,
            'exam_code' => $request->exam_code,
            'category_id' => $request->category_id,
            'certification_type' => $certificationType,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes,
            'exam_standard_id' => $request->exam_standard_id,
            'passing_score_overall' => $request->passing_score_overall ?? 65,
            'total_questions' => $request->total_questions,
        ]);

        // Process Passing Scores if provided
        if ($request->has('passing_scores') && is_array($request->passing_scores)) {
            foreach ($request->passing_scores as $catId => $score) {
                \App\Models\ExamCategoryPassingScore::updateOrCreate(
                    [
                        'exam_id' => $exam->id, 
                        'exam_standard_category_id' => $catId
                    ],
                    ['passing_score' => $score]
                );
            }
        }

        return redirect()->route('admin.exams.index')
            ->with('success', 'Exam Updated Successfully!');
    }

    // DELETE = UPDATE STATUS
    public function destroy($id)
    {
        $exam = Exam::with('sections.caseStudies.questions')->find($id);
        
        if (!$exam) {
            return redirect()->back()->with('error', 'Exam Not Found');
        }

        // Check if exam is locked
        if ($exam->is_active == 1) {
            return redirect()->back()->with('error', 'This exam is locked and cannot be deleted.');
        }

        // Soft delete the Exam
        $exam->update(['status' => 0]); 

        // Cascade soft delete: Deactivate all related Sections
        foreach ($exam->sections as $section) {
            $section->update(['status' => 0]);

            // Deactivate related Case Studies
            foreach ($section->caseStudies as $caseStudy) {
                $caseStudy->update(['status' => 0]);

                // Deactivate related Questions
                foreach ($caseStudy->questions as $question) {
                    $question->update(['status' => 0]);
                }
            }
        }

        return redirect()->route('admin.exams.index')
            ->with('success', 'Exam Deleted Successfully! All related content has been soft deleted.');
    }

    // EXPORT TO CSV
    public function export()
    {
        $exams = Exam::where('status', 1)->get();
        
        $filename = 'exams_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($exams) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Description', 'Duration (Minutes)', 'Created At']);

            foreach ($exams as $exam) {
                fputcsv($file, [
                    $exam->id,
                    $exam->name,
                    $exam->description,
                    $exam->duration_minutes,
                    $exam->created_at->format('Y-m-d H:i:s'),
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
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Skip header row
        fgetcsv($handle);
        
        $imported = 0;
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) >= 3) {
                Exam::create([
                    'name' => $data[0],
                    'description' => $data[1] ?? null,
                    'duration_minutes' => $data[2] ?? 60,
                    'status' => 1,
                    'is_active' => 0, // Imported exams start as inactive
                ]);
                $imported++;
            }
        }
        fclose($handle);

        return redirect()->route('admin.exams.index')
            ->with('success', "Successfully imported $imported exams!");
    }


    // CLONE EXAM (DEEP COPY)
    public function clone(Request $request, $id)
    {
        $request->validate([
            'new_exam_name' => 'required|string|max:255',
            'new_exam_code' => 'required|string|max:50|unique:exams,exam_code',
        ]);

        $sourceExam = Exam::with([
            'sections' => function($query) {
                $query->where('status', 1);
            },
            'sections.caseStudies' => function($query) {
                $query->where('status', 1);
            },
            'sections.caseStudies.questions' => function($query) {
                $query->where('status', 1);
            },
            'sections.caseStudies.questions.options'
        ])->findOrFail($id);

        // Filter to get only active sections
        $activeSections = $sourceExam->sections->where('status', 1);

        // VALIDATION: Check if exam has complete content (only active items)
        if ($activeSections->count() === 0) {
            return redirect()->back()->with('warning', 'Cannot clone this exam: It has no active sections. Please add at least one section with case studies and questions.');
        }

        foreach ($activeSections as $section) {
            $activeCaseStudies = $section->caseStudies->where('status', 1);
            
            if ($activeCaseStudies->count() === 0) {
                return redirect()->back()->with('warning', "Cannot clone this exam: Section '{$section->title}' has no active case studies. Each section must have at least one case study with questions.");
            }

            foreach ($activeCaseStudies as $caseStudy) {
                $activeQuestions = $caseStudy->questions->where('status', 1);
                
                if ($activeQuestions->count() === 0) {
                    return redirect()->back()->with('warning', "Cannot clone this exam: Case Study '{$caseStudy->title}' has no active questions. Each case study must have at least one question.");
                }
            }
        }

        // Create new exam (deep copy)
        $newExam = Exam::create([
            'category_id' => $sourceExam->category_id,
            'exam_code' => $request->new_exam_code,
            'name' => $request->new_exam_name,
            'certification_type' => $sourceExam->certification_type,
            'description' => $sourceExam->description,
            'duration_minutes' => $sourceExam->duration_minutes,
            'exam_standard_id' => $sourceExam->exam_standard_id,
            'total_questions' => $sourceExam->total_questions,
            'passing_score_overall' => $sourceExam->passing_score_overall,
            'cloned_from_id' => $sourceExam->id,
        ]);

        // Clone Passing Scores
        foreach($sourceExam->categoryPassingScores as $score) {
            \App\Models\ExamCategoryPassingScore::create([
                'exam_id' => $newExam->id,
                'exam_standard_category_id' => $score->exam_standard_category_id,
                'passing_score' => $score->passing_score
            ]);
        }

        // Clone all active sections
        foreach ($activeSections as $section) {
            $newSection = \App\Models\Section::create([
                'exam_id' => $newExam->id,
                'title' => $section->title,
                'content' => $section->content,
                'order_no' => $section->order_no,
                'status' => 1, // Always set to active
                'cloned_from_id' => $section->id,
            ]);

            $activeCaseStudies = $section->caseStudies->where('status', 1);

            // Clone all active case studies in this section
            foreach ($activeCaseStudies as $caseStudy) {
                $newCaseStudy = \App\Models\CaseStudy::create([
                    'section_id' => $newSection->id,
                    'title' => $caseStudy->title,
                    'content' => $caseStudy->content,
                    'order_no' => $caseStudy->order_no,
                    'status' => 1, // Always set to active
                    'cloned_from_id' => $caseStudy->id,
                    'cloned_from_section_id' => $caseStudy->section_id,
                    'cloned_at' => now(),
                ]);

                $activeQuestions = $caseStudy->questions->where('status', 1);

                // Clone all active questions in this case study
                foreach ($activeQuestions as $question) {
                    $newQuestion = \App\Models\Question::create([
                        'case_study_id' => $newCaseStudy->id,
                        'question_text' => $question->question_text,
                        'question_type' => $question->question_type,
                        'ig_weight' => $question->ig_weight,
                        'dm_weight' => $question->dm_weight,
                        'status' => 1, // Always set to active
                        'cloned_from_id' => $question->id,
                    ]);

                    // Clone all options for this question
                    foreach ($question->options as $option) {
                        \App\Models\QuestionOption::create([
                            'question_id' => $newQuestion->id,
                            'option_key' => $option->option_key,
                            'option_text' => $option->option_text,
                            'is_correct' => $option->is_correct,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.exams.index')
            ->with('success', "Exam cloned successfully! New exam: {$newExam->name}");
    }
    // PUBLISH EXAM WITH VALIDATION
    public function publish($id)
    {
        $exam = Exam::with(['sections.caseStudies.questions', 'examStandard.categories.contentAreas'])->find($id);

        if (!$exam) {
            return back()->with('error', 'Exam not found');
        }

        // Filter only active sections (status = 1)
        $activeSections = $exam->sections->where('status', 1);

        // 1. Check if exam has at least one active section
        if ($activeSections->isEmpty()) {
            return back()->with('error', 'Cannot publish: The exam must have at least one active section.');
        }

        // 2. Check each active section for at least one active case study
        foreach ($activeSections as $section) {
            // Filter only active case studies (status = 1)
            $activeCaseStudies = $section->caseStudies->where('status', 1);
            
            if ($activeCaseStudies->isEmpty()) {
                return back()->with('error', "Cannot publish: '{$section->title}' must have at least one active case study.");
            }

            // 3. Check each active Case Study for at least one active question
            foreach ($activeCaseStudies as $caseStudy) {
                // Filter only active questions (status = 1)
                $activeQuestions = $caseStudy->questions->where('status', 1);
                
                if ($activeQuestions->isEmpty()) {
                    $csTitle = $caseStudy->title ?? "Case Study";
                    return back()->with('error', "Cannot publish: '{$csTitle}' in '{$section->title}' must have at least one active question.");
                }
            }
        }

        // 4. NEW: Validate Exam Standard Compliance (if exam has a standard assigned)
        if ($exam->exam_standard_id) {
            $validation = $exam->validateStandardCompliance();
            
            if (!$validation['valid']) {
                $errorMessage = "Cannot publish: Exam does not meet the standard requirements.\n\n";
                $errorMessage .= implode("\n", $validation['errors']);
                return back()->with('error', $errorMessage);
            }
        }

        // Validation Passed: Update status
        $exam->update(['is_active' => 1]);

        return back()->with('success', 'Exam published successfully!');
    }

    // TOGGLE EXAM STATUS (Active/Inactive)
    // TOGGLE EXAM STATUS (Active/Inactive)
    public function toggleStatus($id)
    {
        $exam = Exam::with(['sections.caseStudies.questions'])->find($id);

        if (!$exam) {
            return back()->with('error', 'Exam not found');
        }

        $newStatus = $exam->is_active ? 0 : 1;

        // If trying to PUBLISH (activate), run validation check
        if ($newStatus == 1) {
            // 1. Check if exam has at least one section
            if ($exam->sections->isEmpty()) {
                return back()->with('error', 'Cannot publish: The exam must have at least one section.');
            }

            // 2. Check each section for at least one case study
            foreach ($exam->sections as $section) {
                if ($section->caseStudies->isEmpty()) {
                    return back()->with('error', "Cannot publish: '{$section->title}' must have at least one case study.");
                }

                // 3. Check each Case Study for at least one question
                foreach ($section->caseStudies as $caseStudy) {
                    if ($caseStudy->questions->isEmpty()) {
                        $csTitle = $caseStudy->title ?? "Case Study";
                        return back()->with('error', "Cannot publish: '{$csTitle}' in '{$section->title}' must have at least one question.");
                    }
                }
            }

            // 4. EXAM STANDARD VALIDATION (NEW!)
            if ($exam->exam_standard_id) {
                $validation = $exam->validateStandardCompliance();
                
                if (!$validation['valid']) {
                    $errorMessage = "Cannot publish: Exam does not meet standard requirements.\n\n";
                    
                    foreach ($validation['errors'] as $error) {
                        $errorMessage .= "• " . $error . "\n";
                    }
                    
                    return back()->with('error', $errorMessage);
                }
            }
        }

        // Update Status if no validation errors (or if unpublishing)
        $exam->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'published' : 'unpublished';
        return back()->with('success', "Exam {$statusText} successfully!");
    }
    // AJAX Validation for Publishing
    public function validateCompliance($id)
    {
        $exam = Exam::with(['examStandard.categories.contentAreas', 'sections.caseStudies.questions'])->findOrFail($id);
        
        if (!$exam->examStandard) {
             return response()->json([
                 'success' => true, 
                 'no_standard' => true,
                 'message' => 'This exam does not follow a specific standard. It can be published immediately.'
             ]);
        }

        $compliance = $exam->validateStandardCompliance();
        
        return response()->json([
            'success' => true,
            'compliance' => $compliance,
            'exam_name' => $exam->name
        ]);
    }
    // Auto-Fix Compliance: Distribute uncategorized questions to deficient areas
    public function autoFixCompliance($id)
    {
        $exam = Exam::with(['examStandard.categories.contentAreas', 'sections.caseStudies.questions'])->findOrFail($id);
        
        $totalQuestions = $exam->total_questions ?? $exam->questions()->count();
        if($totalQuestions == 0) return response()->json(['success' => false, 'message' => 'No questions in exam']);

        // 1. Calculate Deficiencies
        $deficiencies = []; // [content_area_id => count_needed]
        
        // Helper to check deficiencies
        $checkDeficiency = function($area) use ($totalQuestions, $exam, &$deficiencies) {
             if ($area->percentage == 0) return;
             $current = $exam->questions()->where('content_area_id', $area->id)->count();
             $required = (int) round(($totalQuestions * $area->percentage) / 100);
             if ($current < $required) {
                 $deficiencies[$area->id] = $required - $current;
             }
        };

        if ($exam->examStandard->categories) {
            foreach ($exam->examStandard->categories as $category) {
                if($category->contentAreas) {
                    foreach ($category->contentAreas as $area) $checkDeficiency($area);
                }
            }
        }
        
        if(empty($deficiencies)) {
            return response()->json(['success' => true, 'message' => 'No deficiencies found']);
        }

        // 2. Get Uncategorized Questions
        // We find questions that have NULL content area OR (optional: invalid content area)
        // Simplest: NULL content_area_id
        $uncategorized = $exam->questions()
            ->whereNull('content_area_id')
            ->get();
            
        if($uncategorized->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No uncategorized questions available to distribute']);
        }
        
        // 3. Distribute
        $distributedCount = 0;
        foreach ($deficiencies as $areaId => $needed) {
            if($uncategorized->isEmpty()) break;

            $chunk = $uncategorized->splice(0, $needed);
            foreach ($chunk as $q) {
                // Update question
                $qModel = \App\Models\Question::find($q->id);
                if($qModel) {
                    $qModel->content_area_id = $areaId;
                    $qModel->save();
                    $distributedCount++;
                }
            }
        }
        
        return response()->json(['success' => true, 'distributed' => $distributedCount]);
    }
}
