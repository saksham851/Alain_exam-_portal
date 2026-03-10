<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exam;
use App\Models\Question;

// Delete Exam 6
$exam6 = Exam::find(6);
if ($exam6) {
    echo "Deleting Exam 6 (" . $exam6->name . ")\n";
    $questions = $exam6->getAllQuestions();
    foreach ($questions as $q) {
        $q->options()->delete();
        $q->tags()->delete();
        $q->delete();
    }
    // Delete sections, case studies, etc.
    foreach ($exam6->sections as $s) {
        foreach ($s->caseStudies as $cs) {
            foreach ($cs->visits as $v) {
                $v->delete();
            }
            $cs->delete();
        }
        $s->delete();
    }
    $exam6->delete();
}

echo "Remaining Questions in DB: " . Question::count() . "\n";
foreach (Exam::all() as $e) {
    echo "ID: {$e->id} | Name: {$e->name} | QCount: " . $e->getAllQuestions()->count() . "\n";
}
