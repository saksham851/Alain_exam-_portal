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

<!-- Add Section Modal -->
<div class="modal fade" id="addSectionModal" tabindex="-1" aria-labelledby="addSectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title d-flex align-items-center" id="addSectionModalLabel">
                    <i class="ti ti-layout-list me-2 fs-4"></i> Add New Section
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-4 text-center">How would you like to add a section?</p>
                
                <div class="row g-3">
                    <!-- Option 1: Create from Scratch -->
                    <div class="col-md-6">
                        <a href="{{ route('admin.case-studies.create', request()->has('exam_id') ? ['exam_id' => request('exam_id')] : []) }}" class="card h-100 border-2 hover-shadow text-decoration-none text-dark" style="transition: all 0.3s;">
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

                    <!-- Option 2: Clone Section -->
                    <div class="col-md-6">
                        <div class="card h-100 border-2 border-primary hover-shadow text-decoration-none text-dark" style="cursor: pointer; transition: all 0.3s;" data-bs-toggle="modal" data-bs-target="#cloneSectionModal">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-primary d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                        <i class="ti ti-copy text-primary" style="font-size: 2.2rem;"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold mb-2">Clone Section from Another Exam</h5>
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
                    <i class="ti ti-copy me-2 fs-4"></i> Clone Section from Another Exam
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.case-studies.clone') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted mb-4">Select a source exam and section to clone into a target exam.</p>
                    
                    <div class="row g-4">
                        <!-- Source Configuration -->
                        <div class="col-md-12">
                            <h6 class="fw-bold mb-3 text-primary"><i class="ti ti-file-import me-1"></i> Source Details</h6>
                            
                            <div class="mb-3">
                                <label for="clone_source_exam_id" class="form-label fw-bold">Select Source Exam</label>
                                <select class="form-select" id="clone_source_exam_id" name="source_exam_id" required>
                                    <option value="">-- Select Exam --</option>
                                    @foreach($exams as $exam)
                                        <option value="{{ $exam->id }}">{{ $exam->name }}{{ $exam->exam_code ? ' (' . $exam->exam_code . ')' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Select Sections to Clone</label>
                                <div id="select_exam_message" class="text-muted small f-st-italic mb-2">-- Select Source Exam First --</div>
                                <div id="loading_sections_message" class="text-muted small f-st-italic mb-2" style="display: none;">Loading sections...</div>
                                
                                <div id="source_sections_container" class="border rounded p-3 bg-white" style="max-height: 250px; overflow-y: auto; display: none;">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="select_all_sections">
                                        <label class="form-check-label fw-bold small text-primary" for="select_all_sections">Select All Sections</label>
                                    </div>
                                    <div id="sections_checkbox_list" class="row g-2 border-top pt-2 mt-2">
                                        <!-- Checkboxes injected here -->
                                    </div>
                                </div>
                                <div id="no_sections_message" class="text-danger small mt-2" style="display: none;">No sections found for this exam.</div>
                            </div>
                        </div>

                        <div class="col-md-12">
                             <hr class="my-2">
                        </div>

                        <!-- Target Configuration -->
                        <div class="col-md-12">
                            <h6 class="fw-bold mb-3 text-success"><i class="ti ti-file-export me-1"></i> Target Details</h6>
                            
                            <div class="mb-3">
                                <label for="clone_target_exam_id" class="form-label fw-bold">Select Target Exam</label>
                                <select class="form-select" id="clone_target_exam_id" name="target_exam_id" required>
                                    <option value="">-- Select Target Exam --</option>
                                    @foreach($exams as $exam)
                                        <option value="{{ $exam->id }}" 
                                                data-is-active="{{ $exam->is_active }}"
                                                {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                                            {{ $exam->name }}{{ $exam->exam_code ? ' (' . $exam->exam_code . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">The cloned section will be added to this exam.</small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 mb-0 mt-3">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Note:</strong> Cloning a section will copy all its Case Studies, Questions, and Options. The new section will be set as active.
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <!-- Back to Add Section Modal -->
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Target Flow
    const targetExamSelect = document.getElementById('clone_target_exam_id');
    const targetSectionSelect = document.getElementById('clone_target_section_id'); // This variable is not used in the provided snippet, but I should include it if it's part of the user's intended code.

    if (targetExamSelect) {
        targetExamSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const isActive = selectedOption.getAttribute('data-is-active');

            if (isActive == '1') {
                alert('You cannot clone into an active exam. Please deactivate the exam first.');
                this.value = ""; // Reset selection
                return;
            }
        });
    }

    const sourceExamSelect = document.getElementById('clone_source_exam_id');
    const sectionsContainer = document.getElementById('source_sections_container');
    const sectionsList = document.getElementById('sections_checkbox_list');
    const selectExamMsg = document.getElementById('select_exam_message');
    const loadingMsg = document.getElementById('loading_sections_message');
    const noSectionsMsg = document.getElementById('no_sections_message');
    const selectAllCheckbox = document.getElementById('select_all_sections');

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
            
            // Reset UI
            sectionsContainer.style.display = 'none';
            noSectionsMsg.style.display = 'none';
            selectExamMsg.style.display = 'none';
            sectionsList.innerHTML = '';
            selectAllCheckbox.checked = false;

            if (examId) {
                loadingMsg.style.display = 'block';
                
                fetch(`/admin/case-studies/ajax/sections/${examId}`)
                    .then(response => response.json())
                    .then(data => {
                        loadingMsg.style.display = 'none';
                        
                        if (data.length > 0) {
                            sectionsContainer.style.display = 'block';
                            data.forEach(section => {
                                const checkboxId = `section_${section.id}`;
                                const html = `
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input section-checkbox" type="checkbox" name="source_section_ids[]" value="${section.id}" id="${checkboxId}">
                                            <label class="form-check-label text-truncate d-block" for="${checkboxId}" title="${section.title}">
                                                ${section.title}
                                            </label>
                                        </div>
                                    </div>
                                `;
                                sectionsList.insertAdjacentHTML('beforeend', html);
                            });
                        } else {
                            noSectionsMsg.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching sections:', error);
                        loadingMsg.style.display = 'none';
                        noSectionsMsg.innerHTML = 'Error loading sections. Please try again.';
                        noSectionsMsg.style.display = 'block';
                    });
            } else {
                selectExamMsg.style.display = 'block';
            }
        });

        // Handle Select All
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.section-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        }
    }
});
</script>

            <!-- Compact Filters Section -->
            <div class="card-body bg-light-subtle py-3 border-bottom">
                <form method="GET" action="{{ route('admin.case-studies.index') }}" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <!-- Search -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted small mb-1">SEARCH BY SECTION NAME</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                       placeholder="Section name..." value="{{ request('search') }}">
                            </div>
                        </div>

                        
                        <!-- Exam -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">EXAM</label>
                            <select name="exam_id" class="form-select form-select-sm" id="examSelect">
                                <option value="">All Exams</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                                        {{ $exam->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Category -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">EXAM CATEGORY</label>
                            <select name="category_id" class="form-select form-select-sm" id="categorySelect">
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
                            <select name="certification_type" class="form-select form-select-sm" id="certificationTypeSelect">
                                <option value="">All Types</option>
                                @foreach($certificationTypes as $type)
                                    <option value="{{ $type }}" {{ request('certification_type') == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <!-- Status -->
                        <div class="col-md-1">
                            <label class="form-label fw-bold text-muted small mb-1">EXAM STATUS</label>
                            <select name="is_active" class="form-select form-select-sm" id="statusSelect">
                                <option value="">All</option>
                                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="col-md-2">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('admin.case-studies.index') }}" class="btn btn-sm btn-light-secondary px-3" title="Reset">
                                    <i class="ti ti-rotate"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const filterForm = document.getElementById('filterForm');
                const searchInput = filterForm.querySelector('input[name="search"]');
                const categorySelect = document.getElementById('categorySelect');
                const certificationTypeSelect = document.getElementById('certificationTypeSelect');
                const examSelect = document.getElementById('examSelect');
                const statusSelect = document.getElementById('statusSelect');
                
                let searchTimeout;
                
                // Auto-submit on dropdown change (instant)
                if (categorySelect) {
                    categorySelect.addEventListener('change', function() {
                        filterForm.submit();
                    });
                }
                
                if (certificationTypeSelect) {
                    certificationTypeSelect.addEventListener('change', function() {
                        filterForm.submit();
                    });
                }
                
                if (examSelect) {
                    examSelect.addEventListener('change', function() {
                        filterForm.submit();
                    });
                }
                
                if (statusSelect) {
                    statusSelect.addEventListener('change', function() {
                        filterForm.submit();
                    });
                }
                
                // Auto-submit on search input (debounced - 500ms delay)
                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(function() {
                            filterForm.submit();
                        }, 500);
                    });
                }
            });
            </script>
            
            <!-- Active Filters Indicator -->
            @php
                $hasActiveFilters = request('search') || 
                                  request('exam_id') || 
                                  request('category_id') || 
                                  request('certification_type') ||
                                  request()->filled('is_active');
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
                    @if(request()->filled('is_active'))
                        <span class="badge rounded-pill {{ request('is_active') == 1 ? 'bg-success' : 'bg-danger' }}">
                            <i class="ti ti-toggle-left me-1"></i>{{ request('is_active') == 1 ? 'Active Exams' : 'Inactive Exams' }}
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
                    @if(request('exam_id'))
                        <span class="badge rounded-pill bg-primary">
                            <i class="ti ti-book me-1"></i>{{ $exams->firstWhere('id', request('exam_id'))->name ?? 'Unknown' }}
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
                                <th>Certification Type</th>
                                <th>Exam</th>
                                <th>Section Name</th>
                                <th>Total Case Studies</th>
                                <th>Total Questions</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($caseStudies as $cs)
                            <tr>
                                <td>
                                    @if($cs->exam && $cs->exam->category)
                                        <span class="badge bg-light-info text-info">{{ $cs->exam->category->name }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($cs->exam && $cs->exam->certification_type)
                                        <span class="badge bg-light-success text-success">{{ $cs->exam->certification_type }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $cs->exam->name ?? 'N/A' }}</td>
                                <td>
                                    <h6 class="mb-0">{{ $cs->title }}</h6>
                                    <small class="text-muted">{{ Str::limit(strip_tags($cs->content), 50) }}</small>
                                </td>
                                <td><span class="badge bg-light-primary text-primary">{{ $cs->caseStudies->count() }}</span></td>
                                <td><span class="badge bg-light-info text-info">{{ $cs->caseStudies->sum(function($sc) { return $sc->questions()->where('status', 1)->count(); }) }}</span></td>
                                <td class="text-end">
                                    @php
                                        $isActiveExam = $cs->exam && $cs->exam->is_active == 1;
                                    @endphp
                                    @if($isActiveExam)
                                        <ul class="list-inline mb-0">
                                            <li class="list-inline-item">
                                                <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" title="Exam is active - cannot edit">
                                                    <button class="avtar avtar-s btn-link-success btn-pc-default" style="opacity: 0.5; border: none;" disabled>
                                                        <i class="ti ti-edit f-18"></i>
                                                    </button>
                                                </span>
                                            </li>
                                            <li class="list-inline-item">
                                                <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" title="Exam is active - cannot delete">
                                                    <button class="avtar avtar-s btn-link-danger btn-pc-default" style="opacity: 0.5; border: none;" disabled>
                                                        <i class="ti ti-trash f-18"></i>
                                                    </button>
                                                </span>
                                            </li>
                                        </ul>
                                    @else
                                        <ul class="list-inline mb-0">
                                            <li class="list-inline-item">
                                                <a href="{{ route('admin.case-studies.edit', $cs->id) }}" class="avtar avtar-s btn-link-success btn-pc-default" data-bs-toggle="tooltip" title="Edit Section">
                                                    <i class="ti ti-edit f-18"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item">
                                                <form action="{{ route('admin.case-studies.destroy', $cs->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this section?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="avtar avtar-s btn-link-danger btn-pc-default" style="border:none; background:none;" data-bs-toggle="tooltip" title="Delete Section">
                                                        <i class="ti ti-trash f-18"></i>
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No sections found.</td>
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

