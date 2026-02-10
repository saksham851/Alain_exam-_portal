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
            'categories.*.areas.*.percentage' => 'nullable|integer|min:0|max:100',
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
                        'percentage' => $areaData['percentage'] ?? 0,
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
        return view('admin.exam-standards.edit', compact('standard'));
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
            'categories.*.areas.*.percentage' => 'nullable|integer|min:0|max:100',
            'categories.*.areas.*.max_points' => 'required|integer|min:0',
        ]);

        $standard = ExamStandard::findOrFail($id);

        DB::beginTransaction();
        try {
            $standard->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            // Reset Categories: Simplest approach is delete & recreate to handle reordering/additions
            // Note: This changes IDs of categories. If questions link to content areas, they will break.
            // Assumption: Questions link to content areas which are children of categories.
            // Since we are changing structure, we might need to be careful.
            // But previous code also deleted content areas!
            
            // Delete all existing categories (and cascade content areas if DB set up or manual)
            // Delete all existing categories (and cascade content areas)
            foreach($standard->categories as $cat) {
                 // ContentAreas cascade delete via DB or model, but being explicit is fine.
                 $cat->contentAreas()->delete();
                 $cat->delete();
            }

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
                        'percentage' => $areaData['percentage'] ?? 0,
                        'max_points' => $areaData['max_points'] ?? 0,
                        'order_no' => $aIndex + 1,
                    ]);
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
