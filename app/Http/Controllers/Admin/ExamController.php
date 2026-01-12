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

        // Base query
        $query = Exam::where('status', 1)
            ->with('category'); // Eager load category

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

        // Filter by certification type (through category relationship)
        if ($certificationType) {
            $query->whereHas('category', function($q) use ($certificationType) {
                $q->where('certification_type', $certificationType);
            });
        }

        // Filter by exact duration
        if ($duration !== null && $duration !== '') {
            $query->where('duration_minutes', $duration);
        }

        // Filter by Active Status
        // Filter by Active Status
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
        $certificationTypes = \App\Models\ExamCategory::where('status', 1)
            ->distinct()
            ->orderBy('certification_type')
            ->pluck('certification_type');

        return view('admin.exams.index', compact('exams', 'categories', 'certificationTypes'));
    }

    // CREATE FORM
    public function create()
    {
        $exam = null;
        $categories = \App\Models\ExamCategory::where('status', 1)->orderBy('name')->get();
        return view('admin.exams.edit', compact('exam', 'categories'));
    }

    // SAVE NEW
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'exam_code' => 'required|string|max:50|unique:exams,exam_code',
            'category_id' => 'required|exists:exam_categories,id',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer',
        ]);

        $exam = Exam::create([
            'name' => $request->name,
            'exam_code' => $request->exam_code,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes,
            'status' => 1,
            'is_active' => 0, // New exams start as inactive
        ]);

        return redirect()->route('admin.case-studies.index', [
            'open_modal' => 'create',
            'exam_id' => $exam->id
        ])->with('success', 'Exam Created Successfully!');
    }

    // EDIT FORM
    public function edit($id)
    {
        $exam = Exam::find($id);

        if (!$exam) return redirect()->back()->with('error', 'Exam Not Found');

        $categories = \App\Models\ExamCategory::where('status', 1)->orderBy('name')->get();
        return view('admin.exams.edit', compact('exam', 'categories'));
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

        $request->validate([
            'name' => 'required|string|max:255',
            'exam_code' => 'required|string|max:50|unique:exams,exam_code,' . $id,
            'category_id' => 'required|exists:exam_categories,id',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer',
        ]);

        $exam->update([
            'name' => $request->name,
            'exam_code' => $request->exam_code,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes,
        ]);

        return redirect()->route('admin.exams.index')
            ->with('success', 'Exam Updated Successfully!');
    }

    // DELETE = UPDATE STATUS
    public function destroy($id)
    {
        $exam = Exam::find($id);
        
        if (!$exam) {
            return redirect()->back()->with('error', 'Exam Not Found');
        }

        // Check if exam is locked
        if ($exam->is_active == 1) {
            return redirect()->back()->with('error', 'This exam is locked and cannot be deleted.');
        }

        $exam->update(['status' => 0]); // soft delete logic

        return redirect()->route('admin.exams.index')
            ->with('success', 'Exam Deleted Successfully!');
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
    public function toggleStatus($id)
    {
        $exam = Exam::findOrFail($id);
        // Toggle the is_active status (assuming is_active is boolean or 0/1)
        $exam->is_active = !$exam->is_active;
        $exam->save();

        $message = $exam->is_active ? 'Exam Activated Successfully!' : 'Exam Deactivated Successfully!';
        return redirect()->back()->with('success', $message);
    }
}
