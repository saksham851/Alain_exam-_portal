@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Case Studies Bank</h5>
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
                    All Case Studies 
                    <span class="badge bg-light-secondary ms-2">{{ $caseStudies->total() }} Total</span>
                </h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCaseStudyModal">
                    <i class="ti ti-plus me-1"></i> Add New Case Study
                </button>
            </div>

<!-- Add Case Study Modal -->
<div class="modal fade" id="addCaseStudyModal" tabindex="-1" aria-labelledby="addCaseStudyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title d-flex align-items-center" id="addCaseStudyModalLabel">
                    <i class="ti ti-file-text me-2 fs-4"></i> Add New Case Study
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-4 text-center">How would you like to add a case study?</p>
                
                <div class="row g-3">
                    <!-- Option 1: Create New -->
                    <div class="col-md-6">
                        <a href="{{ route('admin.case-studies-bank.create', request()->only(['exam_id', 'section_id'])) }}" class="card h-100 border-2 hover-shadow text-decoration-none text-dark" style="transition: all 0.3s;">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-primary d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                        <i class="ti ti-plus text-primary" style="font-size: 2.2rem;"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold mb-2">Create Case Study from Scratch</h5>
                                <p class="text-muted small mb-0">Start with a blank case study.</p>
                            </div>
                        </a>
                    </div>

                    <!-- Option 2: Clone Existing -->
                    <div class="col-md-6">
                        <div class="card h-100 border-2 border-primary hover-shadow text-decoration-none text-dark" style="cursor: pointer; transition: all 0.3s;" data-bs-toggle="modal" data-bs-target="#cloneCaseStudyModal">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-primary d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                        <i class="ti ti-copy text-primary" style="font-size: 2.2rem;"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold mb-2">Clone Case Study from Bank</h5>
                                <p class="text-muted small mb-0">Copy an existing case study.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Clone Case Study Modal -->
