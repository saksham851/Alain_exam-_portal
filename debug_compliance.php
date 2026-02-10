<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Section;
use App\Models\Question;
use App\Models\Exam;
use Illuminate\Support\Facades\DB;

$sectionId = 4;
$section = Section::with('examStandardCategory')->find($sectionId);
if (!$section) {
    echo "Section $sectionId not found\n";
    exit;
}

echo "Section Title: " . $section->title . "\n";
echo "Section Category ID: " . $section->exam_standard_category_id . " (" . ($section->examStandardCategory->name ?? 'N/A') . ")\n";

$questions = Question::join('case_studies', 'questions.case_study_id', '=', 'case_studies.id')
    ->where('case_studies.section_id', $sectionId)
    ->where('questions.status', 1)
    ->select('questions.id', 'questions.content_area_id')
    ->get();

echo "Total Active Questions in Section: " . $questions->count() . "\n";

$counts = $questions->groupBy('content_area_id')->map->count();
foreach ($counts as $areaId => $count) {
    $area = DB::table('exam_standard_content_areas')->find($areaId);
    echo "Area ID: $areaId (" . ($area->name ?? 'Unknown') . ") -> Category ID: " . ($area->category_id ?? 'Unknown') . " -> Count: $count\n";
}

$exam = Exam::find($section->exam_id);
if ($exam) {
    echo "\nExam Compliance Summary:\n";
    $compliance = $exam->validateStandardCompliance();
    print_r($compliance['errors']);
}
