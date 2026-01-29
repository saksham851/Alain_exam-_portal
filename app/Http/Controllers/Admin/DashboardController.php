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
    public function index(\Illuminate\Http\Request $request)
    {
        // Get filter parameter for category
        $selectedCategoryId = $request->get('exam_category_id');

        // Get dashboard statistics (filtered by category if selected)
        if ($selectedCategoryId) {
            // Filtered stats for selected category
            $stats = [
                'total_students' => \App\Models\StudentExam::whereHas('exam', function($q) use ($selectedCategoryId) {
                    $q->where('category_id', $selectedCategoryId);
                })->distinct('student_id')->count('student_id'),
                
                'active_exams' => Exam::where('status', 1)
                    ->where('category_id', $selectedCategoryId)
                    ->count(),
                
                'total_questions' => Question::where('status', 1)
                    ->whereHas('caseStudy.section.exam', function($q) use ($selectedCategoryId) {
                        $q->where('category_id', $selectedCategoryId);
                    })->count(),
                
                'recent_attempts_count' => ExamAttempt::whereDate('created_at', today())
                    ->whereHas('studentExam.exam', function($q) use ($selectedCategoryId) {
                        $q->where('category_id', $selectedCategoryId);
                    })->count(),
                
                'exam_categories' => 1, // Selected category
                
                'case_studies' => \App\Models\CaseStudy::where('status', 1)
                    ->whereHas('section.exam', function($q) use ($selectedCategoryId) {
                        $q->where('category_id', $selectedCategoryId);
                    })->count(),
            ];
        } else {
            // Total stats (no filter)
            $stats = [
                'total_students' => User::where('role', 'student')->count(),
                'active_exams' => Exam::where('status', 1)->count(),
                'total_questions' => Question::where('status', 1)->count(),
                'recent_attempts_count' => ExamAttempt::whereDate('created_at', today())->count(),
                'exam_categories' => \App\Models\ExamCategory::where('status', 1)->count(),
                'case_studies' => \App\Models\CaseStudy::where('status', 1)->count(),
            ];
        }

        // Get recent attempts (no filters, just latest 10)
        $recentAttempts = ExamAttempt::with(['studentExam.student', 'studentExam.exam.category'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($attempt) {
                return (object)[
                    'id' => $attempt->id,
                    'student_id' => $attempt->studentExam->student->id,
                    'student_name' => $attempt->studentExam->student->first_name . ' ' . $attempt->studentExam->student->last_name,
                    'student_email' => $attempt->studentExam->student->email,
                    'exam_name' => $attempt->studentExam->exam->name,
                    'exam_category' => $attempt->studentExam->exam->category ? $attempt->studentExam->exam->category->name : '-',
                    'total_score' => $attempt->total_score,
                    'is_passed' => $attempt->is_passed,
                    'created_at' => $attempt->created_at,
                    'duration' => $attempt->formatted_duration,
                    'time_ago' => $attempt->created_at->diffForHumans(),
                ];
            });

        // Get all categories for filter dropdown
        $categories = \App\Models\ExamCategory::where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Get filter parameters for exam overview
        $examSearch = $request->get('exam_search');
        $examCategoryId = $request->get('exam_category_id');
        $certificationType = $request->get('certification_type');

        // Build query for exam overview
        $examQuery = Exam::where('status', 1)
            ->with(['category', 'sections.caseStudies'])
            ->withCount([
                'studentExams as student_count',
                'studentExams as attempt_count' => function($q) {
                    $q->has('attempts');
                }
            ]);

        // Filter by exam name or exam code
        if ($examSearch) {
            $examQuery->where(function($q) use ($examSearch) {
                $q->where('name', 'like', '%' . $examSearch . '%')
                  ->orWhere('exam_code', 'like', '%' . $examSearch . '%');
            });
        }

        // Filter by category
        if ($examCategoryId) {
            $examQuery->where('category_id', $examCategoryId);
        }

        // Filter by certification type
        if ($certificationType) {
            $examQuery->where('certification_type', $certificationType);
        }

        // Get exam overview data
        $examOverview = $examQuery->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($exam) {
                // Count questions for this exam through case studies
                $questionCount = 0;
                $caseStudyCount = 0;
                
                foreach ($exam->sections as $section) {
                    $activeCaseStudies = $section->caseStudies->where('status', 1);
                    $caseStudyCount += $activeCaseStudies->count();
                    
                    // Count questions for each case study
                    foreach ($activeCaseStudies as $caseStudy) {
                        $questionCount += \App\Models\Question::where('case_study_id', $caseStudy->id)
                            ->where('status', 1)
                            ->count();
                    }
                }

                return (object)[
                    'id' => $exam->id,
                    'name' => $exam->name,
                    'exam_code' => $exam->exam_code,
                    'category' => $exam->category ? $exam->category->name : '-',
                    'certification_type' => $exam->certification_type ?? '-',
                    'is_active' => $exam->is_active,
                    'student_count' => $exam->student_count,
                    'question_count' => $questionCount,
                    'case_study_count' => $caseStudyCount,
                    'attempt_count' => $exam->attempt_count,
                ];
            });

        // Get certification types for filter
        $certificationTypes = Exam::where('status', 1)
            ->whereNotNull('certification_type')
            ->distinct()
            ->orderBy('certification_type')
            ->pluck('certification_type');

        // Get filter parameters for student details
        $studentSearch = $request->get('student_search');
        
        // Build query for student details
        $studentQuery = User::where('role', 'student')
            ->withCount('studentExams as enrolled_exams_count')
            ->withSum('studentExams as total_attempts_allowed', 'attempts_allowed')
            ->withSum('studentExams as total_attempts_used', 'attempts_used');

        // Filter by student name or email
        if ($studentSearch) {
            $studentQuery->where(function($q) use ($studentSearch) {
                $q->where('first_name', 'like', '%' . $studentSearch . '%')
                  ->orWhere('last_name', 'like', '%' . $studentSearch . '%')
                  ->orWhere('email', 'like', '%' . $studentSearch . '%')
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $studentSearch . '%']);
            });
        }

        // Get student details data
        $studentDetails = $studentQuery->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($student) {
                // Calculate average score from all attempts
                $attempts = ExamAttempt::whereHas('studentExam', function($q) use ($student) {
                    $q->where('student_id', $student->id);
                })->get();
                
                $averageScore = $attempts->count() > 0 ? $attempts->avg('total_score') : 0;
                
                $remainingAttempts = ($student->total_attempts_allowed ?? 0) - ($student->total_attempts_used ?? 0);

                return (object)[
                    'id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'email' => $student->email,
                    'enrolled_exams' => $student->enrolled_exams_count,
                    'total_attempts' => $remainingAttempts . ' left',
                    'average_score' => round($averageScore, 1),
                    'status' => $student->status,
                    'created_at' => $student->created_at,
                    'joined_date' => $student->created_at->format('M d, Y'),
                ];
            });

        return view('dashboard.admin', compact('stats', 'recentAttempts', 'categories', 'examOverview', 'certificationTypes', 'studentDetails'));
    }
}
