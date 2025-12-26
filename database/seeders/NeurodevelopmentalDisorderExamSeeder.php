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

class NeurodevelopmentalDisorderExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $examName = 'Neurodevelopmental Disorder Professional Certification';
        
        // Remove existing if exists to allow re-runs
        $existingExam = Exam::where('name', $examName)->first();
        if ($existingExam) {
            $this->command->warn("Existing exam '$examName' found. Deleting to recreate...");
            $existingExam->delete();
        }

        // 1. Create the Exam
        $exam = Exam::create([
            'name' => $examName,
            'description' => 'A comprehensive clinical examination focused on the diagnosis, management, and support of various neurodevelopmental disorders throughout the lifespan.',
            'duration_minutes' => 180, // 3 hours
            'status' => 1,
        ]);

        $this->command->info("Exam Created: {$exam->name}");

        // 2. Define 11 Sections with Topics
        $sectionsData = [
            [
                'title' => 'Section 1: Autism Spectrum Disorder (ASD)',
                'content' => '<p>Focus on early clinical markers, social-communication deficits, and restrictive repetitive patterns of behavior.</p><ul><li>DSM-5-TR Diagnostic Criteria</li><li>Screening Tools (M-CHAT, ADOS-2)</li><li>Early Intervention Strategies</li></ul>',
                'topics' => ['ASD Diagnosis', 'Communication Interventions', 'Sensory Processing']
            ],
            [
                'title' => 'Section 2: Attention-Deficit/Hyperactivity Disorder (ADHD)',
                'content' => '<p>Evaluation of inattentive, hyperactive, and impulsive presentations across different environments.</p><ul><li>Executive Functioning Deficits</li><li>Behavioral Modification Patterns</li><li>Classroom Management Techniques</li></ul>',
                'topics' => ['ADHD Assessment', 'Executive Function', 'Multimodal Treatment']
            ],
            [
                'title' => 'Section 3: Intellectual Developmental Disorders',
                'content' => '<p>Assessment of intellectual and adaptive functioning across conceptual, social, and practical domains.</p><ul><li>IQ vs. Adaptive Behavior</li><li>Support Intensity Scales</li><li>Down Syndrome & Fragile X considerations</li></ul>',
                'topics' => ['Cognitive Assessment', 'Adaptive Functioning', 'Genetic Syndromes']
            ],
            [
                'title' => 'Section 4: Specific Learning Disorders (SLD)',
                'content' => '<p>Identification of persistent difficulties in reading, writing, or mathematics despite intervention.</p><ul><li>Dyslexia and Phonological Processing</li><li>Dyscalculia and Number Sense</li><li>Response to Intervention (RTI) Frameworks</li></ul>',
                'topics' => ['Reading Disorders', 'Math Learning Disabilities', 'Writing Impairments']
            ],
            [
                'title' => 'Section 5: Communication Disorders',
                'content' => '<p>Analysis of language, speech sound, and social communication disorders.</p><ul><li>Language Disorder vs. Speech Delay</li><li>Stuttering and Fluency</li><li>Social (Pragmatic) Communication Disorder</li></ul>',
                'topics' => ['Language Impairment', 'Fluency Disorders', 'Pragmatics']
            ],
            [
                'title' => 'Section 6: Motor & Tic Disorders',
                'content' => '<p>Management of Tourette’s Syndrome, Developmental Coordination Disorder (DCD), and Stereotypic Movement Disorder.</p><ul><li>Comprehensive Behavioral Intervention for Tics (CBIT)</li><li>Gross and Fine Motor Milestones</li><li>Comorbidity with Anxiety/OCD</li></ul>',
                'topics' => ['Motor Skills', 'Tourettes Syndrome', 'Coordination Issues']
            ],
            [
                'title' => 'Section 7: Sensory Processing & Integration',
                'content' => '<p>Impact of sensory modulation and discrimination issues on daily living and classroom participation.</p><ul><li>Hyper-responsivity vs Hypo-responsivity</li><li>Sensory Diets and Environmental Modifications</li><li>Occupational Therapy Perspectives</li></ul>',
                'topics' => ['Sensory Modulation', 'Environmental Adapting', 'Integration Therapy']
            ],
            [
                'title' => 'Section 8: Pharmacological & Medical Interventions',
                'content' => '<p>Understanding the role of medication in managing symptoms and co-occurring medical conditions.</p><ul><li>Stimulants and Non-stimulants for ADHD</li><li>Psycho-pharmacology in ASD irritabilities</li><li>Side effect monitoring and titration</li></ul>',
                'topics' => ['Medication Management', 'Medical Side Effects', 'Pharmacotherapy']
            ],
            [
                'title' => 'Section 9: Educational Law & Accommodations',
                'content' => '<p>Navigating IEPs, 504 Plans, and legal rights of students with neurodevelopmental disorders.</p><ul><li>IDEA (Individuals with Disabilities Education Act)</li><li>Least Restrictive Environment (LRE)</li><li>Transition Planning mandates</li></ul>',
                'topics' => ['IEP Development', 'Legal Rights', 'Classroom Accommodations']
            ],
            [
                'title' => 'Section 10: Transition to Adulthood & Vocational Support',
                'content' => '<p>Challenges in post-secondary education, employment, and independent living for neurodiverse adults.</p><ul><li>Vocational Rehabilitation</li><li>Higher Education supports</li><li>Guardian-ship vs. Supported Decision Making</li></ul>',
                'topics' => ['Independent Living', 'Job Coaching', 'Self-Advocacy']
            ],
            [
                'title' => 'Section 11: Family Systems & Ethical Considerations',
                'content' => '<p>Supporting families, siblings, and managing ethical dilemmas in clinical practice.</p><ul><li>Family Respite and Support Groups</li><li>Neurodiversity Movement vs. Medical Model</li><li>Ethical Dilemmas in Genetic Testing</li></ul>',
                'topics' => ['Family Centered Care', 'Clinical Ethics', 'Advocacy']
            ],
        ];

        $i = 1;
        foreach ($sectionsData as $sData) {
            $section = Section::create([
                'exam_id' => $exam->id,
                'title' => $sData['title'],
                'content' => $sData['content'],
                'order_no' => $i++,
                'status' => 1,
            ]);

            $j = 1;
            foreach ($sData['topics'] as $topic) {
                $caseStudy = CaseStudy::create([
                    'section_id' => $section->id,
                    'title' => "Case Study $j: $topic Scenarios",
                    'content' => $this->generateCaseStudyContent($topic),
                    'order_no' => $j++,
                    'status' => 1,
                ]);

                // Create 5 questions per case study
                $this->createQuestionsForCaseStudy($caseStudy->id, $topic);
            }
        }

        // 3. Assign to students
        $students = User::where('role', 'student')->get();
        foreach ($students as $student) {
            StudentExam::updateOrCreate(
                ['student_id' => $student->id, 'exam_id' => $exam->id],
                [
                    'attempts_allowed' => 5,
                    'attempts_used' => 0,
                    'expiry_date' => now()->addYear(),
                    'status' => 1,
                ]
            );
        }

        $this->command->info('Neurodevelopmental Disorder Exam seeded successfully with 11 Sections and 33 Case Studies.');
    }

    private function generateCaseStudyContent($topic)
    {
        return "
        <h3>Clinical Case Scenario: $topic</h3>
        <p><strong>Patient Profile:</strong> Alex is an 8-year-old child referred for an evaluation due to persistent challenges in school and social settings. The primary concerns began during early childhood but have become more pronounced in the structured environment of the 2nd grade. Parents describe Alex as bright but 'distinctly different' from peers.</p>
        <p><strong>Observed Behaviors:</strong></p>
        <ul>
            <li>Strong preference for routine and significant distress when schedules change unexpectedly.</li>
            <li>Intense, specialized interest in historical architectural structures, often reciting facts to others without regard for their social engagement.</li>
            <li>Challenges in interpreting non-verbal cues, such as facial expressions and sarcasm.</li>
            <li>Frequent 'body rocking' or hand flapping during periods of high excitement or stress.</li>
        </ul>
        <p><strong>Environmental Impact:</strong> In the classroom, Alex often struggles to complete group projects, preferring to work alone. When the cafeteria is loud, Alex frequently covers his ears and seeks a quiet corner. Teachers report that while Alex's academic performance in math is above average, his reading comprehension of social stories is below grade level.</p>
        <p>The multidisciplinary team is now reviewing the collected data, including parent interviews, teacher reports, and direct observations using standardized tools to determine the most appropriate diagnosis and support plan.</p>
        ";
    }

    private function createQuestionsForCaseStudy($caseStudyId, $topic)
    {
        $questionTemplates = [
            [
                'text' => "Based on the clinical observations of Alex, which 'Information Gathering' (IG) step is most critical for confirming a neurodevelopmental diagnosis?",
                'type' => 'single',
                'ig' => 1, 'dm' => 0,
                'options' => [
                    ['Conducting a standardized observation tool like the ADOS-2.', true],
                    ['Testing his blood sugar levels regularly.', false],
                    ['Asking about the parents\' favorite hobbies.', false],
                    ['Reviewing his grandfather\'s dental records.', false],
                ]
            ],
            [
                'text' => "Alex covers his ears in the loud cafeteria. What 'Decision Making' (DM) intervention should be prioritized for his sensory comfort?",
                'type' => 'single',
                'ig' => 0, 'dm' => 1,
                'options' => [
                    ['Providing noise-canceling headphones for use during high-decibel activities.', true],
                    ['Telling Alex to ignore the noise and stay in the room.', false],
                    ['Moving Alex to a different school entirely.', false],
                    ['Punishing Alex for covering his ears.', false],
                ]
            ],
            [
                'text' => "Which of the following behavioral patterns observed in Alex align with DSM-5-TR criteria for ASD? (Select ALL that apply)",
                'type' => 'multiple',
                'ig' => 1, 'dm' => 1,
                'options' => [
                    ['Restricted, repetitive patterns of behavior (e.g., hand flapping).', true],
                    ['Deficits in social-emotional reciprocity.', true],
                    ['Hyper-reactivity to sensory input (loud noise).', true],
                    ['High proficiency in mathematics.', false],
                ]
            ],
            [
                'text' => "When Alex recites architectural facts without noticing the boredom of others, he is demonstrating a deficit in which area?",
                'type' => 'single',
                'ig' => 1, 'dm' => 0,
                'options' => [
                    ['Social-pragmatic communication.', true],
                    ['Gross motor coordination.', false],
                    ['Short-term memory capacity.', false],
                    ['Phonological awareness.', false],
                ]
            ],
            [
                'text' => "The school team wants to implement a 'Decision Making' (DM) strategy for Alex's preference for routine. Which of the following is most effective?",
                'type' => 'multiple',
                'ig' => 0, 'dm' => 1,
                'options' => [
                    ['Using a visual schedule to signal upcoming transitions.', true],
                    ['Providing a Five-Minute Warning before changing activities.', true],
                    ['Removing all surprises from the school year.', false],
                    ['Changing the schedule every day to force flexibility.', false],
                ]
            ],
        ];

        foreach ($questionTemplates as $qT) {
            $question = Question::create([
                'case_study_id' => $caseStudyId,
                'question_text' => $qT['text'],
                'question_type' => $qT['type'],
                'ig_weight' => $qT['ig'],
                'dm_weight' => $qT['dm'],
                'status' => 1,
            ]);

            foreach ($qT['options'] as $index => $opt) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_key' => chr(65 + $index),
                    'option_text' => $opt[0],
                    'is_correct' => $opt[1],
                ]);
            }
        }
    }
}
