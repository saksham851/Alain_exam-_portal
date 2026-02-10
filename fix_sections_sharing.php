<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Section;
use App\Models\Exam;
use App\Models\ExamStandardCategory;

$examId = Section::find(4)->exam_id;
echo "Handling Exam ID: $examId\n";

$sections = Section::where('exam_id', $examId)->where('status', 1)->get();

foreach ($sections as $section) {
    echo "Section: " . $section->title . " (ID: " . $section->id . ") - Current Category ID: " . ($section->exam_standard_category_id ?? 'NULL') . "\n";
    
    if (!$section->exam_standard_category_id) {
        // Simple heuristic: if title contains 'Clinical' or questions inside it belong to Cat 3 (Clinical Domains), link it.
        // For Section 4 (Ethical Codes), link to Cat 4.
        if (stripos($section->title, 'Ethical') !== false || $section->id == 4) {
            $section->update(['exam_standard_category_id' => 4]);
            echo "   -> Updated to Ethical Foundations (Category 4)\n";
        } elseif (stripos($section->title, 'Clinical') !== false) {
            $section->update(['exam_standard_category_id' => 3]);
            echo "   -> Updated to Clinical Domains (Category 3)\n";
        }
    }
}

$exam = Exam::find($examId);
echo "\nFinal Compliance Check:\n";
$compliance = $exam->validateStandardCompliance();
print_r($compliance['errors'] ?: 'COMPLIANT!');
