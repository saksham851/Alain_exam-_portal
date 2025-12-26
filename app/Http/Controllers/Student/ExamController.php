<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentExam;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    /**
     * Display list of exams assigned to student
     */
    public function index()
    {
        $studentId = auth()->id();
        
        // Get exams assigned to the logged-in student
        $studentExams = StudentExam::where('student_id', $studentId)
            ->with('exam')
            ->get();
        
        // Extract the exam objects with student-specific data
        $exams = $studentExams->map(function($studentExam) {
            $attemptsLeft = $studentExam->attempts_allowed - $studentExam->attempts_used;
            $exam = $studentExam->exam;
            $exam->attempts_left = $attemptsLeft;
            $exam->max_attempts = $studentExam->attempts_allowed;
            $exam->expiry_date = $studentExam->expiry_date;
            $exam->student_exam_id = $studentExam->id;
            return $exam;
        });
        
        return view('exams.index', compact('exams'));
    }
    
    /**
     * Show exam details
     */
    public function show($id)
    {
        $studentId = auth()->id();
        
        // Get exam details for student
        $studentExam = StudentExam::where('student_id', $studentId)
            ->where('exam_id', $id)
            ->with(['exam.sections.caseStudies.questions', 'attempts' => function($query) {
                $query->where('status', 'submitted')->orderBy('created_at', 'desc');
            }])
            ->firstOrFail();
            
        $attemptsLeft = $studentExam->attempts_allowed - $studentExam->attempts_used;
        $exam = $studentExam->exam;
        $exam->attempts_left = $attemptsLeft;
        $exam->max_attempts = $studentExam->attempts_allowed;
        $exam->expiry_date = $studentExam->expiry_date;
        $exam->student_exam_id = $studentExam->id;
        
        // Get all completed attempts
        $attempts = $studentExam->attempts;
        
        return view('exams.show', compact('exam', 'attempts'));
    }
    
    /**
     * Start exam - Create attempt record
     */
    public function start($id)
    {
        $studentId = auth()->id();
        $studentExam = StudentExam::where('student_id', $studentId)
            ->where('exam_id', $id)
            ->firstOrFail();
            
        $attemptsLeft = $studentExam->attempts_allowed - $studentExam->attempts_used;
        
        // Validate can start
        if ($attemptsLeft <= 0) {
            return redirect()->route('exams.show', $id)
                ->with('error', 'No attempts remaining');
        }
        
        if (now() > $studentExam->expiry_date) {
            return redirect()->route('exams.show', $id)
                ->with('error', 'Exam has expired');
        }
        
        // Create attempt
        $attempt = \App\Models\ExamAttempt::create([
            'student_exam_id' => $studentExam->id,
            'started_at' => now(),
            'status' => 'in_progress',
            'time_remaining' => $studentExam->exam->duration_minutes * 60, // in seconds
        ]);
        
        // Deduct attempt immediately
        $studentExam->increment('attempts_used');
        
        return redirect()->route('exams.take', $id);
    }
    
    /**
     * Take exam - Display questions
     */
    public function take($id)
    {
        $studentId = auth()->id();
        $studentExam = StudentExam::where('student_id', $studentId)
            ->where('exam_id', $id)
            ->with('exam.sections.caseStudies.questions.options')
            ->firstOrFail();
            
        // Get active attempt
        $attempt = \App\Models\ExamAttempt::where('student_exam_id', $studentExam->id)
            ->where('status', 'in_progress')
            ->latest()
            ->first();
            
        if (!$attempt) {
            // Check if they have a completed attempt recently? 
            // Or maybe they refreshed and the session was lost?
            // Since we deducted the attempt, we should find the one we just made.
            
            // If strictly "in_progress", user might be locked out if status changed.
            // But standard flow: status is in_progress until submit.
            return redirect()->route('exams.show', $id)
                ->with('error', 'No active exam session found.');
        }
        
        $exam = $studentExam->exam;
        
        return view('exams.take', compact('exam', 'attempt'));
    }
    
    /**
     * Submit exam answers
     */
    public function submit(Request $request, $id)
    {
        $studentId = auth()->id();
        $studentExam = StudentExam::where('student_id', $studentId)
            ->where('exam_id', $id)
            ->firstOrFail();
            
        $attempt = \App\Models\ExamAttempt::where('student_exam_id', $studentExam->id)
            ->where('status', 'in_progress')
            ->latest()
            ->firstOrFail();
            
        // Use DB Transaction to handle high concurrency (100-200 users)
        \Illuminate\Support\Facades\DB::transaction(function () use ($attempt, $request) {
            // Save answers
            $answers = $request->input('answers', []);
            $answerData = [];
            foreach ($answers as $questionId => $answer) {
                $answerData[] = [
                    'attempt_id' => $attempt->id,
                    'question_id' => $questionId,
                    'selected_options' => is_array($answer) ? json_encode($answer) : json_encode([$answer]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Bulk insert for performance
            if (!empty($answerData)) {
                \App\Models\AttemptAnswer::insert($answerData);
            }
            
            // Calculate score using scoring service
            $scoringService = new \App\Services\ExamScoringService();
            $result = $scoringService->calculateScore($attempt->id);
            
            // Update attempt status
            $attempt->update([
                'ended_at' => now(),
                'status' => 'submitted',
                'ig_score' => $result['ig_score'],
                'dm_score' => $result['dm_score'],
                'total_score' => $result['total_score'],
                'is_passed' => $result['is_passed'],
            ]);
        });
        
        // Send Webhook Notification
        try {
            $user = auth()->user();
            $payload = [
                "name" => $user->first_name . ' ' . $user->last_name,
                "email" => $user->email,
                "phone" => $user->phone ?? "",
                "ig_score" => $attempt->ig_score,
                "dm_score" => $attempt->dm_score,
                "total_score" => $attempt->total_score,
                "attempts" => $studentExam->attempts_used,
                "status" => "completed",
                "exam_name" => $studentExam->exam->name,
            ];

            // Use Http facade to send the webhook
            \Illuminate\Support\Facades\Http::post('https://webhook.site/4f8b5dd3-8d7d-4526-8f62-9edd16b21ead', $payload);
        } catch (\Exception $e) {
            // Log error silently so it doesn't crash the user experience
            \Illuminate\Support\Facades\Log::error('Exam Completion Webhook Failed: ' . $e->getMessage());
        }

        // Email notification removed as per request

        // Note: attempts_used is now incremented in start(), so we don't do it here.
        
        return redirect()->route('exams.result', $attempt->id);
    }
    
    /**
     * Show exam result
     */
    public function result($attemptId)
    {
        $attempt = \App\Models\ExamAttempt::with('studentExam.exam')
            ->findOrFail($attemptId);
            
        // Verify ownership
        if ($attempt->studentExam->student_id !== auth()->id()) {
            abort(403);
        }
        
        return view('exams.result', compact('attempt'));
    }
    
    /**
     * Download exam result PDF
     */
    public function download($attemptId)
    {
        $attempt = \App\Models\ExamAttempt::with(['studentExam.student', 'studentExam.exam.sections.caseStudies.questions', 'answers'])
            ->findOrFail($attemptId);
            
        // Verify ownership
        if ($attempt->studentExam->student_id !== auth()->id()) {
            abort(403);
        }
        
        // Get all questions from the exam
        $allQuestions = [];
        foreach ($attempt->studentExam->exam->sections as $section) {
            foreach ($section->caseStudies as $caseStudy) {
                foreach ($caseStudy->questions as $question) {
                    $allQuestions[] = $question->id;
                }
            }
        }

        // Prepare questions data with attempted status
        $questionsData = [];
        $answeredQuestionIds = $attempt->answers->pluck('question_id')->toArray();
        
        foreach ($allQuestions as $questionId) {
            $answer = $attempt->answers->firstWhere('question_id', $questionId);
            
            $questionsData[] = [
                'is_attempted' => in_array($questionId, $answeredQuestionIds),
                'is_correct' => $answer ? ($answer->is_correct ?? false) : false,
            ];
        }
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.exam-result', compact('attempt', 'questionsData'));
        
        return $pdf->download('exam-result-' . $attempt->id . '.pdf');
    }
    
    /**
     * Download answer key PDF for an exam
     */
    public function downloadAnswerKey($id)
    {
        $studentId = auth()->id();
        
        // Get exam details for student
        $studentExam = StudentExam::where('student_id', $studentId)
            ->where('exam_id', $id)
            ->with(['exam.sections.caseStudies.questions.options'])
            ->firstOrFail();
            
        $exam = $studentExam->exam;
        
        // Collect all questions with their correct answers
        $answerKey = [];
        $questionNumber = 1;
        
        foreach ($exam->sections as $section) {
            foreach ($section->caseStudies as $caseStudy) {
                foreach ($caseStudy->questions as $question) {
                    // Get correct options - use option_key (A, B, C, D) instead of option_text
                    $correctOptions = $question->options->where('is_correct', true)->pluck('option_key')->toArray();
                    
                    $answerKey[] = [
                        'number' => $questionNumber,
                        'question_text' => $question->question_text,
                        'correct_answers' => $correctOptions,
                        'category' => $question->category,
                        'marks' => $question->marks,
                    ];
                    
                    $questionNumber++;
                }
            }
        }
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.answer-key', compact('exam', 'answerKey'));
        
        return $pdf->download('answer-key-' . $exam->name . '.pdf');
    }
    
}