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
        $query = CaseStudy::where('status', 1)->with([
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
            $query->whereHas('section.exam', function($q) use ($request) {
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
        $certificationTypes = Exam::where('status', 1)
            ->distinct()
            ->orderBy('certification_type')
            ->pluck('certification_type')
            ->filter()
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
    public function create(Request $request)
    {
        $exams = Exam::where('status', 1)->orderBy('name')->get();
        
        $existingCaseStudies = collect();
        if ($request->has('section_id')) {
            $existingCaseStudies = CaseStudy::where('section_id', $request->section_id)
                ->where('status', 1)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('admin.case-studies-bank.create', compact('exams', 'existingCaseStudies'));
    }

    /**
 * Store a new case study
 */
public function store(Request $request)
{
    $request->validate([
        'section_id' => 'required|exists:sections,id',
        'existing_case_studies' => 'nullable|array',
        'existing_case_studies.*.title' => 'required|string|max:255',
        'existing_case_studies.*.content' => 'nullable|string',
        'existing_case_studies.*.order_no' => 'required|integer|min:1',
        'deleted_case_studies' => 'nullable|array',
        'deleted_case_studies.*' => 'integer|exists:case_studies,id',
        'case_studies' => 'nullable|array',
        'case_studies.*.title' => 'required_with:case_studies|string|max:255',
        'case_studies.*.content' => 'nullable|string',
        'case_studies.*.order_no' => 'required_with:case_studies|integer|min:1',
    ]);
    
    // Ensure at least one action is performed (update, create, or delete)
    if (empty($request->existing_case_studies) && empty($request->case_studies) && empty($request->deleted_case_studies)) {
        return redirect()->back()->with('error', 'Please add, edit, or delete at least one case study.');
    }

    // Check if exam is active
    $section = Section::with('exam')->findOrFail($request->section_id);
    if ($section->exam && $section->exam->is_active == 1) {
        return redirect()->back()->with('error', 'Cannot add/edit case studies to an active exam. Please deactivate the exam first.');
    }

    DB::beginTransaction();
    
    try {
        $createdCount = 0;
        $updatedCount = 0;
        $deletedCount = 0;

        // Delete Case Studies
        if ($request->has('deleted_case_studies')) {
            foreach ($request->deleted_case_studies as $id) {
                $caseStudy = CaseStudy::find($id);
                // Ensure it belongs to the correct section for safety
                if ($caseStudy && $caseStudy->section_id == $request->section_id) {
                     $caseStudy->update(['status' => 0]);
                     $deletedCount++;
                }
            }
        }
        
        // Update Existing Case Studies
        if ($request->has('existing_case_studies')) {
            foreach ($request->existing_case_studies as $id => $data) {
                // If this ID is also in deleted_case_studies (edge case), skip update
                if (in_array($id, $request->input('deleted_case_studies', []))) continue;

                $existingCaseStudy = CaseStudy::find($id);
                if ($existingCaseStudy && $existingCaseStudy->section_id == $request->section_id) {
                    $existingCaseStudy->update([
                        'title' => $data['title'],
                        'content' => $data['content'] ?? null,
                        'order_no' => $data['order_no'],
                    ]);
                    $updatedCount++;
                }
            }
        }

        // Create New Case Studies
        if ($request->has('case_studies')) {
            foreach ($request->case_studies as $caseStudyData) {
                // Skip empty entries if any
                if(empty($caseStudyData['title'])) continue;

                CaseStudy::create([
                    'section_id' => $request->section_id,
                    'title' => $caseStudyData['title'],
                    'content' => $caseStudyData['content'] ?? null,
                    'order_no' => $caseStudyData['order_no'],
                    'status' => 1,
                ]);
                $createdCount++;
            }
        }
        
        DB::commit();
        
        $messageParts = [];
        if ($createdCount > 0) $messageParts[] = "created {$createdCount} new " . \Illuminate\Support\Str::plural('case study', $createdCount);
        if ($updatedCount > 0) $messageParts[] = "updated {$updatedCount} existing " . \Illuminate\Support\Str::plural('case study', $updatedCount);
        if ($deletedCount > 0) $messageParts[] = "deleted {$deletedCount} existing " . \Illuminate\Support\Str::plural('case study', $deletedCount);
        
        $message = "Successfully " . implode(' and ', $messageParts) . "!";
        
        return redirect()->route('admin.case-studies-bank.index')
            ->with('case_study_created_success', true)
            ->with('selected_exam_id', $section->exam_id)
            ->with('selected_section_id', $request->section_id)
            ->with('success', $message);
        
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Error processing request: ' . $e->getMessage());
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
            $targetSection = Section::with('exam')->findOrFail($request->target_section_id);
            if ($targetSection->exam && $targetSection->exam->is_active == 1) {
                return redirect()->back()->with('error', 'Cannot clone case studies into an active exam. Please deactivate the exam first.');
            }
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
                ->route('admin.case-studies-bank.index')
                ->with('case_study_created_success', true)
                ->with('selected_exam_id', $targetSection->exam_id)
                ->with('selected_section_id', $targetSection->id)
                ->with('success', "Successfully copied {$copiedCount} " . \Illuminate\Support\Str::plural('case study', $copiedCount) . " with all their questions to the selected section!");

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

    public function edit($id)
    {
        $caseStudy = CaseStudy::with('section.exam')->findOrFail($id);
        $exams = Exam::where('status', 1)->orderBy('name')->get();
        return view('admin.case-studies-bank.edit', compact('caseStudy', 'exams'));
    }

    public function update(Request $request, $id)
    {
        $caseStudy = CaseStudy::with('section.exam')->findOrFail($id);
        
        // Check if current exam is active
        if ($caseStudy->section && $caseStudy->section->exam && $caseStudy->section->exam->is_active == 1) {
             return redirect()->back()->with('error', 'Cannot edit case study in an active exam.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'section_id' => 'required|exists:sections,id',
            'content' => 'nullable|string',
            'order_no' => 'required|integer|min:1',
        ]);

        // Check if target section exam is active (if changing section)
        $newSection = Section::with('exam')->find($request->section_id);
        if ($newSection && $newSection->exam && $newSection->exam->is_active == 1) {
            return redirect()->back()->with('error', 'Cannot move case study to an active exam.');
        }

        $caseStudy->update([
            'title' => $request->title,
            'section_id' => $request->section_id,
            'content' => $request->content,
            'order_no' => $request->order_no,
        ]);

        return redirect()->route('admin.case-studies-bank.index')->with('success', 'Case Study updated successfully.');
    }

    public function destroy($id)
    {
         try {
             $caseStudy = CaseStudy::with('section.exam')->findOrFail($id);

             if ($caseStudy->section && $caseStudy->section->exam && $caseStudy->section->exam->is_active == 1) {
                 if (request()->wantsJson()) {
                      return response()->json(['success' => false, 'message' => 'Cannot delete case study from an active exam.']);
                 }
                 return redirect()->back()->with('error', 'Cannot delete case study from an active exam.');
             }
             
             // Explicitly set status and save
             $caseStudy->status = 0;
             $saved = $caseStudy->save();
             
             if ($saved) {
                 if (request()->wantsJson()) {
                      return response()->json(['success' => true, 'message' => 'Case Study deleted successfully.']);
                 }
                 return redirect()->back()->with('success', 'Case Study deleted successfully.');
             } else {
                 if (request()->wantsJson()) {
                      return response()->json(['success' => false, 'message' => 'Failed to update case study status.']);
                 }
                 return redirect()->back()->with('error', 'Failed to update case study status.');
             }
         } catch (\Exception $e) {
             if (request()->wantsJson()) {
                  return response()->json(['success' => false, 'message' => 'Error deleting case study: ' . $e->getMessage()]);
             }
             return redirect()->back()->with('error', 'Error deleting case study: ' . $e->getMessage());
         }
    }
}
