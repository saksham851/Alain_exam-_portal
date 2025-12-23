<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Exam;
use App\Models\Question;
use App\Models\ExamAttempt;
use App\Models\StudentExam;

class DashboardController extends Controller
{
    public function index()
    {
        // Get dashboard statistics
        $stats = [
            'total_students' => User::where('role', 'student')->count(),
            'active_exams' => Exam::where('status', 1)->count(),
            'total_questions' => Question::where('status', 1)->count(),
            'recent_attempts_count' => ExamAttempt::whereDate('created_at', today())->count(),
        ];

        // Get recent attempts with relationships
        $recentAttempts = ExamAttempt::with(['studentExam.student', 'studentExam.exam'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($attempt) {
                return (object)[
                    'id' => $attempt->id,
                    'student_id' => $attempt->studentExam->student->id,
                    'student_name' => $attempt->studentExam->student->first_name . ' ' . $attempt->studentExam->student->last_name,
                    'student_email' => $attempt->studentExam->student->email,
                    'exam_name' => $attempt->studentExam->exam->name,
                    'total_score' => $attempt->total_score,
                    'is_passed' => $attempt->is_passed,
                    'created_at' => $attempt->created_at,
                    'time_ago' => $attempt->created_at->diffForHumans(),
                ];
            });

        return view('dashboard.admin', compact('stats', 'recentAttempts'));
    }
}
