<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionTag;
use App\Models\ScoreCategory;
use App\Models\ContentArea;
use Illuminate\Support\Facades\DB;

class RestoreQuestionTagsSeeder extends Seeder
{
    /**
     * Automatically re-tags all questions in all exams that have an exam_standard_id.
     * Tags are distributed proportionally based on standard's max_points.
     */
    public function run(): void
    {
        $exams = Exam::whereNotNull('exam_standard_id')->where('status', 1)->with([
            'examStandard.categories.contentAreas'
        ])->get();

        if ($exams->isEmpty()) {
            $this->command->warn('No exams with exam standards found.');
            return;
        }

        foreach ($exams as $exam) {
            $this->command->info("Processing Exam: {$exam->name}");

            $standard = $exam->examStandard;
            if (!$standard) {
                $this->command->warn("  No standard found, skipping.");
                continue;
            }

            // Get all active questions for this exam
            $questions = Question::whereHas('visit.caseStudy.section', function ($q) use ($exam) {
                $q->where('exam_id', $exam->id);
            })->where('status', 1)->get();

            if ($questions->isEmpty()) {
                $this->command->warn("  No questions found, skipping.");
                continue;
            }

            $this->command->info("  Found {$questions->count()} questions.");

            // Build a pool of content areas for each category
            // Sorted by how many points each area needs
            $cat1 = $standard->categories->where('category_number', 1)->first();
            $cat2 = $standard->categories->where('category_number', 2)->first();

            if (!$cat1 || !$cat2) {
                $this->command->warn("  Standard does not have both Category 1 & 2, skipping.");
                continue;
            }

            // Clear existing tags for questions in this exam first
            $questionIds = $questions->pluck('id')->toArray();
            $deletedCount = QuestionTag::whereIn('question_id', $questionIds)->delete();
            $this->command->info("  Cleared {$deletedCount} old tags.");

            // Build a weighted tag assignment pool
            // For each category, pick areas weighted by their max_points
            $assignedCount = 0;

            foreach ($questions as $question) {
                $points = $question->max_question_points ?? 1;

                // Assign to Category 1 - find the content area most in need of points
                $area1 = $this->findBestArea($cat1, $questionIds, $points);
                if ($area1) {
                    QuestionTag::create([
                        'question_id'      => $question->id,
                        'score_category_id' => $cat1->id,
                        'content_area_id'  => $area1->id,
                    ]);
                }

                // Assign to Category 2 - find the content area most in need of points
                $area2 = $this->findBestArea($cat2, $questionIds, $points);
                if ($area2) {
                    QuestionTag::create([
                        'question_id'      => $question->id,
                        'score_category_id' => $cat2->id,
                        'content_area_id'  => $area2->id,
                    ]);
                }

                $assignedCount++;
            }

            $this->command->info("  Successfully tagged {$assignedCount} questions for both categories!");
        }

        $this->command->info("\n✅ Done! All question tags have been restored.");
    }

    /**
     * Find the content area within a category that still needs the most points filled.
     */
    private function findBestArea(ScoreCategory $category, array $allQuestionIds, float $questionPoints): ?ContentArea
    {
        $bestArea = null;
        $maxDeficit = -1;

        foreach ($category->contentAreas as $area) {
            if ($area->max_points <= 0) continue;

            // Calculate points already tagged to this area from these questions
            $taggedPoints = DB::table('question_tags')
                ->join('questions', 'question_tags.question_id', '=', 'questions.id')
                ->where('question_tags.content_area_id', $area->id)
                ->whereIn('question_tags.question_id', $allQuestionIds)
                ->sum('questions.max_question_points');

            $deficit = $area->max_points - $taggedPoints;

            if ($deficit > $maxDeficit) {
                $maxDeficit = $deficit;
                $bestArea = $area;
            }
        }

        // If all areas are filled, just return area with most max_points (to cover edge cases)
        if (!$bestArea) {
            $bestArea = $category->contentAreas->where('max_points', '>', 0)->sortByDesc('max_points')->first();
        }

        return $bestArea;
    }
}
