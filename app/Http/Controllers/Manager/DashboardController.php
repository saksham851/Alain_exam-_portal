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

        $totalExams = Exam::where('status', 1)->count();

        $recentExams = Exam::where('status', 1)
            ->with(['category'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('manager.dashboard', compact(
            'totalStudents',
            'totalExams',
            'recentExams'
        ));
    }
}
