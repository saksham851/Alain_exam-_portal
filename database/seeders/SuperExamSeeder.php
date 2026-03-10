<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exam;
use App\Models\Section;
use App\Models\CaseStudy;
use App\Models\Visit;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTag;
use App\Models\ExamStandard;
use App\Models\ScoreCategory;
use App\Models\ContentArea;
use App\Models\ExamCategory;
use Illuminate\Support\Facades\DB;

class SuperExamSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();
        try {
            // 1. Get or Create the Standard
            $standard = ExamStandard::where('name', 'Counselor Exam Blueprint')->first();
            if (!$standard) {
                $standard = ExamStandard::create([
                    'name' => 'Counselor Exam Blueprint',
                    'description' => 'Blueprint for Counselor Work Behavior Areas and CACREP Areas',
                ]);

                $domainCategory = ScoreCategory::create([
                    'exam_standard_id' => $standard->id,
                    'name' => 'Counselor Work Behavior Areas',
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
            }

            $domainCat = ScoreCategory::where('exam_standard_id', $standard->id)->where('category_number', 1)->first();
            $cacrepCat = ScoreCategory::where('exam_standard_id', $standard->id)->where('category_number', 2)->first();

            $domainContentAreas = ContentArea::where('score_category_id', $domainCat->id)->get();
            $cacrepContentAreas = ContentArea::where('score_category_id', $cacrepCat->id)->get();

            // Prepare exactly 100 tag pairs to fulfill the standard
            $domainTagPool = [];
            foreach ($domainContentAreas as $area) {
                for ($i = 0; $i < $area->max_points; $i++) {
                    $domainTagPool[] = $area->id;
                }
            }
            // Domain total is 100 points

            $cacrepTagPool = [];
            foreach ($cacrepContentAreas as $area) {
                if ($area->max_points > 0) {
                    for ($i = 0; $i < $area->max_points; $i++) {
                        $cacrepTagPool[] = $area->id;
                    }
                }
            }
            // CACREP total is 100 points

            // Shuffle to distribute randomly but keep the sums correct
            shuffle($domainTagPool);
            shuffle($cacrepTagPool);

            // 2. Create Exam
            $category = ExamCategory::firstOrCreate(['name' => 'Counseling'], ['status' => 1]);
            
            $examName = 'Certified Counselor Examination (100% Compliant)';
            // Delete old ones to avoid confusion
            Exam::where('name', $examName)->delete();

            $exam = Exam::create([
                'name' => $examName,
                'exam_code' => 'NCMHCE-PRO-' . time(),
                'category_id' => $category->id,
                'certification_type' => 'Professional',
                'description' => 'A highly structured examination with 11 sections. Every question is meticulously tagged to fulfill the Counselor Exam Blueprint requirements exactly.',
                'duration_minutes' => 180,
                'total_questions' => 100, 
                'exam_standard_id' => $standard->id,
                'passing_score_overall' => 65,
                'status' => 1,
                'is_active' => 0
            ]);

            $this->command->info("Creating Exam: {$exam->name}");

            // 3. Create 11 Sections
            $sections = [
                "Clinical Assessment & Diagnosis",
                "Ethics and Legal Standards",
                "Counseling Theories & Practice",
                "Human Growth & Development",
                "Social & Cultural foundations",
                "Helping Relationships",
                "Group Work & Dynamics",
                "Career & Lifestyle Development",
                "Appraisal & Testing",
                "Research & Evaluation",
                "Professional Orientation"
            ];

            $qCounter = 0;
            foreach ($sections as $sIndex => $sTitle) {
                $section = Section::create([
                    'exam_id' => $exam->id,
                    'title' => "Section " . ($sIndex + 1) . ": " . $sTitle,
                    'content' => "Welcome to Section " . ($sIndex + 1) . ". This portion focuses on '" . $sTitle . "' which is critical for clinical proficiency.",
                    'order_no' => $sIndex + 1,
                    'status' => 1
                ]);

                // Usually 9 questions per section, but we need 100 total.
                // 11 * 9 = 99. We add 1 more to the last section.
                $qsInSection = ($sIndex == 10) ? 10 : 9;
                
                // We need 3 Case Studies per section (total 33)
                // 3 questions per case study is 9. For the last section one CS will have 4 questions.
                for ($csIndex = 1; $csIndex <= 3; $csIndex++) {
                    $caseStudy = CaseStudy::create([
                        'section_id' => $section->id,
                        'title' => "Case Analysis " . ($sIndex + 1) . "." . $csIndex,
                        'content' => "Patient presents with issues specifically related to " . strtolower($sTitle) . ". Clinical history shows moderate distress and a need for targeted intervention.",
                        'order_no' => $csIndex,
                        'status' => 1
                    ]);

                    // Visits - We'll ensure total visits match the questions needed
                    for ($vIndex = 1; $vIndex <= 3; $vIndex++) {
                        // For the very last case study in the last section, we add a 4th visit to get 100 total questions
                        if ($sIndex == 10 && $csIndex == 3 && $vIndex == 3) {
                            $specialVisitsCount = 2; // (3rd and 4th)
                        } else {
                            $specialVisitsCount = 1;
                        }

                        for ($extra = 0; $extra < $specialVisitsCount; $extra++) {
                            $currentVNum = $vIndex + $extra;
                            $visit = Visit::create([
                                'case_study_id' => $caseStudy->id,
                                'title' => "Visit #" . $currentVNum,
                                'description' => "Detailed clinical interaction for Visit #" . $currentVNum . ". Assessment data gathered indicates specific progression in the case.",
                                'order_no' => $currentVNum,
                                'status' => 1
                            ]);

                            // Create Question
                            $q = Question::create([
                                'visit_id' => $visit->id,
                                'question_text' => "Considering the progression in Visit " . $currentVNum . ", which clinical decision demonstrates the highest fidelity to '" . $sTitle . "' standards?",
                                'question_type' => 'single',
                                'max_question_points' => 1,
                                'status' => 1
                            ]);

                            // Options
                            QuestionOption::create(['question_id' => $q->id, 'option_key' => 'A', 'option_text' => 'Follow established clinical guidelines for ' . $sTitle, 'is_correct' => 1]);
                            QuestionOption::create(['question_id' => $q->id, 'option_key' => 'B', 'option_text' => 'Bypass standard protocol for immediate relief', 'is_correct' => 0]);
                            QuestionOption::create(['question_id' => $q->id, 'option_key' => 'C', 'option_text' => 'Delegate the decision to a secondary team', 'is_correct' => 0]);
                            QuestionOption::create(['question_id' => $q->id, 'option_key' => 'D', 'option_text' => 'Wait for further symptomatic manifestation', 'is_correct' => 0]);

                            // Assign Tags from our pools
                            if (isset($domainTagPool[$qCounter])) {
                                QuestionTag::create([
                                    'question_id' => $q->id, 
                                    'score_category_id' => $domainCat->id, 
                                    'content_area_id' => $domainTagPool[$qCounter]
                                ]);
                            }
                            if (isset($cacrepTagPool[$qCounter])) {
                                QuestionTag::create([
                                    'question_id' => $q->id, 
                                    'score_category_id' => $cacrepCat->id, 
                                    'content_area_id' => $cacrepTagPool[$qCounter]
                                ]);
                            }

                            $qCounter++;
                        }
                        if ($sIndex == 10 && $csIndex == 3 && $vIndex == 3) break; // We already handled the extra question
                    }
                }
            }

            DB::commit();
            $this->command->info('Master Exam (100% COMPLIANT) seeded successfully!');
            $this->command->info('Total Questions: ' . $qCounter);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Seeding failed: ' . $e->getMessage());
            $this->command->error($e->getTraceAsString());
        }
    }
}
