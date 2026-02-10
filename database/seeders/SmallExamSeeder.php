<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Exam;
use App\Models\ExamStandard;
use App\Models\ExamStandardCategory;
use App\Models\ExamStandardContentArea;
use App\Models\Section;
use App\Models\CaseStudy;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\ExamCategory;

class SmallExamSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();
        try {
            $this->command->info("Creating 100-Question Practice Exam...");

            // 1. Get or create exam category
            $examCategory = ExamCategory::firstOrCreate(
                ['name' => 'NCMHCE Practice'],
                ['status' => 1]
            );

            // 2. Create a simple standard for 100 questions
            $standard = ExamStandard::create([
                'name' => 'NCMHCE 100Q Standard',
                'description' => '100-question practice exam standard',
            ]);

            // 3. Category 1: Work Behavior (50% = 50 questions)
            $cat1 = ExamStandardCategory::create([
                'exam_standard_id' => $standard->id,
                'name' => 'Work Behavior Areas',
                'category_number' => 1,
            ]);
            
            $cat1_areas = [
                ['name' => 'Professional Practice', 'percentage' => 20],  // 20 questions
                ['name' => 'Assessment & Diagnosis', 'percentage' => 30], // 30 questions
            ];

            $cat1ContentAreas = [];
            foreach ($cat1_areas as $index => $area) {
                $cat1ContentAreas[] = ExamStandardContentArea::create([
                    'category_id' => $cat1->id,
                    'name' => $area['name'],
                    'percentage' => $area['percentage'],
                    'order_no' => $index + 1
                ]);
            }

            // 4. Category 2: CACREP (50% = 50 questions)
            $cat2 = ExamStandardCategory::create([
                'exam_standard_id' => $standard->id,
                'name' => 'CACREP Areas',
                'category_number' => 2,
            ]);

            $cat2_areas = [
                ['name' => 'Counseling Relationships', 'percentage' => 30], // 30 questions
                ['name' => 'Assessment & Testing', 'percentage' => 20],     // 20 questions
            ];

            $cat2ContentAreas = [];
            foreach ($cat2_areas as $index => $area) {
                $cat2ContentAreas[] = ExamStandardContentArea::create([
                    'category_id' => $cat2->id,
                    'name' => $area['name'],
                    'percentage' => $area['percentage'],
                    'order_no' => $index + 1
                ]);
            }

            // 5. Create Exam (100 questions)
            $exam = Exam::create([
                'category_id' => $examCategory->id,
                'exam_code' => 'DEMO-' . rand(1000,9999),
                'name' => 'NCMHCE Demo Exam 100Q',
                'description' => 'Demo exam with 100 questions following standard distribution.',
                'duration_minutes' => 120,
                'exam_standard_id' => $standard->id,
                'total_questions' => 100,
                'passing_score_overall' => 65,
                'passing_score_category_1' => 65,
                'passing_score_category_2' => 65,
                'certification_type' => 'NCMHCE',
                'status' => 1,
                'is_active' => 0,
            ]);

            // 6. Create Questions Distribution
            $distribution = [
                ['area' => $cat1ContentAreas[0], 'count' => 20, 'name' => 'Professional Practice'],
                ['area' => $cat1ContentAreas[1], 'count' => 30, 'name' => 'Assessment & Diagnosis'],
                ['area' => $cat2ContentAreas[0], 'count' => 30, 'name' => 'Counseling Relationships'],
                ['area' => $cat2ContentAreas[1], 'count' => 20, 'name' => 'Assessment & Testing'],
            ];

            $sectionOrder = 1;
            $totalQuestionsCreated = 0;

            foreach ($distribution as $dist) {
                // Create 1 section per content area
                $section = Section::create([
                    'exam_id' => $exam->id,
                    'title' => "Section {$sectionOrder}: {$dist['name']}",
                    'content' => '<p>This section covers ' . $dist['name'] . '</p>',
                    'order_no' => $sectionOrder,
                    'status' => 1
                ]);
                $sectionOrder++;

                // Create 2-3 case studies per section
                $caseStudiesCount = 2;
                $questionsPerCaseStudy = ceil($dist['count'] / $caseStudiesCount);

                for ($cs = 0; $cs < $caseStudiesCount; $cs++) {
                    $caseStudy = CaseStudy::create([
                        'section_id' => $section->id,
                        'title' => "Case Study " . ($cs + 1),
                        'content' => '<p>Client scenario for ' . $dist['name'] . '...</p>',
                        'order_no' => $cs + 1,
                        'status' => 1
                    ]);

                    // Add questions to this case study
                    $questionsToAdd = min($questionsPerCaseStudy, $dist['count'] - ($cs * $questionsPerCaseStudy));
                    
                    for ($q = 0; $q < $questionsToAdd && $totalQuestionsCreated < 100; $q++) {
                        $totalQuestionsCreated++;
                        
                        $question = Question::create([
                            'case_study_id' => $caseStudy->id,
                            'question_text' => "Q{$totalQuestionsCreated} ({$dist['name']}): What is the most appropriate intervention?",
                            'question_type' => 'single',
                            'ig_weight' => 1,
                            'dm_weight' => 2,
                            'content_area_1_id' => $dist['area']->id,
                            'content_area_2_id' => null,
                            'status' => 1
                        ]);

                        // Add options
                        QuestionOption::create(['question_id' => $question->id, 'option_text' => 'Correct answer for ' . $dist['name'], 'is_correct' => 1, 'option_key' => 'a']);
                        QuestionOption::create(['question_id' => $question->id, 'option_text' => 'Incorrect option 1', 'is_correct' => 0, 'option_key' => 'b']);
                        QuestionOption::create(['question_id' => $question->id, 'option_text' => 'Incorrect option 2', 'is_correct' => 0, 'option_key' => 'c']);
                        QuestionOption::create(['question_id' => $question->id, 'option_text' => 'Incorrect option 3', 'is_correct' => 0, 'option_key' => 'd']);
                    }
                }
            }

            DB::commit();
            
            $this->command->info("✅ SUCCESS! Created '{$exam->name}'");
            $this->command->info("   📊 Sections: " . ($sectionOrder - 1));
            $this->command->info("   📝 Questions: {$totalQuestionsCreated}");
            $this->command->info("   🎯 Standard: {$standard->name}");
            $this->command->info("   ✨ Distribution:");
            $this->command->info("      - Professional Practice: 20 questions (20%)");
            $this->command->info("      - Assessment & Diagnosis: 30 questions (30%)");
            $this->command->info("      - Counseling Relationships: 30 questions (30%)");
            $this->command->info("      - Assessment & Testing: 20 questions (20%)");
            
        } catch (\Exception $e) {
            DB::rollBack();
            file_put_contents('seeder_error.txt', $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->command->error("❌ FAILURE: " . $e->getMessage());
        }
    }
}
