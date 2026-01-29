<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Exam;
use App\Models\Section;
use App\Models\CaseStudy;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use App\Models\StudentExam;

class Exam01Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Exam 01
        $exam = Exam::create([
            'name' => 'Exam 01',
            'description' => 'Comprehensive examination covering 9 sections with multiple case studies',
            'duration_minutes' => 180, // 3 hours
            'status' => 1,
        ]);

        $this->command->info("Exam created: {$exam->name}");

        // Create 9 Sections
        for ($sectionNum = 1; $sectionNum <= 9; $sectionNum++) {
            $section = Section::create([
                'exam_id' => $exam->id,
                'title' => "Section {$sectionNum}: Topic Area {$sectionNum}",
                'content' => $this->getSectionContent($sectionNum),
                'order_no' => $sectionNum,
                'status' => 1,
            ]);

            $this->command->info("Created Section {$sectionNum}");

            // Create 3 Case Studies per Section
            for ($caseNum = 1; $caseNum <= 3; $caseNum++) {
                $caseStudy = CaseStudy::create([
                    'section_id' => $section->id,
                    'title' => "Case Study {$sectionNum}.{$caseNum}: Scenario {$caseNum}",
                    'content' => $this->getCaseStudyContent($sectionNum, $caseNum),
                    'order_no' => $caseNum,
                    'status' => 1,
                ]);

                $this->command->info("  Created Case Study {$sectionNum}.{$caseNum}");

                // Create 5 Questions per Case Study
                for ($qNum = 1; $qNum <= 5; $qNum++) {
                    $this->addQuestion(
                        $caseStudy->id,
                        "Question {$qNum} for Section {$sectionNum}, Case Study {$caseNum}: What is the best approach to solve this scenario?",
                        $qNum % 3 == 0 ? 'multiple' : 'single', // Every 3rd question is multiple choice
                        $qNum % 2, // Alternate ig_weight
                        ($qNum + 1) % 2, // Alternate dm_weight
                        $this->getQuestionOptions($qNum % 3 == 0 ? 'multiple' : 'single')
                    );
                }
            }
        }

        // Assign Exam to ALL Students
        $students = User::where('role', 'student')->get();
        foreach ($students as $student) {
            StudentExam::create([
                'student_id' => $student->id,
                'exam_id' => $exam->id,
                'attempts_allowed' => 3,
                'attempts_used' => 0,
                'expiry_date' => now()->addMonths(6),
                'status' => 1,
            ]);
        }

        $this->command->info("Assigned exam to {$students->count()} students");
        $this->command->info('Exam 01 seeded successfully with 9 sections, 27 case studies, and 135 questions!');
    }

    private function getSectionContent($sectionNum)
    {
        $topics = [
            1 => 'Data Privacy and Security',
            2 => 'Financial Ethics and Compliance',
            3 => 'Corporate Governance',
            4 => 'Risk Management',
            5 => 'Business Strategy',
            6 => 'Operations Management',
            7 => 'Human Resources',
            8 => 'Marketing and Sales',
            9 => 'Technology and Innovation'
        ];

        $topic = $topics[$sectionNum] ?? "General Topic {$sectionNum}";

        return "
<p><strong>Section {$sectionNum}: {$topic}</strong></p>
<p>This section evaluates your understanding of {$topic} principles and practices. You will encounter real-world scenarios that test your ability to apply theoretical knowledge to practical situations.</p>

<p><strong>Key Learning Objectives:</strong></p>
<ul>
    <li>Understand core concepts and frameworks in {$topic}</li>
    <li>Apply best practices to complex business scenarios</li>
    <li>Analyze case studies and identify optimal solutions</li>
    <li>Demonstrate critical thinking and decision-making skills</li>
</ul>

<p>Each case study in this section presents a unique challenge that requires careful analysis and strategic thinking. Consider all aspects of the scenario before selecting your answers.</p>
";
    }

    private function getCaseStudyContent($sectionNum, $caseNum)
    {
        $scenarios = [
            "You are a senior manager at a multinational corporation facing a critical decision that will impact multiple stakeholders. The situation requires balancing competing interests while maintaining ethical standards and regulatory compliance.",
            "Your organization is undergoing a significant transformation. As a key decision-maker, you must navigate complex challenges while ensuring business continuity and stakeholder satisfaction.",
            "A critical incident has occurred that threatens the organization's reputation and operational stability. You must respond quickly and effectively while adhering to established protocols and best practices."
        ];

        return "
<p><strong>Scenario {$sectionNum}.{$caseNum}</strong></p>
<p>{$scenarios[$caseNum - 1]}</p>

<p><strong>Background Information:</strong></p>
<p>The organization has been operating successfully for several years, but recent market changes and internal challenges have created new pressures. Your team has identified several potential approaches, each with distinct advantages and risks.</p>

<p><strong>Key Considerations:</strong></p>
<ul>
    <li><strong>Stakeholder Impact:</strong> How will different groups be affected by your decisions?</li>
    <li><strong>Resource Allocation:</strong> What resources are available and how should they be deployed?</li>
    <li><strong>Timeline:</strong> What are the time constraints and critical deadlines?</li>
    <li><strong>Risk Assessment:</strong> What are the potential risks and how can they be mitigated?</li>
</ul>

<p>Based on this scenario, answer the following questions that test your analytical and decision-making capabilities.</p>
";
    }

    private function getQuestionOptions($type)
    {
        if ($type === 'multiple') {
            return [
                ['Implement a comprehensive risk assessment framework', 1],
                ['Engage with all stakeholders to gather input', 1],
                ['Ignore the problem and hope it resolves itself', 0],
                ['Document all decisions and rationale', 1],
                ['Take immediate action without consultation', 0]
            ];
        } else {
            return [
                ['Conduct thorough analysis before making decisions', 1],
                ['Make quick decisions based on intuition alone', 0],
                ['Delegate the decision to junior staff', 0],
                ['Postpone the decision indefinitely', 0]
            ];
        }
    }

    private function addQuestion($caseStudyId, $text, $type, $igWeight, $dmWeight, $options)
    {
        $q = Question::create([
            'case_study_id' => $caseStudyId,
            'question_text' => $text,
            'question_type' => $type,
            'ig_weight' => $igWeight,
            'dm_weight' => $dmWeight,
            'status' => 1,
        ]);

        foreach($options as $index => $opt) {
            QuestionOption::create([
                'question_id' => $q->id,
                'option_key' => chr(65 + $index),
                'option_text' => $opt[0],
                'is_correct' => $opt[1]
            ]);
        }
    }
}
