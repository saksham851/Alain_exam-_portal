<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CaseStudy;

$emptyActiveCS = CaseStudy::where('status', 1)
    ->whereDoesntHave('questions')
    ->get();

if ($emptyActiveCS->isEmpty()) {
    echo "No active case studies are empty.\n";
} else {
    foreach ($emptyActiveCS as $cs) {
        $section = $cs->section;
        echo "Empty Active CS: ID {$cs->id} | Title: {$cs->title} | Section: " . ($section ? $section->title : 'N/A') . "\n";
    }
}
