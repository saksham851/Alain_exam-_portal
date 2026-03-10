<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exam;
use App\Models\Section;
use App\Models\CaseStudy;
use App\Models\Visit;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTag;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();

try {
    // 1. Create Exam
    $exam = Exam::create([
        'name' => 'Standard Counselor Licensing Exam (11 Sections)',
        'exam_code' => 'CLE-11-' . time(),
        'description' => 'A standard compliant exam with 11 sections, each containing a case study and questions.',
        'certification_type' => 'Full License',
        'duration_minutes' => 240,
        'status' => 1,
        'is_active' => 1,
        'exam_standard_id' => 1,
        'total_questions' => 100,
        'passing_score_overall' => 70,
    ]);

    echo "Exam Created: ID [{$exam->id}]\n";

    // 2. Create 11 Sections
    $sectionTitles = [
        'Ethical Practice and Self-Regulation',
        'Professional Relationship and Communication',
        'Counseling Theory and Applied Practice',
        'Human Growth and Development Patterns',
        'Diverse Populations and Inclusion',
        'Group Counseling Dynamics',
        'Lifestyle and Career Evaluation',
        'Clinical Assessment and DSM-5 Diagnosis',
        'Research Principles and Evaluation',
        'Treatment Planning and Goal Setting',
        'Intervention Strategies and Crisis Management'
    ];

    $sections = [];
    foreach ($sectionTitles as $i => $title) {
        $sections[] = Section::create([
            'exam_id' => $exam->id,
            'title' => ($i + 1) . ". " . $title,
            'content' => "Overview and instructions for " . $title,
            'order_no' => $i + 1,
            'status' => 1,
        ]);
    }
    echo "11 Sections Created.\n";

    // 3. Create Case Studies and Visits
    $visits = [];
    foreach ($sections as $i => $section) {
        $cs = CaseStudy::create([
            'section_id' => $section->id,
            'title' => "Case Study: " . $section->title,
            'content' => "Comprehensive clinical background for Section " . ($i + 1) . ". Includes history, presenting problem, and initial assessment data.",
            'order_no' => 1,
            'status' => 1,
        ]);

        $visits[] = Visit::create([
            'case_study_id' => $cs->id,
            'title' => "Clinical Visit 1",
            'description' => "Detailed observations and diagnostic data from the first clinical encounter.",
            'order_no' => 1,
            'status' => 1,
        ]);
    }
    echo "11 Case Studies and Visits Created.\n";

    // 4. Tag Requirements for Standard 1 (to satisfy the "Standard Compliance")
    $cat1_areas = [
        1 => 15, // Professional Practice and Ethics
        2 => 25, // Intake, Assessment and Diagnosis
        3 => 15, // Treatment Planning Counseling Skills
        4 => 30, // Counseling Skills and Interventions
        5 => 15, // Core Counselor Attributes
    ];

    $cat2_areas = [
        6 => 10,  // Human Growth and Development
        7 => 10,  // Social and Cultural Diversity
        8 => 40,  // Counseling and Helping Relationships
        9 => 10,  // Group Counseling and Group Work
        10 => 20, // Assessment and Testing
        11 => 10, // Research and Program Evaluation
    ];

    $pool1 = [];
    foreach ($cat1_areas as $areaId => $count) {
        for ($k = 0; $k < $count; $k++) { $pool1[] = $areaId; }
    }
    shuffle($pool1);

    $pool2 = [];
    foreach ($cat2_areas as $areaId => $count) {
        for ($k = 0; $k < $count; $k++) { $pool2[] = $areaId; }
    }
    shuffle($pool2);

    // 5. Create 100 Questions (Distribute across visits)
    for ($qIdx = 0; $qIdx < 100; $qIdx++) {
        $visit = $visits[$qIdx % 11];

        $question = Question::create([
            'visit_id' => $visit->id,
            'question_text' => "Standard Question " . ($qIdx + 1) . ": based on the provided clinical data, what is the priority action?",
            'question_type' => 'single',
            'status' => 1,
            'max_question_points' => 1,
        ]);

        // Add 4 Options
        for ($opt = 1; $opt <= 4; $opt++) {
            QuestionOption::create([
                'question_id' => $question->id,
                'option_key' => $opt,
                'option_text' => "Clinical Option " . $opt . " for Question " . ($qIdx + 1),
                'is_correct' => ($opt === 1) ? 1 : 0,
            ]);
        }

        // Add Tags for Standard Compliance
        QuestionTag::create([
            'question_id' => $question->id,
            'score_category_id' => 1,
            'content_area_id' => $pool1[$qIdx],
        ]);

        QuestionTag::create([
            'question_id' => $question->id,
            'score_category_id' => 2,
            'content_area_id' => $pool2[$qIdx],
        ]);
    }

    echo "100 Questions created and tagged correctly.\n";

    DB::commit();
    echo "SUCCESS: Exam satisfying 'Counselor Exam Blueprint (Shell)' standard has been created.\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "LINE: " . $e->getLine() . "\n";
}
