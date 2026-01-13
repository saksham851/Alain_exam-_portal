@extends('layouts.app')

@section('content')
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Edit Case Study</h5>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row" x-data="editCaseStudyForm()">
    <div class="col-md-12">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('admin.case-studies-bank.update', $caseStudy->id) }}" method="POST" id="caseStudyForm">
            @csrf
            @method('PUT')
            
            <div class="card">
                <div class="card-header">
                    <h5>Case Study Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Select Exam <span class="text-danger">*</span></label>
                            <select name="exam_id" id="examSelect" class="form-select" x-model="selectedExamId" @change="fetchSections()" required>
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
                                    <option :value="section.id" x-text="section.title" :selected="section.id == selectedSectionId"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" 
                                   value="{{ old('title', $caseStudy->title) }}" placeholder="Enter case study title" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Order No <span class="text-danger">*</span></label>
                            <input type="number" name="order_no" class="form-control" 
                                   value="{{ old('order_no', $caseStudy->order_no) }}" min="1" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Content / Scenario</label>
                            <textarea id="editor" name="content" class="form-control" rows="4">{{ old('content', $caseStudy->content) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end bg-light">
                    <a href="{{ route('admin.case-studies-bank.index') }}" class="btn btn-secondary me-2">
                        <i class="ti ti-x me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> Update Case Study
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    function editCaseStudyForm() {
        return {
            selectedExamId: '{{ $caseStudy->section->exam_id }}',
            selectedSectionId: '{{ $caseStudy->section_id }}',
            sections: [],
            
            init() {
                if(this.selectedExamId) {
                    this.fetchSections();
                }
                
                ClassicEditor.create(document.querySelector('#editor'))
                    .catch(error => { console.error(error); });
            },

            async fetchSections() {
                if(!this.selectedExamId) {
                    this.sections = [];
                    return;
                }
                
                try {
                    const response = await fetch(`/admin/questions-ajax/case-studies/${this.selectedExamId}`);
                    this.sections = await response.json();
                } catch(e) {
                    console.error('Error fetching sections', e);
                }
            }
        }
    }
</script>
@endsection
