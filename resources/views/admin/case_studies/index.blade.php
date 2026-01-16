@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Sections</h5>
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
                    All Sections 
                    <span class="badge bg-light-secondary ms-2">{{ $caseStudies->total() }} Total</span>
                </h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                    <i class="ti ti-plus me-1"></i> Add New Section
                </button>
            </div>

            <!-- Filter Section -->
            <div class="card-body bg-light-subtle py-3 border-bottom">
                <form method="GET" action="{{ route('admin.sections.index') }}" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <!-- Search -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted small mb-1">SEARCH SECTIONS</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
                                <input type="text" name="search" id="searchInput" class="form-control border-start-0 ps-0" 
                                       placeholder="Search section title..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <!-- Exam Category Filter -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">EXAM CATEGORY</label>
                            <select name="category_id" id="examCategoryFilter" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Certification Type Filter -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">CERTIFICATION TYPE</label>
                            <select name="certification_type" id="certificationTypeFilter" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
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
                            <select name="exam_id" id="examFilter" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                                <option value="">All Exams</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                                        {{ $exam->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">STATUS</label>
                            <select name="is_active" class="form-select form-select-sm" id="statusFilter" onchange="document.getElementById('filterForm').submit()">
                                <option value="">All Status</option>
                                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active Exam</option>
                                <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive Exam</option>
                            </select>
                        </div>
                        
                        <!-- Clear Button -->
                        <div class="col-md-1">
                            <a href="{{ route('admin.sections.index') }}" class="btn btn-sm btn-light-secondary w-100" title="Clear Filters">
                                <i class="ti ti-rotate"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Section Title</th>
                                <th>Exam</th>
                                <th>Exam Category</th>
                                <th>Certification Type</th>
                                <th class="text-center">Total Case Studies</th>
                                <th class="text-center">Total Questions</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($caseStudies as $section)
                            <tr>
                                <td>
                                    <strong>{{ $section->title }}</strong>
                                    @if($section->cloned_from_id)
                                        <span class="badge bg-warning text-dark ms-2" style="font-size: 0.75rem;">
                                            <i class="ti ti-copy me-1"></i> Cloned
                                        </span>
                                        <div class="small text-muted mt-1 d-flex align-items-center">
                                            <i class="ti ti-file-description me-1"></i> 
                                            {{ $section->clonedFrom->title ?? 'Unknown Section' }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-dark">{{ $section->exam->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light-primary">{{ $section->exam->category->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light-info">{{ $section->exam->certification_type ?? 'N/A' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light-success">{{ $section->caseStudies->count() }} Case Studies</span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $questionCount = $section->caseStudies->sum(function($cs) {
                                            return $cs->questions->count();
                                        });
                                    @endphp
                                    <span class="badge bg-light-warning text-warning">{{ $questionCount }} Questions</span>
                                </td>
                                <td class="text-end">
                                    @php
                                        // Check if the exam is active
                                        $isActiveExam = $section->exam && $section->exam->is_active == 1;
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
                                                <a class="dropdown-item" href="{{ route('admin.sections.show', $section->id) }}">
                                                    <i class="ti ti-eye me-2"></i>View Section
                                                </a>
                                            </li>
                                            
                                            <li>
                                                @if($isActiveExam)
                                                    <button class="dropdown-item text-muted" style="cursor: not-allowed; opacity: 0.6;" disabled title="Exam is active - cannot edit">
                                                        <i class="ti ti-edit me-2"></i>Edit Section
                                                    </button>
                                                @else
                                                    <a class="dropdown-item" href="{{ route('admin.sections.edit', $section->id) }}">
                                                        <i class="ti ti-edit me-2"></i>Edit Section
                                                    </a>
                                                @endif
                                            </li>

                                            @if($section->status == 0)
                                                <li>
                                                    <form action="{{ route('admin.sections.activate', $section->id) }}" method="GET" class="d-inline-block w-100" id="activateSectionForm{{ $section->id }}">
                                                        <button type="button" class="dropdown-item text-success" onclick="if(confirm('Are you sure you want to activate this section?')) document.getElementById('activateSectionForm{{ $section->id }}').submit();">
                                                            <i class="ti ti-check me-2"></i>Activate Section
                                                        </button>
                                                    </form>
                                                </li>
                                            @else
                                                <li>
                                                    @if($isActiveExam)
                                                        <button class="dropdown-item text-muted" style="cursor: not-allowed; opacity: 0.6;" disabled title="Exam is active - cannot delete">
                                                            <i class="ti ti-trash me-2"></i>Delete Section
                                                        </button>
                                                    @else
                                                        <form action="{{ route('admin.sections.destroy', $section->id) }}" method="POST" class="d-inline delete-form" id="delete-form-{{ $section->id }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" class="dropdown-item text-danger" onclick="if(confirm('Are you sure you want to delete this section?')) document.getElementById('delete-form-{{ $section->id }}').submit();">
                                                                <i class="ti ti-trash me-2"></i>Delete Section
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
                                <td colspan="7" class="text-center py-4">No sections found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card-footer d-flex align-items-center justify-content-between">
                <div class="text-muted small">
                    Showing {{ $caseStudies->firstItem() }} to {{ $caseStudies->lastItem() }} of {{ $caseStudies->total() }} entries
                </div>
                <div>
                     {{ $caseStudies->appends(request()->all())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Section Choice Modal -->
<div class="modal fade" id="addSectionModal" tabindex="-1" aria-labelledby="addSectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title d-flex align-items-center" id="addSectionModalLabel">
                    <i class="ti ti-file-plus me-2 fs-4"></i> Add New Section
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-4 text-center">How would you like to add a section?</p>
                
                <div class="row g-3">
                    <!-- Option 1: Create New -->
                    <div class="col-md-6">
                        <a href="{{ route('admin.sections.create', ['exam_id' => session('new_exam_id')]) }}" class="card h-100 border-2 hover-shadow text-decoration-none text-dark" style="transition: all 0.3s;">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-primary d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                        <i class="ti ti-plus text-primary" style="font-size: 2.2rem;"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold mb-2">Create Section from Scratch</h5>
                                <p class="text-muted small mb-0">Start with a blank section.</p>
                            </div>
                        </a>
                    </div>

                    <!-- Option 2: Clone Existing -->
                    <div class="col-md-6">
                        <div class="card h-100 border-2 border-primary hover-shadow text-decoration-none text-dark" style="cursor: pointer; transition: all 0.3s;" data-bs-toggle="modal" data-bs-target="#cloneSectionModal">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-primary d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                        <i class="ti ti-copy text-primary" style="font-size: 2.2rem;"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold mb-2">Clone Section from Exam</h5>
                                <p class="text-muted small mb-0">Copy an existing section.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Clone Section Modal -->
<div class="modal fade" id="cloneSectionModal" tabindex="-1" aria-labelledby="cloneSectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title d-flex align-items-center" id="cloneSectionModalLabel">
                    <i class="ti ti-copy me-2 fs-4"></i> Clone Section from Exam
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.sections.clone-external') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted mb-4">Select a source exam to clone sections into a target exam.</p>
                    
                    <div class="row g-4">
                        <!-- Source Configuration -->
                        <div class="col-md-12">
                            <h6 class="fw-bold mb-3 text-primary"><i class="ti ti-file-import me-1"></i> Source Details</h6>
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="clone_source_exam_id" class="form-label fw-bold">Source Exam</label>
                                    <select class="form-select" id="clone_source_exam_id" required>
                                        <option value="">-- Select Exam --</option>
                                        @foreach($exams as $exam)
                                            <option value="{{ $exam->id }}" data-is-active="{{ $exam->is_active }}">{{ $exam->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0">Select Sections to Clone</label>
                                    <div class="form-check m-0">
                                        <input class="form-check-input" type="checkbox" id="select_all_sections">
                                        <label class="form-check-label fw-bold small text-primary" for="select_all_sections">Select All</label>
                                    </div>
                                </div>
                                
                                <div id="sections_checkbox_list" class="row g-2 border rounded p-3 bg-white" style="max-height: 250px; overflow-y: auto; display: none;">
                                    <!-- Checkboxes injected here -->
                                </div>

                                <div id="sec_loading" class="text-center py-3" style="display: none;">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <span class="ms-2 text-muted small">Loading sections...</span>
                                </div>

                                <div id="sec_no_data" class="text-center py-3 text-muted small">
                                    -- Select Source Exam First --
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
                                <div class="col-md-12 mb-3">
                                    <label for="clone_target_exam_id" class="form-label fw-bold">Target Exam</label>
                                    <select class="form-select" id="clone_target_exam_id" name="target_exam_id" required>
                                        <option value="">-- Select Target Exam --</option>
                                        @foreach($exams as $exam)
                                            <option value="{{ $exam->id }}" {{ (request('exam_id') == $exam->id || session('new_exam_id') == $exam->id) ? 'selected' : '' }}>{{ $exam->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 mb-0 mt-3">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Note:</strong> Cloning will copy the Section, Case Studies, and Questions.
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary me-auto" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                        <i class="ti ti-arrow-left me-1"></i> Back
                    </button>
                    
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-copy me-1"></i> Clone Section
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Section Success Modal -->
<div class="modal fade" id="sectionSuccessModal" tabindex="-1" aria-labelledby="sectionSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title d-flex align-items-center" id="sectionSuccessModalLabel">
                    Section Created Successfully!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-4 text-center fs-5">Would you like to add another section or move to case studies?</p>
                
                <div class="row g-3 justify-content-center">
                    <!-- Option 1: Create Another Section -->
                    <div class="col-md-4">
                        <a href="{{ route('admin.sections.create', ['exam_id' => session('created_exam_id')]) }}" class="card h-100 border hover-shadow text-decoration-none text-dark shadow-sm" style="transition: all 0.3s; border-color: #e2e8f0;">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-primary d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="ti ti-plus text-primary fw-bold" style="font-size: 1.8rem;"></i>
                                    </div>
                                </div>
                                <h6 class="fw-bold mb-2">Create Another Section</h6>
                                <p class="text-muted small mb-0">Start blank.</p>
                            </div>
                        </a>
                    </div>

                    <!-- Option 2: Clone Section -->
                     <div class="col-md-4">
                        <div class="card h-100 border-2 border-primary hover-shadow text-decoration-none text-dark shadow-sm" style="cursor: pointer; transition: all 0.3s;" onclick="openCloneModalFromSuccess()">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-info d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="ti ti-copy text-info fw-bold" style="font-size: 1.8rem;"></i>
                                    </div>
                                </div>
                                <h6 class="fw-bold mb-2">Clone Section</h6>
                                <p class="text-muted small mb-0">From existing.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Option 3: Add Case Studies -->
                    <div class="col-md-4">
                        <a href="{{ route('admin.case-studies-bank.create', ['exam_id' => session('created_exam_id'), 'section_id' => session('created_section_id')]) }}" class="card h-100 border-2 border-primary hover-shadow text-decoration-none text-dark shadow-sm" style="transition: all 0.3s;">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-primary d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="ti ti-arrow-right text-primary fw-bold" style="font-size: 1.8rem;"></i>
                                    </div>
                                </div>
                                <h6 class="fw-bold mb-2">Add Case Studies</h6>
                                <p class="text-muted small mb-0">Proceed to content.</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Ensure pagination links are visible */
.card-footer .pagination {
    margin: 0;
}
</style>

<script>
function openCloneModalFromSuccess() {
    // Hide success modal
    var sectionSuccessModalEl = document.getElementById('sectionSuccessModal');
    var sectionSuccessModal = bootstrap.Modal.getInstance(sectionSuccessModalEl);
    if (sectionSuccessModal) {
        sectionSuccessModal.hide();
    }
    
    // Open clone modal
    var cloneSectionModal = new bootstrap.Modal(document.getElementById('cloneSectionModal'));
    cloneSectionModal.show();
    
    // Pre-select the exam in the clone modal target if avaiable
    @if(session('created_exam_id'))
        var targetSelect = document.getElementById('clone_target_exam_id');
        if(targetSelect) {
            targetSelect.value = "{{ session('created_exam_id') }}";
        }
    @endif
}

document.addEventListener('DOMContentLoaded', function() {
    // Check if we need to auto-open the Add Section modal (e.g. after creating an exam)
    @if(session('open_add_section_modal'))
        var addSectionModal = new bootstrap.Modal(document.getElementById('addSectionModal'));
        addSectionModal.show();
    @endif

    // Check if we need to auto-open the Section Success Modal (after creating a section)
    @if(session('section_created_success'))
        var sectionSuccessModal = new bootstrap.Modal(document.getElementById('sectionSuccessModal'));
        // wait a tiny bit to ensure DOM is ready
        setTimeout(function() {
            sectionSuccessModal.show();
        }, 100);
    @endif

    // Clone Flow
    const sourceExamSelect = document.getElementById('clone_source_exam_id');
    const sectionsContainer = document.getElementById('sections_checkbox_list');
    const secLoading = document.getElementById('sec_loading');
    const secNoData = document.getElementById('sec_no_data');
    const selectAllSec = document.getElementById('select_all_sections');

    // Select All Logic
    if (selectAllSec) {
        selectAllSec.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.section-source-checkbox');
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
                }
            }

            if(sectionsContainer) {
                sectionsContainer.innerHTML = '';
                sectionsContainer.style.display = 'none';
                secNoData.style.display = 'none';
                secLoading.style.display = 'block';
            }

            if (examId) {
                // Use the AJAX route we saw in web.php: questions-ajax/case-studies/{examId} which maps to SectionController@getSections
                fetch(`/admin/questions-ajax/case-studies/${examId}`)
                    .then(response => response.json())
                    .then(data => {
                        secLoading.style.display = 'none';
                        // The endpoint returns array of objects {id, title}
                        // It might return object directly or array
                        // Looking at controller: return response()->json($sections);
                        
                        if (data && data.length > 0) {
                            if(sectionsContainer) {
                                sectionsContainer.style.display = 'flex'; // row
                                
                                data.forEach(sec => {
                                    const checkboxId = `sec_source_${sec.id}`;
                                    const html = `
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input section-source-checkbox" type="checkbox" name="source_section_ids[]" value="${sec.id}" id="${checkboxId}">
                                                <label class="form-check-label text-truncate d-block" for="${checkboxId}" title="${sec.title}">
                                                    ${sec.title}
                                                </label>
                                            </div>
                                        </div>
                                    `;
                                    sectionsContainer.insertAdjacentHTML('beforeend', html);
                                });
                            }
                        } else {
                            if(secNoData) {
                                secNoData.innerText = 'No sections found for this exam';
                                secNoData.style.display = 'block';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching sections:', error);
                        secLoading.style.display = 'none';
                        if(secNoData) {
                            secNoData.innerText = 'Error loading sections';
                            secNoData.style.display = 'block';
                        }
                    });
            } else {
                secLoading.style.display = 'none';
                if(secNoData) {
                    secNoData.innerText = '-- Select Source Exam First --';
                    secNoData.style.display = 'block';
                }
            }
        });
    }
});

// Auto-submit filters on change (already handled by onchange events in elements)
// Debounced search - auto-submit after 500ms of no typing
let searchTimeout;
const searchInput = document.getElementById('searchInput');

if (searchInput) {
    // Auto-focus if there is a value (restores focus after reload)
    if (searchInput.value) {
        searchInput.focus();
        const val = searchInput.value;
        searchInput.value = '';
        searchInput.value = val;
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            document.getElementById('filterForm').submit();
        }, 500);
    });
}
</script>
@endsection
