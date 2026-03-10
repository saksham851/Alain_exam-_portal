<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$examName = "Empty Exam for CSV Import (11 Sections)";
$sections = [
    'Section 1: Foundations of Counseling', 'Section 2: Clinical Assessment', 'Section 3: DSM-5 Diagnosis', 
    'Section 4: Treatment Planning', 'Section 5: Cognitive Interventions', 'Section 6: Ethical Standards',
    'Section 7: Multicultural Issues', 'Section 8: Lifespan Development', 'Section 9: Research and Evaluation',
    'Section 10: Group Process', 'Section 11: Career Transitions'
];

$domainAreas = [
    'Professional Practice and Ethics', 'Intake, Assessment and Diagnosis', 
    'Treatment Planning Counseling Skills', 'Counseling Skills and Interventions', 
    'Core Counselor Attributes'
];

$cacrepAreas = [
    'Human Growth and Development', 'Social and Cultural Diversity', 
    'Counseling and Helping Relationships', 'Group Counseling and Group Work', 
    'Assessment and Testing', 'Research and Program Evaluation'
];

$filename = 'EXAM_MASTER_100_QUESTIONS.csv';
$f = fopen($filename, 'w');

// CSV Headers (16 Columns)
fputcsv($f, [
    'exam_name', 'section_title', 'case_study_title', 'visit_title', 'question_text',
    'question_type', 'max_question_points', 'option_1', 'option_2',
    'option_3', 'option_4', 'correct_option', 
    'tag_1_category', 'tag_1_area', 'tag_2_category', 'tag_2_area'
]);

for ($i = 1; $i <= 100; $i++) {
    $secIdx = ($i - 1) % 11;
    $section = $sections[$secIdx];
    $csTitle = "Clinical Case Study " . ($secIdx + 1);
    
    // Pick random but valid tags from the seeded standard
    $domain = $domainAreas[array_rand($domainAreas)];
    $cacrep = $cacrepAreas[array_rand($cacrepAreas)];
    
    fputcsv($f, [
        $examName,
        $section,
        $csTitle,
        "Session 1",
        "Master Question $i: [Topic: " . str_replace(['Section ' . ($secIdx+1) . ': ', 'Section 10: ', 'Section 11: '], '', $section) . "] Based on the clinical data, what is the best ethical approach?",
        "single",
        1,
        "Option A: Clinical protocol prioritized for the patient.",
        "Option B: Secondary diagnostic evaluation required.",
        "Option C: Consultation with a multi-disciplinary team.",
        "Option D: Immediate documentation and ethical review.",
        rand(1, 4),
        "Counselor Work Behavior Areas (Domains)",
        $domain,
        "CACREP Areas",
        $cacrep
    ]);
}

fclose($f);
echo "SUCCESS: File '{$filename}' generated with exactly 100 questions.\n";
echo "TARGET EXAM: {$examName}\n";
echo "SECTIONS: 11 (Properly mapped)\n";
echo "TAGGING: Dual-tags applied to all 100 questions.\n";
