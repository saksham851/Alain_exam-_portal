<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentExam;
use App\Models\ExamAttempt;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $studentId = auth()->id();
        
        // Get purchased exams (assigned exams from StudentExam)
        $purchasedExams = StudentExam::where('student_id', $studentId)
            ->with('exam')
            ->get()
            ->map(function($studentExam) {
                $isExpired = now() > $studentExam->expiry_date;
                $attemptsLeft = $studentExam->attempts_allowed - $studentExam->attempts_used;
                
                return (object)[
                    'id' => $studentExam->exam->id,
                    'title' => $studentExam->exam->name,
                    'duration' => $studentExam->exam->duration,
                    'status' => $isExpired ? 'expired' : 'active',
                    'expiry_date' => $studentExam->expiry_date,
                    'attempts_left' => $attemptsLeft,
                    'max_attempts' => $studentExam->attempts_allowed,
                    'attempts_taken' => $studentExam->attempts_used,
                    'can_attempt' => !$isExpired && $attemptsLeft > 0,
                ];
            });
        
        // Get recent exam attempts
        // Get student's exam IDs first
        $studentExamIds = StudentExam::where('student_id', $studentId)
            ->pluck('id');
        
        $attempts = ExamAttempt::whereIn('student_exam_id', $studentExamIds)
            ->with('studentExam.exam')
            ->orderBy('ended_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($attempt) {
                $passingScore = 65; // 65%
                $passed = $attempt->total_score >= $passingScore;
                
                return (object)[
                    'id' => $attempt->id, // Added ID
                    'exam_title' => $attempt->studentExam->exam->name ?? 'Unknown Exam',
                    'date' => $attempt->ended_at,
                    'score' => round($attempt->total_score),
                    'status' => $attempt->is_passed ? 'Pass' : 'Fail', // Use is_passed from DB directly
                ];
            });
        
        return view('dashboard.student', compact('purchasedExams', 'attempts'));
    }

    public function history()
    {
        $studentId = auth()->id();
        $studentExamIds = StudentExam::where('student_id', $studentId)->pluck('id');
        
        $attempts = ExamAttempt::whereIn('student_exam_id', $studentExamIds)
            ->with('studentExam.exam')
            ->orderBy('ended_at', 'desc')
            ->paginate(10); // Pagination
            
        // No mapping here, passing paginated object to view
        
        return view('dashboard.history', compact('attempts'));
    }
}
