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
                    'exam_code' => $studentExam->exam->exam_code,
                    'duration' => $studentExam->exam->duration_minutes,
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
                
                return (object)[
                    'id' => $attempt->id,
                    'exam_title' => $attempt->studentExam->exam->name ?? 'Unknown Exam',
                    'date' => $attempt->ended_at,
                    'duration' => $attempt->formatted_duration,
                    'score' => round($attempt->total_score),
                    'status' => $attempt->is_passed ? 'Pass' : 'Fail',
                ];
            });
        
        // Calculate Dashboard Stats
        $totalEnrolled = $purchasedExams->count();
        
        // Get all unique exams passed
        $passedExamIds = ExamAttempt::whereIn('student_exam_id', $studentExamIds)
            ->where('is_passed', true)
            ->pluck('student_exam_id')
            ->unique()
            ->count();
            
        // Average Score across all attempts
        $averageScore = ExamAttempt::whereIn('student_exam_id', $studentExamIds)
            ->avg('total_score') ?? 0;
            
        // Success Rate (Passed Attempts / Total Attempts)
        $totalAttempts = ExamAttempt::whereIn('student_exam_id', $studentExamIds)->count();
        $totalPassedAttempts = ExamAttempt::whereIn('student_exam_id', $studentExamIds)->where('is_passed', true)->count();
        $successRate = $totalAttempts > 0 ? ($totalPassedAttempts / $totalAttempts) * 100 : 0;

        $stats = [
            'enrolled' => $totalEnrolled,
            'passed_exams' => $passedExamIds,
            'average_score' => round($averageScore, 1),
            'success_rate' => round($successRate, 1)
        ];
        
        return view('dashboard.student', compact('purchasedExams', 'attempts', 'stats'));
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
