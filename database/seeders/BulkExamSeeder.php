<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exam;
use App\Models\Section;
use App\Models\CaseStudy;
use App\Models\Visit;
use App\Models\ExamCategory;
use App\Models\ExamStandard;
use App\Models\ScoreCategory;
use App\Models\ContentArea;
use Illuminate\Support\Facades\DB;

class BulkExamSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // 1. Get or Create Exam Category
            $category = ExamCategory::firstOrCreate(['name' => 'Professional Licensing'], ['status' => 1]);

            // 2. Create Exam Standard (Matching the Screenshot)
            $standard = ExamStandard::create([
                'name' => 'Counselor Exam Blueprint (Shell)',
                'description' => 'Structure for 11 Sections. Import all questions via CSV.',
            ]);

            // 3. Create Score Categories
            $scoreCat1 = ScoreCategory::create([
                'exam_standard_id' => $standard->id,
                'name' => 'Counselor Work Behavior Areas (Domains)',
                'category_number' => 1,
            ]);

            $scoreCat2 = ScoreCategory::create([
                'exam_standard_id' => $standard->id,
                'name' => 'CACREP Areas',
                'category_number' => 2,
            ]);

            // 4. Create Content Areas for Category 1
            $cat1AreasData = [
                ['name' => 'Professional Practice and Ethics', 'pts' => 15],
                ['name' => 'Intake, Assessment and Diagnosis', 'pts' => 25],
                ['name' => 'Treatment Planning Counseling Skills', 'pts' => 15],
                ['name' => 'Counseling Skills and Interventions', 'pts' => 30],
                ['name' => 'Core Counselor Attributes', 'pts' => 15],
            ];
            
            foreach ($cat1AreasData as $index => $data) {
                ContentArea::create([
                    'score_category_id' => $scoreCat1->id,
                    'name' => $data['name'],
                    'max_points' => $data['pts'],
                    'percentage' => 0, 
                    'order_no' => $index + 1,
                ]);
            }

            // 5. Create Content Areas for Category 2
            $cat2AreasData = [
                ['name' => 'Human Growth and Development', 'pts' => 10],
                ['name' => 'Social and Cultural Diversity', 'pts' => 10],
                ['name' => 'Counseling and Helping Relationships', 'pts' => 40],
                ['name' => 'Group Counseling and Group Work', 'pts' => 10],
                ['name' => 'Assessment and Testing', 'pts' => 20],
                ['name' => 'Research and Program Evaluation', 'pts' => 10],
            ];
            
            foreach ($cat2AreasData as $index => $data) {
                ContentArea::create([
                    'score_category_id' => $scoreCat2->id,
                    'name' => $data['name'],
                    'max_points' => $data['pts'],
                    'percentage' => 0,
                    'order_no' => $index + 1,
                ]);
            }

            // 6. Create the Exam
            $exam = Exam::create([
                'category_id' => $category->id,
                'exam_code' => 'NCMHCE-SHELL',
                'name' => 'Empty Exam for CSV Import (11 Sections)',
                'description' => 'This exam contains 11 empty sections. Use CSV to import all questions.',
                'certification_type' => 'NCMHCE',
                'duration_minutes' => 240,
                'exam_standard_id' => $standard->id,
                'total_questions' => 100, 
                'passing_score_overall' => 65,
                'status' => 1,
                'is_active' => 0,
            ]);

            // 7. Create 11 Sections + Case Studies + Visits (NO QUESTIONS)
            for ($i = 1; $i <= 11; $i++) {
                $section = Section::create([
                    'exam_id' => $exam->id,
                    'title' => "Section $i: " . $this->getSectionName($i),
                    'content' => "Section description for " . $this->getSectionName($i),
                    'order_no' => $i,
                    'status' => 1,
                    'exam_standard_category_id' => $scoreCat1->id, 
                ]);

                $caseStudy = CaseStudy::create([
                    'section_id' => $section->id,
                    'title' => "Case Study for Section $i",
                    'content' => "<p>Clinical data for " . $this->getSectionName($i) . "</p>",
                    'order_no' => 1,
                    'status' => 1,
                ]);

                Visit::create([
                    'case_study_id' => $caseStudy->id,
                    'title' => "Visit 1",
                    'order_no' => 1,
                    'status' => 1,
                ]);
            }

            DB::commit();
            $this->command->info('Successfully seeded Shell (0 Questions)!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Seeding failed: ' . $e->getMessage());
        }
    }

    private function getSectionName($i) {
        $names = [
            'Foundations of Counseling', 'Clinical Assessment', 'DSM-5 Diagnosis', 
            'Treatment Planning', 'Cognitive Interventions', 'Ethical Standards',
            'Multicultural Issues', 'Lifespan Development', 'Research and Evaluation',
            'Group Process', 'Career Transitions'
        ];
        return $names[$i - 1] ?? "Module $i";
    }
}
