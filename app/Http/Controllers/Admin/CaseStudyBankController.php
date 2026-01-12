<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CaseStudy;
use App\Models\Section;
use App\Models\Exam;
use App\Models\ExamCategory;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Support\Facades\DB;

class CaseStudyBankController extends Controller
{
    /**
     * Display the case studies bank with filters
     */
    public function index(Request $request)
    {
        $query = CaseStudy::with([
            'section.exam.category',
            'clonedFrom',
            'clonedFromSection.exam'
        ]);

        // Apply filters
        if ($request->filled('exam_category')) {
            $query->whereHas('section.exam.category', function($q) use ($request) {
                $q->where('id', $request->exam_category);
            });
        }

        if ($request->filled('certification_type')) {
            $query->whereHas('section.exam.category', function($q) use ($request) {
                $q->where('certification_type', $request->certification_type);
            });
        }

        if ($request->filled('exam')) {
            $query->whereHas('section.exam', function($q) use ($request) {
                $q->where('id', $request->exam);
            });
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $caseStudies = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get filter data
        $examCategories = ExamCategory::where('status', 1)->orderBy('name')->get();
        $exams = Exam::where('status', 1)->orderBy('name')->get();
        
        // Get certification types
        $certificationTypes = ExamCategory::where('status', 1)
            ->distinct()
            ->pluck('certification_type')
            ->filter()
            ->sort()
            ->values();

        return view('admin.case-studies-bank.index', compact(
            'caseStudies',
            'examCategories',
            'exams',
            'certificationTypes'
        ));
    }

    /**
     * Show the form for creating a new case study
     */
    public function create()
    {
        $exams = Exam::where('status', 1)->orderBy('name')->get();
        return view('admin.case-studies-bank.create', compact('exams'));
    }

    /**
 * Store a new case study
 */
public function store(Request $request)
{
    $request->validate([
        'section_id' => 'required|exists:sections,id',
        'case_studies' => 'required|array|min:1',
        'case_studies.*.title' => 'required|string|max:255',
        'case_studies.*.content' => 'nullable|string',
        'case_studies.*.order_no' => 'required|integer|min:1',
    ]);

    // Check if exam is active
    $section = Section::with('exam')->findOrFail($request->section_id);
    if ($section->exam && $section->exam->is_active == 1) {
        return redirect()->back()->with('error', 'Cannot add case studies to an active exam. Please deactivate the exam first.');
    }

    DB::beginTransaction();
    
    try {
        $createdCount = 0;
        
        foreach ($request->case_studies as $caseStudyData) {
            CaseStudy::create([
                'section_id' => $request->section_id,
                'title' => $caseStudyData['title'],
                'content' => $caseStudyData['content'] ?? null,
                'order_no' => $caseStudyData['order_no'],
                'status' => 1,
            ]);
            $createdCount++;
        }
        
        DB::commit();
        
        $message = $createdCount > 1 
            ? "Successfully created {$createdCount} case studies!" 
            : 'Case Study created successfully!';
        
        return redirect()->route('admin.case-studies-bank.index')
            ->with('case_study_created_success', true)
            ->with('selected_exam_id', $request->exam_id)
            ->with('selected_section_id', $request->section_id)
            ->with('success', $message);
        
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Error creating case studies: ' . $e->getMessage());
    }
}    

    /**
     * Copy selected case studies to a target section
     */
    public function copy(Request $request)
    {
        $request->validate([
            'case_study_ids' => 'required|array',
            'case_study_ids.*' => 'exists:case_studies,id',
            'target_section_id' => 'required|exists:sections,id',
        ]);

        DB::beginTransaction();

        try {
            $targetSection = Section::findOrFail($request->target_section_id);
            $copiedCount = 0;

            foreach ($request->case_study_ids as $caseStudyId) {
                $sourceCaseStudy = CaseStudy::with(['questions.options'])->findOrFail($caseStudyId);

                // Create a deep clone of the case study
                $newCaseStudy = $sourceCaseStudy->replicate();
                $newCaseStudy->section_id = $targetSection->id;
                $newCaseStudy->order_no = CaseStudy::where('section_id', $targetSection->id)->max('order_no') + 1;
                
                // Track clone information
                $newCaseStudy->cloned_from_id = $sourceCaseStudy->id;
                $newCaseStudy->cloned_from_section_id = $sourceCaseStudy->section_id;
                $newCaseStudy->cloned_at = now();
                
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

                $copiedCount++;
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', "Successfully copied {$copiedCount} case study(ies) with all their questions to the selected section!");

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Error copying case studies: ' . $e->getMessage());
        }
    }

    /**
     * Get sections for a specific exam (AJAX)
     */
    public function getSectionsByExam($examId)
    {
        $sections = Section::where('exam_id', $examId)
            ->orderBy('title')
            ->get(['id', 'title']);

        return response()->json([
            'success' => true,
            'sections' => $sections
        ]);
    }
}
