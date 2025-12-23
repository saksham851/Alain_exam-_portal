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

class FreshExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Clear old content data
        Schema::disableForeignKeyConstraints();
        
        DB::table('attempt_answers')->truncate();
        DB::table('exam_attempts')->truncate();
        DB::table('question_options')->truncate();
        DB::table('questions')->truncate();
        DB::table('case_studies')->truncate(); // formerly sub_case_studies
        DB::table('sections')->truncate();     // formerly case_studies
        DB::table('student_exams')->truncate();
        DB::table('exams')->truncate();

        Schema::enableForeignKeyConstraints();

        $this->command->info('Old exam content cleared. Users preserved.');

        // 2. Create New Exam
        $exam = Exam::create([
            'name' => 'Certified Professional Ethics & Compliance Exam 2025',
            'description' => 'A comprehensive evaluation of ethical standards, data privacy regulations, and corporate compliance protocols suitable for senior management roles.',
            'duration_minutes' => 120, // 2 hours
            'status' => 1,
        ]);

        $this->command->info("Exam created: {$exam->name}");

        // Content Templates (approx 150-200 words with structure)
        
        // --- Section 1 Content ---
        $section1Content = "
<p><strong>Overview of Data Privacy Standards</strong></p>
<p>In the modern digital landscape, data privacy has emerged as a cornerstone of corporate integrity. Organizations are no longer simple custodians of data; they are active guardians responsible for the lifecycle of sensitive information. The transition from voluntary adherence to mandatory compliance frameworks like GDPR and CCPA has fundamentally shifted operational priorities. This section evaluates the candidate's understanding of these evolving responsibilities.</p>

<p>Key areas of focus include:</p>
<ul>
    <li><strong>Consent Management:</strong> Ensuring valid, informed, and explicit consent is obtained before data collection.</li>
    <li><strong>Data Minimization:</strong> collecting only what is strictly necessary for the stated purpose.</li>
    <li><strong>Right to be Forgotten:</strong> Implementing technical mechanisms to erase user data upon request effectively.</li>
</ul>

<p>Failure to adhere to these principles not only attracts severe financial penalties—often a percentage of global turnover—but also causes irreparable reputational damage. As we explore the following scenarios, consider the balance between operational efficiency and the rigorous demands of privacy compliance. A proactive approach to data governance is not just a legal requirement but a strategic thoroughfare to building lasting consumer trust.</p>
";

        // --- Sub Case 1.1 Content ---
        $subCase1Content = "
<p><strong>Scenario: The Third-Party Breach</strong></p>
<p>You are the Compliance Officer for FinTech Solutions Ltd., a mid-sized financial services provider. The company recently outsourced its customer support ticketing system to 'CloudHelp Inc.', a third-party vendor. Three months into the contract, CloudHelp Inc. notifies you of a security incident: a misconfigured server exposed the support tickets of 15,000 customers. These tickets contained names, email addresses, and in some cases, partial account numbers.</p>

<p><strong>Incident Details:</strong></p>
<ul>
    <li><strong>Exposure Duration:</strong> The data was publicly accessible for approximately 14 days.</li>
    <li><strong>Discovery:</strong> Discovered by an independent security researcher who notified the vendor.</li>
    <li><strong>Initial Response:</strong> The vendor patched the server immediately but delayed notifying FinTech Solutions for 48 hours to 'confirm the scope'.</li>
</ul>

<p>Your internal IT team proposes an immediate severance of the contract, while the legal team warns of service disruption liabilities. Meanwhile, under relevant data protection laws, the clock is ticking for regulatory notification. You must decide on the immediate steps to contain the breach, notify affected customers, and manage the vendor relationship without causing a complete collapse of customer support operations during a peak business season.</p>
";

        // --- Section 2 Content ---
        $section2Content = "
<p><strong>Corporate Governance and Financial Ethics</strong></p>
<p>Financial ethics goes beyond merely following the letter of the law; it encompasses the moral implications of financial decision-making and the fostering of a culture of transparency. In this section, we examine the complexities of conflict of interest, insider trading, and fiduciary duties. The role of a leader is to navigate these grey areas where profitable opportunities often conflict with ethical mandates.</p>

<p>Core principles to consider:</p>
<ul>
    <li><strong>Fiduciary Responsibility:</strong> Acting solely in the best interest of the client or stakeholders, prioritizing their gains over personal or corporate profit.</li>
    <li><strong>Transparency & Disclosure:</strong> The obligation to disclose all material facts that could influence a decision-making process.</li>
    <li><strong>Fairness:</strong> Treating all investors and stakeholders equally, ensuring no privileged group benefits from non-public information.</li>
</ul>

<p>Recent market events have highlighted how fragile investor confidence can be. A single lapse in ethical judgment by an executive can wipe out billions in market value. This section challenges you to identify potential ethical pitfalls in complex financial transactions and to propose remediation strategies that uphold the highest standards of corporate integrity.</p>
";

        // --- Sub Case 2.1 Content ---
        $subCase2Content = "
