@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Exam Management</h5>
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
                <h5 class="mb-0">All Exams <span class="badge bg-light-secondary text-secondary ms-2 small">{{ \App\Models\Exam::where('status', 1)->count() }} Total</span></h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createExamModal">
                    <i class="ti ti-plus me-1"></i> Create Exam
                </button>
            </div>

<!-- Create Exam Modal -->
<div class="modal fade" id="createExamModal" tabindex="-1" aria-labelledby="createExamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title d-flex align-items-center" id="createExamModalLabel">
                    <i class="ti ti-school me-2 fs-4"></i> Create New Exam
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-4 text-center">How would you like to start?</p>
                
                <div class="row g-3">
                    <!-- Option 1: Scratch -->
                    <div class="col-md-6">
                        <a href="{{ route('admin.exams.create') }}" class="card h-100 border-2 hover-shadow text-decoration-none text-dark" style="transition: all 0.3s;">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-primary d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                        <i class="ti ti-file-plus text-primary" style="font-size: 2.2rem;"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold mb-2">Create from Scratch</h5>
                                <p class="text-muted small mb-0">Start with a blank exam and build it step by step.</p>
                            </div>
                        </a>
                    </div>

                    <!-- Option 2: Clone -->
                    <div class="col-md-6">
                        @if($allExams->isNotEmpty())
                            <div class="card h-100 border-2 border-primary hover-shadow text-decoration-none text-dark" style="cursor: pointer; transition: all 0.3s;" data-bs-toggle="modal" data-bs-target="#cloneExamModal">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <div class="rounded-circle bg-light-primary d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                            <i class="ti ti-copy text-primary" style="font-size: 2.2rem;"></i>
                                        </div>
                                    </div>
                                    <h5 class="fw-bold mb-2">Clone Existing Exam</h5>
                                    <p class="text-muted small mb-0">Copy structure and content from an existing exam.</p>
                                </div>
                            </div>
                        @else
                            <div class="card h-100 border-2 bg-light text-muted" style="cursor: not-allowed; opacity: 0.6;" title="No exams available to clone">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <div class="rounded-circle bg-light-secondary d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                            <i class="ti ti-copy text-secondary" style="font-size: 2.2rem;"></i>
                                        </div>
                                    </div>
                                    <h5 class="fw-bold mb-2">Clone Existing Exam</h5>
                                    <p class="small mb-0">No existing exams available to clone.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
</div>
    </div>
</div>

