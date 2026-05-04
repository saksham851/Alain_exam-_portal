<?php
$headers = [
    'exam_name',
    'section_title',
    'case_study_title',
    'visit_title',
    'visit_content',
    'question_text',
    'max_point',
    'option_1',
    'option_2',
    'option_3',
    'option_4',
    'correct_option',
    'score_category_1',
    'content_area_1',
    'score_category_2',
    'content_area_2'
];

$file = fopen('exam_import_11_sections.csv', 'w');
fputcsv($file, $headers);

$areas1 = [
    'Professional Practice and Ethics',
    'Intake, Assessment and Diagnosis',
    'Treatment Planning Counseling Skills',
    'Counseling Skills and Interventions',
    'Core Counseling Attributes'
];

$areas2 = [
    'Professional Counseling Orientation and Ethical Practice',
    'Social and Cultural Diversity',
    'Human Growth and Development',
    'Career Development',
    'Counseling and Helping Relationships',
    'Group Counseling and Group Work',
    'Assessment and Testing',
    'Research and Program Evaluation'
];

$q_count = 0;
for ($s = 1; $s <= 11; $s++) {
    $section_title = "Section $s: Core Domain $s";
    $case_study_title = "Case Study $s - Clinical Scenario";
    
    for ($v = 1; $v <= 3; $v++) {
        $visit_title = "Visit $v";
        $visit_content = "This is the clinical information for Section $s, Visit $v. The patient presents with various symptoms and requires assessment and intervention.";
        
        for ($q = 1; $q <= 5; $q++) {
            $q_count++;
            $area1 = $areas1[($s - 1) % count($areas1)];
            $area2 = $areas2[($q_count - 1) % count($areas2)];
            
            fputcsv($file, [
                'Master Exam - 11 Sections',
                $section_title,
                $case_study_title,
                $visit_title,
                $visit_content,
                "Question $q for Section $s, Visit $v",
                '1',
                "Option A for Q$q_count",
                "Option B for Q$q_count",
                "Option C for Q$q_count",
                "Option D for Q$q_count",
                'A',
                'Counselor Work Behavior Areas (Domains)',
                $area1,
                'CACREP Areas',
                $area2
            ]);
        }
    }
}

fclose($file);
echo "CSV generated with $q_count questions.\n";