@if(session('section_created_success'))
<!-- Create Section Success Modal -->
<div class="modal fade" id="createSectionSuccessModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title d-flex align-items-center">
                    <i class="ti ti-check-circle me-2 fs-4"></i> Section Created Successfully!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-4 text-center">Would you like to add another section or move to case studies?</p>
                
                <div class="row g-3">
                    <!-- Option 1: Create Another Section -->
                    <div class="col-md-4">
                        <a href="{{ route('admin.case-studies.create', ['exam_id' => session('created_exam_id')]) }}" class="card h-100 border-2 hover-shadow text-decoration-none text-dark" style="transition: all 0.3s;">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-primary d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="ti ti-plus text-primary" style="font-size: 1.8rem;"></i>
                                    </div>
                                </div>
                                <h6 class="fw-bold mb-2">Create Another Section</h6>
                                <p class="text-muted small mb-0">Start blank.</p>
                            </div>
                        </a>
                    </div>

                    <!-- Option 2: Clone Another Section -->
                    <div class="col-md-4">
                        <div class="card h-100 border-2 border-primary hover-shadow text-decoration-none text-dark" 
                             style="cursor: pointer; transition: all 0.3s;" 
                             data-bs-toggle="modal" 
                             data-bs-target="#cloneSectionModal"
                             onclick="setCloneTarget('{{ session('created_exam_id') }}')">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-info d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="ti ti-copy text-info" style="font-size: 1.8rem;"></i>
                                    </div>
                                </div>
                                <h6 class="fw-bold mb-2">Clone Section</h6>
                                <p class="text-muted small mb-0">From existing.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Option 3: Proceed -->
                    <div class="col-md-4">
                        <a href="{{ route('admin.case-studies-bank.index', ['open_modal' => 'create', 'exam_id' => session('created_exam_id'), 'section_id' => session('created_section_id')]) }}" class="card h-100 border-2 border-primary hover-shadow text-decoration-none text-dark" style="transition: all 0.3s;">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-light-primary d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="ti ti-arrow-right text-primary" style="font-size: 1.8rem;"></i>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show success alert first
    showAlert.success('Section created successfully!', 'Success!');
    
    // Then show the modal
    var modal = new bootstrap.Modal(document.getElementById('createSectionSuccessModal'));
    modal.show();
});
</script>
@endif

@if(request('open_modal'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = new bootstrap.Modal(document.getElementById('addSectionModal'));
    modal.show();
});
</script>
@endif

<script>
function setCloneTarget(examId) {
    // Small delay to ensure modal is ready
    setTimeout(() => {
        const targetSelect = document.getElementById('clone_target_exam_id');
        if (targetSelect && examId) {
            targetSelect.value = examId;
            // Trigger change event manually
            targetSelect.dispatchEvent(new Event('change'));
        }
    }, 200);
}

// Initialize Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

@endsection
