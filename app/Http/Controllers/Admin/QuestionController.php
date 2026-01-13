<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Exam;
use App\Models\Section;
use App\Models\CaseStudy;
use Illuminate\Support\Facades\DB;
use App\Models\ExamCategory; // Added this line

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $query = Question::where('status', 1)
            ->with(['caseStudy.section.exam.category', 'options']);

        // Filter by Exam Category (Primary Filter)
        if ($request->filled('exam_category')) {
            $query->whereHas('caseStudy.section.exam', function($q) use ($request) {
                $q->where('category_id', $request->exam_category);
            });
        }

        // Filter by Exam (depends on Exam Category selection)
        if ($request->filled('exam')) {
            $query->whereHas('caseStudy.section', function($q) use ($request) {
                $q->where('exam_id', $request->exam);
            });
        }

        // Filter by Case Study (depends on Exam selection)
        if ($request->filled('case_study')) {
            $query->where('case_study_id', $request->case_study);
        }

        // Filter by Category (IG or DM)
        if ($request->filled('category')) {
            if ($request->category === 'ig') {
                $query->where('ig_weight', '>', 0);
            } elseif ($request->category === 'dm') {
                $query->where('dm_weight', '>', 0);
            }
        }

        // Filter by Certification Type (through Exam Category)
        if ($request->filled('certification_type')) {
            $query->whereHas('caseStudy.section.exam.category', function($q) use ($request) {
                $q->where('certification_type', $request->certification_type);
            });
        }

        // Filter by Question Type
        if ($request->filled('question_type')) {
            $query->where('question_type', $request->question_type);
        }

        $questions = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        
        // Get all exam categories for filter dropdown
        $examCategories = \App\Models\ExamCategory::where('status', 1)
            ->orderBy('name')
            ->get();

        // Get all unique certification types for filter dropdown
        $certificationTypes = \App\Models\ExamCategory::where('status', 1)
            ->distinct()
            ->orderBy('certification_type')
            ->pluck('certification_type');
        
        // Get exams based on selected exam category (if any)
        $examsQuery = Exam::where('status', 1);
        
        if ($request->filled('exam_category')) {
            $examsQuery->where('category_id', $request->exam_category);
        }

        // Also filter exams by certification type if selected
        if ($request->filled('certification_type')) {
             $examsQuery->whereHas('category', function($q) use ($request) {
                $q->where('certification_type', $request->certification_type);
            });
        }
        
        $exams = $examsQuery->orderBy('name')->get();
        
        // Get case studies based on selected exam (if any)
        $caseStudiesQuery = CaseStudy::where('status', 1)
            ->with('section.exam');
        
        if ($request->filled('exam')) {
            $caseStudiesQuery->whereHas('section', function($q) use ($request) {
                $q->where('exam_id', $request->exam);
            });
        }
        
        $caseStudies = $caseStudiesQuery->orderBy('title')->get();
        
        return view('admin.questions.index', compact('questions', 'caseStudies', 'exams', 'examCategories', 'certificationTypes'));
    }

    public function create(Request $request)
    {
        $question = null;
        $exams = Exam::where('status', 1)->get();
        
        $existingQuestions = collect();
        if ($request->has('case_study_id')) {
            $existingQuestions = Question::where('case_study_id', $request->case_study_id)
                ->where('status', 1)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('admin.questions.edit', compact('question', 'exams', 'existingQuestions'));
    }

    public function store(Request $request)
    {
        // Check if exam is active
        $caseStudy = CaseStudy::with('section.exam')->find($request->sub_case_id);
        if ($caseStudy && $caseStudy->section && $caseStudy->section->exam && $caseStudy->section->exam->is_active == 1) {
            return redirect()->back()->with('error', 'Cannot add question to an active exam. Please deactivate the exam first.');
        }

        $request->validate([
            'sub_case_id' => 'required|exists:case_studies,id',
            'existing_questions' => 'nullable|array',
            'existing_questions.*.question_text' => 'required|string',
            'existing_questions.*.question_type' => 'required|in:single,multiple',
            'existing_questions.*.question_category' => 'required|in:ig,dm',
            'existing_questions.*.options' => 'required|array|min:2',
            'existing_questions.*.options.*.text' => 'required|string',
            'questions' => 'nullable|array',
            'questions.*.question_text' => 'required_with:questions|string',
            'questions.*.question_type' => 'required_with:questions|in:single,multiple',
            'questions.*.question_category' => 'required_with:questions|in:ig,dm',
            'questions.*.options' => 'required_with:questions|array|min:2',
            'questions.*.options.*.text' => 'required_with:questions|string',
        ]);
        
        if (empty($request->existing_questions) && empty($request->questions)) {
             return redirect()->back()->with('error', 'Please add at least one question or edit existing ones.');
        }

        DB::beginTransaction();

        try {
            $createdCount = 0;
            $updatedCount = 0;

            // Update Existing Questions
            if ($request->has('existing_questions')) {
                foreach ($request->existing_questions as $qId => $qData) {
                    $question = Question::find($qId);
                    if ($question && $question->case_study_id == $request->sub_case_id) {
                        $igWeight = $qData['question_category'] === 'ig' ? 1 : 0;
                        $dmWeight = $qData['question_category'] === 'dm' ? 1 : 0;

                        $question->update([
                            'question_text' => $qData['question_text'],
                            'question_type' => $qData['question_type'],
                            'ig_weight' => $igWeight,
                            'dm_weight' => $dmWeight,
                        ]);

                        // Handle Options - simpler to delete and recreate for consistency with strict key matching
                        QuestionOption::where('question_id', $question->id)->delete();
                        
                        if (isset($qData['options']) && is_array($qData['options'])) {
                            foreach ($qData['options'] as $index => $optionData) {
                                $isCorrect = 0;
                                if (isset($optionData['is_correct']) && ($optionData['is_correct'] == 1 || $optionData['is_correct'] === '1')) {
                                    $isCorrect = 1;
                                }
                                
                                QuestionOption::create([
                                    'question_id' => $question->id,
                                    'option_key' => chr(65 + $index),
                                    'option_text' => $optionData['text'],
                                    'is_correct' => $isCorrect,
                                ]);
                            }
                        }
                        $updatedCount++;
                    }
                }
            }

            // Create New Questions
            if ($request->has('questions')) {
                foreach ($request->questions as $qData) {
                    // Skip if empty text (just in case)
                    if(empty($qData['question_text'])) continue;

                    // Set weights based on category
                    $igWeight = $qData['question_category'] === 'ig' ? 1 : 0;
                    $dmWeight = $qData['question_category'] === 'dm' ? 1 : 0;

                    // Create question
                    $question = Question::create([
                        'case_study_id' => $request->sub_case_id,
                        'question_text' => $qData['question_text'],
                        'question_type' => $qData['question_type'],
                        'ig_weight' => $igWeight,
                        'dm_weight' => $dmWeight,
                        'status' => 1,
                    ]);

                    // Create options
                    if (isset($qData['options']) && is_array($qData['options'])) {
                        foreach ($qData['options'] as $index => $optionData) {
                            $isCorrect = 0;
                            if (isset($optionData['is_correct']) && ($optionData['is_correct'] == 1 || $optionData['is_correct'] === '1')) {
                                $isCorrect = 1;
                            }
                            
                            QuestionOption::create([
                                'question_id' => $question->id,
                                'option_key' => chr(65 + $index),
                                'option_text' => $optionData['text'],
                                'is_correct' => $isCorrect,
                            ]);
                        }
                    }
                    $createdCount++;
                }
            }

            DB::commit();

            $messageParts = [];
            if ($createdCount > 0) $messageParts[] = "created {$createdCount} new question(s)";
            if ($updatedCount > 0) $messageParts[] = "updated {$updatedCount} existing question(s)";
            
            $message = "Successfully " . implode(' and ', $messageParts) . "!";

            return redirect()->route('admin.questions.index')
                ->with('question_created_success', true)
                ->with('selected_exam_id', $caseStudy->section->exam_id)
                ->with('selected_section_id', $caseStudy->section_id)
                ->with('selected_case_study_id', $request->sub_case_id)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error processing questions: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $question = Question::with(['options', 'caseStudy.section.exam'])->find($id);
        
        if (!$question || $question->status == 0) {
            return back()->with('error', 'Question not found');
        }

        $exams = Exam::where('status', 1)->get();
        
        return view('admin.questions.edit', compact('question', 'exams'));
    }

    public function update(Request $request, $id)
    {
        // Check if exam is active
        $caseStudy = CaseStudy::with('section.exam')->find($request->sub_case_id);
        if ($caseStudy && $caseStudy->section && $caseStudy->section->exam && $caseStudy->section->exam->is_active == 1) {
            return redirect()->back()->with('error', 'Cannot modify question in an active exam. Please deactivate the exam first.');
        }

        $request->validate([
            'sub_case_id' => 'required|exists:case_studies,id',
            'question_text' => 'required|string',
            'question_type' => 'required|in:single,multiple',
            'question_category' => 'required|in:ig,dm',
            'options' => 'required|array|min:2',
            'options.*.text' => 'required|string',
        ]);

        $question = Question::find($id);
        
        if (!$question || $question->status == 0) {
            return back()->with('error', 'Question not found');
        }

        $igWeight = $request->question_category === 'ig' ? 1 : 0;
        $dmWeight = $request->question_category === 'dm' ? 1 : 0;

        $question->update([
            'case_study_id' => $request->sub_case_id,
            'question_text' => $request->question_text,
            'question_type' => $request->question_type,
            'ig_weight' => $igWeight,
            'dm_weight' => $dmWeight,
        ]);

        QuestionOption::where('question_id', $question->id)->delete();
        
        foreach ($request->options as $index => $optionData) {
            $isCorrect = 0;
            if (isset($optionData['is_correct']) && ($optionData['is_correct'] == 1 || $optionData['is_correct'] === '1')) {
                $isCorrect = 1;
            }
            
            QuestionOption::create([
                'question_id' => $question->id,
                'option_key' => chr(65 + $index),
                'option_text' => $optionData['text'],
                'is_correct' => $isCorrect,
            ]);
        }

        return redirect()->route('admin.questions.index')
            ->with('success', 'Question updated successfully!');
    }

    public function destroy($id)
    {
        $question = Question::with('caseStudy.section.exam')->find($id);
        
        if (!$question) {
            return back()->with('error', 'Question not found');
        }

        // Check if exam is active
        if ($question->caseStudy && $question->caseStudy->section && $question->caseStudy->section->exam && $question->caseStudy->section->exam->is_active == 1) {
            return back()->with('error', 'Cannot delete question from an active exam. Please deactivate the exam first.');
        }

        $question->update(['status' => 0]);
        return back()->with('success', 'Question deleted successfully!');
    }

    // AJAX endpoints
    // Renamed caseStudies -> sections
    public function getCaseStudies($examId)
    {
        // This actually fetches Sections for the Exam
        // The endpoint name in route is still 'questions.getCaseStudies' for now.
        $exam = Exam::find($examId);
        $sections = Section::where('exam_id', $examId)
            ->where('status', 1)
            ->get(['id', 'title']);
        
        // Add exam active status to each section
        $sections->each(function($section) use ($exam) {
            $section->exam_is_active = $exam ? $exam->is_active : 0;
        });
        
        return response()->json($sections);
    }

    public function getSubCaseStudies($sectionId)
    {
        // This fetches CaseStudies for the Section
        $caseStudies = CaseStudy::where('section_id', $sectionId)
            ->where('status', 1)
            ->get(['id', 'title', 'order_no', 'content']);
        
        return response()->json($caseStudies);
    }

    public function getQuestions($caseStudyId)
    {
        $questions = Question::where('case_study_id', $caseStudyId)
            ->where('status', 1)
            ->with(['options'])
            ->get(); // Return full objects
            
        return response()->json($questions);
    }

    // EXPORT TO CSV
    public function export()
    {
        $questions = Question::where('status', 1)
            ->with(['caseStudy.section', 'options'])
            ->get();
        
        $filename = 'questions_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($questions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Case Study', 'Question Text', 'Type', 'IG Weight', 'DM Weight', 'Created At']);

            foreach ($questions as $q) {
                fputcsv($file, [
                    $q->id,
                    $q->caseStudy->title ?? '',
                    strip_tags($q->question_text),
                    $q->question_type,
                    $q->ig_weight,
                    $q->dm_weight,
                    $q->created_at->format('Y-m-d H:i:s'),
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
            'sub_case_id' => 'required|exists:case_studies,id'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        
        fgetcsv($handle); // Skip header
        
        $imported = 0;
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) >= 4) { 
                Question::create([
                    'case_study_id' => $request->sub_case_id,
                    'question_text' => $data[0],
                    'question_type' => $data[1] ?? 'single',
                    'ig_weight' => $data[2] ?? 0,
                    'dm_weight' => $data[3] ?? 0,
                    'status' => 1,
                ]);
                $imported++;
            }
        }
        fclose($handle);

        return redirect()->route('admin.questions.index')
            ->with('success', "Successfully imported $imported questions!");
    }
}
