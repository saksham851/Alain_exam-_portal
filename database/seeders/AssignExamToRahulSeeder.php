<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Exam;
use App\Models\StudentExam;

class AssignExamToRahulSeeder extends Seeder
{
    public function run(): void
    {
        // Find rahul0@student.com
        $student = User::where('email', 'rahul0@student.com')->first();
        
        if (!$student) {
            echo "Student rahul0@student.com not found!\n";
            return;
        }
        
        // Get first exam
        $exam = Exam::first();
        
        if (!$exam) {
            echo "No exams found in database!\n";
            return;
        }
        
        // Check if already assigned
        $existing = StudentExam::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->first();
            
        if ($existing) {
            echo "Exam '{$exam->name}' is already assigned to {$student->email}\n";
            return;
        }
        
        // Assign exam to student
        StudentExam::create([
            'student_id' => $student->id,
            'exam_id' => $exam->id,
            'attempts_allowed' => 3,
            'attempts_used' => 1, // Already used 1 attempt
            'expiry_date' => now()->addWeeks(4), // 4 weeks from now
        ]);
        
        echo "✅ Exam '{$exam->name}' successfully assigned to {$student->email}!\n";
        echo "   - Max Attempts: 3\n";
        echo "   - Used: 1 attempt\n";
        echo "   - Remaining: 2 attempts\n";
        echo "   - Expires in: 4 weeks (" . now()->addWeeks(4)->format('Y-m-d') . ")\n";
    }
}
