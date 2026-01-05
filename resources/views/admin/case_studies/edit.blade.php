@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">{{ isset($caseStudy) ? 'Edit Section' : 'Create Section' }}</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('admin.case-studies.index') }}">Sections</a></li>
          <li class="breadcrumb-item" aria-current="page">{{ isset($caseStudy) ? 'Edit' : 'Create' }}</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row" x-data="caseStudyForm()">
    <div class="col-md-12">
        @if(isset($caseStudy) && $caseStudy->exam && $caseStudy->exam->is_active == 1)
        <div class="alert alert-warning d-flex align-items-start gap-3 mb-4" role="alert">
            <i class="ti ti-lock" style="font-size: 20px; margin-top: 3px;"></i>
            <div>
                <strong>This Exam is Active/Locked</strong>
                <p class="mb-0 mt-2">This exam is currently active. You cannot edit this section or add/delete case studies. Please deactivate the exam first.</p>
            </div>
        </div>
        @endif

        <form id="caseStudyForm" action="{{ isset($caseStudy) ? route('admin.case-studies.update', $caseStudy->id) : route('admin.case-studies.store') }}" method="POST">
            @csrf
            @if(isset($caseStudy)) @method('PUT') @endif

            <div class="card">
                <div class="card-header">
                    <h5>Main Section Details</h5>
                </div>
                <div class="card-body" {{ isset($caseStudy) && $caseStudy->exam && $caseStudy->exam->is_active == 1 ? 'style=opacity:0.5;pointer-events:none' : '' }}>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Assign to Exam</label>
                            <select name="exam_id" class="form-select" required>
                                <option value="">Select Exam</option>
                                @foreach($exams as $exam)
                                   <option value="{{ $exam->id }}" {{ (old('exam_id', $caseStudy->exam_id ?? '') == $exam->id) ? 'selected' : '' }}>{{ $exam->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Section Name</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $caseStudy->title ?? '') }}" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description / Scenario</label>
                            <textarea id="cs_description" name="content" class="form-control">{{ old('content', $caseStudy->content ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sub Case Studies Logic -->
            <div class="card mt-3" {{ isset($caseStudy) && $caseStudy->exam && $caseStudy->exam->is_active == 1 ? 'style=opacity:0.5;pointer-events:none' : '' }}>
                <!-- Warning Alert for Case Study Changes -->
<div class="alert alert-warning alert-dismissible fade show d-flex align-items-start gap-2 m-3 mb-0" role="alert">
                    <i class="ti ti-alert-triangle" style="font-size: 1.3rem; margin-top: 2px;"></i>
                    <div>
                        <strong>Important Notice for Admins:</strong> 
                        <p class="mb-0 mt-1 small">
                            When making changes to case studies, please be careful as your changes will affect all questions linked to this case study.
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Case Studies</h5>
                    <button type="button" @click="addSubCase" class="btn btn-sm btn-light-primary"><i class="ti ti-plus"></i> Add Case Study</button>
                </div>
                <div class="card-body">
                    <template x-for="(subCase, index) in subCases" :key="index">
                        <div class="border rounded p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between mb-2">
                                <h6 class="mb-0 text-primary">Case Study #<span x-text="index + 1"></span></h6>
                                <button type="button" class="btn btn-sm btn-outline-danger" @click="removeSubCase(index)"><i class="ti ti-trash"></i></button>
                            </div>
                            
                            <div class="mb-2">
                                <label class="form-label small">Case Study Title</label>
                                <input type="hidden" :name="'sub_cases['+index+'][id]'" x-model="subCase.id">
                                <input type="text" :name="'sub_cases['+index+'][title]'" x-model="subCase.title" class="form-control form-control-sm" required>
                            </div>
                            
                            <div class="mb-2">
                                <label class="form-label small">Content / Scenario</label>
                                <textarea :id="'subcase_content_'+index" :name="'sub_cases['+index+'][content]'" x-model="subCase.content" class="form-control subcase-editor" rows="3"></textarea>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="mt-3 text-end">
                <a href="{{ route('admin.case-studies.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary" {{ isset($caseStudy) && $caseStudy->exam && $caseStudy->exam->is_active == 1 ? 'disabled' : '' }}>Save Section</button>
            </div>
        </form>
    </div>
</div>

@php
    $defaultSubCases = [['id' => null, 'title' => '', 'content' => '']];
    // Load sub-cases from relationship
    if(isset($caseStudy) && $caseStudy->caseStudies) {
        $currentSubCases = $caseStudy->caseStudies->map(function($sc) {
            return ['id' => $sc->id, 'title' => $sc->title, 'content' => $sc->content ?? ''];
        })->toArray();
    } else {
        $currentSubCases = $defaultSubCases;
    }
@endphp

<script>
    function caseStudyForm() {
        return {
            subCases: @json($currentSubCases),
            addSubCase() {
                this.subCases.push({
                    id: null,
                    title: '', 
                    content: ''
                });
                // Initialize CKEditor for new field after DOM update
                this.$nextTick(() => {
                    const index = this.subCases.length - 1;
                    const editorId = 'subcase_content_' + index;
                    if (document.getElementById(editorId) && !document.getElementById(editorId).classList.contains('ck-editor__editable')) {
                        ClassicEditor.create(document.getElementById(editorId))
                            .catch(error => { console.error(error); });
                    }
                });
            },
            removeSubCase(index) {
                if(confirm('Remove this case study?')) {
                    this.subCases.splice(index, 1);
                }
            }
        }
    }
</script>

<!-- AlpineJS for dynamic form handling -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<script>
    let mainEditor;
    let subCaseEditors = [];
    
    document.addEventListener("DOMContentLoaded", function() {
        // Main case study description editor
        ClassicEditor.create(document.querySelector('#cs_description'))
            .then(editor => {
                mainEditor = editor;
            })
            .catch(error => { console.error(error); });
        
        // Initialize editors for existing sub-case content fields
        document.querySelectorAll('.subcase-editor').forEach((element, index) => {
            ClassicEditor.create(element)
                .then(editor => {
                    subCaseEditors[index] = editor;
                })
                .catch(error => { console.error(error); });
        });
        
        // Sync CKEditor data to textareas before form submit
        document.getElementById('caseStudyForm').addEventListener('submit', function(e) {
            // Update main description
            if (mainEditor) {
                document.querySelector('#cs_description').value = mainEditor.getData();
            }
            
            // Update all sub-case editors
            document.querySelectorAll('.subcase-editor').forEach((element, index) => {
                if (subCaseEditors[index]) {
                    element.value = subCaseEditors[index].getData();
                }
            });
        });
    });
</script>
@endsection
