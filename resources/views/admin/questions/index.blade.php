@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Question Bank</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item" aria-current="page">Questions</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    All Questions 
                    <span class="badge bg-light-secondary ms-2">{{ $questions->total() }} Total</span>
                </h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                    <i class="ti ti-plus me-1"></i> Add Question
                </button>
            </div>

<!-- Add Question Modal -->
<div class="modal fade" id="addQuestionModal" tabindex="-1" aria-labelledby="addQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title d-flex align-items-center" id="addQuestionModalLabel">
                    <i class="ti ti-help me-2 fs-4"></i> Add New Question
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-4 text-center">How would you like to add a question?</p>
                
                <div class="row g-3">
                    <!-- Option 1: Create from Scratch -->
                    <div class="col-md-6">
                        <a href="{{ route('admin.questions.create', request()->only(['exam_id', 'section_id'])) }}" class="card h-100 border-2 hover-shadow text-decoration-none text-dark" style="transition: all 0.3s;">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-primary d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                        <i class="ti ti-plus text-primary" style="font-size: 2.2rem;"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold mb-2">Create Question from Scratch</h5>
                                <p class="text-muted small mb-0">Start with a blank question.</p>
                            </div>
                        </a>
                    </div>

                    <!-- Option 2: Clone Question -->
                    <div class="col-md-6">
                        <div class="card h-100 border-2 border-primary hover-shadow text-decoration-none text-dark" style="cursor: pointer; transition: all 0.3s;" onclick="alert('Clone question feature coming soon!')">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-info d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                        <i class="ti ti-copy text-info" style="font-size: 2.2rem;"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold mb-2">Clone Question from Bank</h5>
                                <p class="text-muted small mb-0">Copy an existing question.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
            
            <!-- Compact Filters Section -->
            <div class="card-body bg-light-subtle py-3 border-bottom">
                <form method="GET" action="{{ route('admin.questions.index') }}" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <!-- Certification Type Filter -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">CERTIFICATION TYPE</label>
                            <select name="certification_type" id="certification_type" class="form-select form-select-sm" onchange="handleCertificationTypeChange()">
                                <option value="">All Types</option>
                                @foreach($certificationTypes as $type)
                                    <option value="{{ $type }}" {{ request('certification_type') == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Exam Category Filter -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">EXAM CATEGORY</label>
                            <select name="exam_category" id="exam_category" class="form-select form-select-sm" onchange="handleExamCategoryChange()">
                                <option value="">All Categories</option>
                                @foreach($examCategories as $examCategory)
                                    <option value="{{ $examCategory->id }}" {{ request('exam_category') == $examCategory->id ? 'selected' : '' }}>
                                        {{ $examCategory->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Exam Filter -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">EXAM</label>
                            <select name="exam" id="exam" class="form-select form-select-sm" onchange="handleExamChange()">
                                <option value="">All Exams</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}" {{ request('exam') == $exam->id ? 'selected' : '' }}>
                                        {{ $exam->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Case Study Filter -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">CASE STUDY</label>
                            <select name="case_study" id="case_study" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                                <option value="">All Case Studies</option>
                                @foreach($caseStudies as $caseStudy)
                                    <option value="{{ $caseStudy->id }}" {{ request('case_study') == $caseStudy->id ? 'selected' : '' }}>
                                        {{ $caseStudy->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Category Filter -->
                        <div class="col-md-1">
                            <label class="form-label fw-bold text-muted small mb-1">Groups</label>
                            <select name="category" id="category" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                                <option value="">All Groups</option>
                                <option value="ig" {{ request('category') == 'ig' ? 'selected' : '' }}>IG</option>
                                <option value="dm" {{ request('category') == 'dm' ? 'selected' : '' }}>DM</option>
                            </select>
                        </div>

                        <!-- Question Type Filter -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">QUESTION TYPE</label>
                            <select name="question_type" id="question_type" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                                <option value="">All Types</option>
                                <option value="single" {{ request('question_type') == 'single' ? 'selected' : '' }}>Single Choice</option>
                                <option value="multiple" {{ request('question_type') == 'multiple' ? 'selected' : '' }}>Multiple Choice</option>
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="col-md-1">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('admin.questions.index') }}" class="btn btn-sm btn-light-secondary px-3" title="Clear Filters">
                                    <i class="ti ti-rotate"></i>
                                </a>
                                {{-- Hidden submit button to allow Enter key to submit if focused --}}
                                <button type="submit" class="d-none"></button>
                            </div>
                        </div>
                    </div>

                    <!-- Active Filters Indicator -->
                    @if(request()->hasAny(['certification_type', 'exam_category', 'exam', 'category', 'case_study', 'question_type']))
                        <div class="mt-2 d-flex align-items-center flex-wrap gap-2">
                            <span class="text-muted small fw-semibold">
                                <i class="ti ti-filter-check me-1"></i>ACTIVE:
                            </span>
                            @if(request('certification_type'))
                                <span class="badge rounded-pill bg-success small">{{ request('certification_type') }}</span>
                            @endif
                            @if(request('exam_category'))
                                @php $selectedExamCategory = $examCategories->firstWhere('id', request('exam_category')); @endphp
                                @if($selectedExamCategory)
                                    <span class="badge rounded-pill bg-secondary small">{{ $selectedExamCategory->name }}</span>
                                @endif
                            @endif
                            @if(request('exam'))
                                @php $selectedExam = $exams->firstWhere('id', request('exam')); @endphp
                                @if($selectedExam)
                                    <span class="badge rounded-pill bg-primary small">{{ $selectedExam->name }}</span>
                                @endif
                            @endif
                            @if(request('case_study'))
                                @php $selectedCaseStudy = $caseStudies->firstWhere('id', request('case_study')); @endphp
                                @if($selectedCaseStudy)
                                    <span class="badge rounded-pill bg-info small">{{ $selectedCaseStudy->title }}</span>
                                @endif
                            @endif
                            @if(request('category'))
                                <span class="badge rounded-pill bg-warning small text-dark">{{ request('category') == 'ig' ? 'IG' : 'DM' }}</span>
                            @endif
                            @if(request('question_type'))
                                <span class="badge rounded-pill bg-success small">{{ request('question_type') == 'single' ? 'Single' : 'Multiple' }}</span>
                            @endif
                        </div>
                    @endif
                </form>
            </div>
            
            <script>
            function handleCertificationTypeChange() {
                const form = document.getElementById('filterForm');
                form.submit();
            }

            function handleExamCategoryChange() {
                const form = document.getElementById('filterForm');
                const examSelect = document.getElementById('exam');
                const caseStudySelect = document.getElementById('case_study');
                
                // Clear exam and case study selections when exam category changes
                examSelect.value = '';
                caseStudySelect.value = '';
                
                // Submit the form to reload with filtered exams
                form.submit();
            }
            
            function handleExamChange() {
                const form = document.getElementById('filterForm');
                const caseStudySelect = document.getElementById('case_study');
                
                // Clear case study selection when exam changes
                caseStudySelect.value = '';
                
                // Submit the form to reload with filtered case studies
                form.submit();
            }
            </script>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 35%;">Question</th>
                                <th>Case Study</th>
                                <th>Type</th>
                                <th>Groups</th>
                                <th>Options</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($questions as $question)
                            <tr>
                                <td>
                                    <div class="fw-bold mb-1">{{ Str::limit(strip_tags($question->question_text), 80) }}</div>
                                    <small class="text-muted">
                                        <i class="ti ti-file-text"></i> {{ $question->caseStudy->section->title ?? 'N/A' }}
                                    </small>
                                </td>
                                <td>
                                    <div class="small">
                                        <strong>{{ $question->caseStudy->title ?? 'N/A' }}</strong><br>
                                        <span class="text-muted">{{ $question->caseStudy->section->exam->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($question->question_type == 'single')
                                        <span class="badge bg-light-primary">Single Choice</span>
                                    @else
                                        <span class="badge bg-light-success">Multiple Choice</span>
                                    @endif
                                </td>
                                <td>
                                    @if($question->ig_weight > 0)
                                        <span class="badge bg-info">IG - Internal Governance</span>
                                    @elseif($question->dm_weight > 0)
                                        <span class="badge bg-warning">DM - Decision Making</span>
                                    @else
                                        <span class="text-muted">Not Set</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light-info">{{ $question->options->count() }} Options</span>
                                    <span class="badge bg-light-success">{{ $question->options->where('is_correct', 1)->count() }} Correct</span>
                                </td>
                                <td class="text-end">
                                    @php
                                        $isActiveExamQuestion = $question->caseStudy && $question->caseStudy->section && $question->caseStudy->section->exam && $question->caseStudy->section->exam->is_active == 1;
                                    @endphp
                                    
                                    @if($isActiveExamQuestion)
                                        <button class="btn btn-icon btn-link-secondary btn-sm" style="opacity: 0.5; background: transparent; border: none;" title="Exam is active - cannot edit" disabled>
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <div class="d-inline-block">
                                            <button class="btn btn-icon btn-link-secondary btn-sm" style="opacity: 0.5; background: transparent; border: none;" title="Exam is active - cannot delete" disabled>
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    @else
                                        <a href="{{ route('admin.questions.edit', $question->id) }}" class="btn btn-icon btn-link-primary btn-sm" title="Edit Question">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.questions.destroy', $question->id) }}" method="POST" class="d-inline-block" id="deleteForm{{ $question->id }}">
                                            @csrf @method('DELETE')
                                            <button type="button" class="btn btn-icon btn-link-danger btn-sm" title="Delete Question" onclick="showDeleteModal(document.getElementById('deleteForm{{ $question->id }}'), 'Are you sure you want to delete this question?')">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No questions found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            {{-- Custom Pagination --}}
            <x-custom-pagination :paginator="$questions" />
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.questions.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Questions from CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>CSV Format:</strong> Question Text, Type (single/multiple), IG Weight, DM Weight<br>
                        <small>Example: "What is Laravel?","single","1","0"</small>
                    </div>
                    <div class="mb-3">
                        <label for="sub_case_id" class="form-label">Select Case Study</label>
                        <select class="form-select" name="sub_case_id" id="sub_case_id" required>
                            <option value="">Choose Case Study...</option>
                            @foreach(\App\Models\CaseStudy::where('status', 1)->with('section.exam')->get() as $caseStudy)
                                <option value="{{ $caseStudy->id }}">
                                    {{ $caseStudy->title }} ({{ $caseStudy->section->exam->name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label">Select CSV File</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".csv" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-upload me-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(request('open_modal'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = new bootstrap.Modal(document.getElementById('addQuestionModal'));
    modal.show();
});
</script>
@endif

@endsection
