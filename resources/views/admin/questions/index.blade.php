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
                        <div class="card h-100 border-2 border-primary hover-shadow text-decoration-none text-dark" style="cursor: pointer; transition: all 0.3s;" data-bs-toggle="modal" data-bs-target="#cloneQuestionModal">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-primary d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                        <i class="ti ti-copy text-primary" style="font-size: 2.2rem;"></i>
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
                        <div class="col-md-1">
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
                        <div class="col-md-1">
                            <label class="form-label fw-bold text-muted small mb-1">QUESTION TYPE</label>
                            <select name="question_type" id="question_type" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                                <option value="">All Types</option>
                                <option value="single" {{ request('question_type') == 'single' ? 'selected' : '' }}>Single Choice</option>
                                <option value="multiple" {{ request('question_type') == 'multiple' ? 'selected' : '' }}>Multiple Choice</option>
                            </select>
                        </div>

                        <!-- Status (Toggle) -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">STATUS</label>
                            <div class="form-check form-switch mt-1">
                                <input type="hidden" name="status" id="statusFilterInput" value="{{ request('status', 'active') }}">
                                <input class="form-check-input" type="checkbox" role="switch" id="statusFilterSwitch" style="width: 3em; height: 1.5em;"
                                       {{ request('status', 'active') == 'active' ? 'checked' : '' }}
                                       onchange="document.getElementById('statusFilterInput').value = this.checked ? 'active' : 'inactive'; document.getElementById('filterForm').submit()">
                                <label class="form-check-label ms-2 mt-1" for="statusFilterSwitch">
                                    {{ request('status', 'active') == 'active' ? 'Active' : 'Inactive' }}
                                </label>
                            </div>
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

                </form>
            </div>

            <!-- Active Filters Indicator -->
            @php
                $hasActiveFilters = request('certification_type') || 
                                  request('exam_category') || 
                                  request('exam') || 
                                  request('category') || 
                                  request('case_study') || 
                                  request('question_type') ||
                                  request('status') === 'inactive';
            @endphp
            
            @if($hasActiveFilters)
            <div class="card-body border-top border-bottom bg-light-subtle py-3">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="text-muted small fw-semibold">
                        <i class="ti ti-filter-check me-1"></i>ACTIVE FILTERS:
                    </span>
                    @if(request('status') === 'inactive')
                        <span class="badge rounded-pill bg-danger">
                            <i class="ti ti-trash me-1"></i>Deleted
                        </span>
                    @endif
                    @if(request('certification_type'))
                        <span class="badge rounded-pill bg-success">
                            <i class="ti ti-certificate me-1"></i>{{ request('certification_type') }}
                        </span>
                    @endif
                    @if(request('exam_category'))
                        <span class="badge rounded-pill bg-info">
                            <i class="ti ti-category me-1"></i>{{ $examCategories->firstWhere('id', request('exam_category'))->name ?? 'Unknown' }}
                        </span>
                    @endif
                    @if(request('exam'))
                        <span class="badge rounded-pill bg-primary">
                            <i class="ti ti-file-text me-1"></i>{{ $exams->firstWhere('id', request('exam'))->name ?? 'Unknown' }}
                        </span>
                    @endif
                    @if(request('case_study'))
                        <span class="badge rounded-pill bg-secondary">
                            <i class="ti ti-file-analytics me-1"></i>{{ $caseStudies->firstWhere('id', request('case_study'))->title ?? 'Unknown' }}
                        </span>
                    @endif
                    @if(request('category'))
                        <span class="badge rounded-pill bg-warning text-dark">
                            <i class="ti ti-bookmark me-1"></i>{{ request('category') == 'ig' ? 'IG' : 'DM' }}
                        </span>
                    @endif
                    @if(request('question_type'))
                        <span class="badge rounded-pill bg-dark">
                            <i class="ti ti-help me-1"></i>{{ request('question_type') == 'single' ? 'Single' : 'Multiple' }}
                        </span>
                    @endif
                </div>
            </div>
            @endif
            
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
                                <th style="width: 30%;">Question</th>
                                <th style="width: 15%;">Case Study</th>
                                <th style="width: 15%;">Source</th>
                                <th style="width: 10%;">Type</th>
                                <th style="width: 10%;">Groups</th>
                                <th style="width: 10%;">Options</th>
                                <th class="text-end" style="width: 10%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($questions as $question)
                            <tr>
                                <td>
                                    <div class="fw-bold mb-1">
                                        {{ Str::limit(strip_tags($question->question_text), 80) }}
                                    </div>
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
                                    @if($question->cloned_from_id && $question->clonedFrom && $question->clonedFrom->caseStudy && $question->clonedFrom->caseStudy->section && $question->clonedFrom->caseStudy->section->exam)
                                         <span class="text-muted small">
                                            Clone from <strong>{{ Str::limit($question->clonedFrom->caseStudy->section->exam->name, 20) }}</strong>
                                        </span>
                                    @elseif(!empty($question->cloned_from_id))
                                        <span class="text-muted small"><em>Source Deleted</em></span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
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
                                    
                                    <div class="dropdown">
                                        <button class="btn p-0 text-secondary bg-transparent border-0 shadow-none" type="button" 
                                                data-bs-toggle="dropdown" 
                                                data-bs-boundary="viewport" 
                                                data-bs-popper-config='{"strategy":"fixed"}'
                                                aria-expanded="false">
                                            <i class="ti ti-dots-vertical f-18"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.questions.show', $question->id) }}">
                                                    <i class="ti ti-eye me-2"></i>View Question
                                                </a>
                                            </li>
                                            @if($question->status == 0)
                                                <li>
                                                    <form action="{{ route('admin.questions.activate', $question->id) }}" method="POST" class="d-inline-block w-100" id="activateForm{{ $question->id }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="button" class="dropdown-item text-success" onclick="showAlert.confirm('Are you sure you want to restore this question?', 'Restore Question', function() { document.getElementById('activateForm{{ $question->id }}').submit(); })">
                                                            <i class="ti ti-check me-2"></i>Activate Question
                                                        </button>
                                                    </form>
                                                </li>
                                            @else
                                                <li>
                                                    @if($isActiveExamQuestion)
                                                        <button class="dropdown-item text-muted" style="cursor: not-allowed; opacity: 0.6;" disabled title="Exam is active - cannot edit">
                                                            <i class="ti ti-edit me-2"></i>Edit Question
                                                        </button>
                                                    @else
                                                        <a class="dropdown-item" href="{{ route('admin.questions.edit', $question->id) }}">
                                                            <i class="ti ti-edit me-2"></i>Edit Question
                                                        </a>
                                                    @endif
                                                </li>
                                                <li>
                                                    @if($isActiveExamQuestion)
                                                        <button class="dropdown-item text-muted" style="cursor: not-allowed; opacity: 0.6;" disabled title="Exam is active - cannot delete">
                                                            <i class="ti ti-trash me-2"></i>Delete Question
                                                        </button>
                                                    @else
                                                        <form action="{{ route('admin.questions.destroy', $question->id) }}" method="POST" class="d-inline-block w-100" id="deleteForm{{ $question->id }}">
                                                            @csrf @method('DELETE')
                                                            <button type="button" class="dropdown-item text-danger" onclick="showDeleteModal(document.getElementById('deleteForm{{ $question->id }}'), 'Are you sure you want to delete this question?')">
                                                                <i class="ti ti-trash me-2"></i>Delete Question
                                                            </button>
                                                        </form>
                                                    @endif
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
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

