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
        $query = Section::with(['exam.category', 'caseStudies.questions', 'clonedFrom.exam']);

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

        $sections = $query->orderBy('created_at', 'desc')->paginate(15);

        // Append query parameters to pagination links
        $sections->appends($request->all());

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
        $exams = Exam::where('status', 1)->get();
        
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
                return $query->where('exam_id', $request->exam_id);
            }),
        ],
        'exam_id' => 'required|exists:exams,id',
        'content' => 'nullable|string',
    ], [
        'title.unique' => 'A section with this name already exists in the selected exam.',
    ]);

    // Create main section
    $section = Section::create([
        'exam_id' => $request->exam_id,
        'title' => $request->title,
        'content' => $request->content,
        'order_no' => Section::where('exam_id', $request->exam_id)->max('order_no') + 1,
        'status' => 1,
    ]);

    return redirect()->route('admin.sections.index')
        ->with('section_created_success', true)
        ->with('created_exam_id', $request->exam_id)
        ->with('created_section_id', $section->id);
}

    public function edit($id)
    {
        $section = Section::with('caseStudies')->find($id);
        if (!$section || $section->status == 0) return back()->with('error', 'Section not found');

        $exams = Exam::where('status', 1)->get();
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
                return $query->where('exam_id', $request->exam_id);
            })->ignore($id),
        ],
        'exam_id' => 'required|exists:exams,id',
        'content' => 'nullable|string',
    ], [
        'title.unique' => 'A section with this name already exists in the selected exam.',
    ]);

    $section = Section::find($id);
    if (!$section || $section->status == 0) return back()->with('error', 'Section not found');

    // Update main section
    $section->update([
        'exam_id' => $request->exam_id,
        'title' => $request->title,
        'content' => $request->content,
    ]);

    return redirect()->route('admin.sections.index')->with('success', 'Section updated successfully.');
}

    public function show($id)
    {
        $section = Section::with(['exam.category', 'caseStudies.questions.options'])->find($id);

        if (!$section) {
            return redirect()->route('admin.sections.index')->with('error', 'Section not found');
        }

        return view('admin.sections.show', compact('section'));
    }

    public function destroy($id)
    {
        $section = Section::with('exam')->find($id);
        if (!$section) {
            return back()->with('error', 'Section not found');
        }

        // Check if exam is active
        if ($section->exam && $section->exam->is_active == 1) {
            return back()->with('error', 'Cannot delete section from an active exam. Please deactivate the exam first.');
        }

        $section->update(['status' => 0]); 
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
        $sections = Section::where('exam_id', $examId)
            ->where('status', 1)
            ->orderBy('title')
            ->get(['id', 'title']);
        
        return response()->json($sections);
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

        DB::beginTransaction();
        try {
            $clonedCount = 0;
            $lastCreatedSectionId = null;
            
            foreach ($request->source_section_ids as $sectionId) {
                $sourceSection = Section::with(['caseStudies.questions.options'])->find($sectionId);
                
                if (!$sourceSection) continue;

                // Clone Section
                $newSection = Section::create([
                    'exam_id' => $targetExam->id,
                    'title' => $sourceSection->title,
                    'content' => $sourceSection->content,
                    'order_no' => Section::where('exam_id', $targetExam->id)->max('order_no') + 1,
                    'status' => 1,
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

                    // Clone Questions
                    foreach ($caseStudy->questions as $question) {
                        $newQuestion = \App\Models\Question::create([
                            'case_study_id' => $newCaseStudy->id,
                            'question_text' => $question->question_text,
                            'question_type' => $question->question_type,
                            'ig_weight' => $question->ig_weight,
                            'dm_weight' => $question->dm_weight,
                            'status' => $question->status,
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
