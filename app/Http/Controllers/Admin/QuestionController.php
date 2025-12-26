<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Exam;
use App\Models\Section;
use App\Models\CaseStudy;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $query = Question::where('status', 1)
            ->with(['caseStudy.section.exam', 'options']);

        // Filter by Exam (Primary Filter)
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

        // Filter by Question Type
        if ($request->filled('question_type')) {
            $query->where('question_type', $request->question_type);
        }

        $questions = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        
        // Get all exams for filter dropdown
        $exams = Exam::where('status', 1)
            ->orderBy('name')
            ->get();
        
        // Get case studies based on selected exam (if any)
        $caseStudiesQuery = CaseStudy::where('status', 1)
            ->with('section.exam');
        
        if ($request->filled('exam')) {
            $caseStudiesQuery->whereHas('section', function($q) use ($request) {
                $q->where('exam_id', $request->exam);
            });
        }
        
        $caseStudies = $caseStudiesQuery->orderBy('title')->get();
        
        return view('admin.questions.index', compact('questions', 'caseStudies', 'exams'));
    }

    public function create()
    {
        $question = null;
        $exams = Exam::where('status', 1)->get();
        
        return view('admin.questions.edit', compact('question', 'exams'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sub_case_id' => 'required|exists:case_studies,id',
            'question_text' => 'required|string',
            'question_type' => 'required|in:single,multiple',
            'question_category' => 'required|in:ig,dm',
            'options' => 'required|array|min:2',
            'options.*.text' => 'required|string',
        ]);

        // Set weights based on category
        $igWeight = $request->question_category === 'ig' ? 1 : 0;
        $dmWeight = $request->question_category === 'dm' ? 1 : 0;

        // Create question
        // Note: The form still sends 'sub_case_id' because we didn't update the View form inputs yet
        // But we are mapping it to 'case_study_id' for the DB.
        $question = Question::create([
            'case_study_id' => $request->sub_case_id,
            'question_text' => $request->question_text,
            'question_type' => $request->question_type,
            'ig_weight' => $igWeight,
            'dm_weight' => $dmWeight,
            'status' => 1,
        ]);

        // Create options
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
            ->with('success', 'Question created successfully!');
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
        $question = Question::find($id);
        
        if ($question) {
            $question->update(['status' => 0]);
        }
        
        return back()->with('success', 'Question deleted successfully!');
    }

    // AJAX endpoints
    // Renamed caseStudies -> sections
    public function getCaseStudies($examId)
    {
        // This actually fetches Sections for the Exam
        // The endpoint name in route is still 'questions.getCaseStudies' for now.
        $sections = Section::where('exam_id', $examId)
            ->where('status', 1)
            ->get(['id', 'title']);
        
        return response()->json($sections);
    }

    public function getSubCaseStudies($sectionId)
    {
        // This fetches CaseStudies for the Section
        $caseStudies = CaseStudy::where('section_id', $sectionId)
            ->where('status', 1)
            ->get(['id', 'title']);
        
        return response()->json($caseStudies);
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
