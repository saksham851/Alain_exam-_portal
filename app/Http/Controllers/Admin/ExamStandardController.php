<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExamStandard;
use App\Models\ScoreCategory;
use App\Models\ContentArea;
use Illuminate\Support\Facades\DB;

class ExamStandardController extends Controller
{
    /**
     * Display a listing of exam standards
     */
    public function index()
    {
        $standards = ExamStandard::with(['categories.contentAreas'])->get();
        return view('admin.exam-standards.index', compact('standards'));
    }

    /**
     * Show the form for creating a new exam standard
     */
    public function create()
    {
        return view('admin.exam-standards.create');
    }

    /**
     * Store a newly created exam standard
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'categories' => 'required|array|min:1',
            'categories.*.name' => 'required|string|max:255',
            'categories.*.areas' => 'required|array|min:1',
            'categories.*.areas.*.name' => 'required|string|max:255',
            'categories.*.areas.*.max_points' => 'required|integer|min:0',
        ]);

        // Removed percentage sum validation as per user requirement "Score categories are not weighted at 100"

        DB::beginTransaction();
        try {
            $standard = ExamStandard::create([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            foreach($request->categories as $cIndex => $catData) {
                $category = ScoreCategory::create([
                    'exam_standard_id' => $standard->id,
                    'name' => $catData['name'],
                    'category_number' => $cIndex + 1,
                ]);

                foreach($catData['areas'] as $aIndex => $areaData) {
                    ContentArea::create([
                        'score_category_id' => $category->id,
                        'name' => $areaData['name'],
                        'max_points' => $areaData['max_points'] ?? 0,
                        'order_no' => $aIndex + 1,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.exam-standards.index')->with('success', 'Exam standard created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified exam standard
     */
    public function show($id)
    {
        $standard = ExamStandard::with(['categories.contentAreas'])->findOrFail($id);
        return view('admin.exam-standards.show', compact('standard'));
    }

    /**
     * Show the form for editing the specified exam standard
     */
    public function edit($id)
    {
        $standard = ExamStandard::with(['categories.contentAreas'])->findOrFail($id);
        $hasActiveExams = $standard->exams()->where('is_active', 1)->exists();
        return view('admin.exam-standards.edit', compact('standard', 'hasActiveExams'));
    }

    /**
     * Update the specified exam standard
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'categories' => 'required|array|min:1',
            'categories.*.name' => 'required|string|max:255',
            'categories.*.areas' => 'required|array|min:1',
            'categories.*.areas.*.name' => 'required|string|max:255',
            'categories.*.areas.*.max_points' => 'required|integer|min:0',
        ]);

        $standard = ExamStandard::findOrFail($id);

        // Check for active exams
        if ($standard->exams()->where('is_active', 1)->exists()) {
            return back()->withErrors(['error' => 'This standard is being used by an active exam and cannot be modified. Please deactivate the associated exam(s) first.'])->withInput();
        }

        DB::beginTransaction();
        try {
            $standard->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            $existingCategoryIds = $standard->categories->pluck('id')->toArray();
            $processedCategoryIds = [];

            foreach($request->categories as $cIndex => $catData) {
                $category = ScoreCategory::updateOrCreate(
                    [
                        'id' => (!empty($catData['id'])) ? $catData['id'] : null,
                        'exam_standard_id' => $standard->id
                    ],
                    [
                        'name' => $catData['name'],
                        'category_number' => $cIndex + 1,
                    ]
                );
                
                $processedCategoryIds[] = $category->id;

                $existingAreaIds = $category->contentAreas->pluck('id')->toArray();
                $processedAreaIds = [];

                if (isset($catData['areas']) && is_array($catData['areas'])) {
                    foreach($catData['areas'] as $aIndex => $areaData) {
                        $area = ContentArea::updateOrCreate(
                            [
                                'id' => (!empty($areaData['id'])) ? $areaData['id'] : null,
                                'score_category_id' => $category->id
                            ],
                            [
                                'name' => $areaData['name'],
                                'max_points' => $areaData['max_points'] ?? 0,
                                'order_no' => $aIndex + 1,
                            ]
                        );
                        $processedAreaIds[] = $area->id;
                    }
                }

                // Delete areas removed in the UI for this category
                $areasToDelete = array_diff($existingAreaIds, $processedAreaIds);
                if (!empty($areasToDelete)) {
                    ContentArea::whereIn('id', $areasToDelete)->delete();
                }
            }

            // Delete categories removed in the UI
            $categoriesToDelete = array_diff($existingCategoryIds, $processedCategoryIds);
            if (!empty($categoriesToDelete)) {
                foreach(ScoreCategory::whereIn('id', $categoriesToDelete)->get() as $cat) {
                    $cat->contentAreas()->delete();
                    $cat->delete();
                }
            }

            DB::commit();
            return redirect()->route('admin.exam-standards.index')->with('success', 'Exam standard updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified exam standard
     */
    public function destroy($id)
    {
        $standard = ExamStandard::findOrFail($id);
        
        // Check if any exams are using this standard
        if ($standard->exams()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete this standard. It is being used by ' . $standard->exams()->count() . ' exam(s).']);
        }

        $standard->delete();
        return redirect()->route('admin.exam-standards.index')->with('success', 'Exam standard deleted successfully!');
    }
}