<!-- Clone Question Modal -->
<div class="modal fade" id="cloneQuestionModal" tabindex="-1" aria-labelledby="cloneQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title d-flex align-items-center" id="cloneQuestionModalLabel">
                    <i class="ti ti-copy me-2 fs-4"></i> Clone Questions from Bank
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.questions.clone') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted mb-4">Select source questions to clone into a target case study.</p>
                    
                    <div class="row g-4">
                        <!-- Source Configuration -->
                        <div class="col-md-12">
                            <h6 class="fw-bold mb-3 text-primary"><i class="ti ti-file-import me-1"></i> Source Details</h6>
                            
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="clone_source_exam_id" class="form-label fw-bold">Source Exam</label>
                                    <select class="form-select form-select-sm" id="clone_source_exam_id" required>
                                        <option value="">-- Select Exam --</option>
                                        @foreach($exams as $exam)
                                            <option value="{{ $exam->id }}" data-is-active="{{ $exam->is_active }}">{{ $exam->name }}{{ $exam->exam_code ? ' (' . $exam->exam_code . ')' : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="clone_source_section_id" class="form-label fw-bold">Source Section</label>
                                    <select class="form-select form-select-sm" id="clone_source_section_id" required disabled>
                                        <option value="">-- Select Source Exam First --</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="clone_source_case_study_id" class="form-label fw-bold">Source Case Study</label>
                                    <select class="form-select form-select-sm" id="clone_source_case_study_id" required disabled>
                                        <option value="">-- Select Source Section First --</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0">Select Questions to Clone</label>
                                    <div class="form-check m-0">
                                        <input class="form-check-input" type="checkbox" id="select_all_questions">
                                        <label class="form-check-label fw-bold small text-primary" for="select_all_questions">Select All</label>
                                    </div>
                                </div>
                                
                                <div id="questions_checkbox_list" class="row g-2 border rounded p-3 bg-white" style="max-height: 250px; overflow-y: auto; display: none;">
                                    <!-- Checkboxes injected here -->
                                </div>

                                <div id="q_loading" class="text-center py-3" style="display: none;">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <span class="ms-2 text-muted small">Loading questions...</span>
                                </div>

                                <div id="q_no_data" class="text-center py-3 text-muted small">
                                    -- Select Source Case Study First --
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                             <hr class="my-2">
                        </div>

                        <!-- Target Configuration -->
                        <div class="col-md-12">
                            <h6 class="fw-bold mb-3 text-success"><i class="ti ti-file-export me-1"></i> Target Details</h6>
                            
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="clone_target_exam_id" class="form-label fw-bold">Target Exam</label>
                                    <select class="form-select form-select-sm" id="clone_target_exam_id" required>
                                        <option value="">-- Select Target Exam --</option>
                                        @foreach($exams as $exam)
                                            <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="clone_target_section_id" class="form-label fw-bold">Target Section</label>
                                    <select class="form-select form-select-sm" id="clone_target_section_id" required disabled>
                                        <option value="">-- Select Target Exam First --</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="clone_target_case_study_id" class="form-label fw-bold">Target Case Study</label>
                                    <select class="form-select form-select-sm" id="clone_target_case_study_id" name="target_case_study_id" required disabled>
                                        <option value="">-- Select Target Section First --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">The selected questions will be added to this case study.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary me-auto" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                        <i class="ti ti-arrow-left me-1"></i> Back
                    </button>
                    
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-copy me-1"></i> Clone Questions
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Helper to fetch sections (using existing endpoint: questions-ajax/case-studies/{examId} which returns SECTIONS)
    function fetchSections(examId, sectionSelect) {
        sectionSelect.innerHTML = '<option value="">Loading...</option>';
        sectionSelect.disabled = true;

        if (examId) {
            return fetch(`/admin/questions-ajax/case-studies/${examId}`)
                .then(response => response.json())
                .then(data => {
                    sectionSelect.innerHTML = '<option value="">-- Select Section --</option>';
                    if (data.length > 0) {
                        data.forEach(section => {
                            sectionSelect.innerHTML += `<option value="${section.id}">${section.title}</option>`;
                        });
                        sectionSelect.disabled = false;
                    } else {
                        sectionSelect.innerHTML = '<option value="">No sections found</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching sections:', error);
                    sectionSelect.innerHTML = '<option value="">Error loading sections</option>';
                });
        } else {
            sectionSelect.innerHTML = '<option value="">-- Select Exam First --</option>';
            sectionSelect.disabled = true;
            return Promise.resolve();
        }
    }

    // Helper to fetch case studies (using questions-ajax/sub-case-studies/{sectionId})
    function fetchCaseStudies(sectionId, caseStudySelect) {
        caseStudySelect.innerHTML = '<option value="">Loading...</option>';
        caseStudySelect.disabled = true;

        if (sectionId) {
            return fetch(`/admin/questions-ajax/sub-case-studies/${sectionId}`)
                .then(response => response.json())
                .then(data => {
                    caseStudySelect.innerHTML = '<option value="">-- Select Case Study --</option>';
                    if (data.length > 0) {
                        data.forEach(cs => {
                            caseStudySelect.innerHTML += `<option value="${cs.id}">${cs.title}</option>`;
                        });
                        caseStudySelect.disabled = false;
                    } else {
                        caseStudySelect.innerHTML = '<option value="">No case studies found</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching case studies:', error);
                    caseStudySelect.innerHTML = '<option value="">Error loading case studies</option>';
                });
        } else {
            caseStudySelect.innerHTML = '<option value="">-- Select Section First --</option>';
            caseStudySelect.disabled = true;
            return Promise.resolve();
        }
    }

    // Expose function globally to use in inline onclick for success modal
    window.setQuestionCloneTarget = function(examId, sectionId, caseStudyId) {
        // Small delay to ensure modal is ready
        setTimeout(() => {
            const targetExamSelect = document.getElementById('clone_target_exam_id');
            const targetSectionSelect = document.getElementById('clone_target_section_id');
            const targetCaseStudySelect = document.getElementById('clone_target_case_study_id');

            if (targetExamSelect && examId) {
                targetExamSelect.value = examId;
                
                // Chain the fetches
                fetchSections(examId, targetSectionSelect).then(() => {
                    if (targetSectionSelect && sectionId) {
                        targetSectionSelect.value = sectionId;
                        
                        fetchCaseStudies(sectionId, targetCaseStudySelect).then(() => {
                            if (targetCaseStudySelect && caseStudyId) {
                                targetCaseStudySelect.value = caseStudyId;
                            }
                        });
                    }
                });
            }
        }, 200);
    };

    // Helper to fetch questions (using questions-ajax/questions/{caseStudyId})
    function fetchQuestions(caseStudyId) {
        // Clear UI
        if(questionsContainer) {
            questionsContainer.innerHTML = '';
            questionsContainer.style.display = 'none';
            qLoading.style.display = 'block';
            qNoData.style.display = 'none';
        }

        if (caseStudyId) {
            fetch(`/admin/questions-ajax/questions/${caseStudyId}`)
                .then(response => response.json())
                .then(data => {
                    qLoading.style.display = 'none';
                    if (data.length > 0) {
                        if(questionsContainer) {
                            questionsContainer.style.display = 'flex'; // It's a row
                            data.forEach(q => {
                                // Strip HTML tags for display
                                const div = document.createElement('div');
                                div.innerHTML = q.question_text;
                                const text = div.textContent || div.innerText || '';
                                const shortText = text.substring(0, 100) + (text.length > 100 ? '...' : '');

                                const checkboxId = `q_source_${q.id}`;
                                const html = `
                                    <div class="col-md-12">
                                        <div class="form-check border-bottom pb-2">
                                            <input class="form-check-input question-source-checkbox" type="checkbox" name="source_question_ids[]" value="${q.id}" id="${checkboxId}">
                                            <label class="form-check-label d-block" for="${checkboxId}">
                                                <span class="badge bg-light-primary me-2">${q.question_type}</span>
                                                <span class="text-dark small">${shortText}</span>
                                            </label>
                                        </div>
                                    </div>
                                `;
                                questionsContainer.insertAdjacentHTML('beforeend', html);
                            });
                        }
                        if(qNoData) qNoData.style.display = 'none';
                    } else {
                         if(qNoData) {
                            qNoData.innerText = 'No questions found in this case study';
                            qNoData.style.display = 'block';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching questions:', error);
                    qLoading.style.display = 'none';
                    if(qNoData) {
                        qNoData.innerText = 'Error loading questions';
                        qNoData.style.display = 'block';
                    }
                });
        } else {
             qLoading.style.display = 'none';
             if(qNoData) {
                qNoData.innerText = '-- Select Source Case Study First --';
                qNoData.style.display = 'block';
             }
        }
    }

    // Source Flow
    const sExam = document.getElementById('clone_source_exam_id');
    const sSection = document.getElementById('clone_source_section_id');
    const sCaseStudy = document.getElementById('clone_source_case_study_id');
    
    // UI Elements for questions list
    const questionsContainer = document.getElementById('questions_checkbox_list');
    const qLoading = document.getElementById('q_loading');
    const qNoData = document.getElementById('q_no_data');
    const selectAllQ = document.getElementById('select_all_questions');

    // Select All Logic
    if (selectAllQ) {
        selectAllQ.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.question-source-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }

    if (sExam) {
        sExam.addEventListener('change', function() {
             const examId = this.value;

            // Update Target Exam Dropdown: Prevent selecting the same exam as source
            const tExam = document.getElementById('clone_target_exam_id');
            if (tExam) {
                Array.from(tExam.options).forEach(option => {
                    option.hidden = false;
                    option.disabled = false;
                    
                    if (examId && option.value == examId) {
                        option.hidden = true; // Use 'hidden' attribute to remove from view
                        option.disabled = true; // Disable as fallback
                    }
                });
                
                // If the disabled exam was currently selected, reset the selection
                if (tExam.value == examId) {
                    tExam.value = "";
                    const tSection = document.getElementById('clone_target_section_id');
                    if(tSection) {
                         tSection.innerHTML = '<option value="">-- Select Target Exam First --</option>';
                         tSection.disabled = true;
                    }
                    const tCaseStudy = document.getElementById('clone_target_case_study_id');
                    if(tCaseStudy) {
                        tCaseStudy.innerHTML = '<option value="">-- Select Target Section First --</option>';
                        tCaseStudy.disabled = true;
                    }
                }
            }

            fetchSections(this.value, sSection);
            sCaseStudy.innerHTML = '<option value="">-- Select Source Section First --</option>';
            sCaseStudy.disabled = true;
            
            // Reset questions list
            if(questionsContainer) {
                questionsContainer.innerHTML = '';
                questionsContainer.style.display = 'none';
                qNoData.innerText = '-- Select Source Case Study First --';
                qNoData.style.display = 'block';
                if(selectAllQ) selectAllQ.checked = false;
            }
        });
    }

    if (sSection) {
        sSection.addEventListener('change', function() {
            fetchCaseStudies(this.value, sCaseStudy);
            
             // Reset questions list
            if(questionsContainer) {
                questionsContainer.innerHTML = '';
                questionsContainer.style.display = 'none';
                
                if(this.value) {
                     qNoData.innerText = '-- Select Source Case Study First --';
                } else {
                     qNoData.innerText = '-- Select Source Section First --';
                }
                
                qNoData.style.display = 'block';
                if(selectAllQ) selectAllQ.checked = false;
            }
        });
    }

    if (sCaseStudy) {
        sCaseStudy.addEventListener('change', function() {
            fetchQuestions(this.value);
        });
    }

    // Target Flow
    const tExam = document.getElementById('clone_target_exam_id');
    const tSection = document.getElementById('clone_target_section_id');
    const tCaseStudy = document.getElementById('clone_target_case_study_id');

    if (tExam) {
        tExam.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const isActive = selectedOption.getAttribute('data-is-active');

            if (isActive == '1') {
                alert('You cannot clone into an active exam. Please deactivate the exam first.');
                this.value = ""; // Reset selection
                tSection.innerHTML = '<option value="">-- Select Target Exam First --</option>';
                tSection.disabled = true;
                tCaseStudy.innerHTML = '<option value="">-- Select Target Section First --</option>';
                tCaseStudy.disabled = true;
                return;
            }

            fetchSections(this.value, tSection);
            tCaseStudy.innerHTML = '<option value="">-- Select Target Section First --</option>';
            tCaseStudy.disabled = true;
        });
    }

    if (tSection) {
        tSection.addEventListener('change', function() {
            fetchCaseStudies(this.value, tCaseStudy);
        });
    }
});
</script>

