<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Exam;
use App\Models\ExamAttempt;

class DashboardController extends Controller
{
    public function index()
    {
        // Get statistics for manager dashboard
        $totalStudents = User::where('role', 'student')
            ->where('status', 1)
            ->count();

        $totalExams = Exam::where('status', 1)
            ->where('is_active', 1)
            ->count();

        $totalAttempts = ExamAttempt::whereHas('studentExam.student', function($q) {
            $q->where('status', 1);
        })->count();

        $recentAttempts = ExamAttempt::with(['studentExam.student', 'studentExam.exam'])
            ->whereHas('studentExam.student', function($q) {
                $q->where('status', 1);
            })
            ->whereNotNull('ended_at')
            ->orderBy('ended_at', 'desc')
            ->limit(10)
            ->get();

        return view('manager.dashboard', compact(
            'totalStudents',
            'totalExams',
            'totalAttempts',
            'recentAttempts'
        ));
    }
}
