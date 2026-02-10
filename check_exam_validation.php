<?php

use App\Models\Exam;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "===========================================\n";
echo "EXAM VALIDATION REPORT\n";
echo "===========================================\n\n";

$exam = Exam::where('exam_code', 'like', 'TEST-%')->with('examStandard.category1.contentAreas', 'examStandard.category2.contentAreas')->first();

if (!$exam) {
    echo "❌ No test exam found!\n";
    exit;
}

echo "📋 Exam: {$exam->name}\n";
echo "📊 Total Questions Required: {$exam->total_questions}\n";
echo "🎯 Standard: {$exam->examStandard->name}\n\n";

// Run validation
$validation = $exam->validateStandardCompliance();

echo "-------------------------------------------\n";
echo "CATEGORY 1: {$exam->examStandard->category1->name}\n";
echo "-------------------------------------------\n";
foreach ($validation['category1'] as $area) {
    $status = $area['valid'] ? '✅' : '❌';
    echo "{$status} {$area['name']}\n";
    echo "   Required: {$area['required']} questions ({$area['percentage']}%)\n";
    echo "   Current:  {$area['current']} questions\n";
    if (!$area['valid']) {
        echo "   ⚠️  MISMATCH!\n";
    }
    echo "\n";
}

echo "-------------------------------------------\n";
echo "CATEGORY 2: {$exam->examStandard->category2->name}\n";
echo "-------------------------------------------\n";
foreach ($validation['category2'] as $area) {
    $status = $area['valid'] ? '✅' : '❌';
    echo "{$status} {$area['name']}\n";
    echo "   Required: {$area['required']} questions ({$area['percentage']}%)\n";
    echo "   Current:  {$area['current']} questions\n";
    if (!$area['valid']) {
        echo "   ⚠️  MISMATCH!\n";
    }
    echo "\n";
}

echo "===========================================\n";
if ($validation['valid']) {
    echo "✅ VALIDATION PASSED! Exam can be published.\n";
} else {
    echo "❌ VALIDATION FAILED! Fix these errors:\n\n";
    foreach ($validation['errors'] as $error) {
        echo "   • {$error}\n";
    }
}
echo "===========================================\n";
