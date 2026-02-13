<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Exam;
use App\Models\Section;
use App\Models\CaseStudy;
use App\Models\Visit;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTag;
use App\Models\ExamStandard;
use App\Models\ContentArea;
use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\StudentExam;

class GenerateStandardExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Clear Existing Data
        $this->command->info('Clearing existing exam data...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate relevant tables including student attempts
        if (class_exists(AttemptAnswer::class)) AttemptAnswer::truncate();
        if (class_exists(Attempt::class)) Attempt::truncate();
        if (class_exists(StudentExam::class)) StudentExam::truncate();

        QuestionTag::truncate();
        QuestionOption::truncate();
        Question::truncate();
        Visit::truncate();
        CaseStudy::truncate();
        Section::truncate();
        Exam::truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Load Standard
        $standardId = 1;
        $standard = ExamStandard::with('categories.contentAreas')->find($standardId);

        if (!$standard) {
            $this->command->error("Exam Standard with ID $standardId not found!");
            return;
        }

        $this->command->info("Loaded Standard: {$standard->name}");

        // 3. Prepare Point Buckets for Distribution
        // We assume each question is 1 point.
        $categoryBuckets = [];
        
        foreach ($standard->categories as $category) {
            $areas = [];
            foreach ($category->contentAreas as $area) {
                if ($area->max_points > 0) {
                     // We use 'max_points' as the target count of questions
                     for ($i = 0; $i < $area->max_points; $i++) {
                         $areas[] = $area->id;
                     }
                }
            }
            shuffle($areas);
            $categoryBuckets[$category->id] = $areas;
        }

        // 4. Create Exam
        $exam = Exam::create([
            'name'  => 'Counselor Exam (Standard Compliant)',
            'description' => 'Automatically generated exam following Counselor Blueprint.',
            'exam_standard_id' => $standard->id,
            'is_active' => 1,
            'status' => 1,
            'duration_minutes' => 240, 
            'certification_type' => 'Counselor',
            'total_questions' => 0, // Will update later
            'passing_score_overall' => 70,
        ]);
        
        // 5. Create Structure (11 Sections -> 1 Case Study -> 3 Visits)
        // Total Visits = 33.
        $allVisits = [];

        for ($s = 1; $s <= 11; $s++) {
            $section = Section::create([
                'exam_id' => $exam->id,
                'title' => "Section $s",
                'content' => "Section $s description",
                'order_no' => $s,
                'status' => 1,
            ]);

            $caseStudy = CaseStudy::create([
                'section_id' => $section->id,
                'title' => "Case Study $s",
                'content' => "Scenario for Case Study $s...",
                'order_no' => 1,
                'status' => 1,
            ]);

            for ($v = 1; $v <= 3; $v++) {
                $visit = Visit::create([
                    'case_study_id' => $caseStudy->id,
                    'title' => "Visit $v of CS $s",
                    'description' => "Description for visit $v...",
                    'order_no' => $v,
                    'status' => 1,
                ]);
                $allVisits[] = $visit;
            }
        }

        $this->command->info("Created Structure: 1 Exam, 11 Sections, 11 Case Studies, " . count($allVisits) . " Visits.");

        // 6. Create Questions and Distribute
        $maxQuestions = 0;
        foreach ($categoryBuckets as $catId => $areas) {
            $count = count($areas);
            if ($count > $maxQuestions) $maxQuestions = $count;
        }

        $this->command->info("Plan to create $maxQuestions questions to meet standard requirements.");

        $this->command->info("Starting Question Loop for $maxQuestions questions...");
        
        try {
            for ($qIndex = 0; $qIndex < $maxQuestions; $qIndex++) {
                // Cyclical assignment to visits
                if (count($allVisits) === 0) {
                    $this->command->error("No visits created!");
                    break;
                }
                $visit = $allVisits[$qIndex % count($allVisits)];

                $question = Question::create([
                    'visit_id' => $visit->id,
                    'question_text' => "Standard Question " . ($qIndex + 1) . " - Testing consistency.",
                    'question_type' => 'single',
                    'max_question_points' => 1, // 1 point per question
                    'status' => 1,
                ]);

                // Create Options
                QuestionOption::create(['question_id' => $question->id, 'option_key' => 'A', 'option_text' => 'Correct Answer', 'is_correct' => 1]);
                QuestionOption::create(['question_id' => $question->id, 'option_key' => 'B', 'option_text' => 'Distractor 1', 'is_correct' => 0]);
                QuestionOption::create(['question_id' => $question->id, 'option_key' => 'C', 'option_text' => 'Distractor 2', 'is_correct' => 0]);
                QuestionOption::create(['question_id' => $question->id, 'option_key' => 'D', 'option_text' => 'Distractor 3', 'is_correct' => 0]);

                // Assign Tags for EACH Category
                foreach ($categoryBuckets as $catId => &$areas) {
                   if (!empty($areas)) {
                        $areaId = array_pop($areas); // Take one needed area
                    
                        QuestionTag::create([
                            'question_id' => $question->id,
                            'score_category_id' => $catId,
                            'content_area_id' => $areaId,
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->command->error("CRITICAL ERROR creating question $qIndex: " . $e->getMessage());
            // Do not rethrow, just log and exit gracefully or continue
            return;
        }
        
        $exam->update(['total_questions' => $maxQuestions]);

        $this->command->info('Exam Generation Completed Successfully.');
    }
}
