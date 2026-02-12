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

        // Dynamic Categories and Content Areas
        $categoryStats = [];
        $contentAreaStats = [];
        $passingThresholds = [];

        // 1. Initialize Categories and Content Areas from Standard
        if ($exam->examStandard && $exam->examStandard->categories) {
            foreach ($exam->examStandard->categories as $cat) {
                $categoryStats[$cat->id] = [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'max_points' => 0,
                    'earned_points' => 0,
                    'percentage' => 0,
                ];

                // Get passing requirement for this category
                $threshold = $exam->categoryPassingScores->where('exam_standard_category_id', $cat->id)->first();
                $passingThresholds[$cat->id] = $threshold ? $threshold->passing_score : ($exam->passing_score_overall ?? 65);

                // Initialize Content Areas
                foreach ($cat->contentAreas as $area) {
                    $contentAreaStats[$area->id] = [
                        'id' => $area->id,
                        'name' => $area->name,
                        'category_id' => $cat->id,
                        'max_points' => 0,
                        'earned_points' => 0,
                        'percentage' => 0,
                    ];
                }
            }
        } else {
            // Fallback for exams with no standard
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
        $answers = $attempt->answers()->with(['question.tags', 'question.options'])->get();

        foreach ($answers as $answer) {
            $question = $answer->question;
            $overallStats['total_questions']++;

            $correctnessMultiplier = $this->calculateCorrectness($answer, $question);
            $questionMaxPoints = $question->max_question_points ?? 1;
            if ($questionMaxPoints < 1) $questionMaxPoints = 1;

            $pointsEarned = $correctnessMultiplier * $questionMaxPoints;

            $answer->update([
                'is_correct' => $correctnessMultiplier > 0 ? 1 : 0,
                'score' => $pointsEarned
            ]);
            
            // 3. Distribute to Categories and Content Areas
            if ($exam->examStandard) {
                $hitCategories = [];
                $hitContentAreas = [];
                
                if ($question->tags) {
                    foreach ($question->tags as $tag) {
                        // Mark Category Hit
                        if (isset($categoryStats[$tag->score_category_id])) {
                            $hitCategories[$tag->score_category_id] = true;
                        }
                        // Mark Content Area Hit
                        if (isset($contentAreaStats[$tag->content_area_id])) {
                            $hitContentAreas[$tag->content_area_id] = true;
                        }
                    }
                }
                
                // Add points to EACH hit category
                foreach (array_keys($hitCategories) as $catId) {
                    $categoryStats[$catId]['max_points'] += $questionMaxPoints;
                    $categoryStats[$catId]['earned_points'] += $pointsEarned;
                }

                // Add points to EACH hit content area
                foreach (array_keys($hitContentAreas) as $areaId) {
                    $contentAreaStats[$areaId]['max_points'] += $questionMaxPoints;
                    $contentAreaStats[$areaId]['earned_points'] += $pointsEarned;
                }
            } else {
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

            // Check Category Pass/Fail (Threshold is now points)
            $threshold = $passingThresholds[$catId] ?? 65;
            $stat['threshold_points'] = $threshold; // Store for UI
            $stat['passed'] = $stat['earned_points'] >= $threshold;
            
            if (!$stat['passed']) {
                $allCategoriesPassed = false;
            }
        }

        foreach ($contentAreaStats as $areaId => &$stat) {
            if ($stat['max_points'] > 0) {
                $stat['percentage'] = round(($stat['earned_points'] / $stat['max_points']) * 100, 2);
            } else {
                $stat['percentage'] = 0;
            }
        }

        // Overall Percentage
        if ($overallStats['max_points'] > 0) {
            $overallStats['percentage'] = round(($overallStats['earned_points'] / $overallStats['max_points']) * 100, 2);
        }

        // Final Exam Pass Logic
        // Rule: Overall Points Earned >= Overall Passing Score (points)
        $overallThresholdPoints = $exam->passing_score_overall ?? 65;
        
        $overallPassed = $overallStats['earned_points'] >= $overallThresholdPoints;
        $finalPassed = $allCategoriesPassed && $overallPassed;

        return [
            'total_score' => $overallStats['earned_points'], // Primary score is now Points
            'earned_points' => $overallStats['earned_points'],
            'max_points' => $overallStats['max_points'],
            'percentage' => $overallStats['percentage'],
            'is_passed' => $finalPassed,
            'category_breakdown' => $categoryStats, 
            'content_area_breakdown' => $contentAreaStats,
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
