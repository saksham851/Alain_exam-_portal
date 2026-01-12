@extends('layouts.app')

@section('content')
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Add Case Studies</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('admin.case-studies-bank.index') }}">Case Studies Bank</a></li>
          <li class="breadcrumb-item" aria-current="page">Create</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row" x-data="caseStudyForm()">
    <div class="col-md-12">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('admin.case-studies-bank.store') }}" method="POST" id="caseStudyForm">
            @csrf
            
            <div class="card">
                <div class="card-header">
                    <h5>Select Target Section</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Select Exam <span class="text-danger">*</span></label>
                            <select name="exam_id" id="examSelect" class="form-select" x-model="selectedExamId" @change="fetchSections()" required {{ request('exam_id') ? 'style=pointer-events:none;background-color:#e9ecef;' : '' }}>
                                <option value="">Choose Exam...</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Select Section <span class="text-danger">*</span></label>
                            <select name="section_id" id="sectionSelect" class="form-select" x-model="selectedSectionId" required>
                                <option value="">Select Exam First...</option>
                                <template x-for="section in sections" :key="section.id">
                                    <option :value="section.id" x-text="section.title"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 text-muted">Case Studies</h6>
                        <button type="button" class="btn btn-sm btn-primary" @click="addCaseStudy()">
                            <i class="ti ti-plus me-1"></i> Add Another Case Study
                        </button>
                    </div>

                    <template x-for="(caseStudy, index) in caseStudies" :key="index">
                        <div class="card border mb-3">
                            <div class="card-body position-relative">
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2" 
                                        @click="removeCaseStudy(index)" x-show="caseStudies.length > 1">
                                    <i class="ti ti-x"></i>
                                </button>
                                
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                                        <input type="text" :name="'case_studies['+index+'][title]'" class="form-control" 
                                               x-model="caseStudy.title" placeholder="Enter case study title" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Order No <span class="text-danger">*</span></label>
                                        <input type="number" :name="'case_studies['+index+'][order_no]'" class="form-control" 
                                               x-model="caseStudy.order_no" min="1" required>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Content / Scenario</label>
                                        <textarea :id="'editor_'+index" :name="'case_studies['+index+'][content]'" 
                                                  class="form-control" rows="4" x-model="caseStudy.content"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="card-footer text-end bg-light">
                    <a href="{{ route('admin.case-studies-bank.index') }}" class="btn btn-secondary me-2">
                        <i class="ti ti-x me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> Save All Case Studies
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    function caseStudyForm() {
        return {
            selectedExamId: '{{ request("exam_id") ?? "" }}',
            selectedSectionId: '{{ request("section_id") ?? "" }}',
            sections: [],
            caseStudies: [
                { title: '', order_no: 1, content: '' }
            ],
            editors: [],
            
            init() {
                if(this.selectedExamId) {
                    this.fetchSections();
                }
                
                this.$nextTick(() => { this.initEditor(0); });
            },

            addCaseStudy() {
                this.caseStudies.push({ 
                    title: '', 
                    order_no: this.caseStudies.length + 1, 
                    content: '' 
                });
                this.$nextTick(() => {
                    this.initEditor(this.caseStudies.length - 1);
                });
            },

            removeCaseStudy(index) {
                if(this.caseStudies.length > 1) {
                    this.caseStudies.splice(index, 1);
                }
            },

            initEditor(index) {
                const el = document.getElementById('editor_' + index);
                if(el && !this.editors[index]) {
                    ClassicEditor.create(el)
                        .then(editor => {
                            this.editors[index] = editor;
                        })
                        .catch(error => { console.error(error); });
                }
            },

            async fetchSections() {
                if(!this.selectedExamId) {
                    this.sections = [];
                    return;
                }
                
                try {
                    const response = await fetch(`/admin/questions-ajax/case-studies/${this.selectedExamId}`);
                    this.sections = await response.json();
                    
                    if(this.selectedSectionId) {
                         this.$nextTick(() => {
                             document.getElementById('sectionSelect').value = this.selectedSectionId;
                         });
                    }
                } catch(e) {
                    console.error('Error fetching sections', e);
                }
            }
        }
    }
</script>

@endsection
