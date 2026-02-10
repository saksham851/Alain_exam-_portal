<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Exam;
use App\Models\Question;

class ExplainExamScore extends Command
{
    protected $signature = 'exam:explain-score {exam_code}';
    protected $description = 'Explain how questions satisfy multiple score categories';

    public function handle()
    {
        $code = $this->argument('exam_code');
        $exam = Exam::where('exam_code', $code)->with(['examStandard.categories.contentAreas'])->first();

        if (!$exam) {
            $this->error("Exam not found: $code");
            return;
        }

        $this->info("Exam: {$exam->name}");
        $this->info("Standard: " . ($exam->examStandard->name ?? 'None'));
        $this->newLine();

        $questions = $exam->questions()->with('tags')->get();

        foreach ($questions as $q) {
            $this->line("Question: <comment>{$q->question_text}</comment> (Points: <info>{$q->max_question_points}</info>)");
            $this->line("  Tags:");

            $tags = $q->tags;
            if ($tags->isEmpty()) {
                $this->error("    - Uncategorized!");
            } else {
                foreach ($tags as $tag) {
                    // Find category Name
                    $catName = "Unknown Category";
                    $areaName = "Unknown Area";
                    
                    if ($exam->examStandard) {
                        $cat = $exam->examStandard->categories->where('id', $tag->score_category_id)->first();
                        if ($cat) {
                            $catName = $cat->name;
                            $area = $cat->contentAreas->where('id', $tag->content_area_id)->first();
                            if ($area) $areaName = $area->name;
                        }
                    }

                    $this->line("    - [$catName] -> [$areaName] : Adds <info>+{$q->max_question_points}</info> pts");
                }
            }
            $this->newLine();
        }
        
        $this->info("Summary Logic:");
        $this->info("1. A question contributes its FULL points to EVERY category it is tagged in.");
        $this->info("2. Points are NOT divided. 10 points = 10 points to Domain A AND 10 points to CACREP B.");
    }
}
