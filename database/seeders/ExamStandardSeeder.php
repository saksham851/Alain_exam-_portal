<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamStandard;
use App\Models\ExamStandardCategory;
use App\Models\ExamStandardContentArea;

class ExamStandardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create NCMHCE 2026 Exam Standard
        $standard = ExamStandard::create([
            'name' => 'NCMHCE 2026',
            'description' => 'National Clinical Mental Health Counseling Examination 2026 Standard',
        ]);

        // Category 1: Counselor Work Behavior Areas
        $category1 = ExamStandardCategory::create([
            'exam_standard_id' => $standard->id,
            'name' => 'Counselor Work Behavior Areas',
            'category_number' => 1,
        ]);

        // Category 1 Content Areas
        $cat1Areas = [
            ['name' => 'Professional Practice & Ethics', 'percentage' => 15, 'order_no' => 1],
            ['name' => 'Intake, Assessment & Diagnosis', 'percentage' => 25, 'order_no' => 2],
            ['name' => 'Treatment Planning', 'percentage' => 15, 'order_no' => 3],
            ['name' => 'Counseling Skills & Interventions', 'percentage' => 30, 'order_no' => 4],
            ['name' => 'Core Counselor Attributes', 'percentage' => 15, 'order_no' => 5],
        ];

        foreach ($cat1Areas as $area) {
            ExamStandardContentArea::create([
                'category_id' => $category1->id,
                'name' => $area['name'],
                'percentage' => $area['percentage'],
                'order_no' => $area['order_no'],
            ]);
        }

        // Category 2: CACREP Areas
        $category2 = ExamStandardCategory::create([
            'exam_standard_id' => $standard->id,
            'name' => 'CACREP Areas',
            'category_number' => 2,
        ]);

        // Category 2 Content Areas
        $cat2Areas = [
            ['name' => 'Professional Counseling Orientation & Ethical Practice', 'percentage' => 12, 'order_no' => 1],
            ['name' => 'Social & Cultural Diversity', 'percentage' => 3, 'order_no' => 2],
            ['name' => 'Human Growth & Development', 'percentage' => 3, 'order_no' => 3],
            ['name' => 'Career Development', 'percentage' => 0, 'order_no' => 4], // 0% - will be skipped
            ['name' => 'Counseling & Helping Relationships', 'percentage' => 61, 'order_no' => 5],
            ['name' => 'Group Counseling & Group Work', 'percentage' => 3, 'order_no' => 6],
            ['name' => 'Assessment & Testing', 'percentage' => 17, 'order_no' => 7],
            ['name' => 'Research & Program Evaluation', 'percentage' => 1, 'order_no' => 8],
        ];

        foreach ($cat2Areas as $area) {
            // Skip 0% areas during seeding (they won't be created)
            if ($area['percentage'] > 0) {
                ExamStandardContentArea::create([
                    'category_id' => $category2->id,
                    'name' => $area['name'],
                    'percentage' => $area['percentage'],
                    'order_no' => $area['order_no'],
                ]);
            }
        }

        $this->command->info('✅ NCMHCE 2026 Exam Standard created successfully!');
        $this->command->info('   - Category 1: ' . $category1->name . ' (' . $category1->contentAreas->count() . ' content areas)');
        $this->command->info('   - Category 2: ' . $category2->name . ' (' . $category2->contentAreas->count() . ' content areas)');
    }
}
