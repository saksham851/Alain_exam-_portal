<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\Section;
use App\Models\CaseStudy;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Support\Facades\DB;

class CaseStudyCloneController extends Controller
{
    /**
     * Show available case studies from other exams for cloning
     */
    public function showAvailableCaseStudies($examId)
    {
        $targetExam = Exam::findOrFail($examId);
        
        // Get all other exams with their sections and case studies
        $sourceExams = Exam::where('id', '!=', $examId)
            ->where('status', 1)
            ->with(['sections.caseStudies'])
            ->orderBy('name')
            ->get();
        
        return view('admin.case-studies.clone', compact('targetExam', 'sourceExams'));
    }
    
    /**
     * Clone selected case studies to target exam
     */
    public function cloneCaseStudies(Request $request, $examId)
    {
        $request->validate([
            'case_study_ids' => 'required|array',
            'case_study_ids.*' => 'exists:case_studies,id',
        ]);
        
        $targetExam = Exam::findOrFail($examId);
        $caseStudyIds = $request->case_study_ids;
        
        DB::beginTransaction();
        
        try {
            $clonedCount = 0;
            
            foreach ($caseStudyIds as $caseStudyId) {
                $sourceCaseStudy = CaseStudy::with(['questions.options'])->findOrFail($caseStudyId);
                
                // Get or create section in target exam
                $sourceSection = $sourceCaseStudy->section;
                $targetSection = Section::firstOrCreate(
                    [
                        'exam_id' => $targetExam->id,
                        'title' => $sourceSection->title,
                    ],
                    [
                        'content' => $sourceSection->content,
                        'order_no' => Section::where('exam_id', $targetExam->id)->max('order_no') + 1,
                        'status' => 1,
                    ]
                );
                
                // Clone the case study
                $newCaseStudy = $sourceCaseStudy->replicate();
                $newCaseStudy->section_id = $targetSection->id;
                $newCaseStudy->order_no = CaseStudy::where('section_id', $targetSection->id)->max('order_no') + 1;
                $newCaseStudy->save();
                
                // Clone all questions and their options
                foreach ($sourceCaseStudy->questions as $sourceQuestion) {
                    $newQuestion = $sourceQuestion->replicate();
                    $newQuestion->case_study_id = $newCaseStudy->id;
                    $newQuestion->save();
                    
                    // Clone question options
                    foreach ($sourceQuestion->options as $sourceOption) {
                        $newOption = $sourceOption->replicate();
                        $newOption->question_id = $newQuestion->id;
                        $newOption->save();
                    }
                }
                
                $clonedCount++;
            }
            
            DB::commit();
            
            return redirect()
                ->route('admin.exams.edit', $examId)
                ->with('success', "Successfully cloned {$clonedCount} case study(ies) with all their questions!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->with('error', 'Error cloning case studies: ' . $e->getMessage());
        }
    }
    
    /**
     * Get case studies for a specific exam (AJAX)
     */
    public function getCaseStudiesByExam($examId)
    {
        $sections = Section::where('exam_id', $examId)
            ->with(['caseStudies' => function($query) {
                $query->withCount('questions');
            }])
            ->get();
        
        return response()->json([
            'success' => true,
            'sections' => $sections
        ]);
    }
}
