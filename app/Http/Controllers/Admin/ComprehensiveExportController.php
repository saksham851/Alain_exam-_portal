<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\CaseStudy;
use App\Models\SubCaseStudy;
use App\Models\Question;
use App\Models\QuestionOption;

class ComprehensiveExportController extends Controller
{
    // EXPORT COMPLETE DATA WITH HIERARCHY
    public function exportComplete()
    {
        $questions = Question::where('status', 1)
            ->with(['subCase.caseStudy.exam', 'options'])
            ->get();
        
        $filename = 'complete_exam_data_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($questions) {
            $file = fopen('php://output', 'w');
            
            // Header with complete hierarchy
            fputcsv($file, [
                'Exam Name',
                'Exam Duration (mins)',
                'Case Study Title',
                'Case Study Order',
                'Sub Case Study Title',
                'Sub Case Study Order',
                'Question Text',
                'Question Type',
                'Internal Governance (IG)?',
                'Decision Making (DM)?',
                'Option A',
                'Option B',
                'Option C',
                'Option D',
                'Correct Answer(s)'
            ]);

            foreach ($questions as $question) {
                $options = $question->options->sortBy('option_key');
                $correctAnswers = $options->where('is_correct', 1)->pluck('option_key')->toArray();
                
                // User-friendly question type
                $questionType = $question->question_type == 'single' ? 'Single Choice' : 'Multiple Choice';
                
                // User-friendly IG/DM
                $isIG = $question->ig_weight > 0 ? 'Yes' : 'No';
                $isDM = $question->dm_weight > 0 ? 'Yes' : 'No';
                
                $row = [
                    $question->subCase->caseStudy->exam->name ?? '',
                    $question->subCase->caseStudy->exam->duration_minutes ?? '',
                    $question->subCase->caseStudy->title ?? '',
                    $question->subCase->caseStudy->order_no ?? '',
                    $question->subCase->title ?? '',
                    $question->subCase->order_no ?? '',
                    strip_tags($question->question_text),
                    $questionType,
                    $isIG,
                    $isDM,
                ];

                // Add up to 4 options
                for ($i = 0; $i < 4; $i++) {
                    $option = $options->get($i);
                    $row[] = $option ? strip_tags($option->option_text) : '';
                }
                
                // Add correct answers
                $row[] = implode(',', $correctAnswers);
                
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // IMPORT COMPLETE DATA
    public function importComplete(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Skip header
        fgetcsv($handle);
        
        $imported = 0;
        $errors = [];
        
        while (($data = fgetcsv($handle)) !== false) {
            try {
                if (count($data) < 10) continue;

                // Get or create Exam
                $exam = Exam::firstOrCreate(
                    ['name' => $data[0]],
                    [
                        'description' => 'Imported exam',
                        'duration_minutes' => $data[1] ?? 60,
                        'status' => 1,
                    ]
                );

                // Get or create Case Study
                $caseStudy = CaseStudy::firstOrCreate(
                    [
                        'exam_id' => $exam->id,
                        'title' => $data[2]
                    ],
                    [
                        'content' => '',
                        'order_no' => $data[3] ?? 1,
                        'status' => 1,
                    ]
                );

                // Get or create Sub Case Study
                $subCaseStudy = SubCaseStudy::firstOrCreate(
                    [
                        'case_study_id' => $caseStudy->id,
                        'title' => $data[4]
                    ],
                    [
                        'content' => '',
                        'order_no' => $data[5] ?? 1,
                        'status' => 1,
                    ]
                );

                // Get correct answers
                $correctAnswers = isset($data[14]) ? explode(',', $data[14]) : ['A'];

                // Convert user-friendly text to database values
                $questionType = strtolower(trim($data[7] ?? ''));
                if (strpos($questionType, 'multiple') !== false) {
                    $questionType = 'multiple';
                } else {
                    $questionType = 'single';
                }
                
                $igWeight = (strtolower(trim($data[8] ?? '')) == 'yes') ? 1 : 0;
                $dmWeight = (strtolower(trim($data[9] ?? '')) == 'yes') ? 1 : 0;

                // Create Question
                $question = Question::create([
                    'sub_case_id' => $subCaseStudy->id,
                    'question_text' => $data[6],
                    'question_type' => $questionType,
                    'ig_weight' => $igWeight,
                    'dm_weight' => $dmWeight,
                    'status' => 1,
                ]);

                // Create Options (A, B, C, D)
                $optionKeys = ['A', 'B', 'C', 'D'];
                for ($i = 0; $i < 4; $i++) {
                    if (!empty($data[10 + $i])) {
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_key' => $optionKeys[$i],
                            'option_text' => $data[10 + $i],
                            'is_correct' => in_array($optionKeys[$i], $correctAnswers) ? 1 : 0,
                        ]);
                    }
                }

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row error: " . $e->getMessage();
            }
        }
        fclose($handle);

        $message = "Successfully imported $imported questions!";
        if (!empty($errors)) {
            $message .= " With " . count($errors) . " errors.";
        }

        return redirect()->back()->with('success', $message);
    }

    // DOWNLOAD SAMPLE CSV FILE
    public function downloadSample()
    {
        $filename = 'sample_exam_import_template.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'Exam Name',
                'Exam Duration (mins)',
                'Case Study Title',
                'Case Study Order',
                'Sub Case Study Title',
                'Sub Case Study Order',
                'Question Text',
                'Question Type',
                'Internal Governance (IG)?',
                'Decision Making (DM)?',
                'Option A',
                'Option B',
                'Option C',
                'Option D',
                'Correct Answer(s)'
            ]);

            // Sample data row 1
            fputcsv($file, [
                'Laravel Fundamentals',
                '60',
                'Introduction to Laravel',
                '1',
                'Basic Concepts',
                '1',
                'What is Laravel?',
                'Single Choice',
                'Yes',
                'No',
                'A PHP framework',
                'A database',
                'A programming language',
                'An operating system',
                'A'
            ]);

            // Sample data row 2
            fputcsv($file, [
                'Laravel Fundamentals',
                '60',
                'Introduction to Laravel',
                '1',
                'Basic Concepts',
                '1',
                'Which of the following are Laravel features? (Multiple)',
                'Multiple Choice',
                'Yes',
                'No',
                'Eloquent ORM',
                'Blade Templating',
                'Built-in Authentication',
                'File Storage',
                'A,B,C'
            ]);

            // Sample data row 3
            fputcsv($file, [
                'Advanced PHP',
                '90',
                'Object Oriented Programming',
                '1',
                'Classes and Objects',
                '1',
                'What is encapsulation in OOP?',
                'Single Choice',
                'No',
                'Yes',
                'Hiding internal details',
                'Creating multiple objects',
                'Deleting objects',
                'None of the above',
                'A'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
