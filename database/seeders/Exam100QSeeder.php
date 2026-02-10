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

class Exam100QSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();

        try {
            $this->command->info('Creating 100-Question Exam with Standard Enforcement...');

            // 1. Create a Standard specifically for 100 questions
            $standard = ExamStandard::create([
                'name' => 'Standard 100Q',
                'description' => 'A balanced 100-question standard with two categories.',
            ]);

            // Category 1: Clinical Domains (50%)
            $cat1 = ExamStandardCategory::create([
                'exam_standard_id' => $standard->id,
                'name' => 'Clinical Domains',
                'category_number' => 1,
            ]);

            $cat1Areas = [
                ['name' => 'Assessment', 'percentage' => 20], // 20 questions
                ['name' => 'Intervention', 'percentage' => 30], // 30 questions
            ];

            foreach ($cat1Areas as $index => $area) {
                ExamStandardContentArea::create([
                    'category_id' => $cat1->id,
                    'name' => $area['name'],
                    'percentage' => $area['percentage'],
                    'order_no' => $index + 1,
                ]);
            }

            // Category 2: Ethical Foundations (50%)
            $cat2 = ExamStandardCategory::create([
                'exam_standard_id' => $standard->id,
                'name' => 'Ethical Foundations',
                'category_number' => 2,
            ]);

            $cat2Areas = [
                ['name' => 'Legal Issues', 'percentage' => 25], // 25 questions
                ['name' => 'Ethics Code', 'percentage' => 25], // 25 questions
            ];

            foreach ($cat2Areas as $index => $area) {
                ExamStandardContentArea::create([
                    'category_id' => $cat2->id,
                    'name' => $area['name'],
                    'percentage' => $area['percentage'],
                    'order_no' => $index + 1,
                ]);
            }

            // 2. Create Exam
            $examCat = \App\Models\ExamCategory::firstOrCreate(['name' => 'General Certification']);

            $exam = Exam::create([
                'name' => '100-Question Standard Exam',
                'exam_code' => 'STD-100-001',
                'category_id' => $examCat->id,
                'certification_type' => 'Standard',
                'description' => 'A balanced exam where Category 1 (50Q) and Category 2 (50Q) are in separate sections.',
                'duration_minutes' => 120, 
                'exam_standard_id' => $standard->id,
                'total_questions' => 100, // Exact 100
                'passing_score_overall' => 70,
                'status' => 1,
                'is_active' => 1,
            ]);

            // 3. Create Sections & Questions
            
            // --- SECTION 1: CLINICAL (Assigned to Category 1) ---
            $section1 = Section::create([
                'exam_id' => $exam->id,
                'exam_standard_category_id' => $cat1->id,
                'title' => 'Section A: Clinical Practice',
                'order_no' => 1,
                'status' => 1,
            ]);

            $cs1 = CaseStudy::create([
                'section_id' => $section1->id,
                'title' => 'Clinical Case Study',
                'content' => '<p>Questions in this section belong to Clinical Domains.</p>',
                'order_no' => 1,
                'status' => 1,
            ]);

            // Create 50 questions for Category 1 based on percentages
            foreach ($cat1->contentAreas as $area) {
                for ($i = 0; $i < $area->percentage; $i++) {
                    $q = Question::create([
                        'case_study_id' => $cs1->id,
                        'content_area_id' => $area->id,
                        'question_text' => "<p>Clinical [{$area->name}] Question #$i</p>",
                        'question_type' => 'single',
                        'status' => 1,
                    ]);
                    QuestionOption::create(['question_id' => $q->id, 'option_text' => 'Correct', 'is_correct' => 1, 'option_key' => 'A']);
                    QuestionOption::create(['question_id' => $q->id, 'option_text' => 'Incorrect', 'is_correct' => 0, 'option_key' => 'B']);
                }
            }

            // --- SECTION 2: ETHICS (Assigned to Category 2) ---
            $section2 = Section::create([
                'exam_id' => $exam->id,
                'exam_standard_category_id' => $cat2->id,
                'title' => 'Section B: Ethical Codes',
                'order_no' => 2,
                'status' => 1,
            ]);

            $cs2 = CaseStudy::create([
                'section_id' => $section2->id,
                'title' => 'Ethics Case Study',
                'content' => '<p>Questions in this section belong to Ethical Foundations.</p>',
                'order_no' => 1,
                'status' => 1,
            ]);

            // Create 50 questions for Category 2 based on percentages
            foreach ($cat2->contentAreas as $area) {
                for ($i = 0; $i < $area->percentage; $i++) {
                    $q = Question::create([
                        'case_study_id' => $cs2->id,
                        'content_area_id' => $area->id,
                        'question_text' => "<p>Ethics [{$area->name}] Question #$i</p>",
                        'question_type' => 'single',
                        'status' => 1,
                    ]);
                    QuestionOption::create(['question_id' => $q->id, 'option_text' => 'Correct Option', 'is_correct' => 1, 'option_key' => 'A']);
                    QuestionOption::create(['question_id' => $q->id, 'option_text' => 'Distractor', 'is_correct' => 0, 'option_key' => 'B']);
                }
            }

            $this->command->info('Seeding of 100-question exam (50 Clinical + 50 Ethics) completed!');
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error($e->getMessage());
        }
    }
}
