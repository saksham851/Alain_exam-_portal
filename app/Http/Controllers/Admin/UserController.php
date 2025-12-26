<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $examId = $request->get('exam_id');
        $categoryId = $request->get('category_id');
        $attemptsMin = $request->get('attempts_min');
        $attemptsMax = $request->get('attempts_max');

        // Base query - only show students, not admins
        $query = User::where('role', 'student')
            ->where('status', 1)
            ->with(['studentExams.exam.category', 'studentExams.attempts']);

        // Filter by exam
        if ($examId) {
            $query->whereHas('studentExams', function($q) use ($examId) {
                $q->where('exam_id', $examId);
            });
        }

        // Filter by exam category
        if ($categoryId) {
            $query->whereHas('studentExams.exam', function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        // Filter by number of attempts
        if ($attemptsMin !== null || $attemptsMax !== null) {
            $query->whereHas('studentExams', function($q) use ($attemptsMin, $attemptsMax) {
                $q->withCount('attempts');
                
                if ($attemptsMin !== null) {
                    $q->having('attempts_count', '>=', $attemptsMin);
                }
                if ($attemptsMax !== null) {
                    $q->having('attempts_count', '<=', $attemptsMax);
                }
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get all exams and categories for filter dropdowns
        $exams = \App\Models\Exam::where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        $categories = \App\Models\ExamCategory::where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return view('admin.users.index', compact('users', 'exams', 'categories'));
    }

    public function create()
    {
        $user = null;
        return view('admin.users.edit', compact('user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone' => 'nullable|string|max:20',
        ]);

        User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'student', // Always create as student
            'status' => 1,
            'is_blocked' => false,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Student created successfully!');
    }

    public function edit($id)
    {
        $user = User::where('role', 'student')->findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::where('role', 'student')->findOrFail($id);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'phone' => 'nullable|string|max:20',
        ]);

        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Student updated successfully!');
    }

    public function destroy($id)
    {
        $user = User::where('role', 'student')->findOrFail($id);
        $user->update(['status' => 0]); // Soft delete
        
        return back()->with('success', 'Student deleted successfully!');
    }
}
