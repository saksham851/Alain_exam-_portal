<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\Question;

class ExamScoringService
{
    /**
     * Calculate scores for an exam attempt
     * 
     * @param int $attemptId
     * @return array
     */
    public function calculateScore($attemptId)
    {
        $attempt = ExamAttempt::findOrFail($attemptId);
        
        $igScore = 0;
        $dmScore = 0;
        $igTotal = 0;
        $dmTotal = 0;
        $correctAnswers = 0;
        $totalQuestions = 0;

        // Get all answers for this attempt with question details
        $answers = $attempt->answers()->with('question.options')->get();

        foreach ($answers as $answer) {
            $question = $answer->question;
            $totalQuestions++;

            // Determine question category (assuming questions are either IG or DM based on weight)
            // If both weights > 0, it contributes to both? Typically usually one or the other.
            // Based on user prompt "two groups: IG and DM", we track them separately.
            $isIG = $question->ig_weight > 0;
            $isDM = $question->dm_weight > 0;
            
            // Fallback mostly for questions without weights if any
            if (!$isIG && !$isDM) {
                // Default to IG/General if undefined, or skip. Let's count as IG for safety if not specified.
                $isIG = true; 
            }

            // Add to category totals (1 point per question)
            if ($isIG) $igTotal++;
            if ($isDM) $dmTotal++;

            // Check if answer is correct based on rules
            $points = $this->calculatePoints($answer, $question);

            if ($points > 0) {
                $correctAnswers += $points;
                
                // Add score to appropriate category
                if ($isIG) {
                    $igScore += $points;
                    $answer->update(['ig_score' => $points, 'is_correct' => 1]);
                }
                if ($isDM) {
                    $dmScore += $points;
                    $answer->update(['dm_score' => $points, 'is_correct' => 1]);
                }
            } else {
                $answer->update(['ig_score' => 0, 'dm_score' => 0, 'is_correct' => 0]);
            }
        }

        // Calculate percentages
        $igPercentage = $igTotal > 0 ? ($igScore / $igTotal) * 100 : 0;
        $dmPercentage = $dmTotal > 0 ? ($dmScore / $dmTotal) * 100 : 0;
        $totalPercentage = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;

        // Pass condition: 65% in BOTH groups
        $isPassed = ($igPercentage >= 65) && ($dmPercentage >= 65);

        return [
            'ig_score' => round($igPercentage, 2),
            'dm_score' => round($dmPercentage, 2),
            'total_score' => round($totalPercentage, 2),
            'is_passed' => $isPassed,
        ];
    }

    /**
     * Calculate points for an answer
     * Rules:
     * - Correct answer → 1 mark
     * - Wrong answer → 0 mark
     * - Multiple-Select:
     *   - Selects all correct + no wrong → 1 mark
     *   - Selects any wrong → 0 mark
     *   - Selects some correct + no wrong → 1 mark (Partial Correctness Rule)
     * 
     * @param \App\Models\AttemptAnswer $answer
     * @param \App\Models\Question $question
     * @return int 1 or 0
     */
    private function calculatePoints($answer, $question)
    {
        // Decode selected options (stored as JSON array of strings e.g. ["Option Text 1", "Option Text 2"])
        // Note: The previous code was storing texts. Let's verify if we are comparing texts or keys.
        // Looking at the take.blade.php I wrote earlier: value="{{ $option->option_text }}"
        // So we are comparing option texts.
        
        // selected_options is cast to array in AttemptAnswer model, so it should be an array.
        // However, we handle both cases just to be safe.
        $selectedOptions = $answer->selected_options;
        
        if (is_string($selectedOptions)) {
             $selectedOptions = json_decode($selectedOptions, true);
        }
        
        if (empty($selectedOptions) || !is_array($selectedOptions)) {
            return 0; // No answer selected
        }

        // Get correct and incorrect options from DB
        // We need to compare specific texts.
        $correctOptions = $question->options->where('is_correct', 1)->pluck('option_text')->toArray();
        $incorrectOptions = $question->options->where('is_correct', 0)->pluck('option_text')->toArray();

        // Check if ANY wrong option is selected
        // Intersection of selected vs incorrect should be empty
        $wrongSelected = array_intersect($selectedOptions, $incorrectOptions);
        
        if (count($wrongSelected) > 0) {
            return 0; // Rule: If any wrong option option selected → 0 mark
        }

        // Check if AT LEAST ONE correct option is selected (and we passed the wrong check above)
        // Intersection of selected vs correct should be > 0
        $correctSelected = array_intersect($selectedOptions, $correctOptions);

        if (count($correctSelected) > 0) {
            return 1; // Rule: No wrong options + at least one correct option → 1 mark
        }

        return 0;
    }
}
