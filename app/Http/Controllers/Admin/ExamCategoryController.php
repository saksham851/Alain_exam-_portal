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
        $search = $request->get('search');
        $certificationType = $request->get('certification_type');
        $examCount = $request->get('exam_count');

        // Base query
        $query = ExamCategory::where('status', 1)
            ->withCount('exams');

        // Search by category name
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Filter by certification type
        if ($certificationType) {
            $query->where('certification_type', $certificationType);
        }

        // Filter by exact exam count
        if ($examCount !== null && $examCount !== '') {
            $query->has('exams', '=', $examCount);
        }

        $categories = $query->orderBy('name')->paginate(15);

        // Append query parameters to pagination links
        $categories->appends($request->all());

        // Get all unique certification types for filter dropdown
        $certificationTypes = ExamCategory::where('status', 1)
            ->distinct()
            ->orderBy('certification_type')
            ->pluck('certification_type');
        
        return view('admin.exam-categories.index', compact('categories', 'certificationTypes'));
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
        $request->validate([
            'name' => 'required|string|max:255|unique:exam_categories,name',
            'certification_type' => 'required|string|max:255',
        ]);

        ExamCategory::create([
            'name' => $request->name,
            'certification_type' => $request->certification_type,
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
        $request->validate([
            'name' => 'required|string|max:255|unique:exam_categories,name,' . $id,
            'certification_type' => 'required|string|max:255',
        ]);

        $category = ExamCategory::find($id);
        
        if (!$category) {
            return redirect()->back()->with('error', 'Category Not Found');
        }

        $category->update([
            'name' => $request->name,
            'certification_type' => $request->certification_type,
        ]);

        return redirect()->route('admin.exam-categories.index')
            ->with('success', 'Exam Category Updated Successfully!');
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
