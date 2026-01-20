<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExamCategory;

class ExamCategoryController extends Controller
{
    // List all categories
    public function index(Request $request)
    {
        // Get filter parameters
        // Get filter parameters
        $search = $request->get('search');
        $examCount = $request->get('exam_count');
        $status = $request->get('status', 'active');

        // Base query
        $query = ExamCategory::withCount('exams');

        // Filter by status
        if ($status === 'inactive') {
            $query->where('status', 0);
        } else {
            $query->where('status', 1);
        }

        // Search by category name
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Filter by exact exam count
        if ($examCount !== null && $examCount !== '') {
            $query->has('exams', '=', $examCount);
        }

        $categories = $query->orderBy('created_at', 'desc')->paginate(15);

        // Append query parameters to pagination links
        $categories->appends($request->all());

        return view('admin.exam-categories.index', compact('categories'));
    }

    // Create form
    public function create()
    {
        $category = null;
        return view('admin.exam-categories.edit', compact('category'));
    }

    // Store new category
    public function store(Request $request)
    {
        // Sanitize input: remove extra spaces
        if ($request->has('name')) {
            $request->merge([
                'name' => trim(preg_replace('/\s+/', ' ', $request->name))
            ]);
        }
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:exam_categories,name',
                'regex:/^[a-zA-Z0-9\s]+$/',
            ],
        ], [
            'name.regex' => 'The category name must only contain letters, numbers, and spaces.',
        ]);

        ExamCategory::create([
            'name' => $request->name,
            'status' => 1,
        ]);

        return redirect()->route('admin.exam-categories.index')
            ->with('success', 'Exam Category Created Successfully!');
    }

    // Edit form
    public function edit($id)
    {
        $category = ExamCategory::find($id);

        if (!$category) {
            return redirect()->back()->with('error', 'Category Not Found');
        }

        return view('admin.exam-categories.edit', compact('category'));
    }

    // Update category
    public function update(Request $request, $id)
    {
        // Sanitize input: remove extra spaces
        if ($request->has('name')) {
            $request->merge([
                'name' => trim(preg_replace('/\s+/', ' ', $request->name))
            ]);
        }
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:exam_categories,name,' . $id,
                'regex:/^[a-zA-Z0-9\s]+$/',
            ],
        ], [
            'name.regex' => 'The category name must only contain letters, numbers, and spaces.',
        ]);

        $category = ExamCategory::find($id);
        
        if (!$category) {
            return redirect()->back()->with('error', 'Category Not Found');
        }

        $category->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.exam-categories.index')
            ->with('success', 'Exam Category Updated Successfully!');
    }

    // Activate category (restore)
    public function activate($id)
    {
        $category = ExamCategory::find($id);
        
        if ($category) {
            $category->update(['status' => 1]);
        }

        return redirect()->route('admin.exam-categories.index', ['status' => 'inactive'])
            ->with('success', 'Exam Category Activated Successfully!');
    }

    // Delete category (soft delete)
    public function destroy($id)
    {
        $category = ExamCategory::find($id);
        
        if ($category) {
            $category->update(['status' => 0]);
        }

        return redirect()->route('admin.exam-categories.index')
            ->with('success', 'Exam Category Deleted Successfully!');
    }
}
