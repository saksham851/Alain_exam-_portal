<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Section;
use App\Models\CaseStudy;
use App\Models\Exam;

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
        $query = Section::where('status', 1)
            ->with(['exam.category', 'caseStudies.questions']);

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

        // Filter by certification type (through exam->category relationship)
        if ($certificationType) {
            $query->whereHas('exam.category', function($q) use ($certificationType) {
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
        $certificationTypes = \App\Models\ExamCategory::where('status', 1)
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

    public function create()
    {
        $section = null;
        $exams = Exam::where('status', 1)->get();
        return view('admin.case_studies.edit', ['caseStudy' => $section, 'exams' => $exams]);
    }

    public function store(Request $request)
    {
        // Check if exam is active
        $exam = Exam::find($request->exam_id);
        if ($exam && $exam->is_active == 1) {
            return redirect()->back()->with('error', 'Cannot add section to an active exam. Please deactivate the exam first.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'exam_id' => 'required|exists:exams,id',
            'content' => 'nullable|string',
            'sub_cases' => 'nullable|array',
            'sub_cases.*.title' => 'required|string|max:255',
            'sub_cases.*.content' => 'nullable|string',
        ]);

        // Create main section
        $section = Section::create([
            'exam_id' => $request->exam_id,
            'title' => $request->title,
            'content' => $request->content,
            'order_no' => Section::where('exam_id', $request->exam_id)->max('order_no') + 1,
            'status' => 1,
        ]);

        // Create case studies if provided
        if ($request->has('sub_cases')) {
            foreach ($request->sub_cases as $index => $caseData) {
                CaseStudy::create([
                    'section_id' => $section->id,
                    'title' => $caseData['title'],
                    'content' => $caseData['content'] ?? null,
                    'order_no' => $index + 1,
                    'status' => 1,
                ]);
            }
        }

        return redirect()->route('admin.case-studies.index')->with('success', 'Section created successfully.');
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

        $request->validate([
            'title' => 'required|string|max:255',
            'exam_id' => 'required|exists:exams,id',
            'content' => 'nullable|string',
            'sub_cases' => 'nullable|array',
            'sub_cases.*.title' => 'required|string|max:255',
            'sub_cases.*.content' => 'nullable|string',
        ]);

        $section = Section::find($id);
        if (!$section || $section->status == 0) return back()->with('error', 'Section not found');

        // Update main section
        $section->update([
            'exam_id' => $request->exam_id,
            'title' => $request->title,
            'content' => $request->content,
        ]);

        // Create, Update, or Delete Case Studies
        $submittedIds = [];
        
        if ($request->has('sub_cases')) {
            foreach ($request->sub_cases as $index => $caseData) {
                // Determine if we are updating or creating
                if (isset($caseData['id']) && $caseData['id']) {
                    // Update existing
                    $subCase = CaseStudy::find($caseData['id']);
                    if ($subCase && $subCase->section_id == $section->id) {
                        $subCase->update([
                            'title' => $caseData['title'],
                            'content' => $caseData['content'] ?? null,
                            'order_no' => $index + 1,
                        ]);
                        $submittedIds[] = $subCase->id;
                    }
                } else {
                    // Create new
                    $newSubCase = CaseStudy::create([
                        'section_id' => $section->id,
                        'title' => $caseData['title'],
                        'content' => $caseData['content'] ?? null,
                        'order_no' => $index + 1,
                        'status' => 1,
                    ]);
                    $submittedIds[] = $newSubCase->id;
                }
            }
        }
        
        // Delete any Case Studies that were NOT submitted (removed from UI)
        CaseStudy::where('section_id', $section->id)
            ->whereNotIn('id', $submittedIds)
            ->delete();

        return redirect()->route('admin.case-studies.index')->with('success', 'Section updated successfully.');
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

        return redirect()->route('admin.case-studies.index')
            ->with('success', "Successfully imported $imported sections!");
    }
}
