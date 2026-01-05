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
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item" aria-current="page">Case Studies Bank</li>
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
                <h5 class="mb-0">
                    All Case Studies 
                    <span class="badge bg-light-secondary ms-2">{{ $caseStudies->total() }} Total</span>
                </h5>
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
                </form>
            </div>

            <form id="copyForm" action="{{ route('admin.case-studies-bank.copy') }}" method="POST">
                @csrf

            <!-- Copy Action Section -->
            <div class="card-body bg-white py-3 border-bottom">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Select Target Exam</label>
                        <select id="targetExam" class="form-select form-select-sm" onchange="loadSections()">
                            <option value="">Choose Exam...</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Select Target Section</label>
                        <select name="target_section_id" id="targetSection" class="form-select form-select-sm" required>
                            <option value="">Choose Section...</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-success" id="copyBtn" disabled>
                            <i class="ti ti-copy me-1"></i> Copy Selected Case Studies
                        </button>
                        <small class="text-muted ms-2" id="selectedCount">0 selected</small>
                    </div>
                </div>
            </div>
                
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Case Study Title</th>
                                <th>Current Section</th>
                                <th>Current Exam</th>
                                <th>Source (If Cloned)</th>
                                <th>Exam Category</th>
                                <th>Certification Type</th>
                                <th class="text-center">Questions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($caseStudies as $caseStudy)
                            <tr>
                                <td>
                                    <input type="checkbox" name="case_study_ids[]" value="{{ $caseStudy->id }}" class="form-check-input case-study-checkbox">
                                </td>
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
            </form>
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
@endsection
