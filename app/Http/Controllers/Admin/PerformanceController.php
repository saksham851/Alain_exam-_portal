<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Models\ExamStandard;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerformanceController extends Controller
{
    public function index(Request $request)
    {
        $standards = ExamStandard::orderBy('name')->get();
        $selectedStandard = $request->get('standard_id');
        $selectedExam = $request->get('exam_id');

        $exams = Exam::where('status', 1)
            ->when($selectedStandard, function($q) use ($selectedStandard) {
                return $q->where('exam_standard_id', $selectedStandard);
            })->orderBy('name')->get();

        $query = ExamAttempt::whereHas('studentExam.exam', function($q) {
            $q->where('status', 1);
        })->with(['studentExam.student', 'studentExam.exam.examStandard']);

        if ($selectedStandard) {
            $query->whereHas('studentExam.exam', function($q) use ($selectedStandard) {
                $q->where('exam_standard_id', $selectedStandard);
            });
        }

        if ($selectedExam) {
            $selectedExams = is_array($selectedExam) ? $selectedExam : [$selectedExam];
            $query->whereHas('studentExam', function($q) use ($selectedExams) {
                $q->whereIn('exam_id', $selectedExams);
            });
        }

        // Student search
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

        $attempts = (clone $query)->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Calculate stats for the current filter
        $statsQuery = clone $query;
        $totalAttempts = (clone $statsQuery)->count();
        $passedAttempts = (clone $statsQuery)->where('is_passed', true)->count();
        $failedAttempts = $totalAttempts - $passedAttempts;
        $avgScore = (clone $statsQuery)->avg('total_score') ?? 0;

        $passRate = $totalAttempts > 0 ? ($passedAttempts / $totalAttempts) * 100 : 0;

        // Group by exam for chart
        $examStats = (clone $statsQuery)
            ->join('student_exams', 'exam_attempts.student_exam_id', '=', 'student_exams.id')
            ->join('exams', 'student_exams.exam_id', '=', 'exams.id')
            ->select('exams.name', DB::raw('AVG(exam_attempts.total_score) as avg_score'), DB::raw('COUNT(*) as total'))
            ->groupBy('exams.id', 'exams.name')
            ->get();

        $routePrefix = request()->is('admin/*') ? 'admin' : 'manager';

        return view('admin.performance.index', compact(
            'standards', 
            'exams', 
            'attempts', 
            'selectedStandard', 
            'selectedExam', 
            'studentSearch',
            'totalAttempts',
            'passedAttempts',
            'failedAttempts',
            'avgScore',
            'passRate',
            'examStats',
            'routePrefix'
        ));
    }

    public function export(Request $request)
    {
        $selectedStandard = $request->get('standard_id');
        $selectedExam = $request->get('exam_id');
        $studentSearch = $request->get('student_search');

        $query = ExamAttempt::whereHas('studentExam.exam', function($q) {
            $q->where('status', 1);
        })->with(['studentExam.student', 'studentExam.exam.examStandard']);

        if ($selectedStandard) {
            $query->whereHas('studentExam.exam', function($q) use ($selectedStandard) {
                $q->where('exam_standard_id', $selectedStandard);
            });
        }

        if ($selectedExam) {
            $selectedExams = is_array($selectedExam) ? $selectedExam : [$selectedExam];
            $query->whereHas('studentExam', function($q) use ($selectedExams) {
                $q->whereIn('exam_id', $selectedExams);
            });
        }

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

        $attempts = $query->orderBy('created_at', 'desc')->get();

        $filename = "student_attempts_" . date('Y-m-d_H-i-s') . ".csv";
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $columns = array('Student Name', 'Student Email', 'Exam', 'Exam Code', 'Standard', 'Score', 'Status', 'Date');

        $callback = function() use ($attempts, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($attempts as $attempt) {
                fputcsv($file, array(
                    $attempt->studentExam->student->first_name . ' ' . $attempt->studentExam->student->last_name,
                    $attempt->studentExam->student->email,
                    $attempt->studentExam->exam->name,
                    $attempt->studentExam->exam->exam_code,
                    $attempt->studentExam->exam->examStandard->name ?? 'N/A',
                    number_format($attempt->total_score, 0),
                    $attempt->is_passed ? 'Passed' : 'Failed',
                    $attempt->created_at->format('M d, Y h:i A'),
                ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
