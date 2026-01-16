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

      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row">
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

        <form id="caseStudyForm" action="{{ isset($caseStudy) ? route('admin.sections.update', $caseStudy->id) : route('admin.sections.store') }}" method="POST">
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
                            <select name="exam_id" class="form-select" required {{ request('exam_id') ? 'style=pointer-events:none;background-color:#e9ecef;' : '' }}>
                                <option value="">Select Exam</option>
                                @foreach($exams as $exam)
                                   <option value="{{ $exam->id }}" {{ (old('exam_id', request('exam_id', $caseStudy->exam_id ?? '')) == $exam->id) ? 'selected' : '' }}>{{ $exam->name }}</option>
                                @endforeach
                            </select>
                            @if(request('exam_id'))
                                <input type="hidden" name="exam_id" value="{{ request('exam_id') }}">
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Section Name</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $caseStudy->title ?? '') }}" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea id="cs_description" name="content" class="form-control">{{ old('content', $caseStudy->content ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 text-end">
                <a href="{{ route('admin.sections.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary" {{ isset($caseStudy) && $caseStudy->exam && $caseStudy->exam->is_active == 1 ? 'disabled' : '' }}>Save Section</button>
            </div>
        </form>



        @if(isset($existingSections) && $existingSections->count() > 0)
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Existing Sections for Selected Exam</h5>
                <span class="badge bg-light-primary">{{ $existingSections->count() }} Sections Found</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Section Name</th>
                                <th>Description</th>
                                <th class="text-end">Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($existingSections as $existingSection)
                            <tr>
                                <td><strong>{{ $existingSection->title }}</strong></td>
                                <td>{{ Str::limit(strip_tags($existingSection->content), 60) }}</td>
                                <td class="text-end text-muted">{{ $existingSection->created_at->format('d M Y, h:i A') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<script>
    let mainEditor;
    
    document.addEventListener("DOMContentLoaded", function() {
        // Main case study description editor
        ClassicEditor.create(document.querySelector('#cs_description'))
            .then(editor => {
                mainEditor = editor;
            })
            .catch(error => { console.error(error); });
        
        // Sync CKEditor data to textarea before form submit
        document.getElementById('caseStudyForm').addEventListener('submit', function(e) {
            // Update main description
            if (mainEditor) {
                document.querySelector('#cs_description').value = mainEditor.getData();
            }
        });
    });
</script>
@endsection
