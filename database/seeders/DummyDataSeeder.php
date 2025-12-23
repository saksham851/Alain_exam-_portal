<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Exam;
use App\Models\CaseStudy;
use App\Models\SubCaseStudy;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\StudentExam;
use App\Models\ExamAttempt;
use App\Models\AttemptAnswer;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Clear existing data to ensure a fresh start
        // We use truncate to reset IDs if possible, or usually db:wipe is run manually. 
        // Here we'll just disable foreign key checks and truncate core tables.
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        Exam::truncate();
        CaseStudy::truncate();
        SubCaseStudy::truncate();
        Question::truncate();
        QuestionOption::truncate();
        StudentExam::truncate();
        ExamAttempt::truncate();
        AttemptAnswer::truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo "✓ Database cleared\n";

        // 2. Create Admin User
        $admin = User::create([
            'first_name' => 'Admin', 'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin', 'status' => 1, 'is_blocked' => false,
        ]);

        echo "✓ Admin user created\n";

        // 3. Create strictly 2 Student Users
        $students = [];
        $studentNames = [['Rahul', 'Sharma'], ['Priya', 'Patel']];

        foreach ($studentNames as $index => $name) {
            $students[] = User::create([
                'first_name' => $name[0], 'last_name' => $name[1],
                'email' => strtolower($name[0]) . '@student.com',
                'password' => Hash::make('password'),
                'phone' => '+91' . rand(7000000000, 9999999999),
                'role' => 'student', 'status' => 1, 'is_blocked' => false,
            ]);
        }

        echo "✓ Created 2 student users (rahul@student.com, priya@student.com)\n";

        // 4. Create strictly 2 Exams
        $examData = [
            [
                'name' => 'Laravel Fundamentals',
                'description' => 'Test your knowledge of Laravel basics and core concepts.',
                'duration_minutes' => 180,
            ],
            [
                'name' => 'Digital Marketing Fundamentals',
                'description' => 'Master the basics of SEO, SEM, SMM, and Email Marketing.',
                'duration_minutes' => 180,
            ],
        ];

        $exams = [];
        foreach ($examData as $examInfo) {
            $exam = Exam::create(array_merge($examInfo, ['status' => 1]));
            $exams[] = $exam;

            // Generate 11 Case Studies
            $totalQuestions = 0;
            
            for ($cs = 1; $cs <= 11; $cs++) {
                $caseStudy = CaseStudy::create([
                    'exam_id' => $exam->id,
                    'title' => "Case Study $cs: " . $this->getTopic($exam->name, $cs),
                    'content' => "Scenario for Case Study $cs... analysis required.",
                    'order_no' => $cs,
                    'status' => 1,
                ]);

                // Generate 3 Sub-Cases per Case Study
                for ($sc = 1; $sc <= 3; $sc++) {
                    $subCase = SubCaseStudy::create([
                        'case_study_id' => $caseStudy->id,
                        'title' => "SC $cs.$sc: Scenario Analysis",
                        'content' => "Sub-scenario details for $cs.$sc...",
                        'order_no' => $sc,
                        'status' => 1,
                    ]);

                    // LOGIC FOR EXACTLY 154 QUESTIONS
                    // Total Subcases = 11 * 3 = 33.
                    // 154 Questions needed.
                    // 154 / 33 = 4 remainder 22.
                    // So 22 subcases get 5 questions, 11 subcases get 4 questions.
                    
                    // Flattened Loop Index (1 to 33)
                    $globalSubCaseIndex = ($cs - 1) * 3 + $sc;
                    
                    // If index <= 22, create 5 questions. Else create 4.
                    $questionsToCreate = ($globalSubCaseIndex <= 22) ? 5 : 4;

                    $categories = ['ig', 'dm'];
                    $questionTypes = ['single', 'multiple'];
                    
                    for ($q = 1; $q <= $questionsToCreate; $q++) {
                        $category = $categories[($q + $sc) % 2]; 
                        $questionType = $questionTypes[rand(0, 1)];
                        
                        $question = Question::create([
                            'sub_case_id' => $subCase->id,
                            'question_text' => "Q$q (SC $cs.$sc): Evaluate the {$category} implications.",
                            'question_type' => $questionType,
                            'ig_weight' => $category === 'ig' ? 1 : 0,
                            'dm_weight' => $category === 'dm' ? 1 : 0,
                            'status' => 1,
                        ]);

                        $this->createOptions($question);
                        $totalQuestions++;
                    }
                }
            }
            echo "✓ Created Exam '{$exam->name}' with $totalQuestions Questions\n";
        }

        // Assign exams to students
        foreach ($students as $student) {
            foreach ($exams as $exam) {
                 StudentExam::create([
                    'student_id' => $student->id,
                    'exam_id' => $exam->id,
                    'expiry_date' => now()->addMonths(6),
                    'attempts_allowed' => 3,
                    'attempts_used' => 0,
                    'source' => 'admin',
                    'status' => 1,
                ]);
            }
        }
        
        echo "✓ Assigned exams to students\n";
    }

    private function getTopic($examName, $index) {
        $topics = [
            'Laravel Fundamentals' => ['Routing', 'Middleware', 'Controllers', 'Views', 'Blade', 'Eloquent', 'Migrations', 'Seeding', 'Auth', 'API', 'Testing'],
            'Advanced PHP Development' => ['OOP', 'Interfaces', 'Traits', 'Generators', 'Attributes', 'JIT', 'Security', 'Performance', 'Composer', 'Standards', 'Async'],
            'Database Design & SQL' => ['Normalization', 'Indexing', 'Joins', 'Transactions', 'Stored Procedures', 'Triggers', 'Views', 'Security', 'Backup', 'NoSQL', 'Scaling'],
            'Digital Marketing Fundamentals' => ['SEO Fundamentals', 'Keyword Research', 'On-Page SEO', 'Off-Page SEO', 'Local SEO', 'PPC Basics', 'Social Media Strategy', 'Email Marketing', 'Analytics', 'Content Strategy', 'Conversion Rate Optimization'],
        ];

        return $topics[$examName][$index - 1] ?? "Topic $index";
    }

    private function createOptions($question) {
        $optionTexts = [
            'Optimal solution implementing best practices.',
            'Sub-optimal approach with some flaws.',
            'Incorrect method that causes errors.',
            'Legacy approach no longer recommended.',
        ];

        foreach ($optionTexts as $optIndex => $optionText) {
            QuestionOption::create([
                'question_id' => $question->id,
                'option_key' => chr(65 + $optIndex), // A, B, C, D
                'option_text' => $optionText,
                'is_correct' => $optIndex === 0 ? 1 : 0, 
            ]);
        }
    }
}
