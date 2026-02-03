<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Section;
use App\Models\CaseStudy;
use App\Models\Question;
use App\Models\QuestionOption;
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
            'Content-Disposition' => 'attachment; filename="sample_questions_import.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'exam_name',
                'section_title',
                'case_study_title',
                'question_text',
                'question_type',
                'ig_weight',
                'dm_weight',
                'option_1',
                'option_2',
                'option_3',
                'option_4',
                'correct_option'
            ]);
            
            // Sample Data Row 1 - Question with case study
            fputcsv($file, [
                'Engineering Fundamentals',
                'Mathematics',
                'Calculus Basics',
                'What is the derivative of x^2?',
                'single',
                '1',
                '1',
                '2x',
                'x',
                'x^2',
                '0',
                '1'
            ]);
            
            // Sample Data Row 2 - Question without case study
            fputcsv($file, [
                'Engineering Fundamentals',
                'Physics',
                '',
                'What is Newton\'s first law?',
                'single',
                '1',
                '1',
                'Law of Inertia',
                'Law of Acceleration',
                'Law of Action-Reaction',
                'Law of Gravity',
                '1'
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function downloadCaseStudySample()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sample_case_studies_import.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'exam_name',
                'section_title',
                'case_study_title',
                'case_study_content',
                'order_no'
            ]);
            
            // Sample Data Row 1
            fputcsv($file, [
                'Engineering Fundamentals',
                'Mathematics',
                'Calculus Basics',
                'This case study covers fundamental concepts of calculus including derivatives and integrals.',
                '1'
            ]);
            
            // Sample Data Row 2
            fputcsv($file, [
                'Engineering Fundamentals',
                'Physics',
                'Newton\'s Laws',
                'This case study explores Newton\'s three laws of motion and their applications.',
                '2'
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

            // Validate headers
            $expectedHeaders = [
                'exam_name', 'section_title', 'case_study_title', 'question_text',
                'question_type', 'ig_weight', 'dm_weight', 'option_1', 'option_2',
                'option_3', 'option_4', 'correct_option'
            ];

            if ($header !== $expectedHeaders) {
                return redirect()->back()->with('error', 'Invalid CSV format. Please download the sample file.');
            }

            DB::beginTransaction();

            $importedCount = 0;
            $errors = [];

            foreach ($csvData as $index => $row) {
                $rowNumber = $index + 2; // +2 because of header and 0-index
                
                try {
                    // Find exam (must be unpublished)
                    $exam = Exam::where('name', $row[0])->where('is_active', 0)->first();
                    if (!$exam) {
                        $errors[] = "Row {$rowNumber}: Exam '{$row[0]}' not found or is published.";
                        continue;
                    }

                    // Find section
                    $section = Section::where('exam_id', $exam->id)
                        ->where('title', $row[1])
                        ->first();
                    if (!$section) {
                        $errors[] = "Row {$rowNumber}: Section '{$row[1]}' not found in exam '{$row[0]}'.";
                        continue;
                    }

                    // Find or skip case study
                    $caseStudyId = null;
                    if (!empty($row[2])) {
                        $caseStudy = CaseStudy::where('section_id', $section->id)
                            ->where('title', $row[2])
                            ->first();
                        if (!$caseStudy) {
                            $errors[] = "Row {$rowNumber}: Case Study '{$row[2]}' not found in section '{$row[1]}'.";
                            continue;
                        }
                        $caseStudyId = $caseStudy->id;
                    }

                    // Create question
                    $question = Question::create([
                        'case_study_id' => $caseStudyId,
                        'question_text' => $row[3],
                        'question_type' => $row[4],
                        'ig_weight' => (int)$row[5],
                        'dm_weight' => (int)$row[6],
                        'status' => 1,
                    ]);

                    // Create options
                    $options = [
                        ['option_key' => 'A', 'option_text' => $row[7], 'is_correct' => ($row[11] == '1')],
                        ['option_key' => 'B', 'option_text' => $row[8], 'is_correct' => ($row[11] == '2')],
                        ['option_key' => 'C', 'option_text' => $row[9], 'is_correct' => ($row[11] == '3')],
                        ['option_key' => 'D', 'option_text' => $row[10], 'is_correct' => ($row[11] == '4')],
                    ];

                    foreach ($options as $option) {
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_key' => $option['option_key'],
                            'option_text' => $option['option_text'],
                            'is_correct' => $option['is_correct'],
                        ]);
                    }

                    $importedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Successfully imported {$importedCount} questions.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode('; ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= " (and " . (count($errors) - 5) . " more errors)";
                }
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function importCaseStudies(Request $request)
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

            // Validate headers
            $expectedHeaders = [
                'exam_name', 'section_title', 'case_study_title',
                'case_study_content', 'order_no'
            ];

            if ($header !== $expectedHeaders) {
                return redirect()->back()->with('error', 'Invalid CSV format. Please download the sample file.');
            }

            DB::beginTransaction();

            $importedCount = 0;
            $errors = [];

            foreach ($csvData as $index => $row) {
                $rowNumber = $index + 2;
                
                try {
                    // Find exam (must be unpublished)
                    $exam = Exam::where('name', $row[0])->where('is_active', 0)->first();
                    if (!$exam) {
                        $errors[] = "Row {$rowNumber}: Exam '{$row[0]}' not found or is published.";
                        continue;
                    }

                    // Find section
                    $section = Section::where('exam_id', $exam->id)
                        ->where('title', $row[1])
                        ->first();
                    if (!$section) {
                        $errors[] = "Row {$rowNumber}: Section '{$row[1]}' not found in exam '{$row[0]}'.";
                        continue;
                    }

                    // Create case study
                    CaseStudy::create([
                        'section_id' => $section->id,
                        'title' => $row[2],
                        'content' => $row[3],
                        'order_no' => (int)$row[4],
                        'status' => 1,
                    ]);

                    $importedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Successfully imported {$importedCount} case studies.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode('; ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= " (and " . (count($errors) - 5) . " more errors)";
                }
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
