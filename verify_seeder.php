<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exam;
use App\Models\QuestionTag;

$exam = Exam::find(6);
$questions = $exam->getAllQuestions();
$count = $questions->count();
$tagsCount = QuestionTag::whereIn('question_id', $questions->pluck('id'))->count();

echo "Exam: " . $exam->name . "\n";
echo "Total Questions: " . $count . "\n";
echo "Total Tags: " . $tagsCount . " (Expected: " . ($count * 2) . ")\n";

$oneQ = $questions->first();
if ($oneQ) {
    echo "Sample Question Tags: " . $oneQ->tags->count() . "\n";
    foreach ($oneQ->tags as $tag) {
        echo "- Cat: " . ($tag->scoreCategory->name ?? 'N/A') . " | Area: " . ($tag->contentArea->name ?? 'N/A') . "\n";
    }
}
