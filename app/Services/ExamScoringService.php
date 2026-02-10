<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\ExamCategoryPassingScore;
use App\Models\Question;

class ExamScoringService
{
    /**
     * Calculate scores for an exam attempt
     * Dynamic scoring based on Exam Standard Categories and Question Tags
     * 
     * @param int $attemptId
     * @return array
     */
    public function calculateScore($attemptId)
    {
        $attempt = ExamAttempt::with(['exam.examStandard.categories', 'exam.categoryPassingScores'])->findOrFail($attemptId);
        $exam = $attempt->exam;
        
        // Data structures for reporting
        $overallStats = [
            'total_questions' => 0,
            'max_points' => 0,
            'earned_points' => 0,
            'percentage' => 0,
            'is_passed' => false,
        ];

        // Dynamic Categories: [category_id => ['name' => '', 'max' => 0, 'earned' => 0, 'passed' => bool]]
        $categoryStats = [];
        $passingThresholds = [];

        // 1. Initialize Categories from Standard
        if ($exam->examStandard && $exam->examStandard->categories) {
            foreach ($exam->examStandard->categories as $cat) {
                $categoryStats[$cat->id] = [
                    'id' => $cat->id,
                    'name' => $cat->name, // e.g. "Counselor Work Behavior Areas"
                    'max_points' => 0,
                    'earned_points' => 0,
                    'percentage' => 0,
                ];

                // Get passing requirement for this category (or default 65)
                $threshold = $exam->categoryPassingScores->where('exam_standard_category_id', $cat->id)->first();
                $passingThresholds[$cat->id] = $threshold ? $threshold->passing_score : ($exam->passing_score_overall ?? 65);
            }
        } else {
            // Fallback for exams with no standard: Create a "General" category
            $categoryStats['general'] = [
                'id' => 'general',
                'name' => 'General Knowledge',
                'max_points' => 0,
                'earned_points' => 0,
                'percentage' => 0,
            ];
            $passingThresholds['general'] = $exam->passing_score_overall ?? 65;
        }

        // 2. Process all answers
        // Eager load: question tags (to find category), question options (to check correctness)
        $answers = $attempt->answers()->with(['question.tags', 'question.options'])->get();

        foreach ($answers as $answer) {
            $question = $answer->question;
            $overallStats['total_questions']++;

            // Calculate points earned for this single question
            // Logic: (Correctness Multiplier 0 or 1) * (Max Question Points)
            $correctnessMultiplier = $this->calculateCorrectness($answer, $question);
            $questionMaxPoints = $question->max_question_points ?? 1;
            
            // Allow for logic where question might just have points = 1 if not set
            if ($questionMaxPoints < 1) $questionMaxPoints = 1;

            $pointsEarned = $correctnessMultiplier * $questionMaxPoints;

            // Update Answer Record
            $answer->update([
                'is_correct' => $correctnessMultiplier > 0 ? 1 : 0,
                'score' => $pointsEarned // We might need to add a generic 'score' column if ig_score/dm_score are deprecated, but for now we can sum them or utilize existing columns if needed. 
                // However, the prompt implies a shift. Let's assume we leverage the existing structure as best as possible or just calculate here.
                // NOTE: Detailed per-question score storage might require schema update if strict tracking needed.
                // for now, we just update is_correct.
            ]);
            
            // 3. Distribute to Categories
            if ($exam->examStandard) {
                // Find which categories this question belongs to via Tags
                // A question might handle multiple Content Areas, hence multiple Categories.
                // We add the points to EACH category it hits.
                $hitCategories = [];
                if ($question->tags) {
                    foreach ($question->tags as $tag) {
                        if (isset($categoryStats[$tag->score_category_id])) {
                            $hitCategories[$tag->score_category_id] = true;
                        }
                    }
                }
                
                // If question has no tags but exam has standard, where does it go?
                // It falls into a "Uncategorized" bucket or we skip category stats for it.
                // Validated exams shouldn't have this.
                foreach (array_keys($hitCategories) as $catId) {
                    $categoryStats[$catId]['max_points'] += $questionMaxPoints;
                    $categoryStats[$catId]['earned_points'] += $pointsEarned;
                }
            } else {
                // No standard -> General Category
                $categoryStats['general']['max_points'] += $questionMaxPoints;
                $categoryStats['general']['earned_points'] += $pointsEarned;
            }

            // Update Overall Stats
            $overallStats['max_points'] += $questionMaxPoints;
            $overallStats['earned_points'] += $pointsEarned;
        }

        // 4. Calculate Final Percentages and Pass/Fail
        $allCategoriesPassed = true;

        foreach ($categoryStats as $catId => &$stat) {
            if ($stat['max_points'] > 0) {
                $stat['percentage'] = round(($stat['earned_points'] / $stat['max_points']) * 100, 2);
            } else {
                $stat['percentage'] = 0;
            }

            // Check Category Pass/Fail
            $threshold = $passingThresholds[$catId] ?? 65;
            $stat['passed'] = $stat['percentage'] >= $threshold;
            
            if (!$stat['passed']) {
                $allCategoriesPassed = false;
            }
        }

        // Overall Percentage
        if ($overallStats['max_points'] > 0) {
            $overallStats['percentage'] = round(($overallStats['earned_points'] / $overallStats['max_points']) * 100, 2);
        }

        // Final Exam Pass Logic
        // Rule: Must pass Overall Threshold AND (Optionally) All Categories?
        // Usually, simplified: Overall Score >= Overall Passing Score
        // OR: Must pass every domain.
        // User's previous request implied "65% in BOTH groups", so likely "All Categories Must Pass".
        // Let's stick to "All Categories Must Pass" if categories exist, plus overall check.
        
        $overallThreshold = $exam->passing_score_overall ?? 65;
        $overallPassed = $overallStats['percentage'] >= $overallThreshold;

        // Final verdict: Strict (All Categories + Overall) or Just Overall?
        // Given "Points ka khel" (Game of points), usually domain passing is required.
        $finalPassed = $allCategoriesPassed && $overallPassed;

        return [
            'total_score' => $overallStats['percentage'], // Overall %
            'earned_points' => $overallStats['earned_points'],
            'max_points' => $overallStats['max_points'],
            'is_passed' => $finalPassed,
            'category_breakdown' => $categoryStats, // Full breakdown for UI
        ];
    }

    /**
     * Determine correctness multiplier (1 or 0)
     * 
     * @param \App\Models\AttemptAnswer $answer
     * @param \App\Models\Question $question
     * @return int 1 or 0
     */
    private function calculateCorrectness($answer, $question)
    {
        $selectedOptions = $answer->selected_options;
        
        if (is_string($selectedOptions)) {
             $selectedOptions = json_decode($selectedOptions, true);
        }
        
        if (empty($selectedOptions) || !is_array($selectedOptions)) {
            return 0; 
        }

        $correctOptions = $question->options->where('is_correct', 1)->pluck('option_text')->toArray();
        $incorrectOptions = $question->options->where('is_correct', 0)->pluck('option_text')->toArray();

        // Rule: Any wrong option selected = 0
        if (count(array_intersect($selectedOptions, $incorrectOptions)) > 0) {
            return 0;
        }

        // Rule: At least one correct option selected = 1 (Partial Correctness / Safety Net)
        // Strict Rule would be: count(intersect) == count(correctOptions)
        // Sticking to previous lenient rule:
        if (count(array_intersect($selectedOptions, $correctOptions)) > 0) {
            return 1;
        }

        return 0;
    }
}
