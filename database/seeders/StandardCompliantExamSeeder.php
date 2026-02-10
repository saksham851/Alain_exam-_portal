<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exam;
use App\Models\Section;
use App\Models\CaseStudy;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTag;
use App\Models\ExamStandard;
use App\Models\ScoreCategory;
use App\Models\ContentArea;
use App\Models\ExamCategory;
use Illuminate\Support\Facades\DB;

class StandardCompliantExamSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();
        try {
            // 1. Get the Standard
            $standard = ExamStandard::where('name', 'Counselor Exam Blueprint')->first();
            if (!$standard) {
                $this->command->error('Counselor Exam Blueprint not found. Please run CounselorExamStandardSeeder first.');
                return;
            }

            // 2. Get Categories
            $domainCat = ScoreCategory::where('exam_standard_id', $standard->id)->where('category_number', 1)->first();
            $cacrepCat = ScoreCategory::where('exam_standard_id', $standard->id)->where('category_number', 2)->first();

            // 3. Create Exam
            $category = ExamCategory::firstOrCreate(['name' => 'Counseling'], ['status' => 1]);
            
            $exam = Exam::create([
                'name' => 'Standard Compliant Mock Exam',
                'exam_code' => 'COMPLIANT-' . time(),
                'category_id' => $category->id,
                'certification_type' => 'Standard Check',
                'description' => 'Exam seeded to exactly fulfill the Counselor Blueprint requirements with 3 sections and 3-mark questions.',
                'duration_minutes' => 180,
                'total_questions' => 35,
                'exam_standard_id' => $standard->id,
                'passing_score_overall' => 65,
                'status' => 1,
                'is_active' => 0
            ]);

            // 4. Create Sections and Case Studies (3 Sections, 1 Case Study each)
            $sections = [];
            $caseStudies = [];
            for ($i = 1; $i <= 3; $i++) {
                $section = Section::create([
                    'exam_id' => $exam->id,
                    'title' => "Section $i: Strategic Approach",
                    'content' => "Focus area for section $i content.",
                    'order_no' => $i,
                    'status' => 1
                ]);
                $sections[] = $section;

                $cs = CaseStudy::create([
                    'section_id' => $section->id,
                    'title' => "Case Study $i: Patient Scenario",
                    'content' => "Detailed case scenario for students to analyze in Section $i.",
                    'order_no' => 1,
                    'status' => 1
                ]);
                $caseStudies[] = $cs;
            }

            // 5. Helper to add questions
            $addQuestion = function($csId, $text, $points, $domainAreaName, $cacrepAreaName) use ($domainCat, $cacrepCat) {
                $q = Question::create([
                    'case_study_id' => $csId,
                    'question_text' => $text,
                    'question_type' => 'single',
                    'max_question_points' => $points,
                    'status' => 1
                ]);

                // Options
                QuestionOption::create(['question_id' => $q->id, 'option_key' => 'A', 'option_text' => 'Correct Strategy', 'is_correct' => 1]);
                QuestionOption::create(['question_id' => $q->id, 'option_key' => 'B', 'option_text' => 'Sub-optimal Option', 'is_correct' => 0]);
                QuestionOption::create(['question_id' => $q->id, 'option_key' => 'C', 'option_text' => 'Incorrect Action', 'is_correct' => 0]);
                QuestionOption::create(['question_id' => $q->id, 'option_key' => 'D', 'option_text' => 'Avoid this path', 'is_correct' => 0]);

                // Tags
                if ($domainAreaName) {
                    $area = $domainCat->contentAreas()->where('name', $domainAreaName)->first();
                    if ($area) {
                        QuestionTag::create(['question_id' => $q->id, 'score_category_id' => $domainCat->id, 'content_area_id' => $area->id]);
                    }
                }
                if ($cacrepAreaName) {
                    $area = $cacrepCat->contentAreas()->where('name', $cacrepAreaName)->first();
                    if ($area) {
                        QuestionTag::create(['question_id' => $q->id, 'score_category_id' => $cacrepCat->id, 'content_area_id' => $area->id]);
                    }
                }
                return $q;
            };

            // 6. Generate 35 Questions to fulfill standard
            // We'll distribute them: 12 in CS 1, 12 in CS 2, 11 in CS 3
            
            $qCount = 1;
            $questionsData = [
                // Batch 1: D1 full, C1 full, C2 full
                ['D' => 'Professional Practice and Ethics', 'C' => 'Professional Counseling Orientation and Ethical Practice'],
                ['D' => 'Professional Practice and Ethics', 'C' => 'Professional Counseling Orientation and Ethical Practice'],
                ['D' => 'Professional Practice and Ethics', 'C' => 'Professional Counseling Orientation and Ethical Practice'],
                ['D' => 'Professional Practice and Ethics', 'C' => 'Professional Counseling Orientation and Ethical Practice'],
                ['D' => 'Professional Practice and Ethics', 'C' => 'Social and Cultural Diversity'],

                // Batch 2: D2 part, C3 full, C7 full, C8 full
                ['D' => 'Intake, Assessment and Diagnosis', 'C' => 'Human Growth and Development'],
                ['D' => 'Intake, Assessment and Diagnosis', 'C' => 'Assessment and Testing'],
                ['D' => 'Intake, Assessment and Diagnosis', 'C' => 'Assessment and Testing'],
                ['D' => 'Intake, Assessment and Diagnosis', 'C' => 'Assessment and Testing'],
                ['D' => 'Intake, Assessment and Diagnosis', 'C' => 'Assessment and Testing'],
                ['D' => 'Intake, Assessment and Diagnosis', 'C' => 'Assessment and Testing'],
                ['D' => 'Intake, Assessment and Diagnosis', 'C' => 'Assessment and Testing'],
                ['D' => 'Intake, Assessment and Diagnosis', 'C' => 'Research and Program Evaluation'],
                ['D' => 'Intake, Assessment and Diagnosis', 'C' => 'Counseling and Helping Relationships'],
                ['D' => 'Intake, Assessment and Diagnosis', 'C' => 'Counseling and Helping Relationships'], // Extra for D2/C5

                // Batch 3: D3 full
                ['D' => 'Treatment Planning Counseling Skills', 'C' => 'Counseling and Helping Relationships'],
                ['D' => 'Treatment Planning Counseling Skills', 'C' => 'Counseling and Helping Relationships'],
                ['D' => 'Treatment Planning Counseling Skills', 'C' => 'Counseling and Helping Relationships'],
                ['D' => 'Treatment Planning Counseling Skills', 'C' => 'Counseling and Helping Relationships'],
                ['D' => 'Treatment Planning Counseling Skills', 'C' => 'Counseling and Helping Relationships'],

                // Batch 4: D4 full
                ['D' => 'Counseling Skills and Interventions', 'C' => 'Counseling and Helping Relationships'],
                ['D' => 'Counseling Skills and Interventions', 'C' => 'Counseling and Helping Relationships'],
                ['D' => 'Counseling Skills and Interventions', 'C' => 'Counseling and Helping Relationships'],
                ['D' => 'Counseling Skills and Interventions', 'C' => 'Counseling and Helping Relationships'],
                ['D' => 'Counseling Skills and Interventions', 'C' => 'Counseling and Helping Relationships'],
                ['D' => 'Counseling Skills and Interventions', 'C' => 'Counseling and Helping Relationships'],
                ['D' => 'Counseling Skills and Interventions', 'C' => 'Counseling and Helping Relationships'],
                ['D' => 'Counseling Skills and Interventions', 'C' => 'Counseling and Helping Relationships'],
                ['D' => 'Counseling Skills and Interventions', 'C' => 'Counseling and Helping Relationships'],
                ['D' => 'Counseling Skills and Interventions', 'C' => 'Counseling and Helping Relationships'],

                // Batch 5: D5 full, C6 full
                ['D' => 'Core Counseling Attributes', 'C' => 'Group Counseling and Group Work'],
                ['D' => 'Core Counseling Attributes', 'C' => 'Counseling and Helping Relationships'],
                ['D' => 'Core Counseling Attributes', 'C' => 'Counseling and Helping Relationships'],
                ['D' => 'Core Counseling Attributes', 'C' => 'Counseling and Helping Relationships'],
                ['D' => 'Core Counseling Attributes', 'C' => 'Counseling and Helping Relationships'],
            ];

            foreach ($questionsData as $index => $tags) {
                // Determine which case study to put it in
                if ($index < 12) $csId = $caseStudies[0]->id;
                else if ($index < 24) $csId = $caseStudies[1]->id;
                else $csId = $caseStudies[2]->id;

                $addQuestion($csId, "Strategic Question #".($index+1)." [".$tags['D']." / ".$tags['C']."]", 3, $tags['D'], $tags['C']);
            }

            DB::commit();
            $this->command->info('Standard Compliant Exam created and seeded successfully with 35 questions (3 marks each).');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Seeding failed: ' . $e->getMessage());
        }
    }
}