<div class="modal fade" id="cloneCaseStudyModal" tabindex="-1" aria-labelledby="cloneCaseStudyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title d-flex align-items-center" id="cloneCaseStudyModalLabel">
                    <i class="ti ti-copy me-2 fs-4"></i> Clone Case Study from Bank
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.case-studies-bank.copy') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted mb-4">Select a source case study to clone into a target section.</p>
                    
                    <div class="row g-4">
                        <!-- Source Configuration -->
                        <div class="col-md-12">
                            <h6 class="fw-bold mb-3 text-primary"><i class="ti ti-file-import me-1"></i> Source Details</h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="clone_source_exam_id" class="form-label fw-bold">Source Exam</label>
                                    <select class="form-select" id="clone_source_exam_id" required>
                                        <option value="">-- Select Exam --</option>
                                        @foreach($exams as $exam)
                                            <option value="{{ $exam->id }}" data-is-active="{{ $exam->is_active }}">{{ $exam->name }}{{ $exam->exam_code ? ' (' . $exam->exam_code . ')' : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="clone_source_section_id" class="form-label fw-bold">Source Section</label>
                                    <select class="form-select" id="clone_source_section_id" required disabled>
                                        <option value="">-- Select Source Exam First --</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <label class="form-label fw-bold mb-0">Select Case Studies to Clone</label>
                                    <div class="form-check m-0">
                                        <input class="form-check-input" type="checkbox" id="select_all_case_studies">
                                        <label class="form-check-label fw-bold small text-primary" for="select_all_case_studies">Select All</label>
                                    </div>
                                </div>
                                
                                <div id="case_studies_checkbox_list" class="row g-2 border rounded p-3 bg-white" style="max-height: 250px; overflow-y: auto; display: none;">
                                    <!-- Checkboxes injected here -->
                                </div>

                                <div id="cs_loading" class="text-center py-3" style="display: none;">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <span class="ms-2 text-muted small">Loading case studies...</span>
                                </div>

                                <div id="cs_no_data" class="text-center py-3 text-muted small">
                                    -- Select Source Section First --
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                             <hr class="my-2">
                        </div>

                        <!-- Target Configuration -->
                        <div class="col-md-12">
                            <h6 class="fw-bold mb-3 text-success"><i class="ti ti-file-export me-1"></i> Target Details</h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="clone_target_exam_id" class="form-label fw-bold">Target Exam</label>
                                    <select class="form-select" id="clone_target_exam_id" required>
                                        <option value="">-- Select Target Exam --</option>
                                        @foreach($exams as $exam)
                                            <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="clone_target_section_id" class="form-label fw-bold">Target Section</label>
                                    <select class="form-select" id="clone_target_section_id" name="target_section_id" required disabled>
                                        <option value="">-- Select Target Exam First --</option>
                                    </select>
                                    <small class="text-muted">The case study will be added to this section.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 mb-0 mt-3">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Note:</strong> Cloning will copy the Case Study logic, Questions, and Options.
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary me-auto" data-bs-toggle="modal" data-bs-target="#addCaseStudyModal">
                        <i class="ti ti-arrow-left me-1"></i> Back
                    </button>
                    
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-copy me-1"></i> Clone Case Study
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
document.addEventListener('DOMContentLoaded', function() {
    // Helper to fetch sections
    function fetchSections(examId, sectionSelect) {
        sectionSelect.innerHTML = '<option value="">Loading...</option>';
        sectionSelect.disabled = true;

        if (examId) {
            return fetch(`/admin/questions-ajax/case-studies/${examId}`)
                .then(response => response.json())
                .then(data => {
                    sectionSelect.innerHTML = '<option value="">-- Select Section --</option>';
                    if (Array.isArray(data) && data.length > 0) {
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

    // Expose function globally to use in inline onclick
    window.setCaseStudyCloneTarget = function(examId, sectionId) {
        // Small delay to ensure modal is ready
        setTimeout(() => {
            const targetExamSelect = document.getElementById('clone_target_exam_id');
            const targetSectionSelect = document.getElementById('clone_target_section_id');

            if (targetExamSelect && examId) {
                targetExamSelect.value = examId;
                
                // Trigger fetch and wait for it to complete before setting section
                fetchSections(examId, targetSectionSelect).then(() => {
                    if (targetSectionSelect && sectionId) {
                        targetSectionSelect.value = sectionId;
                    }
                });
            }
        }, 200);
    };

    // Source Flow
    const sourceExamSelect = document.getElementById('clone_source_exam_id');
    const sourceSectionSelect = document.getElementById('clone_source_section_id');
    
    // Elements for multiple select
    const caseStudiesContainer = document.getElementById('case_studies_checkbox_list');
    const csLoading = document.getElementById('cs_loading');
    const csNoData = document.getElementById('cs_no_data');
    const selectAllCs = document.getElementById('select_all_case_studies');

    // Select All Logic
    if (selectAllCs) {
        selectAllCs.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.case-study-source-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }

    if (sourceExamSelect) {
        sourceExamSelect.addEventListener('change', function() {
            const examId = this.value;

            // Update Target Exam Dropdown: Prevent selecting the same exam as source
            const targetExamSelect = document.getElementById('clone_target_exam_id');
            if (targetExamSelect) {
                Array.from(targetExamSelect.options).forEach(option => {
                    option.hidden = false;
                    option.disabled = false;
                    
                    if (examId && option.value == examId) {
                        option.hidden = true; // Use 'hidden' attribute to remove from view
                        option.disabled = true; // Disable as fallback
                    }
                });
                
                // If the disabled exam was currently selected, reset the selection
                if (targetExamSelect.value == examId) {
                    targetExamSelect.value = "";
                    // Also reset target section
                     const targetSectionSelect = document.getElementById('clone_target_section_id');
                    if(targetSectionSelect) {
                        targetSectionSelect.innerHTML = '<option value="">-- Select Target Exam First --</option>';
                        targetSectionSelect.disabled = true;
                    }
                }
            }

            fetchSections(this.value, sourceSectionSelect);
            
            // Reset case study list
            if(caseStudiesContainer) {
                caseStudiesContainer.innerHTML = '';
                caseStudiesContainer.style.display = 'none';
                csNoData.innerText = '-- Select Source Section First --';
                csNoData.style.display = 'block';
                if(selectAllCs) selectAllCs.checked = false;
            }
        });
    }

    if (sourceSectionSelect) {
        sourceSectionSelect.addEventListener('change', function() {
            const sectionId = this.value;
            
            if(caseStudiesContainer) {
                caseStudiesContainer.innerHTML = '';
                caseStudiesContainer.style.display = 'none';
                csLoading.style.display = 'block';
                csNoData.style.display = 'none';
            }

            if (sectionId) {
                fetch(`/admin/questions-ajax/sub-case-studies/${sectionId}`)
                    .then(response => response.json())
                    .then(data => {
                        csLoading.style.display = 'none';
                        
                        if (data.length > 0) {
                            if(caseStudiesContainer) {
                                caseStudiesContainer.style.display = 'flex'; // It's a row
                                
                                data.forEach(cs => {
                                    const checkboxId = `cs_source_${cs.id}`;
                                    const html = `
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input case-study-source-checkbox" type="checkbox" name="case_study_ids[]" value="${cs.id}" id="${checkboxId}">
                                                <label class="form-check-label text-truncate d-block" for="${checkboxId}" title="${cs.title}">
                                                    ${cs.title}
                                                </label>
                                            </div>
                                        </div>
                                    `;
                                    caseStudiesContainer.insertAdjacentHTML('beforeend', html);
                                });
                            }
                            if(csNoData) csNoData.style.display = 'none';
                        } else {
                            if(csNoData) {
                                csNoData.innerText = 'No case studies found';
                                csNoData.style.display = 'block';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching case studies:', error);
                        csLoading.style.display = 'none';
                        if(csNoData) {
                            csNoData.innerText = 'Error loading case studies';
                            csNoData.style.display = 'block';
                        }
                    });
            } else {
                csLoading.style.display = 'none';
                if(csNoData) {
                    csNoData.innerText = '-- Select Source Section First --';
                    csNoData.style.display = 'block';
                }
            }
        });
    }

    // Target Flow
    const targetExamSelect = document.getElementById('clone_target_exam_id');
    const targetSectionSelect = document.getElementById('clone_target_section_id');

    if (targetExamSelect) {
        targetExamSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const isActive = selectedOption.getAttribute('data-is-active');

            if (isActive == '1') {
                alert('You cannot clone into an active exam. Please deactivate the exam first.');
                this.value = ""; // Reset selection
                targetSectionSelect.innerHTML = '<option value="">-- Select Target Exam First --</option>';
                 targetSectionSelect.disabled = true;
                return;
            }

            fetchSections(this.value, targetSectionSelect);
        });
    }
});
</script>
            
            <!-- Compact Filters Section -->
            <div class="card-body bg-light-subtle py-3 border-bottom">
                <form method="GET" action="{{ route('admin.case-studies-bank.index') }}" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <!-- Search (moved to front) -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted small mb-1">SEARCH CASE STUDIES</label>
                            <input type="text" name="search" id="searchInput" class="form-control form-control-sm" placeholder="Search case study title..." value="{{ request('search') }}">
                        </div>

                        <!-- Exam Category Filter -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">EXAM CATEGORY</label>
                            <select name="exam_category" id="examCategoryFilter" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach($examCategories as $category)
                                    <option value="{{ $category->id }}" {{ request('exam_category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Certification Type Filter -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">CERTIFICATION TYPE</label>
                            <select name="certification_type" id="certificationTypeFilter" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                @foreach($certificationTypes as $type)
                                    <option value="{{ $type }}" {{ request('certification_type') == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Exam Filter -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">EXAM</label>
                            <select name="exam" id="examFilter" class="form-select form-select-sm">
                                <option value="">All Exams</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}" {{ request('exam') == $exam->id ? 'selected' : '' }}>
                                        {{ $exam->name }}
                                    </option>
                                @endforeach
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
                        
                        <!-- Clear Button -->
                        <div class="col-md-1">
                            <a href="{{ route('admin.case-studies-bank.index') }}" class="btn btn-sm btn-light-secondary w-100" title="Clear Filters">
                                <i class="ti ti-rotate"></i>
                            </a>
                        </div>
                    </div>
            </div>
            </form>

            <!-- Active Filters Indicator -->
            @php
                $hasActiveFilters = request('search') || 
                                  request('exam_category') || 
                                  request('certification_type') || 
                                  request('exam') ||
                                  request('status') === 'inactive';
            @endphp
            
            @if($hasActiveFilters)
            <div class="card-body border-top border-bottom bg-light-subtle py-3">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="text-muted small fw-semibold">
                        <i class="ti ti-filter-check me-1"></i>ACTIVE FILTERS:
                    </span>
                    @if(request('search'))
                        <span class="badge rounded-pill bg-dark">
                            <i class="ti ti-search me-1"></i>{{ request('search') }}
                        </span>
                    @endif
                    @if(request('status') === 'inactive')
                        <span class="badge rounded-pill bg-danger">
                            <i class="ti ti-trash me-1"></i>Deleted
                        </span>
                    @endif
                    @if(request('exam_category'))
                        <span class="badge rounded-pill bg-info">
                            <i class="ti ti-category me-1"></i>{{ $examCategories->firstWhere('id', request('exam_category'))->name ?? 'Unknown' }}
                        </span>
                    @endif
                    @if(request('certification_type'))
                        <span class="badge rounded-pill bg-success">
                            <i class="ti ti-certificate me-1"></i>{{ request('certification_type') }}
                        </span>
                    @endif
                    @if(request('exam'))
                        <span class="badge rounded-pill bg-primary">
                            <i class="ti ti-file-text me-1"></i>{{ $exams->firstWhere('id', request('exam'))->name ?? 'Unknown' }}
                        </span>
                    @endif
                </div>
            </div>
            @endif

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Case Study Title</th>
                                <th>Current Section</th>
                                <th>Current Exam</th>
                                <th>Source</th>
                                <th>Exam Category</th>
                                <th>Certification Type</th>
                                <th class="text-center">Questions</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($caseStudies as $caseStudy)
                            <tr>
                                <td>
                                    <strong>{{ $caseStudy->title }}</strong>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $caseStudy->section->title ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    {{ $caseStudy->section->exam->name ?? 'N/A' }}
                                </td>
                                <td>
                                    @if($caseStudy->cloned_from_id && $caseStudy->clonedFromSection && $caseStudy->clonedFromSection->exam)
                                        <span class="text-muted small">
                                           Clone from <strong>{{ Str::limit($caseStudy->clonedFromSection->exam->name, 20) }}</strong>
                                        </span>
                                    @elseif($caseStudy->cloned_from_id)
                                         <span class="text-muted small"><em>Source Deleted</em></span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light-primary">{{ $caseStudy->section->exam->category->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light-info">{{ $caseStudy->section->exam->certification_type ?? 'N/A' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light-success">{{ $caseStudy->questions->count() }} Questions</span>
                                </td>
                                <td class="text-end">
                                    @php
                                        // specific check for case study being in an active exam
                                        $isActiveExam = $caseStudy->section && $caseStudy->section->exam && $caseStudy->section->exam->is_active == 1;
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
                                                <a class="dropdown-item" href="{{ route('admin.case-studies-bank.show', $caseStudy->id) }}">
                                                    <i class="ti ti-eye me-2"></i>View Case Study
                                                </a>
                                            </li>
                                            @if($caseStudy->status == 0)
                                                <li>
                                                    <form action="{{ route('admin.case-studies-bank.activate', $caseStudy->id) }}" method="POST" class="d-inline-block w-100" id="activateCsForm{{ $caseStudy->id }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="button" class="dropdown-item text-success" onclick="showAlert.confirm('Are you sure you want to restore this case study?', 'Restore Case Study', function() { document.getElementById('activateCsForm{{ $caseStudy->id }}').submit(); })">
                                                            <i class="ti ti-check me-2"></i>Activate Case Study
                                                        </button>
                                                    </form>
                                                </li>
                                            @else
                                                <li>
                                                    @if($isActiveExam)
                                                        <button class="dropdown-item text-muted" style="cursor: not-allowed; opacity: 0.6;" disabled title="Exam is active - cannot edit">
                                                            <i class="ti ti-edit me-2"></i>Edit Case Study
                                                        </button>
                                                    @else
                                                        <a class="dropdown-item" href="{{ route('admin.case-studies-bank.edit', $caseStudy->id) }}">
                                                            <i class="ti ti-edit me-2"></i>Edit Case Study
                                                        </a>
                                                    @endif
                                                </li>
                                                <li>
                                                    @if($isActiveExam)
                                                        <button class="dropdown-item text-muted" style="cursor: not-allowed; opacity: 0.6;" disabled title="Exam is active - cannot delete">
                                                            <i class="ti ti-trash me-2"></i>Delete Case Study
                                                        </button>
                                                    @else
                                                        <form action="{{ route('admin.case-studies-bank.destroy', $caseStudy->id) }}" method="POST" class="d-inline delete-form" id="delete-form-{{ $caseStudy->id }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" class="dropdown-item text-danger" onclick="showAlert.confirm('Are you sure you want to delete this case study?', 'Delete Case Study', function() { document.getElementById('delete-form-{{ $caseStudy->id }}').submit(); })">
                                                                <i class="ti ti-trash me-2"></i>Delete Case Study
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
                                <td colspan="8" class="text-center py-4">No case studies found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                </div>
                
                {{-- Custom Pagination --}}
                <x-custom-pagination :paginator="$caseStudies" />
            </div>
            </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Hide the default "Showing X to Y of Z results" text from pagination */
.card-footer .d-flex > div:first-child .hidden,
.card-footer .d-flex > div:first-child .sr-only,
.card-footer .d-flex > div:first-child p,
.card-footer .d-flex > div:first-child span:not(.page-link) {
    display: none !important;
}

/* Hide showing text from right side pagination */
.card-footer .d-flex > div:last-child p,
.card-footer .d-flex > div:last-child > span,
.card-footer .d-flex > div:last-child > div:not(.pagination),
nav[role="navigation"] p,
nav[role="navigation"] > span:not(.page-link),
nav[role="navigation"] > div:not(.pagination) {
    display: none !important;
}

/* Ensure pagination links are visible */
.card-footer .pagination {
    margin: 0;
}
</style>

<script>
// Initialize Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Confirm Delete Function
function confirmDelete(id) {
    const form = document.getElementById('delete-form-' + id);
    if(window.showAlert && window.showAlert.confirm) {
        window.showAlert.confirm('Are you sure you want to delete this case study? This action cannot be undone.', 'Delete Case Study?', function() {
            if(form) form.submit();
        });
    } else {
        if(confirm('Are you sure you want to delete this case study?')) {
            if(form) form.submit();
        }
    }
}

// Auto-submit filters on change
document.getElementById('examCategoryFilter').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});

document.getElementById('certificationTypeFilter').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});

document.getElementById('examFilter').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});

// Debounced search - auto-submit after 500ms of no typing
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() {
        document.getElementById('filterForm').submit();
    }, 500);
});

// Select All functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.case-study-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateSelectedCount();
});

// Update selected count
document.querySelectorAll('.case-study-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedCount);
});

function updateSelectedCount() {
    const selected = document.querySelectorAll('.case-study-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = selected + ' selected';
    document.getElementById('copyBtn').disabled = selected === 0;
}

// Load sections based on selected exam
function loadSections() {
    const examId = document.getElementById('targetExam').value;
    const sectionSelect = document.getElementById('targetSection');
    
    sectionSelect.innerHTML = '<option value="">Loading...</option>';
    
    if (!examId) {
        sectionSelect.innerHTML = '<option value="">Choose Section...</option>';
        return;
    }
    
    fetch(`/admin/questions-ajax/case-studies/${examId}`)
        .then(response => response.json())
        .then(data => {
            if (Array.isArray(data) && data.length > 0) {
                sectionSelect.innerHTML = '<option value="">Choose Section...</option>';
                data.forEach(section => {
                    const option = document.createElement('option');
                    option.value = section.id;
                    option.textContent = section.title;
                    sectionSelect.appendChild(option);
                });
            } else {
                 sectionSelect.innerHTML = '<option value="">No sections found</option>';
            }
        })
        .catch(error => {
            console.error('Error loading sections:', error);
            sectionSelect.innerHTML = '<option value="">Error loading sections</option>';
        });
}

// Form validation
document.getElementById('copyForm').addEventListener('submit', function(e) {
    const selected = document.querySelectorAll('.case-study-checkbox:checked').length;
    const targetSection = document.getElementById('targetSection').value;
    
    if (selected === 0) {
        e.preventDefault();
        alert('Please select at least one case study to copy.');
        return false;
    }
    
    if (!targetSection) {
        e.preventDefault();
        alert('Please select a target section.');
        return false;
    }
    
    const text = selected === 1 ? 'case study' : 'case studies';
    return confirm(`Are you sure you want to copy ${selected} ${text} to the selected section?`);
});

// Initialize
updateSelectedCount();
</script>

@if(session('case_study_created_success'))
<!-- Case Studies Created Success Modal -->
<div class="modal fade" id="caseStudiesCreatedSuccessModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title d-flex align-items-center">
                    <i class="ti ti-check-circle me-2 fs-4"></i> Case Studies Created Successfully!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-4 text-center">Would you like to add more case studies or proceed to add questions?</p>
                
                <div class="row g-3">
                    <!-- Option 1: Create Another Case Study -->
                    <div class="col-md-4">
                        <a href="{{ route('admin.case-studies-bank.create', ['exam_id' => session('selected_exam_id')]) }}" class="card h-100 border-2 hover-shadow text-decoration-none text-dark" style="transition: all 0.3s;">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-primary d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="ti ti-plus text-primary" style="font-size: 1.8rem;"></i>
                                    </div>
                                </div>
                                <h6 class="fw-bold mb-2">Create New</h6>
                                <p class="text-muted small mb-0">Add case study.</p>
                            </div>
                        </a>
                    </div>

                    <!-- Option 2: Clone Case Studies -->
                    <div class="col-md-4">
                        <div class="card h-100 border-2 border-primary hover-shadow text-decoration-none text-dark" 
                             style="cursor: pointer; transition: all 0.3s;" 
                             data-bs-toggle="modal" 
                             data-bs-target="#cloneCaseStudyModal"
                             onclick="setCaseStudyCloneTarget('{{ session('selected_exam_id') }}', '{{ session('selected_section_id') }}')">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-info d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="ti ti-copy text-info" style="font-size: 1.8rem;"></i>
                                    </div>
                                </div>
                                <h6 class="fw-bold mb-2">Clone Existing</h6>
                                <p class="text-muted small mb-0">Copy case studies.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Option 3: Proceed -->
                    <div class="col-md-4">
                        <a href="{{ route('admin.questions.index', ['open_modal' => 'create', 'exam_id' => session('selected_exam_id'), 'section_id' => session('selected_section_id')]) }}" class="card h-100 border-2 border-primary hover-shadow text-decoration-none text-dark" style="transition: all 0.3s;">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-primary d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="ti ti-arrow-right text-primary" style="font-size: 1.8rem;"></i>
                                    </div>
                                </div>
                                <h6 class="fw-bold mb-2">Add Questions</h6>
                                <p class="text-muted small mb-0">Proceed to questions.</p>
                            </div>
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
    var modal = new bootstrap.Modal(document.getElementById('caseStudiesCreatedSuccessModal'));
    modal.show();
});
</script>
@endif

@if(request('open_modal'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = new bootstrap.Modal(document.getElementById('addCaseStudyModal'));
    modal.show();
});
</script>
@endif

@endsection
