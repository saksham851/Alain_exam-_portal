<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamStandard;
use App\Models\ExamStandardCategory;
use App\Models\ExamStandardContentArea;
use App\Models\Exam;
use App\Models\Section;
use App\Models\CaseStudy;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\ExamCategoryPassingScore;
use Illuminate\Support\Facades\DB;

class NCMHCE2026Seeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();

        try {
            $this->command->info('Seeding NCMHCE 2026 Standard...');

            // 1. Create Standard
            $standard = ExamStandard::create([
                'name' => 'NCMHCE 2026',
                'description' => 'National Clinical Mental Health Counseling Examination (2026 Format)',
            ]);

            // 2. Create Categories & Content Areas
            
            // Category 1: Work Behaviors (Domains)
            $cat1 = ExamStandardCategory::create([
                'exam_standard_id' => $standard->id,
                'name' => 'Counselor Work Behavior Areas (Domains)',
                'category_number' => 1,
            ]);

            $cat1Areas = [
                ['name' => 'Professional Practice and Ethics', 'percentage' => 15],
                ['name' => 'Intake, Assessment and Diagnosis', 'percentage' => 25],
                ['name' => 'Treatment Planning Counseling Skills', 'percentage' => 15],
                ['name' => 'Counseling Skills and Interventions', 'percentage' => 30],
                ['name' => 'Core Counseling Attributes', 'percentage' => 15],
            ];

            foreach ($cat1Areas as $index => $area) {
                ExamStandardContentArea::create([
                    'category_id' => $cat1->id,
                    'name' => $area['name'],
                    'percentage' => $area['percentage'],
                    'order_no' => $index + 1,
                ]);
            }

            // Category 2: CACREP Areas
            $cat2 = ExamStandardCategory::create([
                'exam_standard_id' => $standard->id,
                'name' => 'CACREP Areas',
                'category_number' => 2,
            ]);

            $cat2Areas = [
                ['name' => 'Professional Counseling Orientation and Ethical Practice', 'percentage' => 12],
                ['name' => 'Social and Cultural Diversity', 'percentage' => 3],
                ['name' => 'Human Growth and Development', 'percentage' => 3],
                ['name' => 'Career Development', 'percentage' => 0],
                ['name' => 'Counseling and Helping Relationships', 'percentage' => 61],
                ['name' => 'Group Counseling and Group Work', 'percentage' => 3],
                ['name' => 'Assessment and Testing', 'percentage' => 17],
                ['name' => 'Research and Program Evaluation', 'percentage' => 1],
            ];

            foreach ($cat2Areas as $index => $area) {
                ExamStandardContentArea::create([
                    'category_id' => $cat2->id,
                    'name' => $area['name'],
                    'percentage' => $area['percentage'],
                    'order_no' => $index + 1,
                ]);
            }

            // 3. Create Exam
            $this->command->info('Creating NCMHCE Practice Exam...');

            // Get a category ID for the exam (generic)
            $examCat = \App\Models\ExamCategory::firstOrCreate(['name' => 'NCMHCE']);

            $exam = Exam::create([
                'name' => 'NCMHCE Practice Exam 1',
                'exam_code' => 'NCMH26-001',
                'category_id' => $examCat->id,
                'certification_type' => 'NCMHCE',
                'description' => 'Full-length practice exam with separate sections for Domains and CACREP.',
                'duration_minutes' => 240, 
                'exam_standard_id' => $standard->id,
                'total_questions' => 200, // 100% of Domains (100) + 100% of CACREP (100)
                'passing_score_overall' => 65,
                'status' => 1,
                'is_active' => 1,
            ]);

            // 4. Set Passing Scores
            ExamCategoryPassingScore::create(['exam_id' => $exam->id, 'exam_standard_category_id' => $cat1->id, 'passing_score' => 60]);
            ExamCategoryPassingScore::create(['exam_id' => $exam->id, 'exam_standard_category_id' => $cat2->id, 'passing_score' => 60]);

            // 5. Create Content Structure & Questions
            
            // --- SECTION 1: DOMAINS ---
            $section1 = Section::create([
                'exam_id' => $exam->id,
                'exam_standard_category_id' => $cat1->id,
                'title' => 'Domain-Based Questions',
                'order_no' => 1,
                'status' => 1,
            ]);

            $cs1 = CaseStudy::create([
                'section_id' => $section1->id,
                'title' => 'Domain Case Study',
                'content' => '<p>Questions in this section belong to Counselor Work Behavior Areas.</p>',
                'order_no' => 1,
                'status' => 1,
            ]);

            foreach ($cat1->contentAreas as $area) {
                for ($i = 0; $i < $area->percentage; $i++) {
                    $q = Question::create([
                        'case_study_id' => $cs1->id,
                        'content_area_id' => $area->id,
                        'question_text' => "<p>Domain Question: [{$area->name}] #$i</p>",
                        'question_type' => 'single',
                        'status' => 1,
                    ]);
                    QuestionOption::create(['question_id' => $q->id, 'option_text' => 'Correct', 'is_correct' => 1, 'option_key' => 'A']);
                    QuestionOption::create(['question_id' => $q->id, 'option_text' => 'Wrong', 'is_correct' => 0, 'option_key' => 'B']);
                }
            }

            // --- SECTION 2: CACREP ---
            $section2 = Section::create([
                'exam_id' => $exam->id,
                'exam_standard_category_id' => $cat2->id,
                'title' => 'CACREP-Based Questions',
                'order_no' => 2,
                'status' => 1,
            ]);

            $cs2 = CaseStudy::create([
                'section_id' => $section2->id,
                'title' => 'CACREP Case Study',
                'content' => '<p>Questions in this section belong to CACREP Areas.</p>',
                'order_no' => 1,
                'status' => 1,
            ]);

            foreach ($cat2->contentAreas as $area) {
                for ($i = 0; $i < $area->percentage; $i++) {
                    $q = Question::create([
                        'case_study_id' => $cs2->id,
                        'content_area_id' => $area->id,
                        'question_text' => "<p>CACREP Question: [{$area->name}] #$i</p>",
                        'question_type' => 'single',
                        'status' => 1,
                    ]);
                    QuestionOption::create(['question_id' => $q->id, 'option_text' => 'Correct', 'is_correct' => 1, 'option_key' => 'A']);
                    QuestionOption::create(['question_id' => $q->id, 'option_text' => 'Wrong', 'is_correct' => 0, 'option_key' => 'B']);
                }
            }

            $this->command->info('Seeding completed successfully!');
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error($e->getMessage());
        }
    }
}
