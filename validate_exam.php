<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Exam;

$exam = Exam::find(1);
echo "Exam: " . $exam->name . "\n";

foreach ($exam->sections as $section) {
    if ($section->status) {
        echo "Section (ID: {$section->id}): {$section->title}\n";
        $activeCS = $section->caseStudies->where('status', 1);
        if ($activeCS->isEmpty()) {
            echo "  !!! NO ACTIVE CASE STUDIES !!!\n";
        }
        foreach ($activeCS as $cs) {
            $activeQs = $cs->questions->where('status', 1);
            echo "  - Case Study (ID: {$cs->id}): {$cs->title} | Qs: " . $activeQs->count() . "\n";
            if ($activeQs->isEmpty()) {
                echo "    !!! NO ACTIVE QUESTIONS !!!\n";
            }
        }
    }
}
