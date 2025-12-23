<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Exam;
use App\Models\CaseStudy;
use App\Models\SubCaseStudy;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\StudentExam;

class StrictExamSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Clean Database (Delete everything)
        // Disable foreign key checks to allow truncating
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        Exam::truncate();
        CaseStudy::truncate();
        SubCaseStudy::truncate();
        Question::truncate();
        QuestionOption::truncate();
        StudentExam::truncate();
        \App\Models\ExamAttempt::truncate();
        \App\Models\AttemptAnswer::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo "🗑️  Database Cleared.\n";

        // 2. Create Users
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        
        $student = User::create([
            'first_name' => 'Rahul',
            'last_name' => 'Sharma',
            'email' => 'rahul0@student.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        echo "👤 Users Created.\n";

        // 3. Create 1 Exam
        $exam = Exam::create([
            'name' => 'Clinical Competency Exam',
            'description' => 'A comprehensive evaluation of clinical reasoning and decision making.',
            'duration_minutes' => 180, // 3 hours
            'status' => 1,
        ]);

        echo "📝 Exam Created: {$exam->name}\n";

        // 4. Create 11 Case Studies
        for ($c = 1; $c <= 11; $c++) {
            $case = CaseStudy::create([
                'exam_id' => $exam->id,
                'title' => "Case Study $c: Patient Scenario",
                'content' => "<p><strong>Patient $c:</strong> A 45-year-old male presents with symptoms requiring immediate attention. History includes hypertension and diabetes. Vital signs are stable but suggestive of underlying pathology.</p>",
                'order_no' => $c,
                'status' => 1,
            ]);

            // 5. Create 3 Sub Cases per Case Study
            for ($s = 1; $s <= 3; $s++) {
                // Generate ~150 words of dummy medical content
                $dummyText = "Patient presents with a 3-day history of increasing fatigue and shortness of breath. On physical examination, the patient is febrile (38.5°C), tachycardic (110 bpm), and tachypneic (24 breaths/min). Lung auscultation reveals crackles in the right lower lobe. The patient reports a productive cough with rust-colored sputum. Past medical history is significant for type 2 diabetes mellitus and hypertension, both managed with oral medications. Social history includes a 20-pack-year smoking history, though the patient reports quitting 5 years ago. Initial oxygen saturation is 92% on room air. The patient denies recent travel or sick contacts. A chest X-ray is ordered, which demonstrates a consolidation in the right lower lobe consistent with pneumonia. Laboratory results show leukocytosis with a left shift. The medical team initiates broad-spectrum antibiotics and supplemental oxygen. Over the next 4 hours, the patient's respiratory status deteriorates, requiring escalation of care. (Section $c.$s)";

                $subCase = SubCaseStudy::create([
                    'case_study_id' => $case->id,
                    'title' => "Section $c.$s - Clinical Progression",
                    'content' => "<p>$dummyText</p>",
                    'order_no' => $s,
                    'status' => 1,
                ]);

                // 6. Create 3 Questions per Sub Case
                for ($q = 1; $q <= 3; $q++) {
                    $type = ($q === 3) ? 'multiple' : 'single'; // Make 3rd question multiple choice
                    
                    // Assign IG/DM weights
                    // Q1: IG, Q2: DM, Q3: Both/Mixed
                    $igWeight = ($q === 1 || $q === 3) ? 1 : 0;
                    $dmWeight = ($q === 2 || $q === 3) ? 1 : 0;

                    $question = Question::create([
                        'sub_case_id' => $subCase->id,
                        'question_text' => "Question $q: Based on the new findings in Section $c.$s, what is the most appropriate next step?",
                        'question_type' => $type,
                        'ig_weight' => $igWeight,
                        'dm_weight' => $dmWeight,
                        'status' => 1,
                    ]);

                    // Options A, B, C, D
                    $options = [
                        ['A', 'Order further diagnostic imaging', 1], // Correct
                        ['B', 'Discharge the patient immediately', 0],
                        ['C', 'Consult with a specialist', 0],
                        ['D', 'Review past medical history again', 0],
                    ];

                    foreach ($options as $opt) {
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_key' => $opt[0],
                            'option_text' => $opt[1],
                            'is_correct' => $opt[2],
                        ]);
                    }
                }
            }
        }

        echo "📚 Case Studies, Sub-Cases & Questions Created (11 x 3 x 3).\n";

        // 7. Assign Exam to Student
        StudentExam::create([
            'student_id' => $student->id,
            'exam_id' => $exam->id,
            'expiry_date' => now()->addWeeks(4),
            'attempts_allowed' => 3,
            'attempts_used' => 0,
            'status' => 1,
        ]);

        echo "✅ Exam Assigned to Rahul.\n";
    }
}
