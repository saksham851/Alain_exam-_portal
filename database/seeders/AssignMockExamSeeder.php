<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Exam;
use App\Models\StudentExam;

class AssignMockExamSeeder extends Seeder
{
    public function run()
    {
        $student = User::where('role', 'student')->first();
        if (!$student) {
            $student = User::create([
                'first_name' => 'Demo',
                'last_name' => 'Student',
                'email' => 'student@example.com',
                'password' => bcrypt('password'),
                'role' => 'student',
                'status' => 1
            ]);
            $this->command->info('Created Demo Student');
        }

        $exam = Exam::where('exam_code', 'MOCK2026')->first();

        if ($student && $exam) {
            StudentExam::firstOrCreate(
                ['student_id' => $student->id, 'exam_id' => $exam->id],
                [
                    'expiry_date' => now()->addYear(),
                    'attempts_allowed' => 10,
                    'attempts_used' => 0,
                    'status' => 1,
                    'source' => 'Seeder'
                ]
            );
            $this->command->info('Assigned ' . $exam->name . ' to ' . $student->first_name);
        } else {
            $this->command->error('Student or Exam not found');
        }
    }
}
