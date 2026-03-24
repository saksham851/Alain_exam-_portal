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
                'visit_content',
                'question_text',
                'max_point',
                'option_1',
                'option_2',
                'option_3',
                'option_4',
                'correct_option',
                'score_category_1',
                'content_area_1',
                'score_category_2',
                'content_area_2'
            ]);
            
            // Sample Data Row
            fputcsv($file, [
                'Empty Exam for CSV Import (11 Sections)',
                'Section 1: Foundations of Counseling',
                'Case Study for Section 1',
                'Visit 1',
                'As the counselor is entering the building, they notice some construction...',
                'What is the standard ethical protocol for client confidentiality?',
                '1',
                'Option A Text',
                'Option B Text',
                'Option C Text',
                'Option D Text',
                'B', // Option 2 (B)
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
            $handle = fopen($file->getRealPath(), 'r');
            $header = fgetcsv($handle);

            if (count($header) < 12) {
                fclose($handle);
                return redirect()->back()->with('error', 'Invalid CSV format. Missing required columns. Please download the sample file.');
            }

            DB::beginTransaction();

            $importedCount = 0;
            $errors = [];
            $examCounts = []; 
            $rowNumber = 1;

            while (($row = fgetcsv($handle, 10000, ',')) !== false) {
                $rowNumber++;

                try {
                    if (empty(trim($row[0] ?? ''))) continue;

                    // 1. Find or Create Exam
                    $examName = trim($row[0]);
                    $exam = Exam::firstOrCreate(
                        ['name' => $examName],
                        [
                            'duration_minutes' => 180,
                            'total_questions' => 200,
                            'status' => 1,
                            'is_active' => 0
                        ]
                    );

                    if ($exam->is_active == 1) {
                        $errors[] = "Row {$rowNumber}: Exam '{$examName}' is published and cannot be modified.";
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

                    // 3. Find or Create Section
                    $sectionTitle = trim($row[1] ?? 'Default Section');
                    $section = Section::firstOrCreate(
                        ['exam_id' => $exam->id, 'title' => $sectionTitle],
                        [
                            'order_no' => Section::where('exam_id', $exam->id)->max('order_no') + 1,
                            'status' => 1
                        ]
                    );

                    // 4. Find or Create Case Study
                    $caseStudyTitle = !empty(trim($row[2] ?? '')) ? trim($row[2]) : 'Case Study for ' . $sectionTitle;
                    $caseStudy = CaseStudy::firstOrCreate(
                        ['section_id' => $section->id, 'title' => $caseStudyTitle],
                        [
                            'order_no' => CaseStudy::where('section_id', $section->id)->max('order_no') + 1,
                            'status' => 1
                        ]
                    );

                    // 5. Find or create Visit
                    $visitTitle = !empty(trim($row[3] ?? '')) ? trim($row[3]) : 'Visit 1';
                    $visitContent = !empty(trim($row[4] ?? '')) ? trim($row[4]) : '';
                    
                    $visit = Visit::firstOrCreate(
                        ['case_study_id' => $caseStudy->id, 'title' => $visitTitle],
                        [
                            'description' => $visitContent,
                            'order_no' => Visit::where('case_study_id', $caseStudy->id)->max('order_no') + 1,
                            'status' => 1
                        ]
                    );
                    
                    if (!empty($visitContent) && $visit->description !== $visitContent) {
                        $visit->update(['description' => $visitContent]);
                    }

                    // 6. Deduce question type and create question
                    $correctOptionStr = trim($row[11] ?? '');
                    $correctOptions = array_map('trim', explode(',', $correctOptionStr));
                    $isMultiple = count($correctOptions) > 1;
                    
                    $question = Question::create([
                        'visit_id' => $visit->id,
                        'question_text' => trim($row[5] ?? 'Empty Question?'),
                        'question_type' => $isMultiple ? 'multiple' : 'single',
                        'max_question_points' => (int)($row[6] ?? 1),
                        'status' => 1,
                    ]);

                    $examCounts[$exam->id]++;

                    // 7. Create options
                    $optionsData = [
                        ['key' => 'A', 'text' => $row[7] ?? ''],
                        ['key' => 'B', 'text' => $row[8] ?? ''],
                        ['key' => 'C', 'text' => $row[9] ?? ''],
                        ['key' => 'D', 'text' => $row[10] ?? ''],
                    ];

                    foreach ($optionsData as $opt) {
                        if (!empty(trim($opt['text']))) {
                            $isCorrect = false;
                            foreach ($correctOptions as $co) {
                                if (strtoupper($co) === $opt['key']) {
                                    $isCorrect = true;
                                    break;
                                }
                            }

                            QuestionOption::create([
                                'question_id' => $question->id,
                                'option_key' => $opt['key'],
                                'option_text' => trim($opt['text']),
                                'is_correct' => $isCorrect,
                            ]);
                        }
                    }

                    // 8. Handle Dual Tags
                    $this->processTags($question, $row, $rowNumber, $errors);

                    $importedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                }
            }

            fclose($handle);
            DB::commit();

            $message = "Successfully imported {$importedCount} questions.";
            if (!empty($errors)) {
                $message .= " System Warnings: " . implode('; ', array_slice($errors, 0, 5));
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    private function processTags($question, $row, $rowNumber, &$errors)
    {
        // Tag 1 (Category: col 12, Area: col 13)
        $cat1Name = trim($row[12] ?? '');
        $area1Name = trim($row[13] ?? '');

        if (!empty($cat1Name) && !empty($area1Name)) {
            $cat = ScoreCategory::where('name', $cat1Name)->first();
            
            if ($cat) {
                $area = ContentArea::where('score_category_id', $cat->id)
                                   ->where('name', $area1Name)
                                   ->first();
                
                if ($area) {
                    QuestionTag::firstOrCreate([
                        'question_id' => $question->id,
                        'score_category_id' => $cat->id,
                        'content_area_id' => $area->id
                    ]);
                }
            }
        }

        // Tag 2 (Category: col 14, Area: col 15)
        $cat2Name = trim($row[14] ?? '');
        $area2Name = trim($row[15] ?? '');

        if (!empty($cat2Name) && !empty($area2Name)) {
            $cat = ScoreCategory::where('name', $cat2Name)->first();
            
            if ($cat) {
                $area = ContentArea::where('score_category_id', $cat->id)
                                   ->where('name', $area2Name)
                                   ->first();
                
                if ($area) {
                    QuestionTag::firstOrCreate([
                        'question_id' => $question->id,
                        'score_category_id' => $cat->id,
                        'content_area_id' => $area->id
                    ]);
                }
            }
        }
    }


}
