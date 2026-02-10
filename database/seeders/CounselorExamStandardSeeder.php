<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamStandard;
use App\Models\ScoreCategory;
use App\Models\ContentArea;

class CounselorExamStandardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Create the Standard
        $standard = ExamStandard::create([
            'name' => 'Counselor Exam Blueprint',
            'description' => 'Blueprint for Counselor Work Behavior Areas and CACREP Areas',
        ]);

        // 2. Create Categories and Content Areas
        
        // Category 1: Counselor Work Behavior Areas (Domains)
        $domainCategory = ScoreCategory::create([
            'exam_standard_id' => $standard->id,
            'name' => 'Counselor Work Behavior Areas (Domains)',
            'category_number' => 1,
        ]);

        $domainAreas = [
            'Professional Practice and Ethics' => 15,
            'Intake, Assessment and Diagnosis' => 25,
            'Treatment Planning Counseling Skills' => 15,
            'Counseling Skills and Interventions' => 30,
            'Core Counseling Attributes' => 15,
        ];

        foreach ($domainAreas as $name => $points) {
            ContentArea::create([
                'score_category_id' => $domainCategory->id,
                'name' => $name,
                'max_points' => $points,
                'percentage' => $points,
            ]);
        }

        // Category 2: CACREP Areas
        $cacrepCategory = ScoreCategory::create([
            'exam_standard_id' => $standard->id,
            'name' => 'CACREP Areas',
            'category_number' => 2,
        ]);

        $cacrepAreas = [
            'Professional Counseling Orientation and Ethical Practice' => 12,
            'Social and Cultural Diversity' => 3,
            'Human Growth and Development' => 3,
            'Career Development' => 0,
            'Counseling and Helping Relationships' => 61,
            'Group Counseling and Group Work' => 3,
            'Assessment and Testing' => 17,
            'Research and Program Evaluation' => 1,
        ];

        foreach ($cacrepAreas as $name => $points) {
            ContentArea::create([
                'score_category_id' => $cacrepCategory->id,
                'name' => $name,
                'max_points' => $points,
                'percentage' => $points,
            ]);
        }

        $this->command->info('Counselor Exam Blueprint seeded successfully!');
    }
}
