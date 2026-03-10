<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exam;

// Assuming the last created exam is the one we just made
$exam = Exam::latest()->first();
echo "Validating Exam: [{$exam->id}] {$exam->name}\n";

$result = $exam->validateStandardCompliance();

echo "Valid: " . ($result['valid'] ? 'YES' : 'NO') . "\n";
if (!$result['valid']) {
    echo "Errors:\n";
    print_r($result['errors']);
} else {
    echo "This exam fully satisfies the standard requirements.\n";
    echo "Total Questions: " . $result['total_questions'] . "\n";
    echo "Total Achieved Points: " . $result['total_exam_points'] . "\n";
}
