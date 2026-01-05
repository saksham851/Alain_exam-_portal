<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Models\AttemptAnswer;
use App\Models\User;
use Illuminate\Http\Request;

class AttemptController extends Controller
{
    public function index(Request $request)
    {
        // Build query for attempts
        $query = ExamAttempt::with(['studentExam.student', 'studentExam.exam']);
        
        // Filter by time period if 'period' parameter is present
        $period = $request->get('period');
        if ($period) {
            switch ($period) {
                case '24h':
                    $query->where('created_at', '>=', now()->subHours(24));
                    break;
                case '1week':
                    $query->where('created_at', '>=', now()->subWeek());
                    break;
                case '1month':
                    $query->where('created_at', '>=', now()->subMonth());
                    break;
                case '1year':
                    $query->where('created_at', '>=', now()->subYear());
                    break;
            }
        }
        
        // Filter by student name
        $studentSearch = $request->get('student_search');
        if ($studentSearch) {
            $query->whereHas('studentExam.student', function($q) use ($studentSearch) {
                $q->where(function($subQ) use ($studentSearch) {
                    $subQ->where('first_name', 'like', '%' . $studentSearch . '%')
                         ->orWhere('last_name', 'like', '%' . $studentSearch . '%')
                         ->orWhere('email', 'like', '%' . $studentSearch . '%')
                         ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $studentSearch . '%']);
                });
            });
        }
        
        // Filter by exam name
        $examSearch = $request->get('exam_search');
        if ($examSearch) {
            $query->whereHas('studentExam.exam', function($q) use ($examSearch) {
                $q->where('name', 'like', '%' . $examSearch . '%');
            });
        }
        
        // Get all attempts with student and exam information
        $attempts = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        // Transform for the view
        $attempts->getCollection()->transform(function($attempt) {
            $attempt->student_name = $attempt->studentExam->student->first_name . ' ' . $attempt->studentExam->student->last_name;
            $attempt->student_email = $attempt->studentExam->student->email;
            $attempt->exam_name = $attempt->studentExam->exam->name;
            $attempt->percentage = $attempt->total_score; // Assuming total_score is already a percentage
            return $attempt;
        });
        
        // Pass filter values to the view
        $selectedPeriod = $period;
        
        // Get all exams for the dropdown
        $exams = \App\Models\Exam::where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);
            
        // Get all students for the dropdown
        $students = \App\Models\User::where('role', 'student')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        return view('admin.attempts.index', compact('attempts', 'selectedPeriod', 'studentSearch', 'examSearch', 'exams', 'students'));
    }

    public function show($id)
    {
        // Get attempt with all relationships
        $attempt = ExamAttempt::with([
            'studentExam.student',
            'studentExam.exam',
            'answers.question.options'
        ])->findOrFail($id);

        // Transform data for view
        $attemptData = (object)[
            'id' => $attempt->id,
            'student' => (object)[
                'name' => $attempt->studentExam->student->first_name . ' ' . $attempt->studentExam->student->last_name,
                'email' => $attempt->studentExam->student->email,
            ],
            'exam' => (object)[
                'name' => $attempt->studentExam->exam->name,
            ],
            'ig_score' => $attempt->ig_score,
            'dm_score' => $attempt->dm_score,
            'total_score' => $attempt->total_score,
            'is_passed' => $attempt->is_passed,
            'started_at' => $attempt->started_at,
            'ended_at' => $attempt->ended_at,
            'created_at' => $attempt->created_at,
            'tab_switch_count' => $attempt->tab_switch_count,
        ];

        // Get all answers
        $answers = $attempt->answers->map(function($answer) {
            return (object)[
                'question_id' => $answer->question_id,
                'question_text' => $answer->question->question_text,
                'selected_options' => $answer->selected_options, 
                'is_correct' => $answer->is_correct,
                'options' => $answer->question->options,
            ];
        });

        return view('admin.attempts.show', compact('attemptData', 'answers'));
    }

    public function byUser($userId)
    {
        // Get all attempts for a specific student
        $student = User::where('role', 'student')->findOrFail($userId);
        
        $attempts = ExamAttempt::whereHas('studentExam', function($query) use ($userId) {
                $query->where('student_id', $userId);
            })
            ->with(['studentExam.exam'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $attempts->getCollection()->transform(function($attempt) {
            $attempt->exam_name = $attempt->studentExam->exam->name;
            $attempt->percentage = $attempt->total_score;
            return $attempt;
        });

        return view('admin.attempts.by-user', compact('attempts', 'student'));
    }
}