@if(session('question_created_success'))
<!-- Question Created Success Modal -->
<div class="modal fade" id="questionCreatedSuccessModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title d-flex align-items-center">
                    <i class="ti ti-check-circle me-2 fs-4"></i> Question Created Successfully!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-4 text-center">Would you like to add another question or finish?</p>
                
                <div class="row g-3">
                    <!-- Option 1: Create Another Question -->
                    <div class="col-md-6">
                        <a href="{{ route('admin.questions.create', ['exam_id' => session('selected_exam_id'), 'section_id' => session('selected_section_id'), 'case_study_id' => session('selected_case_study_id')]) }}" class="card h-100 border-2 hover-shadow text-decoration-none text-dark" style="transition: all 0.3s;">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-primary d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                        <i class="ti ti-plus text-primary" style="font-size: 2.2rem;"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold mb-2">Add Another Question</h5>
                                <p class="text-muted small mb-0">Add more questions to this case study.</p>
                            </div>
                        </a>
                    </div>

                    <!-- Option 2: Clone Question -->
                    <div class="col-md-6">
                        <div class="card h-100 border-2 border-primary hover-shadow text-decoration-none text-dark" 
                             style="cursor: pointer; transition: all 0.3s;" 
                             data-bs-toggle="modal" 
                             data-bs-target="#cloneQuestionModal"
                             onclick="setQuestionCloneTarget('{{ session('selected_exam_id') }}', '{{ session('selected_section_id') }}', '{{ session('selected_case_study_id') }}')">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-primary d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                        <i class="ti ti-copy text-primary" style="font-size: 2.2rem;"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold mb-2">Clone Question from Bank</h5>
                                <p class="text-muted small mb-0">Copy questions from other sections/exams.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Option 3: Finish -->
                    <div class="col-12 mt-4">
                        <a href="{{ route('admin.exams.index') }}" class="btn btn-primary w-100 py-2 fs-5">
                            Finish & Return to Exams <i class="ti ti-check ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show success alert first
    showAlert.success('{{ session('success') }}', 'Success!');
    
    // Then show the modal
    var modal = new bootstrap.Modal(document.getElementById('questionCreatedSuccessModal'));
    modal.show();
});
</script>
@endif

@if(request('open_modal'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = new bootstrap.Modal(document.getElementById('addQuestionModal'));
    modal.show();
});
</script>
@endif

<script>
// Initialize Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

@endsection