<p><strong>Scenario: The Merger Acquisition Dilemma</strong></p>
<p>Mr. Argus is a senior analyst at 'Global Ventures', an investment firm currently advising 'TechGiant Corp' on a potential acquisition of a smaller, innovative startup, 'NanoSoft'. This acquisition is strictly confidential and, once announced, is expected to drive NanoSoft's stock price up significantly. During a private family dinner, Mr. Argus learns that his brother-in-law, who owns a struggling logistics company, is facing bankruptcy.</p>

<p><strong>The Conflict:</strong></p>
<ul>
    <li><strong>Information Asymmetry:</strong> Mr. Argus possesses material non-public information (MNPI) regarding the imminent acquisition.</li>
    <li><strong>Personal Pressure:</strong> His family is pressuring him to 'help out' in any way possible, unaware of the specific deal.</li>
    <li><strong>The Leak:</strong> Two days later, Mr. Argus observes unusually high trading volume in NanoSoft stock, traced back to a brokerage firm used by his brother-in-law's close associates.</li>
</ul>

<p>Mr. Argus must now navigate a perilous situation. If he reports the suspicion, he risks incriminating family members. If he stays silent and regulators connect the dots, he faces criminal charges for aiding and abetting insider trading, effectively ending his career. He needs to determine the correct course of action compliant with SEC regulations and firm policy.</p>
";

        // 3. Create Section 1 (Section)
        $section1 = Section::create([
            'exam_id' => $exam->id,
            'title' => 'Section 1: Data Privacy & Compliance',
            'content' => $section1Content,
            'order_no' => 1,
            'status' => 1,
        ]);

        // 4. Create Sub Case 1.1 (CaseStudy)
        $subCase1 = CaseStudy::create([
            'section_id' => $section1->id,
            'title' => 'Case Study 1.1: Vendor Risk Management',
            'content' => $subCase1Content,
            'order_no' => 1,
            'status' => 1,
        ]);

        // Add Questions for Sub Case 1.1
        $this->addQuestion($subCase1->id, 'What is the immediate priority for FinTech Solutions according to GDPR/Standard Compliance protocols?', 'single', 1, 0, [
            ['Notify the Regulatory Authority within 72 hours of becoming aware.', 1],
            ['Sue CloudHelp Inc. for immediate breach of contract.', 0],
            ['Wait for the vendor to complete a full forensic audit before acting.', 0],
            ['Publicly deny the breach to prevent panic.', 0]
        ]);

        $this->addQuestion($subCase1->id, 'Which principle of Data Privacy was primarily violated by the "misconfigured server" exposure?', 'single', 0, 1, [
            ['Integrity and Confidentiality', 1],
            ['Data Portability', 0],
            ['Right to Access', 0],
            ['Automated Decision Making', 0]
        ]);

        // 5. Create Section 2 (Section)
        $section2 = Section::create([
            'exam_id' => $exam->id,
            'title' => 'Section 2: Financial Ethics & Governance',
            'content' => $section2Content,
            'order_no' => 2,
            'status' => 1,
        ]);

        // 6. Create Sub Case 2.1 (CaseStudy)
        $subCase2 = CaseStudy::create([
            'section_id' => $section2->id,
            'title' => 'Case Study 2.1: Insider Trading Risks',
            'content' => $subCase2Content,
            'order_no' => 1,
            'status' => 1,
        ]);

         // Add Questions for Sub Case 2.1
         $this->addQuestion($subCase2->id, 'How should Mr. Argus handle the suspected leak of information?', 'single', 1, 0, [
            ['Immediately report the unusual trading activity to his firm\'s Compliance Officer.', 1],
            ['Confront his brother-in-law privately to confirm the suspicion first.', 0],
            ['Ignore the situation as he did not directly execute the trades.', 0],
            ['Sell his own shares in TechGiant Corp to distance himself.', 0]
        ]);

        $this->addQuestion($subCase2->id, 'Select all parties that could be liable for Insider Trading in this scenario.', 'multiple', 0, 1, [
            ['Mr. Argus (if proven he leaked info)', 1],
            ['The brother-in-law\'s associates (tippees)', 1],
            ['TechGiant Corp management', 0],
            ['The general public buying stock', 0]
        ]);

        // 7. Assign Exam to ALL Students
        $students = User::where('role', 'student')->get();
        foreach ($students as $student) {
            StudentExam::create([
                'student_id' => $student->id,
                'exam_id' => $exam->id,
                'attempts_allowed' => 5,
                'attempts_used' => 0,
                'expiry_date' => now()->addYear(),
                'status' => 1,
            ]);
            $this->command->info("Assigned exam to student: " . $student->email);
        }

        $this->command->info('Database seeded successfully with new structure and formatted content.');
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
