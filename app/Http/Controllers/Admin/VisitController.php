<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visit;
use App\Models\CaseStudy;

class VisitController extends Controller
{
    public function ajaxStore(Request $request)
    {
        $request->validate([
            'case_study_id' => 'required|exists:case_studies,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $cs = CaseStudy::with('section.exam')->find($request->case_study_id);
        if ($cs && $cs->section && $cs->section->exam && $cs->section->exam->is_active == 1) {
            return response()->json(['success' => false, 'message' => 'Cannot modify an active exam.'], 422);
        }

        $visit = Visit::create([
            'case_study_id' => $request->case_study_id,
            'title' => $request->title,
            'description' => $request->description,
            'order_no' => Visit::where('case_study_id', $request->case_study_id)->max('order_no') + 1,
            'status' => 1,
        ]);

        return response()->json(['success' => true, 'visit' => $visit]);
    }

    public function ajaxShow($id)
    {
        $visit = Visit::find($id);
        if (!$visit) return response()->json(['success' => false, 'message' => 'Visit not found'], 404);
        return response()->json(['success' => true, 'visit' => $visit]);
    }

    public function ajaxUpdate(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $visit = Visit::with('caseStudy.section.exam')->find($id);
        if (!$visit) return response()->json(['success' => false, 'message' => 'Visit not found'], 404);

        if ($visit->caseStudy && $visit->caseStudy->section && $visit->caseStudy->section->exam && $visit->caseStudy->section->exam->is_active == 1) {
            return response()->json(['success' => false, 'message' => 'Cannot modify an active exam.'], 422);
        }

        $visit->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return response()->json(['success' => true, 'visit' => $visit]);
    }

    public function ajaxDestroy($id)
    {
        $visit = Visit::with('caseStudy.section.exam')->find($id);
        if (!$visit) return response()->json(['success' => false, 'message' => 'Visit not found'], 404);

        if ($visit->caseStudy && $visit->caseStudy->section && $visit->caseStudy->section->exam && $visit->caseStudy->section->exam->is_active == 1) {
            return response()->json(['success' => false, 'message' => 'Cannot modify an active exam.'], 422);
        }

        $visit->update(['status' => 0]);
        return response()->json(['success' => true]);
    }
}
