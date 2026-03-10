<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Section;

$section = Section::find(3);
if (!$section) {
    die("Section 3 not found\n");
}

echo "Section 3: " . $section->title . "\n";
echo "====================================\n";

foreach ($section->caseStudies as $cs) {
    echo "ID: " . $cs->id . "\n";
    echo "Title: " . $cs->title . "\n";
    echo "Status: " . ($cs->status ? "ACTIVE" : "INACTIVE") . "\n";
    echo "Visits Count: " . $cs->visits->count() . "\n";
    echo "Questions Count (Through Visits): " . $cs->questions->count() . "\n";
    foreach ($cs->visits as $v) {
        echo "  -- Visit ID: " . $v->id . " | Title: " . $v->title . " | Qs: " . $v->questions->count() . "\n";
    }
    echo "------------------------------------\n";
}
