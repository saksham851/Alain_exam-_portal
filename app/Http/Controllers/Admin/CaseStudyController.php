<?php

namespace App\Http\Controllers\Admin;

use App\Models\CaseStudy;
use App\Models\Section;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CaseStudyController extends Controller
{
    public function index()
    {
        $caseStudies = CaseStudy::where('status', 1)->with('section')->get();
        return view('admin.sub_case_studies.index', ['subCaseStudies' => $caseStudies]);
    }

    public function create()
    {
        $caseStudy = null;
        $sections = Section::where('status', 1)->get();
        return view('admin.sub_case_studies.edit', ['subCaseStudy' => $caseStudy, 'caseStudies' => $sections]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'section_id' => 'required|exists:sections,id',
            'content' => 'nullable|string',
            'order_no' => 'required|integer',
        ]);

        $data = $request->except('section_id'); 
        $data['section_id'] = $request->section_id;
        $data['status'] = 1;
        CaseStudy::create($data);

        return redirect()->route('admin.sub_case_studies.index')->with('success', 'Case Study created successfully.');
    }

    public function edit($id)
    {
        $caseStudy = CaseStudy::find($id);
        if(!$caseStudy || $caseStudy->status == 0) return back()->with('error', 'Case Study not found');

        $sections = Section::where('status', 1)->get();
        return view('admin.sub_case_studies.edit', ['subCaseStudy' => $caseStudy, 'caseStudies' => $sections]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'section_id' => 'required|exists:sections,id',
            'content' => 'nullable|string',
            'order_no' => 'required|integer',
        ]);

        $caseStudy = CaseStudy::find($id);
        if(!$caseStudy || $caseStudy->status == 0) return back()->with('error', 'Case Study not found');

        $caseStudy->update([
             'title' => $request->title,
             'section_id' => $request->section_id,
             'content' => $request->content,
             'order_no' => $request->order_no,
        ]);

        return redirect()->route('admin.sub_case_studies.index')->with('success', 'Case Study updated successfully.');
    }

    public function destroy($id)
    {
        $caseStudy = CaseStudy::find($id);
        if($caseStudy) {
            $caseStudy->update(['status' => 0]);
        }
        return back()->with('success', 'Case Study deleted successfully.');
    }
}
