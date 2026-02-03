<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$exam = App\Models\Exam::where('name', 'EXAM 3')->first();

if ($exam) {
    $section = $exam->sections()->where('title', 'Section 1: Topic Area 1')->first();
    
    if ($section) {
        echo "Section found: " . $section->title . "\n\n";
        
        $caseStudies = $section->caseStudies;
        echo "Total case studies: " . $caseStudies->count() . "\n\n";
        
        foreach ($caseStudies as $cs) {
            echo "Case Study ID: " . $cs->id . "\n";
            echo "Title: '" . $cs->title . "'\n";
            echo "Title Length: " . strlen($cs->title) . "\n";
            echo "Title (hex): " . bin2hex($cs->title) . "\n";
            echo "---\n";
        }
    } else {
        echo "Section not found\n";
    }
} else {
    echo "Exam not found\n";
}
