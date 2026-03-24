<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exam;
use App\Models\Section;

$examName = 'Seed Exam (11 Sections)';
$exam = Exam::where('name', $examName)->first();

if (!$exam) {
    $exam = Exam::create([
        'name' => $examName,
        'duration_minutes' => 180,
        'total_questions' => 150,
        'status' => 1,
        'is_active' => 0
    ]);
    echo "Exam created with ID: " . $exam->id . "\n";
} else {
    echo "Exam already exists with ID: " . $exam->id . "\n";
}

$sections = [
    'Section 1: Foundations',
    'Section 2: Development',
    'Section 3: Ethics',
    'Section 4: Assessment',
    'Section 5: Career',
    'Section 6: Group Work',
    'Section 7: Research',
    'Section 8: Social/Cultural',
    'Section 9: Counseling Relationship',
    'Section 10: Clinical Supervision',
    'Section 11: Final Synthesis'
];

foreach ($sections as $index => $title) {
    Section::firstOrCreate([
        'exam_id' => $exam->id,
        'title' => $title
    ], [
        'order_no' => $index + 1,
        'status' => 1
    ]);
}

echo "11 Sections created/verified for " . $examName . "\n";
