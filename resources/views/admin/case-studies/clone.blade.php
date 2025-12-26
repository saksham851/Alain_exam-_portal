@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Clone Case Studies to {{ $targetExam->name }}</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('admin.exams.index') }}">Exams</a></li>
          <li class="breadcrumb-item"><a href="{{ route('admin.exams.edit', $targetExam->id) }}">{{ $targetExam->name }}</a></li>
          <li class="breadcrumb-item" aria-current="page">Clone Case Studies</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Select Case Studies to Clone</h5>
                <p class="text-muted mb-0">Choose case studies from existing exams. All questions will be copied automatically.</p>
            </div>
            <div class="card-body">
                @if($sourceExams->isEmpty())
                    <div class="alert alert-info">
                        No other exams available to clone case studies from.
                    </div>
                @else
                    <form action="{{ route('admin.case-studies.clone.store', $targetExam->id) }}" method="POST" id="cloneForm">
                        @csrf
                        
                        <div class="row">
                            <!-- Left Column: Exam List -->
                            <div class="col-md-4">
                                <div class="card border">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">📚 Select Source Exam</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="p-3 border-bottom">
                                            <input type="text" class="form-control form-control-sm" id="examSearchInput" placeholder="🔍 Search exams...">
                                        </div>
                                        <div class="list-group list-group-flush" id="examList" style="max-height: 500px; overflow-y: auto;">
                                            @foreach($sourceExams as $exam)
                                                @php
                                                    $totalCaseStudies = $exam->sections->sum(function($section) {
                                                        return $section->caseStudies->count();
                                                    });
                                                @endphp
                                                <a href="#" 
                                                   class="list-group-item list-group-item-action exam-list-item {{ $loop->first ? 'active' : '' }}" 
                                                   data-exam-id="{{ $exam->id }}"
                                                   data-exam-name="{{ strtolower($exam->name) }}">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong>{{ $exam->name }}</strong>
                                                            <br>
                                                            <small class="text-muted">
                                                                @if($totalCaseStudies > 0)
                                                                    {{ $totalCaseStudies }} Case Studies
                                                                @else
                                                                    No Case Studies
                                                                @endif
                                                            </small>
                                                        </div>
                                                        @if($totalCaseStudies > 0)
                                                            <span class="badge bg-primary rounded-pill">{{ $totalCaseStudies }}</span>
                                                        @else
                                                            <span class="badge bg-secondary rounded-pill">0</span>
                                                        @endif
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Case Studies -->
                            <div class="col-md-8">
                                <div class="card border">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">📋 Case Studies</h6>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllBtn">
                                            ✓ Select All
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <input type="text" class="form-control form-control-sm" id="caseStudySearchInput" placeholder="🔍 Search case studies...">
                                        </div>
                                        
                                        <div id="caseStudiesContainer" style="max-height: 500px; overflow-y: auto;">
                                            @foreach($sourceExams as $exam)
                                                @php
                                                    $totalCaseStudies = $exam->sections->sum(function($section) {
                                                        return $section->caseStudies->count();
                                                    });
                                                @endphp
                                                
                                                <div class="case-studies-section" data-exam-id="{{ $exam->id }}" style="display: {{ $loop->first ? 'block' : 'none' }};">
                                                    @if($totalCaseStudies > 0)
                                                        @foreach($exam->sections as $section)
                                                            @if($section->caseStudies->count() > 0)
                                                                <div class="mb-4">
                                                                    <h6 class="text-primary border-bottom pb-2">
                                                                        📁 {{ $section->title }}
                                                                    </h6>
                                                                    
                                                                    @foreach($section->caseStudies as $caseStudy)
                                                                        <div class="case-study-item mb-2" data-title="{{ strtolower($caseStudy->title) }}">
                                                                            <div class="card border">
                                                                                <div class="card-body p-3">
                                                                                    <div class="form-check">
                                                                                        <input class="form-check-input case-study-checkbox" 
                                                                                               type="checkbox" 
                                                                                               name="case_study_ids[]" 
                                                                                               value="{{ $caseStudy->id }}" 
                                                                                               id="case{{ $caseStudy->id }}"
                                                                                               data-exam-id="{{ $exam->id }}">
                                                                                        <label class="form-check-label w-100" for="case{{ $caseStudy->id }}">
                                                                                            <strong>{{ $caseStudy->title }}</strong>
                                                                                            <br>
                                                                                            <small class="text-muted">
                                                                                                ❓ {{ $caseStudy->questions->count() }} Questions
                                                                                            </small>
                                                                                            @if($caseStudy->content)
                                                                                                <p class="text-muted small mt-2 mb-0">
                                                                                                    {{ Str::limit(strip_tags($caseStudy->content), 80) }}
                                                                                                </p>
                                                                                            @endif
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <div class="alert alert-info">
                                                            This exam doesn't have any case studies yet.
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning mt-4">
                            ⚠️ <strong>Important:</strong> Selected case studies and all their questions will be <strong>copied</strong> to "{{ $targetExam->name }}". 
                            Any changes you make later will NOT affect the original exam.
                        </div>

                        <div class="mt-4 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-info" id="selectedCount">0 selected</span>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary" id="cloneBtn" disabled>
                                    <i class="fas fa-copy"></i> Clone Selected Case Studies
                                </button>
                                <a href="{{ route('admin.exams.edit', $targetExam->id) }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.case-study-checkbox');
    const cloneBtn = document.getElementById('cloneBtn');
    const selectedCount = document.getElementById('selectedCount');
    const examSearchInput = document.getElementById('examSearchInput');
    const caseStudySearchInput = document.getElementById('caseStudySearchInput');
    const selectAllBtn = document.getElementById('selectAllBtn');
    
    let currentExamId = document.querySelector('.exam-list-item.active')?.dataset.examId;
    
    // Update selected count and button state
    function updateSelection() {
        const checkedCount = document.querySelectorAll('.case-study-checkbox:checked').length;
        selectedCount.textContent = checkedCount + ' selected';
        cloneBtn.disabled = checkedCount === 0;
    }
    
    // Add event listeners to all checkboxes
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelection);
    });
    
    // Exam list item click - switch case studies view
    document.querySelectorAll('.exam-list-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Update active state
            document.querySelectorAll('.exam-list-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            
            // Get exam ID
            currentExamId = this.dataset.examId;
            
            // Hide all case study sections
            document.querySelectorAll('.case-studies-section').forEach(section => {
                section.style.display = 'none';
            });
            
            // Show selected exam's case studies
            const targetSection = document.querySelector(`.case-studies-section[data-exam-id="${currentExamId}"]`);
            if (targetSection) {
                targetSection.style.display = 'block';
            }
            
            // Clear case study search
            caseStudySearchInput.value = '';
            
            // Update select all button state
            updateSelectAllButton();
        });
    });
    
    // Select All button
    selectAllBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        if (!currentExamId) return;
        
        const currentCheckboxes = document.querySelectorAll(`.case-study-checkbox[data-exam-id="${currentExamId}"]`);
        const allChecked = Array.from(currentCheckboxes).every(cb => cb.checked);
        
        currentCheckboxes.forEach(cb => {
            cb.checked = !allChecked;
        });
        
        updateSelectAllButton();
        updateSelection();
    });
    
    // Update select all button text
    function updateSelectAllButton() {
        if (!currentExamId) return;
        
        const currentCheckboxes = document.querySelectorAll(`.case-study-checkbox[data-exam-id="${currentExamId}"]`);
        const allChecked = Array.from(currentCheckboxes).every(cb => cb.checked);
        
        selectAllBtn.innerHTML = allChecked ? '✗ Deselect All' : '✓ Select All';
    }
    
    // Search exams
    examSearchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const examItems = document.querySelectorAll('.exam-list-item');
        
        examItems.forEach(item => {
            const examName = item.dataset.examName;
            if (examName.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    // Search case studies
    caseStudySearchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const caseStudyItems = document.querySelectorAll('.case-study-item');
        
        caseStudyItems.forEach(item => {
            const title = item.dataset.title;
            const isInCurrentExam = item.closest('.case-studies-section')?.dataset.examId === currentExamId;
            
            if (isInCurrentExam) {
                if (title.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            }
        });
    });
    
    // Confirm before submit
    document.getElementById('cloneForm').addEventListener('submit', function(e) {
        const checkedCount = document.querySelectorAll('.case-study-checkbox:checked').length;
        if (!confirm(`Are you sure you want to clone ${checkedCount} case study(ies) to {{ $targetExam->name }}?`)) {
            e.preventDefault();
        }
    });
    
    // Initialize select all button state
    updateSelectAllButton();
});
</script>
@endpush

@endsection