<!-- Clone Exam Modal -->
<div class="modal fade" id="cloneExamModal" tabindex="-1" aria-labelledby="cloneExamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title d-flex align-items-center" id="cloneExamModalLabel">
                    <i class="ti ti-copy me-2 fs-4"></i> Clone Existing Exam
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cloneExamForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted mb-4">Select an exam to clone and provide details for the new exam.</p>
                    
                    <!-- Select Exam to Clone -->
                    <div class="mb-4">
                        <label for="source_exam_id" class="form-label fw-bold">
                            <i class="ti ti-file-text me-1"></i> Select Exam to Clone
                        </label>
                        <select class="form-select" id="source_exam_id" name="source_exam_id" required>
                            <option value="">-- Select an exam --</option>
                            @foreach($allExams as $exam)
                                <option value="{{ $exam->id }}" 
                                    data-name="{{ $exam->name }}" 
                                    data-code="{{ $exam->exam_code }}"
                                    data-category="{{ $exam->category ? $exam->category->name : 'N/A' }}"
                                    data-duration="{{ $exam->duration_minutes }}">
                                    {{ $exam->name }} ({{ $exam->exam_code }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">This will create a complete copy of all sections, case studies, and questions.</small>
                    </div>


                    <hr class="my-4">

                    <!-- New Exam Details -->
                    <h6 class="fw-bold mb-3"><i class="ti ti-file-plus me-1"></i> New Exam Details</h6>
                    
                    <div class="mb-3">
                        <label for="new_exam_name" class="form-label fw-bold">
                            Exam Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="new_exam_name" name="new_exam_name" required placeholder="Enter new exam name">
                    </div>

                    <div class="mb-3">
                        <label for="new_exam_code" class="form-label fw-bold">
                            Exam Code <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="new_exam_code" name="new_exam_code" value="{{ $nextCode ?? '' }}" required readonly>
                        <small class="text-muted">Auto-generated unique exam code.</small>
                    </div>

                    <div class="alert alert-info border-0 mb-0">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Note:</strong> The cloned exam will include all sections, case studies, questions, and options as separate copies. The new exam will start as <strong>inactive</strong>.
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-copy me-1"></i> Clone Exam
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle clone exam form submission
document.addEventListener('DOMContentLoaded', function() {
    const sourceExamSelect = document.getElementById('source_exam_id');
    const cloneExamForm = document.getElementById('cloneExamForm');


    // Update form action when exam is selected
    if (sourceExamSelect) {
        sourceExamSelect.addEventListener('change', function() {
            const examId = this.value;
            
            if (examId) {
                // Update form action
                cloneExamForm.action = `/admin/exams/${examId}/clone`;
            } else {
                cloneExamForm.action = '';
            }
        });
    }
});
</script>
            
            <!-- Compact Filters Section -->
            <div class="card-body bg-light-subtle py-3 border-bottom">
                <form method="GET" action="{{ route('admin.exams.index') }}" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <!-- Search -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted small mb-1">SEARCH BY NAME OR CODE</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                       placeholder="Name or code..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">EXAM CATEGORY</label>
                            <select name="category_id" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>



                        <!-- Certification Type -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">CERTIFICATION TYPE</label>
                            <select name="certification_type" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                @foreach($certificationTypes as $type)
                                    <option value="{{ $type }}" {{ request('certification_type') == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Publish Status -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">PUBLISH STATUS</label>
                            <select name="is_active" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Published</option>
                                <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Unpublished</option>
                            </select>
                        </div>

                        <!-- Status (Toggle) -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">STATUS</label>
                            <div class="form-check form-switch mt-1">
                                <input type="hidden" name="status" id="statusFilterInput" value="{{ request('status', 'active') }}">
                                <input class="form-check-input" type="checkbox" role="switch" id="statusFilterSwitch" style="width: 3em; height: 1.5em;"
                                       {{ request('status', 'active') == 'active' ? 'checked' : '' }}
                                       onchange="document.getElementById('statusFilterInput').value = this.checked ? 'active' : 'inactive'; this.form.submit()">
                                <label class="form-check-label ms-2 mt-1" for="statusFilterSwitch">
                                    {{ request('status', 'active') == 'active' ? 'Active' : 'Inactive' }}
                                </label>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="col-md-1">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('admin.exams.index') }}" class="btn btn-sm btn-light-secondary px-3" title="Reset">
                                    <i class="ti ti-rotate"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Active Filters Indicator -->
            @php
                $hasActiveFilters = request('search') || 
                                  request('category_id') || 
                                  request('certification_type') || 
                                  request()->filled('is_active') ||
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
                    @if(request()->filled('is_active'))
                        <span class="badge rounded-pill {{ request('is_active') == 1 ? 'bg-success' : 'bg-danger' }}">
                            <i class="ti ti-toggle-left me-1"></i>{{ request('is_active') == 1 ? 'Published' : 'Unpublished' }}
                        </span>
                    @endif
                    @if(request('category_id'))
                        <span class="badge rounded-pill bg-info">
                            <i class="ti ti-category me-1"></i>{{ $categories->firstWhere('id', request('category_id'))->name ?? 'Unknown' }}
                        </span>
                    @endif
                    @if(request('certification_type'))
                        <span class="badge rounded-pill bg-success">
                            <i class="ti ti-certificate me-1"></i>{{ request('certification_type') }}
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
                                <th>Exam Category</th>
                                <th style="white-space: nowrap;">Certification Type</th>
                                <th>Exam Name</th>
                                <th>Exam Code</th>
                                <th>Source</th>
                                <th style="white-space: nowrap;">Publish Status</th>
                                <th>Duration</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($exams as $exam)
                            <tr>
                                <td style="width: 10%;">
                                    @if($exam->category)
                                        <span class="badge bg-light-info">{{ $exam->category->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td style="width: 10%; white-space: nowrap;">
                                    @if($exam->certification_type)
                                        <span class="badge bg-light-success">{{ $exam->certification_type }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td style="width: 20%;">
                                    <h5 class="mb-1 fw-bold">
                                        {{ $exam->name }}
                                    </h5>
                                    @if($exam->description)
                                        <small class="text-muted d-block">{{ Str::limit($exam->description, 40) }}</small>
                                    @endif
                                </td>
                                <td style="width: 8%;">
                                    @if($exam->exam_code)
                                        <span class="badge bg-light-secondary">{{ $exam->exam_code }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td style="width: 15%;">
                                    @if($exam->cloned_from_id && $exam->clonedFrom)
                                        <span class="text-muted small">
                                            Clone from <strong>{{ $exam->clonedFrom->name }}</strong>
                                        </span>
                                    @elseif($exam->cloned_from_id)
                                        <span class="text-muted small"><em>Source Deleted</em></span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td style="width: 8%;">
                                    @if($exam->is_active == 1)
                                        <span class="badge bg-success">Published</span>
                                    @else
                                        <span class="badge bg-danger">Unpublished</span>
                                    @endif
                                </td>
                                <td style="width: 8%;">
                                    <span class="badge bg-light-primary">
                                        <i class="ti ti-clock me-1"></i>{{ $exam->duration_minutes }} mins
                                    </span>
                                </td>
                                <td class="text-end" style="width: 5%;">
                                    <div class="dropdown">
                                        <button class="btn p-0 text-secondary bg-transparent border-0 shadow-none" type="button" 
                                                data-bs-toggle="dropdown" 
                                                data-bs-boundary="viewport" 
                                                data-bs-popper-config='{"strategy":"fixed"}'
                                                aria-expanded="false">
                                            <i class="ti ti-dots-vertical f-18"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @if($exam->status == 1)
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.exams.show', $exam->id) }}">
                                                        <i class="ti ti-eye me-2"></i>View Exam
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.exams.edit', $exam->id) }}">
                                                        <i class="ti ti-edit me-2"></i>Edit Exam
                                                    </a>
                                                </li>
                                                @if($exam->is_active == 0)
                                                    <li>
                                                        <form action="{{ route('admin.exams.publish', $exam->id) }}" method="POST" class="d-block" id="publishForm{{ $exam->id }}">
                                                            @csrf
                                                            <button type="button" class="dropdown-item" onclick="showPublishModal(document.getElementById('publishForm{{ $exam->id }}'))">
                                                                <i class="ti ti-upload me-2"></i>Publish Exam
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                                <li>
                                                    @if($exam->is_active == 1)
                                                        <li>
                                                            <form action="{{ route('admin.exams.toggle-status', $exam->id) }}" method="POST" class="d-block" id="unpublishForm{{ $exam->id }}">
                                                                @csrf @method('PUT')
                                                                <button type="button" class="dropdown-item text-danger" onclick="showUnpublishModal(document.getElementById('unpublishForm{{ $exam->id }}'))">
                                                                    <i class="ti ti-eye-off me-2"></i>Unpublish Exam
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <span class="d-inline-block w-100" tabindex="0" data-bs-toggle="tooltip" title="Published exam - cannot delete">
                                                                <button class="dropdown-item disabled" type="button" style="pointer-events: none;">
                                                                    <i class="ti ti-trash me-2"></i>Delete Exam
                                                                </button>
                                                            </span>
                                                        </li>
                                                    @else
                                                        {{-- Standard Delete triggers soft delete via controller --}}
                                                        <form action="{{ route('admin.exams.destroy', $exam->id) }}" method="POST" class="d-block" id="deleteForm{{ $exam->id }}">
                                                            @csrf @method('DELETE')
                                                            <button type="button" class="dropdown-item text-danger" onclick="showDeleteModal(document.getElementById('deleteForm{{ $exam->id }}'), 'Are you sure you want to delete this exam?')">
                                                                <i class="ti ti-trash me-2"></i>Delete Exam
                                                            </button>
                                                        </form>
                                                    @endif
                                                </li>
                                            @else
                                                <li>
                                                    <form action="{{ route('admin.exams.activate', $exam->id) }}" method="POST" class="d-block" id="activateForm{{ $exam->id }}">
                                                        @csrf @method('PATCH')
                                                        <button type="button" class="dropdown-item text-success" onclick="showActivateModal(document.getElementById('activateForm{{ $exam->id }}'))">
                                                            <i class="ti ti-check me-2"></i>Activate Exam
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">No exams found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                </div>
                
                {{-- Custom Pagination --}}
                <x-custom-pagination :paginator="$exams" />
            </div>
        </div>
    </div>
</div>

<script>
    function showUnpublishModal(form) {
        if(typeof showAlert !== 'undefined') {
             showAlert.confirm(
                'Are you sure you want to unpublish this exam? It will no longer be visible to students.',
                'Unpublish Exam',
                function() {
                    form.submit();
                }
            );
        } else if(confirm('Are you sure you want to unpublish this exam?')) {
            form.submit();
        }
    }

    function showActivateModal(form) {
        if(typeof showAlert !== 'undefined' && showAlert.confirm) {
             showAlert.confirm(
                'Are you sure you want to activate this exam?',
                'Activate Exam',
                function() {
                    form.submit();
                }
            );
        } else {
            if(confirm('Are you sure you want to activate this exam?')) {
                form.submit();
            }
        }
    }

    function showPublishModal(form) {
        if(typeof showAlert !== 'undefined' && showAlert.confirm) {
             showAlert.confirm(
                'Are you sure you want to publish this exam? ensure that the exam has at least one section, one case study, and one question.',
                'Publish Exam',
                function() {
                    form.submit();
                }
            );
        } else {
            if(confirm('Are you sure you want to publish this exam?')) {
                form.submit();
            }
        }
    }
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const searchInput = filterForm.querySelector('input[name="search"]');
    const categorySelect = filterForm.querySelector('select[name="category_id"]');
    const certificationTypeSelect = filterForm.querySelector('select[name="certification_type"]');
    const statusSelect = filterForm.querySelector('select[name="is_active"]');
    const activeStatusSelect = filterForm.querySelector('select[name="status"]'); // New Status Filter
    
    let searchTimeout;
    
    // Auto-submit on dropdown change (instant)
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            filterForm.submit();
        });
    }

    if (activeStatusSelect) {
         activeStatusSelect.addEventListener('change', function() {
            filterForm.submit();
        });
    }
    
    if (certificationTypeSelect) {
        certificationTypeSelect.addEventListener('change', function() {
            filterForm.submit();
        });
    }
    
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            filterForm.submit();
        });
    }
    
    
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            filterForm.submit();
        });
    }

    // Auto-submit on search input (debounced - 1000ms delay)
    if (searchInput) {
        // Auto-focus if there is a value
        if (searchInput.value) {
            searchInput.focus();
            const val = searchInput.value;
            searchInput.value = '';
            searchInput.value = val;
        }

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                filterForm.submit();
            }, 1000);
        });
    }
});
</script>

