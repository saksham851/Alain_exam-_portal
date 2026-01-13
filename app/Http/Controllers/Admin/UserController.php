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
        $search = $request->get('search');
        $examId = $request->get('exam_id');
        $categoryId = $request->get('category_id');
        $attempts = $request->get('attempts');

        // Base query - only show students, not admins
        $query = User::where('role', 'student')
            ->where('status', 1)
            ->with(['studentExams' => function($q) use ($examId, $categoryId) {
                if ($examId) {
                    $q->where('exam_id', $examId);
                }
                if ($categoryId) {
                    $q->whereHas('exam', function($q2) use ($categoryId) {
                        $q2->where('category_id', $categoryId);
                    });
                }
            }, 'studentExams.exam.category', 'studentExams.attempts'])
            ->withCount('examAttempts');

        // Search by name or email
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $search . '%']);
            });
        }

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

        // Filter by minimum number of attempts
        if ($attempts !== null && $attempts !== '') {
            $attemptCount = (int)$attempts;
            
            // Use a subquery to count attempts per user
            $query->whereIn('id', function($subQuery) use ($attemptCount) {
                $subQuery->select('users.id')
                    ->from('users')
                    ->join('student_exams', 'users.id', '=', 'student_exams.student_id')
                    ->join('exam_attempts', 'student_exams.id', '=', 'exam_attempts.student_exam_id')
                    ->groupBy('users.id')
                    ->havingRaw('COUNT(exam_attempts.id) >= ?', [$attemptCount]);
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        // Append query parameters to pagination links
        $users->appends($request->all());

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

    /**
     * Get student's assigned exams
     */
    public function getAssignedExams($studentId)
    {
        try {
            $assignedExams = \App\Models\StudentExam::where('student_id', $studentId)
                ->with('exam:id,name')
                ->get()
                ->map(function($studentExam) {
                    return [
                        'exam_id' => $studentExam->exam_id,
                        'exam_name' => $studentExam->exam->name ?? 'Unknown'
                    ];
                });

            return response()->json([
                'success' => true,
                'exams' => $assignedExams
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching assigned exams: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student's exam attempts information
     */
    public function getStudentExamAttempts($studentId, $examId)
    {
        try {
            $studentExam = \App\Models\StudentExam::where('student_id', $studentId)
                ->where('exam_id', $examId)
                ->first();

            if (!$studentExam) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exam not assigned to this student'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'attempts_allowed' => $studentExam->attempts_allowed,
                    'attempts_used' => $studentExam->attempts_used,
                    'attempts_remaining' => $studentExam->attempts_allowed - $studentExam->attempts_used,
                    'is_assigned' => true
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching attempts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manage (increase/decrease) student exam attempts
     */
    public function manageAttempts(Request $request)
    {
        try {
            $request->validate([
                'student_id' => 'required|exists:users,id',
                'exam_id' => 'required|exists:exams,id',
                'attempts_adjustment' => 'required|integer'
            ]);

            $studentId = $request->student_id;
            $examId = $request->exam_id;
            $adjustment = $request->attempts_adjustment;

            // Find or create student exam record
            $studentExam = \App\Models\StudentExam::firstOrCreate(
                [
                    'student_id' => $studentId,
                    'exam_id' => $examId
                ],
                [
                    'attempts_allowed' => 0,
                    'attempts_used' => 0,
                    'expiry_date' => now()->addWeeks(4),
                    'source' => 'Admin Manual',
                    'status' => 1
                ]
            );

            // Calculate new attempts
            $newAttempts = $studentExam->attempts_allowed + $adjustment;

            // Prevent negative attempts
            if ($newAttempts < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot reduce attempts below 0'
                ], 400);
            }

            // Update attempts
            $studentExam->attempts_allowed = $newAttempts;
            $studentExam->save();

            $student = User::find($studentId);
            $exam = \App\Models\Exam::find($examId);

            return response()->json([
                'success' => true,
                'message' => "Successfully updated attempts for {$student->first_name} {$student->last_name} - {$exam->name}. New total: {$newAttempts} attempts.",
                'data' => [
                    'attempts_allowed' => $studentExam->attempts_allowed,
                    'attempts_used' => $studentExam->attempts_used,
                    'attempts_remaining' => $studentExam->attempts_allowed - $studentExam->attempts_used
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating attempts: ' . $e->getMessage()
            ], 500);
        }
    }
}
