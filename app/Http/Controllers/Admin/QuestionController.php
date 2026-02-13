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
use App\Models\ExamCategory; 
use App\Models\QuestionTag;
use App\Models\ScoreCategory;
use App\Models\ContentArea;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        // Filter by status
        $status = $request->get('status') === 'inactive' ? 0 : 1;

        $query = Question::where('status', $status)
            ->with(['visit.caseStudy.section.exam.category', 'options', 'clonedFrom.visit.caseStudy.section.exam', 'tags.scoreCategory', 'tags.contentArea']);

        // Filter by Exam Category (Primary Filter)
        if ($request->filled('exam_category')) {
            $query->whereHas('visit.caseStudy.section.exam', function($q) use ($request) {
                $q->where('category_id', $request->exam_category);
            });
        }

        // Filter by Exam (depends on Exam Category selection)
        if ($request->filled('exam')) {
            $query->whereHas('visit.caseStudy.section', function($q) use ($request) {
                $q->where('exam_id', $request->exam);
            });
        }

        // Filter by Case Study (depends on Exam selection)
        if ($request->filled('case_study')) {
            $query->whereHas('visit', function($q) use ($request) {
                $q->where('case_study_id', $request->case_study);
            });
        }



        // Filter by Certification Type (through Exam)
        if ($request->filled('certification_type')) {
            $query->whereHas('visit.caseStudy.section.exam', function($q) use ($request) {
                $q->where('certification_type', $request->certification_type);
            });
        }

        // Filter by Question Type
        if ($request->filled('question_type')) {
            $query->where('question_type', $request->question_type);
        }

        // Only show questions that belong to active case studies and active sections
        $query->whereHas('visit.caseStudy', function($q) {
            $q->where('status', 1)
              ->whereHas('section', function($sq) {
                  $sq->where('status', 1);
              });
        });

        $questions = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        
        // Get all exam categories for filter dropdown
        $examCategories = \App\Models\ExamCategory::where('status', 1)
            ->orderBy('name')
            ->get();

        // Get all unique certification types for filter dropdown
        $certificationTypes = Exam::where('status', 1)
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
             $examsQuery->where('certification_type', $request->certification_type);
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
        $exams = Exam::where('status', 1)->with('examStandard.categories.contentAreas')->get();
        
        $existingQuestions = collect();
        if ($request->has('visit_id')) {
            $existingQuestions = Question::where('visit_id', $request->visit_id)
                ->where('status', 1)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('admin.questions.edit', compact('question', 'exams', 'existingQuestions'));
    }

    /**
     * Check if adding questions would exceed the exam's total_questions limit.
     *
     * @param int $examId
     * @param int $countToAdd
     * @return bool|string True if capacity is okay, Error string if exceeded.
     */
    private function checkExamLimit($examId, $countToAdd = 1)
    {
        $exam = Exam::find($examId);
        if (!$exam || !$exam->total_questions) {
            return true; // No limit defined
        }

        $currentCount = Question::whereHas('visit.caseStudy.section', function($q) use ($examId) {
            $q->where('exam_id', $examId);
        })->where('status', 1)->count();

        if (($currentCount + $countToAdd) > $exam->total_questions) {
            return "Cannot add {$countToAdd} question(s). Exam limit is {$exam->total_questions} and current count is {$currentCount}.";
        }

        return true;
    }

    public function store(Request $request)
    {
        // Check if exam is active
        $visit = \App\Models\Visit::with('caseStudy.section.exam')->find($request->visit_id);
        if ($visit && $visit->caseStudy && $visit->caseStudy->section && $visit->caseStudy->section->exam && $visit->caseStudy->section->exam->is_active == 1) {
            return redirect()->back()->with('error', 'Cannot add question to an active exam. Please deactivate the exam first.');
        }

        $request->validate([
            'visit_id' => 'required|exists:visits,id',
            'existing_questions' => 'nullable|array',
            'existing_questions.*.question_text' => 'required|string',
            'existing_questions.*.question_type' => 'required|in:single,multiple',
            'existing_questions.*.max_question_points' => 'nullable|integer|min:0|max:3',
            'existing_questions.*.tags' => 'nullable|array',
            'existing_questions.*.tags.*.score_category_id' => 'required_with:existing_questions.*.tags|exists:score_categories,id',
            'existing_questions.*.tags.*.content_area_id' => 'required_with:existing_questions.*.tags|exists:content_areas,id',
            'existing_questions.*.options' => 'required|array|min:2',
            'existing_questions.*.options.*.text' => 'required|string',
            'questions' => 'nullable|array',
            'questions.*.question_text' => 'required_with:questions|string',
            'questions.*.question_type' => 'required_with:questions|in:single,multiple',
            'questions.*.max_question_points' => 'required_with:questions|integer|min:0|max:3',
            'questions.*.tags' => 'nullable|array',
            'questions.*.tags.*.score_category_id' => 'required_with:questions.*.tags|exists:score_categories,id',
            'questions.*.tags.*.content_area_id' => 'required_with:questions.*.tags|exists:content_areas,id',
            'questions.*.options' => 'required_with:questions|array|min:2',
            'questions.*.options.*.text' => 'required_with:questions|string',
        ]);
        
        if (empty($request->existing_questions) && empty($request->questions)) {
             return redirect()->back()->with('error', 'Please add at least one question or edit existing ones.');
        }

        // CHECK CAPACITY
        $newCount = 0;
        if($request->has('questions')) {
             $newCount = count($request->questions);
        }
        
        if ($newCount > 0) {
            $capacityCheck = $this->checkExamLimit($visit->caseStudy->section->exam_id, $newCount);
            if ($capacityCheck !== true) {
                return redirect()->back()->with('error', $capacityCheck);
            }
        }

        DB::beginTransaction();

        try {
            $createdCount = 0;
            $updatedCount = 0;

            // Update Existing Questions
            if ($request->has('existing_questions')) {
                foreach ($request->existing_questions as $qId => $qData) {
                    $question = Question::find($qId);
                    if ($question && $question->visit_id == $request->visit_id) {
                        
                        // Check for duplicate options
                        if (isset($qData['options']) && $this->hasDuplicateOptions($qData['options'])) {
                            $strippedText = strip_tags($qData['question_text']);
                            throw new \Exception("Question '{$strippedText}' has duplicate options.");
                        }

                        // Check for duplicate question text in exam
                        if ($this->isQuestionDuplicateInExam($visit->caseStudy->section->exam_id, $qData['question_text'], $question->id)) {
                             $strippedText = strip_tags($qData['question_text']);
                             throw new \Exception("Question '{$strippedText}' already exists in this exam.");
                        }

                        $question->update([
                            'question_text' => trim($qData['question_text']),
                            'question_type' => $qData['question_type'],
                            'max_question_points' => $qData['max_question_points'] ?? 1,
                        ]);

                        // Sync Tags
                        QuestionTag::where('question_id', $question->id)->delete();
                        if (isset($qData['tags']) && is_array($qData['tags'])) {
                            foreach ($qData['tags'] as $tagData) {
                                QuestionTag::create([
                                    'question_id' => $question->id,
                                    'score_category_id' => $tagData['score_category_id'],
                                    'content_area_id' => $tagData['content_area_id'],
                                ]);
                            }
                        }

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
                                    'option_text' => trim($optionData['text']),
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

                    // Check for duplicate options
                    if (isset($qData['options']) && $this->hasDuplicateOptions($qData['options'])) {
                        $strippedText = strip_tags($qData['question_text']);
                        throw new \Exception("New Question '{$strippedText}' has duplicate options.");
                    }

                    // Check for duplicate question text in exam
                    if ($this->isQuestionDuplicateInExam($visit->caseStudy->section->exam_id, $qData['question_text'])) {
                            $strippedText = strip_tags($qData['question_text']);
                            throw new \Exception("Question '{$strippedText}' already exists in this exam.");
                    }

                    // Create question
                    $question = Question::create([
                        'visit_id' => $request->visit_id,
                        'question_text' => trim($qData['question_text']),
                        'question_type' => $qData['question_type'],
                        'max_question_points' => $qData['max_question_points'] ?? 1,
                        'status' => 1,
                    ]);

                    // Create Tags
                    if (isset($qData['tags']) && is_array($qData['tags'])) {
                         foreach ($qData['tags'] as $tagData) {
                             QuestionTag::create([
                                 'question_id' => $question->id,
                                 'score_category_id' => $tagData['score_category_id'],
                                 'content_area_id' => $tagData['content_area_id'],
                             ]);
                         }
                    }

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
                                'option_text' => trim($optionData['text']),
                                'is_correct' => $isCorrect,
                            ]);
                        }
                    }
                    $createdCount++;
                }
            }

            DB::commit();

            $messageParts = [];
            if ($createdCount > 0) $messageParts[] = "created {$createdCount} new " . \Illuminate\Support\Str::plural('question', $createdCount);
            if ($updatedCount > 0) $messageParts[] = "updated {$updatedCount} existing " . \Illuminate\Support\Str::plural('question', $updatedCount);
            
            $message = "Successfully " . implode(' and ', $messageParts) . "!";

            return redirect()->route('admin.questions.index')
                ->with('question_created_success', true)
                ->with('selected_exam_id', $visit->caseStudy->section->exam_id)
                ->with('selected_section_id', $visit->caseStudy->section_id)
                ->with('selected_visit_id', $request->visit_id)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error processing questions: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $question = Question::with(['options', 'visit.caseStudy.section.exam.examStandard.categories.contentAreas', 'tags'])->find($id);
        
        if (!$question || $question->status == 0) {
            return back()->with('error', 'Question not found');
        }

        $exams = Exam::where('status', 1)->with('examStandard.categories.contentAreas')->get();
        
        return view('admin.questions.edit', compact('question', 'exams'));
    }

    public function update(Request $request, $id)
    {
        // Check if exam is active
        $visit = \App\Models\Visit::with('caseStudy.section.exam')->find($request->visit_id);
        if ($visit && $visit->caseStudy && $visit->caseStudy->section && $visit->caseStudy->section->exam && $visit->caseStudy->section->exam->is_active == 1) {
            return redirect()->back()->with('error', 'Cannot modify question in an active exam. Please deactivate the exam first.');
        }

        $request->validate([
            'visit_id' => 'required|exists:visits,id',
            'question_text' => 'required|string',
            'question_type' => 'required|in:single,multiple',
            'max_question_points' => 'required|integer|min:0|max:3',
            'tags' => 'nullable|array',
            'tags.*.score_category_id' => 'required_with:tags|exists:score_categories,id',
            'tags.*.content_area_id' => 'required_with:tags|exists:content_areas,id',
            'options' => 'required|array|min:2',
            'options.*.text' => 'required|string',
        ]);

        $question = Question::find($id);
        
        if (!$question || $question->status == 0) {
            return back()->with('error', 'Question not found');
        }

        if ($this->hasDuplicateOptions($request->options)) {
             return back()->with('error', "The question has duplicate options.");
        }

        // Check for duplicate question text in exam
        // Need to load exam first
        $visit = \App\Models\Visit::with('caseStudy.section.exam')->find($request->visit_id);
        
        if ($this->isQuestionDuplicateInExam($visit->caseStudy->section->exam_id, $request->question_text, $id)) {
             $strippedText = strip_tags($request->question_text);
             return back()->with('error', "Question '{$strippedText}' already exists in this exam.");
        }

        $question->update([
            'visit_id' => $request->visit_id,
            'question_text' => trim($request->question_text),
            'question_type' => $request->question_type,
            'max_question_points' => $request->max_question_points ?? 1,
        ]);

        // Sync Tags
        QuestionTag::where('question_id', $question->id)->delete();
        if ($request->has('tags') && is_array($request->tags)) {
            foreach ($request->tags as $tagData) {
                QuestionTag::create([
                    'question_id' => $question->id,
                    'score_category_id' => $tagData['score_category_id'],
                    'content_area_id' => $tagData['content_area_id'],
                ]);
            }
        }

        QuestionOption::where('question_id', $question->id)->delete();
        
        foreach ($request->options as $index => $optionData) {
            $isCorrect = 0;
            if (isset($optionData['is_correct']) && ($optionData['is_correct'] == 1 || $optionData['is_correct'] === '1')) {
                $isCorrect = 1;
            }
            
            QuestionOption::create([
                'question_id' => $question->id,
                'option_key' => chr(65 + $index),
                'option_text' => trim($optionData['text']),
                'is_correct' => $isCorrect,
            ]);
        }

        return redirect()->route('admin.questions.index')
            ->with('success', 'Question updated successfully!');
    }

    public function destroy($id)
    {
        $question = Question::with('visit.caseStudy.section.exam')->find($id);
        
        if (!$question) {
            return back()->with('error', 'Question not found');
        }

        // Check if exam is active
        if ($question->visit && $question->visit->caseStudy && $question->visit->caseStudy->section && $question->visit->caseStudy->section->exam && $question->visit->caseStudy->section->exam->is_active == 1) {
            return back()->with('error', 'Cannot delete question from an active exam. Please deactivate the exam first.');
        }

        $question->update(['status' => 0]);
        return back()->with('success', 'Question deleted successfully!');
    }

    public function show($id)
    {
        $question = Question::with(['visit.caseStudy.section.exam.category', 'options'])->findOrFail($id);
        return view('admin.questions.show', compact('question'));
    }

    // AJAX endpoints
    // Renamed caseStudies -> sections
    public function getCaseStudies($examId)
    {
        // This actually fetches Sections for the Exam
        $exam = Exam::with('examStandard.categories.contentAreas')->find($examId);
        $sections = Section::where('exam_id', $examId)
            ->where('status', 1)
            ->with(['examStandardCategory'])
            ->get();
        
        // Add exam active status and category data
        $sections->each(function($section) use ($exam) {
            // Count active questions in this section
            $section->questions_count = Question::whereHas('visit.caseStudy', function($q) use ($section) {
                $q->where('section_id', $section->id);
            })->where('status', 1)->count();

            $section->exam_is_active = $exam ? $exam->is_active : 0;
            if ($section->examStandardCategory) {
                // Pass category name and its content areas for filtering in UI
                $section->category_name = $section->examStandardCategory->name;
                $section->category_id = $section->examStandardCategory->id;
                $section->allowed_content_areas = $section->examStandardCategory->contentAreas;
            }
        });
        
        $compliance = $exam ? $exam->validateStandardCompliance() : null;
        
        return response()->json([
            'sections' => $sections,
            'compliance' => $compliance,
            'exam' => $exam // Include total_questions limit
        ]);
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
        $questions = Question::whereHas('visit', function($q) use ($caseStudyId) {
                $q->where('case_study_id', $caseStudyId);
            })
            ->where('status', 1)
            ->with(['options', 'tags'])
            ->get(); // Return full objects
            
        return response()->json($questions);
    }

    // EXPORT TO CSV
    public function export()
    {
        $questions = Question::where('status', 1)
            ->with(['visit.caseStudy.section', 'options', 'tags'])
            ->get();
        
        $filename = 'questions_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($questions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Case Study', 'Question Text', 'Type', 'Max Points', 'Created At']);

            foreach ($questions as $q) {
                fputcsv($file, [
                    $q->id,
                    $q->visit && $q->visit->caseStudy ? $q->visit->caseStudy->title : '',
                    strip_tags($q->question_text),
                    $q->question_type,
                    $q->max_question_points,
                    $q->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // CLONE QUESTIONS
    public function clone(Request $request)
    {
        $request->validate([
            'source_question_ids' => 'required|array',
            'source_question_ids.*' => 'exists:questions,id',
            'source_question_ids.*' => 'exists:questions,id',
            'target_visit_id' => 'required|exists:visits,id',
        ]);

        $targetVisit = \App\Models\Visit::with('caseStudy.section.exam')->findOrFail($request->target_visit_id);

        if ($targetVisit->caseStudy && $targetVisit->caseStudy->section && $targetVisit->caseStudy->section->exam && $targetVisit->caseStudy->section->exam->is_active == 1) {
            return redirect()->back()->with('error', 'Cannot clone questions into an active exam. Please deactivate the exam first.');
        }

        // CHECK CAPACITY
        $countToClone = count($request->source_question_ids);
        $capacityCheck = $this->checkExamLimit($targetVisit->caseStudy->section->exam_id, $countToClone);
        if ($capacityCheck !== true) {
             return redirect()->back()->with('error', $capacityCheck);
        }

        DB::beginTransaction();
        try {
            $clonedCount = 0;
            
            foreach ($request->source_question_ids as $questionId) {
                 $sourceQuestion = Question::with(['options', 'tags'])->findOrFail($questionId);

                // Check for duplicates in TARGET exam
                if ($this->isQuestionDuplicateInExam($targetVisit->caseStudy->section->exam_id, $sourceQuestion->question_text)) {
                     $strippedText = strip_tags($sourceQuestion->question_text);
                     throw new \Exception("Question '{$strippedText}' already exists in the target exam.");
                }

                $newQuestion = Question::create([
                    'visit_id' => $targetVisit->id,
                    'question_text' => trim($sourceQuestion->question_text),
                    'question_type' => $sourceQuestion->question_type,
                    'max_question_points' => $sourceQuestion->max_question_points,
                    'status' => $sourceQuestion->status,
                    'cloned_from_id' => $sourceQuestion->id,
                ]);

                // Clone Tags
                foreach ($sourceQuestion->tags as $tag) {
                     QuestionTag::create([
                         'question_id' => $newQuestion->id,
                         'score_category_id' => $tag->score_category_id,
                         'content_area_id' => $tag->content_area_id,
                     ]);
                }

                foreach ($sourceQuestion->options as $option) {
                    QuestionOption::create([
                        'question_id' => $newQuestion->id,
                        'option_key' => $option->option_key,
                        'option_text' => trim($option->option_text),
                        'is_correct' => $option->is_correct,
                    ]);
                }
                $clonedCount++;
            }
            
            DB::commit();

            return redirect()->route('admin.questions.index')
                ->with('question_created_success', true)
                ->with('selected_exam_id', $targetVisit->caseStudy->section->exam_id)
                ->with('selected_section_id', $targetVisit->caseStudy->section_id)
                ->with('selected_visit_id', $targetVisit->id)
                ->with('success', "$clonedCount question(s) cloned successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error cloning questions: ' . $e->getMessage());
        }
    }

    // IMPORT FROM CSV
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'file' => 'required|file|mimes:csv,txt',
            'visit_id' => 'required|exists:visits,id'
        ]);

        $visit = \App\Models\Visit::with('caseStudy.section.exam')->findOrFail($request->visit_id);
        if ($visit->caseStudy->section->exam && $visit->caseStudy->section->exam->is_active == 1) {
            return redirect()->back()->with('error', 'Cannot import questions into an active exam.');
        }

        $file = $request->file('file');
        
        $csvData = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_shift($csvData); 
        
        $countToImport = 0;
        foreach($csvData as $row) {
            if(count($row) >= 4) {
                 $countToImport++;
            }
        }

        if ($countToImport > 0) {
            $capacityCheck = $this->checkExamLimit($visit->caseStudy->section->exam_id, $countToImport);
            if ($capacityCheck !== true) {
                 return redirect()->back()->with('error', $capacityCheck);
            }
        }

        $handle = fopen($file->getRealPath(), 'r');
        
        fgetcsv($handle); // Skip header
        
        $imported = 0;
        $skipped = 0;
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) >= 4) { 
                $qText = trim($data[0]);
                
                // Check if already exists in Exam
                if ($this->isQuestionDuplicateInExam($visit->caseStudy->section->exam_id, $qText)) {
                    $skipped++;
                    continue; 
                }

                Question::create([
                    'visit_id' => $request->visit_id,
                    'question_text' => $qText,
                    'question_type' => $data[1] ?? 'single',
                    'content_area_id' => null, 
                    'status' => 1,
                ]);
                $imported++;
            }
        }
        fclose($handle);
        
        $msg = "Successfully imported $imported questions!";
        if ($skipped > 0) {
            $msg .= " ($skipped duplicates skipped)";
        }

        return redirect()->route('admin.questions.index')
            ->with('success', $msg);
    }

    // ACTIVATE QUESTION
    public function activate($id)
    {
        $question = Question::with('visit.caseStudy.section.exam')->findOrFail($id);
        
        if ($question->visit && $question->visit->caseStudy && $question->visit->caseStudy->section && $question->visit->caseStudy->section->exam && $question->visit->caseStudy->section->exam->is_active == 1) {
            return redirect()->back()->with('error', 'Cannot activate question in an active exam.');
        }

        if ($question->status == 0) {
             $capacityCheck = $this->checkExamLimit($question->visit->caseStudy->section->exam_id, 1);
             if ($capacityCheck !== true) {
                  return redirect()->back()->with('error', $capacityCheck);
             }
        }

        $question->update(['status' => 1]);
        return redirect()->back()->with('success', 'Question activated successfully!');
    }

    public function updateField(Request $request, $id)
    {
        $request->validate([
            'field' => 'required|in:question_text,question_type,content_area_id,max_question_points',
            'value' => 'nullable'
        ]);

        $question = Question::with('visit.caseStudy.section.exam')->findOrFail($id);

        if ($question->visit && $question->visit->caseStudy && $question->visit->caseStudy->section && $question->visit->caseStudy->section->exam && $question->visit->caseStudy->section->exam->is_active == 1) {
            return response()->json(['success' => false, 'message' => 'Cannot modify question in an active exam.']);
        }

        $field = $request->field;
        $value = $request->value;

        // Special validation/logic if needed
        if ($field === 'content_area_id') {
            if ($value && !\App\Models\ContentArea::where('id', $value)->exists()) {
                 return response()->json(['success' => false, 'message' => 'Invalid content area selected.']);
            }
            if (!$value) $value = null; // Convert empty string to null
        }

        if ($field === 'max_question_points') {
            if (!is_numeric($value) || $value < 0 || $value > 3) {
                return response()->json(['success' => false, 'message' => 'Points must be between 0 and 3.']);
            }
        }

        $question->$field = $value;
        $question->save();

        // Get updated compliance stats
        // Reload relationships to ensure we get the full chain
        $question->load('visit.caseStudy.section.exam.examStandard.categories.contentAreas');
        $exam = $question->visit && $question->visit->caseStudy ? $question->visit->caseStudy->section->exam : null;
        
        $compliance = null;
        if($exam) {
             $compliance = $exam->validateStandardCompliance();
        }

        return response()->json([
            'success' => true, 
            'message' => 'Updated successfully',
            'compliance' => $compliance
        ]);
    }

    /**
     * Get questions by case study ID (AJAX)
     */
    public function getQuestionsByCaseStudy($caseStudyId)
    {
        $questions = Question::whereHas('visit', function($q) use ($caseStudyId) {
                $q->where('case_study_id', $caseStudyId);
            })
            ->where('status', 1)
            ->with(['options', 'tags'])
            ->orderBy('id')
            ->get();
        
        return response()->json($questions);
    }

    public function getVisits($caseStudyId)
    {
        $visits = \App\Models\Visit::where('case_study_id', $caseStudyId)
            ->where('status', 1)
            ->orderBy('order_no')
            ->get(['id', 'title', 'order_no']);
            
        return response()->json($visits);
    }

    public function getQuestionsByVisit($visitId)
    {
        $questions = Question::where('visit_id', $visitId)
            ->where('status', 1)
            ->with(['options', 'tags'])
            ->orderBy('id')
            ->get();
            
        return response()->json($questions);
    }

    /**
     * Check if a question with the same text exists in the given exam.
     *
     * @param int $examId
     * @param string $questionText
     * @param int|null $excludeQuestionId
     * @return bool
     */
    private function isQuestionDuplicateInExam($examId, $questionText, $excludeQuestionId = null)
    {
        return Question::whereHas('visit.caseStudy.section', function($q) use ($examId) {
            $q->where('exam_id', $examId);
        })
        ->where('question_text', trim($questionText))
        ->where('status', 1) // Only check against active questions? Or all? User said "repeat na ho", usually implies visible ones. But to satisfy strict uniqueness, maybe all. Let's stick to status=1 for now as deleted ones might be restored.
        ->when($excludeQuestionId, function($q) use ($excludeQuestionId) {
            $q->where('id', '!=', $excludeQuestionId);
        })
        ->exists();
    }

    /**
     * Check if the provided options array has duplicate texts.
     *
     * @param array $options
     * @return bool
     */
    private function hasDuplicateOptions(array $options)
    {
        $texts = array_map(function($opt) {
            return trim(strtolower($opt['text']));
        }, $options);
        
        return count($texts) !== count(array_unique($texts));
    }
}
