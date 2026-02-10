<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exam;
use App\Models\ExamStandard;
use App\Models\Section;
use App\Models\CaseStudy;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTag;
use App\Models\ExamCategory;

class CreateMockExamSeeder extends Seeder
{
    public function run()
    {
        $standard = ExamStandard::where('name', 'Counselor Exam Blueprint')->first();
        if (!$standard) {
            $this->command->error("Standard 'Counselor Exam Blueprint' not found. Run CounselorExamStandardSeeder first.");
            return;
        }

        // Get a default category for the exam itself
        $examCat = ExamCategory::firstOrCreate(['name' => 'Counseling'], ['status' => 1]);

        // Cleanup previous failed runs
        Exam::where('exam_code', 'MOCK2026')->delete();

        $exam = Exam::create([
            'name' => 'Full Counselor Mock Exam 2026',
            'exam_code' => 'MOCK2026',
            'category_id' => $examCat->id,
            'exam_standard_id' => $standard->id, // Link to standard
            'description' => 'A full mock exam generated to match the Counselor Blueprint.',
            'duration_minutes' => 240,
            'total_questions' => 0, // Not strict anymore
            'passing_score_overall' => 65,
            'status' => 1,
            'is_active' => 1,
            'certification_type' => 'NHMCE'
        ]);

        $section = Section::create([
            'exam_id' => $exam->id,
            'title' => 'Clinical Simulations',
            'order_no' => 1,
            'status' => 1
        ]);

        // We will create one Case Study per Domain to spread questions out, 
        // OR just one big Case Study. Let's do a few to look real.
        $caseStudy = CaseStudy::create([
            'section_id' => $section->id,
            'title' => 'Case Study 1: Adult Anxiety',
            'content' => '<p>Client is a 35 year old male presenting with...</p>',
            'order_no' => 1,
            'status' => 1
        ]);

        // Retrieve valid tagging areas
        $domainCat = $standard->categories()->where('name', 'like', '%Domain%')->first();
        $cacrepCat = $standard->categories()->where('name', 'like', '%CACREP%')->first();

        // Helper to add question
        $addQuestion = function($text, $points, $domainAreaName, $cacrepAreaName) use ($caseStudy, $domainCat, $cacrepCat) {
            
            $qId = \Illuminate\Support\Facades\DB::table('questions')->insertGetId([
                'case_study_id' => $caseStudy->id,
                'question_text' => $text,
                'question_type' => 'single',
                'max_question_points' => $points,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $q = Question::find($qId);

            // Add Options
            QuestionOption::create(['question_id' => $q->id, 'option_key' => 'A', 'option_text' => 'Correct Answer', 'is_correct' => 1]);
            QuestionOption::create(['question_id' => $q->id, 'option_key' => 'B', 'option_text' => 'Wrong Answer A', 'is_correct' => 0]);
            QuestionOption::create(['question_id' => $q->id, 'option_key' => 'C', 'option_text' => 'Wrong Answer B', 'is_correct' => 0]);
            QuestionOption::create(['question_id' => $q->id, 'option_key' => 'D', 'option_text' => 'Wrong Answer C', 'is_correct' => 0]);

            // Tagging
            if ($domainCat && $domainAreaName) {
                $area = $domainCat->contentAreas()->where('name', $domainAreaName)->first();
                if ($area) {
                    QuestionTag::create([
                        'question_id' => $q->id,
                        'score_category_id' => $domainCat->id,
                        'content_area_id' => $area->id
                    ]);
                }
            }

            if ($cacrepCat && $cacrepAreaName) {
                $area = $cacrepCat->contentAreas()->where('name', $cacrepAreaName)->first();
                if ($area) {
                    QuestionTag::create([
                        'question_id' => $q->id,
                        'score_category_id' => $cacrepCat->id,
                        'content_area_id' => $area->id
                    ]);
                }
            }
        };

        // Helper to add multiple questions for a target
        $addBatch = function($namePrefix, $targetPoints, $domainAreaName, $cacrepAreaName) use ($addQuestion) {
            $current = 0;
            $count = 1;
            while ($current < $targetPoints) {
                $points = min(3, $targetPoints - $current);
                $addQuestion($namePrefix . " - Part " . $count, $points, $domainAreaName, $cacrepAreaName);
                $current += $points;
                $count++;
            }
        };

        // Precise Questions to hit targets with MAX 3 POINTS per question
        
        // Domain 1: Professional Practice (Target 15)
        $addBatch('Ethics & Orientation', 12, 'Professional Practice and Ethics', 'Professional Counseling Orientation and Ethical Practice');
        $addBatch('Cultural Competency', 3, 'Professional Practice and Ethics', 'Social and Cultural Diversity');

        // Domain 2: Intake (Target 25)
        $addBatch('Assessment Tools', 17, 'Intake, Assessment and Diagnosis', 'Assessment and Testing');
        $addBatch('Human Development', 3, 'Intake, Assessment and Diagnosis', 'Human Growth and Development');
        $addBatch('Group Counseling', 3, 'Intake, Assessment and Diagnosis', 'Group Counseling and Group Work');
        $addBatch('Intake Relationship', 2, 'Intake, Assessment and Diagnosis', 'Counseling and Helping Relationships');

        // Domain 3: Treatment Planning (Target 15)
        $addBatch('Treatment Goals', 15, 'Treatment Planning Counseling Skills', 'Counseling and Helping Relationships');

        // Domain 4: Counseling Skills (Target 30)
        $addBatch('Active Listening & Skills', 30, 'Counseling Skills and Interventions', 'Counseling and Helping Relationships');

        // Domain 5: Core Attributes (Target 15)
        $addBatch('Empathy & Attributes', 14, 'Core Counseling Attributes', 'Counseling and Helping Relationships');
        $addBatch('Research & Evaluation', 1, 'Core Counseling Attributes', 'Research and Program Evaluation');

        $this->command->info('Mock Exam created with 34 questions (Max 3 pts each) to satisfy Counselor Blueprint!');
    }
}
