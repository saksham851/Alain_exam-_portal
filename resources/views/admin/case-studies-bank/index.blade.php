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
                    <!-- Option 1: Create from Scratch -->
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

                    <!-- Option 2: Clone Case Study -->
                    <div class="col-md-6">
                        <div class="card h-100 border-2 border-primary hover-shadow text-decoration-none text-dark" style="cursor: pointer; transition: all 0.3s;" onclick="alert('Clone case study feature coming soon!')">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-info d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                        <i class="ti ti-copy text-info" style="font-size: 2.2rem;"></i>
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
            
            <!-- Compact Filters Section -->
            <div class="card-body bg-light-subtle py-3 border-bottom">
                <form method="GET" action="{{ route('admin.case-studies-bank.index') }}" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <!-- Exam Category Filter -->
                        <div class="col-md-3">
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
                        <div class="col-md-3">
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

                        <!-- Search -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted small mb-1">SEARCH CASE STUDIES</label>
                            <input type="text" name="search" id="searchInput" class="form-control form-control-sm" placeholder="Search case study title..." value="{{ request('search') }}">
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

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Case Study Title</th>
                                <th>Current Section</th>
                                <th>Current Exam</th>
                                <th>Source (If Cloned)</th>
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
                                    @if($caseStudy->cloned_from_id)
                                        <div class="d-flex flex-column gap-1">
                                            <span class="badge bg-warning text-dark">
                                                <i class="ti ti-copy"></i> Cloned
                                            </span>
                                            <small class="text-muted">
                                                <strong>From:</strong> {{ $caseStudy->clonedFromSection->title ?? 'N/A' }}
                                            </small>
                                            <small class="text-muted">
                                                <strong>Exam:</strong> {{ $caseStudy->clonedFromSection->exam->name ?? 'N/A' }}
                                            </small>
                                            <small class="text-muted">
                                                <strong>Date:</strong> {{ $caseStudy->cloned_at ? $caseStudy->cloned_at->format('d M Y, h:i A') : 'N/A' }}
                                            </small>
                                        </div>
                                    @else
                                        <span class="badge bg-light-success">
                                            <i class="ti ti-star"></i> Original
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light-primary">{{ $caseStudy->section->exam->category->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light-info">{{ $caseStudy->section->exam->category->certification_type ?? 'N/A' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light-success">{{ $caseStudy->questions->count() }} Questions</span>
                                </td>
                                <td class="text-end">
                                    @php
                                        // specific check for case study being in an active exam
                                        $isActiveExam = $caseStudy->section && $caseStudy->section->exam && $caseStudy->section->exam->is_active == 1;
                                    @endphp
                                    <ul class="list-inline mb-0">
                                        <li class="list-inline-item">
                                            @if($isActiveExam)
                                                <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" title="Exam is active - cannot edit">
                                                    <button class="btn btn-icon btn-link-secondary btn-sm" disabled style="opacity: 0.5; border: none;">
                                                        <i class="ti ti-edit f-18"></i>
                                                    </button>
                                                </span>
                                            @else
                                                <a href="{{ route('admin.case-studies-bank.edit', $caseStudy->id) }}" class="avtar avtar-s btn-link-success btn-pc-default" data-bs-toggle="tooltip" title="Edit Case Study">
                                                    <i class="ti ti-edit f-18"></i>
                                                </a>
                                            @endif
                                        </li>
                                        <li class="list-inline-item">
                                            @if($isActiveExam)
                                                <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" title="Exam is active - cannot delete">
                                                    <button class="btn btn-icon btn-link-secondary btn-sm" disabled style="opacity: 0.5; border: none;">
                                                        <i class="ti ti-trash f-18"></i>
                                                    </button>
                                                </span>
                                            @else

                                                <form action="{{ route('admin.case-studies-bank.destroy', $caseStudy->id) }}" method="POST" class="d-inline delete-form" id="delete-form-{{ $caseStudy->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="avtar avtar-s btn-link-danger btn-pc-default" style="border:none; background:none;" onclick="confirmDelete('{{ $caseStudy->id }}')" data-bs-toggle="tooltip" title="Delete Case Study">
                                                        <i class="ti ti-trash f-18"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </li>
                                    </ul>
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
    
    fetch(`/admin/case-studies-bank/sections/${examId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                sectionSelect.innerHTML = '<option value="">Choose Section...</option>';
                data.sections.forEach(section => {
                    const option = document.createElement('option');
                    option.value = section.id;
                    option.textContent = section.title;
                    sectionSelect.appendChild(option);
                });
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
    
    return confirm(`Are you sure you want to copy ${selected} case study(ies) to the selected section?`);
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
                        <div class="card h-100 border-2 border-primary hover-shadow text-decoration-none text-dark" style="cursor: pointer; transition: all 0.3s;" onclick="alert('Clone case studies feature coming soon!')">
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
