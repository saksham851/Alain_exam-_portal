<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Section;
use App\Models\CaseStudy;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTag;
use App\Models\ScoreCategory;
use App\Models\ContentArea;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DataManagementController extends Controller
{
    public function index()
    {
        return view('admin.data-management.index');
    }

    public function downloadQuestionSample()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="master_questions_import.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'exam_name',
                'section_title',
                'case_study_title',
                'visit_title',
                'question_text',
                'question_type',
                'max_question_points',
                'option_1',
                'option_2',
                'option_3',
                'option_4',
                'correct_option',
                'tag_1_category',
                'tag_1_area',
                'tag_2_category',
                'tag_2_area'
            ]);
            
            // Sample Data Row
            fputcsv($file, [
                'Empty Exam for CSV Import (11 Sections)',
                'Section 1: Foundations of Counseling',
                'Case Study for Section 1',
                'Visit 1',
                'What is the standard ethical protocol for client confidentiality?',
                'single',
                '1',
                'Option A Text',
                'Option B Text',
                'Option C Text',
                'Option D Text',
                '2',
                'Counselor Work Behavior Areas (Domains)',
                'Professional Practice and Ethics',
                'CACREP Areas',
                'Counseling and Helping Relationships'
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }



    public function importQuestions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $file = $request->file('file');
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            $header = array_shift($csvData);

            if (count($header) < 12) {
                return redirect()->back()->with('error', 'Invalid CSV format. Missing required columns. Please download the sample file.');
            }

            DB::beginTransaction();

            $importedCount = 0;
            $errors = [];
            $examCounts = []; 

            foreach ($csvData as $index => $row) {
                $rowNumber = $index + 2;
                
                try {
                    if (empty($row[0])) continue;

                    // 1. Find Exam
                    $exam = Exam::where('name', trim($row[0]))->where('is_active', 0)->first();
                    if (!$exam) {
                        $errors[] = "Row {$rowNumber}: Exam '" . ($row[0] ?? 'N/A') . "' not found or is published.";
                        continue;
                    }

                    // 2. Capacity Check
                    if (!isset($examCounts[$exam->id])) {
                        $examCounts[$exam->id] = Question::whereHas('visit.caseStudy.section', function($q) use ($exam) {
                            $q->where('exam_id', $exam->id);
                        })->where('status', 1)->count();
                    }

                    if ($exam->total_questions && $examCounts[$exam->id] >= $exam->total_questions) {
                        $errors[] = "Row {$rowNumber}: Exam '{$row[0]}' is full (Limit: {$exam->total_questions}).";
                        continue;
                    }

                    // 3. Find Section
                    $section = Section::where('exam_id', $exam->id)
                        ->where('title', trim($row[1]))
                        ->first();
                    if (!$section) {
                        $errors[] = "Row {$rowNumber}: Section '{$row[1]}' not found in exam '{$row[0]}'.";
                        continue;
                    }

                    // 4. Find or create Case Study
                    $caseStudyTitle = !empty($row[2]) ? trim($row[2]) : 'Default Case Study';
                    $caseStudy = CaseStudy::where('section_id', $section->id)
                        ->where('title', $caseStudyTitle)
                        ->first();
                    
                    if (!$caseStudy) {
                        $caseStudy = CaseStudy::create([
                            'section_id' => $section->id,
                            'title' => $caseStudyTitle,
                            'order_no' => CaseStudy::where('section_id', $section->id)->max('order_no') + 1,
                            'status' => 1
                        ]);
                    }

                    // 5. Find or create Visit
                    $visitTitle = !empty($row[3]) ? trim($row[3]) : 'Visit 1';
                    $visit = Visit::where('case_study_id', $caseStudy->id)
                        ->where('title', $visitTitle)
                        ->first();
                    
                    if (!$visit) {
                        $visit = Visit::create([
                            'case_study_id' => $caseStudy->id,
                            'title' => $visitTitle,
                            'order_no' => Visit::where('case_study_id', $caseStudy->id)->max('order_no') + 1,
                            'status' => 1
                        ]);
                    }

                    // 6. Create question
                    $question = Question::create([
                        'visit_id' => $visit->id,
                        'question_text' => trim($row[4]),
                        'question_type' => trim($row[5] ?? 'single'),
                        'max_question_points' => (int)($row[6] ?? 1),
                        'status' => 1,
                    ]);

                    $examCounts[$exam->id]++;

                    // 7. Create options
                    $options = [
                        ['key' => 'A', 'text' => $row[7] ?? '', 'idx' => '1'],
                        ['key' => 'B', 'text' => $row[8] ?? '', 'idx' => '2'],
                        ['key' => 'C', 'text' => $row[9] ?? '', 'idx' => '3'],
                        ['key' => 'D', 'text' => $row[10] ?? '', 'idx' => '4'],
                    ];

                    foreach ($options as $opt) {
                        if (!empty($opt['text'])) {
                            QuestionOption::create([
                                'question_id' => $question->id,
                                'option_key' => $opt['key'],
                                'option_text' => trim($opt['text']),
                                'is_correct' => (trim($row[11] ?? '') == $opt['idx']),
                            ]);
                        }
                    }

                    // 8. Handle Dual Tags
                    $this->processTags($question, $row);

                    $importedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Successfully imported {$importedCount} questions.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode('; ', array_slice($errors, 0, 5));
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    private function processTags($question, $row)
    {
        // Tag 1 (Category: col 12, Area: col 13)
        if (!empty($row[12]) && !empty($row[13])) {
            $cat = ScoreCategory::where('name', trim($row[12]))->first();
            if ($cat) {
                $area = ContentArea::where('score_category_id', $cat->id)
                    ->where('name', trim($row[13]))
                    ->first();
                if ($area) {
                    QuestionTag::create([
                        'question_id' => $question->id,
                        'score_category_id' => $cat->id,
                        'content_area_id' => $area->id
                    ]);
                }
            }
        }

        // Tag 2 (Category: col 14, Area: col 15)
        if (!empty($row[14]) && !empty($row[15])) {
            $cat = ScoreCategory::where('name', trim($row[14]))->first();
            if ($cat) {
                $area = ContentArea::where('score_category_id', $cat->id)
                    ->where('name', trim($row[15]))
                    ->first();
                if ($area) {
                    QuestionTag::create([
                        'question_id' => $question->id,
                        'score_category_id' => $cat->id,
                        'content_area_id' => $area->id
                    ]);
                }
            }
        }
    }


}
