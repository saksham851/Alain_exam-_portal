<?php

use App\Models\Exam;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$exam = Exam::where('exam_code', 'like', 'NCMHCE-%')->with('examStandard.category1.contentAreas', 'examStandard.category2.contentAreas')->first();

if (!$exam) {
    echo "No exam found!\n";
    exit;
}

echo "===========================================\n";
echo "NCMHCE EXAM VALIDATION REPORT\n";
echo "===========================================\n\n";
echo "Exam: {$exam->name}\n";
echo "Total Questions: {$exam->total_questions}\n";
echo "Standard: {$exam->examStandard->name}\n\n";

$validation = $exam->validateStandardCompliance();

echo "-------------------------------------------\n";
echo "CONTENT AREAS BREAKDOWN\n";
echo "-------------------------------------------\n";

$cat1Total = 0;
$cat2Total = 0;

foreach ($validation['content_areas'] as $area) {
    $status = $area['valid'] ? '✅' : '❌';
    echo "{$status} {$area['name']}\n";
    echo "   Required: {$area['required']} ({$area['percentage']}%)\n";
    echo "   Current:  {$area['current']}\n";
    
    // Determine which category
    $isCategory1 = false;
    foreach ($exam->examStandard->category1->contentAreas as $c1Area) {
        if ($c1Area->name == $area['name']) {
            $isCategory1 = true;
            $cat1Total += $area['current'];
            break;
        }
    }
    if (!$isCategory1) {
        $cat2Total += $area['current'];
    }
    
    if (!$area['valid']) {
        echo "   ⚠️  MISMATCH!\n";
    }
    echo "\n";
}

echo "-------------------------------------------\n";
echo "SUMMARY\n";
echo "-------------------------------------------\n";
echo "Category 1 Questions: {$cat1Total}\n";
echo "Category 2 Questions: {$cat2Total}\n";
echo "Total: " . ($cat1Total + $cat2Total) . " / {$exam->total_questions}\n\n";

if ($validation['valid']) {
    echo "✅ VALIDATION PASSED!\n";
} else {
    echo "❌ VALIDATION FAILED!\n";
    foreach ($validation['errors'] as $error) {
        echo "   • {$error}\n";
    }
}
echo "===========================================\n";