@if(session('show_section_modal'))
<!-- Exam Created Success Modal -->
<div class="modal fade" id="examCreatedSuccessModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title d-flex align-items-center">
                    <i class="ti ti-check-circle me-2 fs-4"></i> Exam Created Successfully!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-1 text-center">Exam "<strong>{{ session('created_exam_name') }}</strong>" has been created.</p>
                <p class="text-muted mb-4 text-center">How would you like to add sections?</p>
                
                <div class="row g-3">
                    <!-- Option 1: Create -->
                    <div class="col-md-6">
                        <a href="{{ route('admin.case-studies.index', ['open_modal' => 'create']) }}" class="card h-100 border-2 hover-shadow text-decoration-none text-dark" style="transition: all 0.3s;">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-primary d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                        <i class="ti ti-file-plus text-primary" style="font-size: 2.2rem;"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold mb-2">Create Section from Scratch</h5>
                                <p class="text-muted small mb-0">Start with a blank section.</p>
                            </div>
                        </a>
                    </div>

                    <!-- Option 2: Clone -->
                    <div class="col-md-6">
                        <a href="{{ route('admin.case-studies.index', ['open_modal' => 'clone']) }}" class="card h-100 border-2 border-success hover-shadow text-decoration-none text-dark" style="transition: all 0.3s;">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-success d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                        <i class="ti ti-copy text-success" style="font-size: 2.2rem;"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold mb-2">Clone Section from Another Exam</h5>
                                <p class="text-muted small mb-0">Copy an existing section.</p>
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
    var modal = new bootstrap.Modal(document.getElementById('examCreatedSuccessModal'));
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
