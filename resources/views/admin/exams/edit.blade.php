@extends('layouts.app')

@php
    $role = auth()->user()->role;
    $routePrefix = ($role === 'manager') ? 'manager.exams' : 'admin.exams';
    $baseUrl = ($role === 'manager') ? 'manager' : 'admin';
@endphp

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">{{ isset($exam) ? 'Edit Exam' : 'Create Exam' }}</h5>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route($baseUrl . '.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route($routePrefix . '.index') }}">Exams</a></li>
            <li class="breadcrumb-item" aria-current="page">{{ isset($exam) ? 'Edit Exam' : 'Create Exam' }}</li>
          </ul>
        </div>

      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<style>
    /* Clean Minimalist Styling */
    .section-card {
        border: 1px solid #e2e8f0 !important;
        background: #fff;
        transition: all 0.2s ease;
        border-radius: 12px !important;
        cursor: pointer;
    }
    .section-card:hover {
        border-color: #5d87ff !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04) !important;
    }
    .section-card.active-section {
        border-color: #5d87ff !important;
        background: #f0f7ff !important;
        box-shadow: inset 0 0 0 1px #5d87ff !important;
    }

    .icon-square {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #f1f5f9;
        color: #64748b;
    }
    .active-section .icon-square {
        background: #5d87ff;
        color: #fff;
    }

    /* Fixed Clean Dropdown */
    .btn-link:hover {
        text-decoration: none !important;
    }
    .dropdown-menu {
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        border-radius: 8px;
        min-width: 120px;
        padding: 4px;
    }
    .dropdown-item {
        font-size: 12px;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 500;
        display: flex;
        align-items: center;
    }
    .dropdown-item i {
        font-size: 14px;
        margin-right: 8px;
    }

    /* Content Area Styles */
    .case-study-card {
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: none !important;
    }
    .visit-block {
        background: #fbfcfe;
        border: 1px solid #edf2f7;
        border-radius: 10px;
        padding: 16px !important;
    }
    .question-item {
        background: #fff;
        border: 1px solid #edf2f7 !important;
        border-radius: 8px !important;
        padding: 12px 16px !important;
        transition: all 0.2s ease;
    }
    .question-item:hover {
        border-color: #5d87ff !important;
        transform: translateX(4px);
    }

    .border-dashed { border-style: dashed !important; }
    .btn-xs { padding: 4px 8px; font-size: 11px; }

    .animate-in {
        animation: fadeIn 0.3s ease forwards;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

@if(isset($exam) && $exam->exam_standard_id)
<!-- Progress Tracker Widget -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom-0 py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="bg-primary-subtle p-2 rounded-3 me-3">
                        <i class="ti ti-chart-infographic text-primary fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Standard Compliance Tracker</h6>
                        <small class="text-muted">Real-time validation against the assigned standard</small>
                    </div>
                </div>
                <button class="btn btn-sm btn-icon btn-light-secondary rounded-circle collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#complianceTrackerBody" aria-expanded="false">
                    <i class="ti ti-chevron-down"></i>
                </button>
            </div>
            <div class="collapse" id="complianceTrackerBody">
                <div class="card-body pt-0">
                    @php
                        $validation = $exam->validateStandardCompliance();
                        $totalQuestions = $validation['total_questions'];
                        $expectedTotal = $exam->total_questions ?? 0;
                        $progressPercent = $expectedTotal > 0 ? round(($totalQuestions / $expectedTotal) * 100) : 0;
                        $isValidStatus = $validation['valid'];
                    @endphp

                    <!-- Overall Progress -->
                    <div class="bg-white rounded-3 p-4 mb-4 border border-light-subtle shadow-sm position-relative overflow-hidden">
                        <!-- Decorative left border -->
                        <div class="position-absolute top-0 bottom-0 start-0 {{ $isValidStatus ? 'bg-success' : 'bg-warning' }}" style="width: 4px;"></div>
                        
                        <div class="row align-items-center ps-2">
                            <div class="col">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-bold text-dark fs-5">Overall Exam Capacity</span>
                                    <span class="badge {{ $isValidStatus ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' }} rounded-pill px-3 py-2 fw-bold fs-6">
                                        @if($expectedTotal > 0)
                                            {{ $totalQuestions }} / {{ $expectedTotal }} Points ({{ $progressPercent }}%)
                                        @else
                                            {{ $totalQuestions }} Points
                                        @endif
                                    </span>
                                </div>
                                @if($expectedTotal > 0)
                                <div class="progress rounded-pill overflow-hidden shadow-none mb-3" style="height: 14px; background: rgba(0,0,0,0.04);">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated {{ $isValidStatus ? 'bg-success' : 'bg-warning' }}" 
                                         role="progressbar" 
                                         style="width: {{ min($progressPercent, 100) }}%;" 
                                         aria-valuenow="{{ $progressPercent }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                @endif
                                <div class="d-flex align-items-center bg-light p-2 rounded-2">
                                    @if(!$isValidStatus)
                                        <i class="ti ti-alert-triangle text-warning me-2 fs-4"></i>
                                        <span class="text-dark fw-medium">
                                            @if($expectedTotal > 0 && $expectedTotal != $totalQuestions)
                                                @if($expectedTotal > $totalQuestions)
                                                    Action required: <span class="text-warning fw-bold">Add {{ $expectedTotal - $totalQuestions }} more points</span>
                                                @else
                                                    Action required: <span class="text-danger fw-bold">Remove {{ $totalQuestions - $expectedTotal }} points</span>
                                                @endif
                                            @else
                                                Action required: <strong class="text-danger">Questions are not properly categorized</strong> (See breakdown below)
                                            @endif
                                        </span>
                                    @else
                                        <i class="ti ti-circle-check text-success me-2 fs-4"></i>
                                        <span class="text-dark fw-bold">All requirements met! This exam is ready to be published.</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route(($role === 'manager' ? 'manager' : 'admin') . '.questions.index', ['exam_id' => $exam->id]) }}" class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm">
                                    <i class="ti ti-settings me-1"></i> Manage Questions
                                </a>
                            </div>
                        </div>
                    </div>



                    <!-- Detailed Breakdown Content -->
                    <div id="progressDetails">
                    <div class="row g-4">
                        @php
                            $groupedAreas = collect($validation['content_areas'])->groupBy('category');
                        @endphp

                        @foreach($groupedAreas as $categoryName => $areas)
                        <div class="col-md-6">
                            <div class="h-100 bg-white border rounded-2 shadow-sm overflow-hidden">
                                <div class="bg-light px-3 py-2 border-bottom d-flex align-items-center">
                                    <div class="bg-white p-1 rounded-1 shadow-sm me-2 border border-light">
                                        <i class="ti ti-category text-primary"></i>
                                    </div>
                                    <h6 class="mb-0 fw-bold text-dark fs-6">{{ $categoryName }}</h6>
                                </div>
                                <div class="p-3 vstack gap-3">
                                    @foreach($areas as $area)
                                    <div class="content-area-item">
                                        <div class="d-flex justify-content-between align-items-end mb-2">
                                            <span class="text-dark fw-bold" style="font-size: 14px;">{{ $area['name'] }}</span>
                                            <div class="text-end">
                                                <span class="fs-5 {{ $area['valid'] ? 'text-success' : 'text-danger' }} fw-bold lh-1">
                                                    {{ $area['current'] }}
                                                </span>
                                                <span class="text-muted small fw-medium">/ {{ $area['required'] }}</span>
                                            </div>
                                        </div>
                                        <div class="progress rounded-pill overflow-hidden" style="height: 8px; background: rgba(0,0,0,0.06);">
                                            <div class="progress-bar {{ $area['valid'] ? 'bg-success' : 'bg-warning progress-bar-striped progress-bar-animated' }}" 
                                                 style="width: {{ $area['required'] > 0 ? min(($area['current'] / $area['required']) * 100, 100) : 0 }}%;"></div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-2 align-items-center">
                                            <span class="badge bg-light text-muted border fw-normal" style="font-size: 11px;">{{ $area['required'] }} point requirement</span>
                                            @if(!$area['valid'])
                                                <span class="badge bg-warning-subtle text-warning-emphasis fw-bold" style="font-size: 11px;">
                                                    <i class="ti ti-alert-circle me-1"></i>
                                                    {{ $area['required'] - $area['current'] > 0 ? 'Short by ' . ($area['required'] - $area['current']) . ' pts' : 'Exceeds by ' . ($area['current'] - $area['required']) . ' pts' }}
                                                </span>
                                            @else
                                                <span class="text-success small fw-bold" style="font-size: 11px;"><i class="ti ti-check me-1"></i>Verified</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if(!$loop->last)
                                        <hr class="my-0 border-light opacity-50">
                                    @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endif

<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom-0 py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="bg-primary-subtle p-2 rounded-3 me-3">
                        <i class="ti ti-settings text-primary fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Exam Configuration</h6>
                        <small class="text-muted">General settings and meta information</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if(isset($exam) && auth()->user()->role !== 'manager')
                         <form action="{{ route('admin.exams.toggle-status', $exam->id) }}" method="POST" id="status-toggle-form" class="m-0">
                            @csrf
                            @method('PUT')
                            <button type="button" class="btn btn-sm {{ $exam->is_active ? 'btn-light-danger' : 'btn-light-success' }} px-3 rounded-pill" onclick="confirmStatusChange()">
                                <i class="ti {{ $exam->is_active ? 'ti-lock' : 'ti-lock-open' }} me-1"></i>
                                {{ $exam->is_active ? 'Unpublish' : 'Publish' }}
                            </button>
                        </form>
                    @endif
                    <button class="btn btn-sm btn-icon btn-light-secondary rounded-circle" type="button" data-bs-toggle="collapse" data-bs-target="#examConfigurationBody" aria-expanded="true">
                        <i class="ti ti-chevron-down"></i>
                    </button>
                </div>
            </div>
            <div class="collapse show" id="examConfigurationBody">
                <div class="card-body">
                    <form action="{{ isset($exam) ? route($routePrefix . '.update', $exam->id) : route($routePrefix . '.store') }}" method="POST">
                    @csrf
                    @if(isset($exam))
                        @method('PUT')
                    @endif

                    @if(isset($exam) && $exam->is_active == 1)
                    <div class="alert alert-warning d-flex align-items-start gap-3 mb-4" role="alert">
                        <i class="ti ti-lock" style="font-size: 20px; margin-top: 3px;"></i>
                        <div>
                            <strong>This Exam is Published/Locked</strong>
                            <p class="mb-0 mt-2">This exam is currently published and locked for editing. To make changes, please check the "Force Edit" checkbox below to confirm you want to edit this published exam.</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="force_edit" id="forceEdit" value="1">
                            <label class="form-check-label" for="forceEdit">
                                I understand this exam is published. <strong>Force Edit this exam</strong>
                            </label>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Exam Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', optional($exam)->name) }}" required pattern="^[a-zA-Z0-9\s\-\_\(\)\.\&\:\,]+$" title="Only letters, numbers, spaces, and common symbols ( - _ ( ) . & : , ) are allowed.">
                            @error('name') 
                                <small class="text-danger">{{ $message }}</small> 
                            @else
                                <small class="text-muted">Only letters, numbers, spaces, and common symbols are allowed.</small>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Exam Code <span class="text-danger">*</span></label>
                            <input type="text" name="exam_code" class="form-control" value="{{ old('exam_code', optional($exam)->exam_code ?? ($nextCode ?? '')) }}" required readonly>
                            @error('exam_code') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Exam Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', optional($exam)->category_id ?? request('category_id')) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Duration (Minutes) <span class="text-danger">*</span></label>
                            <input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', optional($exam)->duration_minutes ?? 180) }}" required min="1" title="Duration must be at least 1 minute.">
                            @error('duration_minutes') 
                                <small class="text-danger">{{ $message }}</small> 
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Certification Type <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select name="certification_type" id="certificationTypeSelect" class="form-select" required>
                                    <option value="">Select Certification Type</option>
                                    <option value="NHMCE" {{ old('certification_type', optional($exam)->certification_type) == 'NHMCE' ? 'selected' : '' }}>NHMCE</option>
                                </select>
                                <input type="text" name="new_certification_type" id="newCertificationTypeInput" class="form-control" placeholder="Enter new certification type" style="display: none;" pattern="^[a-zA-Z0-9\s\-\_\(\)\.\&\:\,]+$" title="Only letters, numbers, spaces, and common symbols ( - _ ( ) . & : , ) are allowed.">
                                <button type="button" id="addNewTypeBtn" class="btn btn-primary" style="white-space: nowrap;">
                                    <i class="ti ti-plus me-1"></i> Add New
                                </button>
                            </div>
                            @error('certification_type') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                            @error('new_certification_type') 
                                <small class="text-danger d-block mt-1">{{ $message }}</small> 
                            @else
                                <small class="text-muted d-none mt-1" id="certTypeHelper">Only letters, numbers, spaces, and common symbols are allowed.</small>
                            @enderror
                        </div>



                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const selectElement = document.getElementById('certificationTypeSelect');
                            const newInputElement = document.getElementById('newCertificationTypeInput');
                            const addNewBtn = document.getElementById('addNewTypeBtn');
                            let isAddingNew = false;
                            
                            // Check if we should be in "add new" mode initially (e.g. on validation error)
                            @if(old('new_certification_type') || (isset($exam) && $exam->certification_type && $exam->certification_type != 'NHMCE'))
                                toggleMode();
                            @endif

                            function toggleMode() {
                                isAddingNew = !isAddingNew;
                                if (isAddingNew) {
                                    // Show input field for new certification type
                                    selectElement.style.display = 'none';
                                    selectElement.required = false;
                                    selectElement.disabled = true; // Disable so it's not submitted

                                    newInputElement.style.display = 'block';
                                    newInputElement.required = true;
                                    newInputElement.disabled = false;
                                    if(newInputElement.value === '') {
                                        newInputElement.value = "{{ isset($exam) && $exam->certification_type != 'NHMCE' ? $exam->certification_type : old('new_certification_type') }}";
                                    }
                                    newInputElement.focus();

                                    addNewBtn.innerHTML = '<i class="ti ti-x me-1"></i> Cancel';
                                    addNewBtn.classList.remove('btn-primary');
                                    addNewBtn.classList.add('btn-secondary');
                                    document.getElementById('certTypeHelper').classList.remove('d-none');
                                } else {
                                    // Hide input field and show dropdown
                                    selectElement.style.display = 'block';
                                    selectElement.required = true;
                                    selectElement.disabled = false;

                                    newInputElement.style.display = 'none';
                                    newInputElement.required = false;
                                    newInputElement.disabled = true; // Disable so it's not submitted
                                    
                                    addNewBtn.innerHTML = '<i class="ti ti-plus me-1"></i> Add New';
                                    addNewBtn.classList.remove('btn-secondary');
                                    addNewBtn.classList.add('btn-primary');
                                    document.getElementById('certTypeHelper').classList.add('d-none');
                                }
                            }

                            addNewBtn.addEventListener('click', function() {
                                toggleMode();
                            });
                        });
                        </script>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', optional($exam)->description) }}</textarea>
                            @error('description') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <!-- Exam Standard Section -->
                        <div class="col-md-12 mb-3">
                            <hr class="my-4">
                            <h6 class="mb-3">Exam Standard (Optional)</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Exam Standard</label>
                            <select name="exam_standard_id" id="examStandardSelect" class="form-select">
                                <option value="">No Standard (Optional)</option>
                                @foreach($examStandards as $standard)
                                    <option value="{{ $standard->id }}" 
                                            data-categories='@json($standard->categories->map(function($c) { return ['id' => $c->id, 'name' => $c->name]; }))'
                                            {{ old('exam_standard_id', optional($exam)->exam_standard_id) == $standard->id ? 'selected' : '' }}>
                                        {{ $standard->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select an exam standard to enable compliance validation</small>
                            @error('exam_standard_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6 mb-3"></div>

                        <!-- Passing Scores Section -->
                        <div id="passingScoresSection" class="col-12" style="display: none;">
                            <div class="mt-4">
                                <div class="alert alert-primary bg-primary-subtle border-0 d-flex align-items-center mb-4">
                                    <div class="bg-primary text-white p-2 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="ti ti-info-circle fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="alert-heading mb-0 fw-bold text-primary">Scoring Configuration</h6>
                                        <p class="mb-0 small text-primary-emphasis">Define the target scores for individual categories and the overall exam.</p>
                                    </div>
                                </div>

                                <div class="row g-4" id="allPassingScoresRow">

                                    <!-- Dynamic category scores will be injected here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const standardSelect = document.getElementById('examStandardSelect');
                        const passingScoresSection = document.getElementById('passingScoresSection');
                        const dynamicContainer = document.getElementById('allPassingScoresRow');

                        function removeDynamicCards() {
                            const dynCards = dynamicContainer.querySelectorAll('.dynamic-card');
                            dynCards.forEach(c => c.remove());
                        }
                        
                        // Existing scores from backend (for Edit)
                        const existingScores = @json(isset($exam) ? $exam->categoryPassingScores->pluck('passing_score', 'exam_standard_category_id') : []);
                        // Old input (for validation errors)
                        const oldScores = @json(old('passing_scores', []));

                        function updatePassingScoresSection() {
                            const selectedOption = standardSelect.options[standardSelect.selectedIndex];
                            
                            if (standardSelect.value) {
                                passingScoresSection.style.display = 'block';
                                removeDynamicCards(); 

                                try {
                                    const categories = JSON.parse(selectedOption.getAttribute('data-categories'));
                                    
                                    if (categories && categories.length > 0) {
                                        categories.forEach(cat => {
                                            // Determine value: Old Input > Existing DB Value > Default 65
                                            let value = 65;
                                            if (oldScores && oldScores[cat.id]) {
                                                value = oldScores[cat.id];
                                            } else if (existingScores && existingScores[cat.id]) {
                                                value = existingScores[cat.id];
                                            }

                                            const col = document.createElement('div');
                                            col.className = 'col-md-4 dynamic-card';
                                            col.innerHTML = `
                                                <div class="p-3 border rounded-3 bg-white h-100 shadow-sm border-light-subtle d-flex flex-column justify-content-between">
                                                    <label class="form-label fw-bold text-muted small text-uppercase">${cat.name} Passing (points)</label>
                                                    <div class="input-group">
                                                        <input type="number" name="passing_scores[${cat.id}]" class="form-control border-end-0" 
                                                            value="${value}" 
                                                            min="0">
                                                        <span class="input-group-text bg-white border-start-0 text-muted">pts</span>
                                                    </div>
                                                </div>
                                            `;
                                            dynamicContainer.appendChild(col);
                                        });
                                    }
                                } catch (e) {
                                    console.error("Error parsing categories data", e);
                                }

                            } else {
                                passingScoresSection.style.display = 'none';
                            }
                        }

                        // Initial state
                        updatePassingScoresSection();

                        // Listen to changes
                        standardSelect.addEventListener('change', updatePassingScoresSection);
                    });
                    </script>

                    <div class="mt-4 pt-4 border-top d-flex justify-content-between align-items-center mb-3 px-3">
                        <a href="{{ route($routePrefix . '.index') }}" class="btn btn-link text-secondary text-decoration-none">
                            <i class="ti ti-arrow-left me-1"></i> Back to List
                        </a>
                        <div class="d-flex gap-2">
                             <a href="{{ route($routePrefix . '.index') }}" class="btn btn-light-secondary px-4">Cancel</a>
                             <button type="submit" class="btn btn-primary px-4 shadow-sm" id="submitBtn">
                                 <i class="ti {{ isset($exam) ? 'ti-device-floppy' : 'ti-plus' }} me-2"></i>
                                 {{ isset($exam) ? 'Save Changes' : 'Create Exam' }}
                             </button>
                        </div>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </div>
</div>

@if(isset($exam))
<!-- Sections Management -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom-0 py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="bg-info-subtle p-2 rounded-3 me-3">
                        <i class="ti ti-layout-grid text-info fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Exam Sections</h6>
                        <small class="text-muted">Manage the logical divisions of this exam</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="toggleDeletedSections()" id="deletedSectionsToggleBtn">
                        <i class="ti ti-trash me-1"></i> Deleted Sections
                    </button>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="openCreateSectionModal()">
                        <i class="ti ti-plus me-1"></i> Add Section
                    </button>
                    <button class="btn btn-sm btn-icon btn-light-secondary rounded-circle" type="button" data-bs-toggle="collapse" data-bs-target="#examSectionsBody" aria-expanded="true">
                        <i class="ti ti-chevron-down"></i>
                    </button>
                </div>
            </div>
            <div class="collapse show" id="examSectionsBody">
                <div class="card-body pt-0">
                    <div id="sectionsLoading" class="text-center py-4" style="display: none;">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    <div id="sectionsList" class="row g-3">
                        <!-- Loaded via AJAX -->
                    </div>

                    <!-- Deleted Sections Panel -->
                    <div id="deletedSectionsPanel" style="display:none;" class="mt-4">
                        <div class="border-top pt-3">
                            <div class="d-flex align-items-center mb-3">
                                <i class="ti ti-trash text-danger me-2 fs-5"></i>
                                <h6 class="mb-0 fw-bold text-danger">Deleted Sections</h6>
                                <span class="badge bg-danger ms-2" id="deletedSectionsCount">0</span>
                            </div>
                            <div id="deletedSectionsLoading" class="text-center py-3" style="display:none;">
                                <div class="spinner-border spinner-border-sm text-danger" role="status"></div>
                            </div>
                            <div id="deletedSectionsList" class="row g-2">
                                <!-- Deleted sections loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Exam Questions (Visualized by Case Study/Visit) -->
<div class="row mb-4" id="examContentManagementSection" style="display: none;">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom-0 py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="bg-success-subtle p-2 rounded-3 me-3">
                        <i class="ti ti-clipboard-list text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Section Content: <span id="activeSectionTitle" class="text-primary">-</span></h6>
                        <small class="text-muted">Manage Case Studies, Visits, and Questions</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3" onclick="openCreateCaseStudyModal()">
                        <i class="ti ti-plus me-1"></i> Add Case Study
                    </button>
                    <button class="btn btn-sm btn-icon btn-light-secondary rounded-circle" type="button" data-bs-toggle="collapse" data-bs-target="#examContentBody" aria-expanded="true">
                        <i class="ti ti-chevron-down"></i>
                    </button>
                </div>
            </div>
            <div class="collapse show" id="examContentBody">
                <div class="card-body pt-0">
                    <div id="examContentLoading" class="text-center py-4" style="display: none;">
                        <div class="spinner-border text-success" role="status"></div>
                    </div>
                    <div id="examContentList">
                        <!-- Loaded via AJAX after selecting a section -->
                        <div class="text-center py-5 text-muted">
                            <i class="ti ti-arrow-up fs-1 mb-2 d-block"></i>
                            <p>Please select a section above to manage its content.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if(isset($exam))
<script>
let currentExamId = "{{ $exam->id }}";
let activeSectionId = null;
let isExamActive = {{ $exam->is_active ? 'true' : 'false' }};
const rolePrefix = "{{ $baseUrl }}";

function showLockedModal(action = 'modify') {
    Swal.fire({
        title: 'Exam is Published!',
        text: `This exam is currently published and locked. You must unpublish the exam before you can ${action} it.`,
        icon: 'lock',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        confirmButtonText: '<i class="ti ti-eye-off me-1"></i> Unpublish Now',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show a simple loading state and submit directly
            Swal.fire({
                title: 'Processing...',
                text: 'Unpublishing exam, please wait.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            document.getElementById('status-toggle-form').submit();
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    loadExamSections().then(() => {
        // Check for section_id in URL params
        const urlParams = new URLSearchParams(window.location.search);
        const sectionId = urlParams.get('section_id');
        if (sectionId) {
            // Find the section element and trigger selection
            const sectionCard = document.querySelector(`.section-card[data-section-id="${sectionId}"]`);
            if (sectionCard) {
                const title = sectionCard.getAttribute('data-section-title');
                selectSection(sectionId, title);
            }
        }
    });
    
    // Event delegation for sections - REMOVED since we use inline onclick now to avoid stopPropagation issues
    // document.getElementById('sectionsList').addEventListener('click', function(e) { ... });

    // Event delegation for Case Studies and Visits
    document.getElementById('examContentList').addEventListener('click', function(e) {
        const editCs = e.target.closest('.btn-edit-cs');
        const deleteCs = e.target.closest('.btn-delete-cs');
        const editVisit = e.target.closest('.btn-edit-visit');
        const deleteVisit = e.target.closest('.btn-delete-visit');
        const editQ = e.target.closest('.btn-edit-q');
        const deleteQ = e.target.closest('.btn-delete-q');

        if (editCs) editCaseStudy(editCs.getAttribute('data-id'));
        if (deleteCs) deleteCaseStudy(deleteCs.getAttribute('data-id'));
        if (editVisit) editVisit(editVisit.getAttribute('data-id'));
        if (deleteVisit) deleteVisit(deleteVisit.getAttribute('data-id'));
        if (editQ) editQuestion(editQ.getAttribute('data-id'));
        if (deleteQ) deleteQuestion(deleteQ.getAttribute('data-id'));
    });
});

function handleSectionCardClick(card) {
    const id = card.getAttribute('data-section-id');
    const title = card.getAttribute('data-section-title');
    selectSection(id, title);
}

async function loadExamSections() {
    const loading = document.getElementById('sectionsLoading');
    const container = document.getElementById('sectionsList');
    
    loading.style.display = 'block';
    container.innerHTML = '';

    try {
        const response = await fetch(`/${rolePrefix}/api/sections/${currentExamId}`);
        const data = await response.json();
        
        loading.style.display = 'none';
        
                if (data.success && data.sections.length > 0) {
            data.sections.forEach(section => {
                const isActive = activeSectionId == section.id;
                const col = document.createElement('div');
                col.className = 'col-md-3 col-sm-6 mb-3';
                col.innerHTML = `
                    <div class="card section-card h-100 animate-in ${isActive ? 'active-section' : ''}" 
                         data-section-id="${section.id}" 
                         data-section-title="${section.title.replace(/"/g, '&quot;')}"
                         onclick="handleSectionCardClick(this)">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="icon-square">
                                    <i class="ti ti-list-details fs-5"></i>
                                </div>
                                <div class="dropdown" onclick="event.stopPropagation()">
                                    <button class="btn btn-link text-muted p-0 border-0" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ti ti-dots-vertical fs-5"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                        <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="editSection('${section.id}')"><i class="ti ti-edit text-primary"></i> Edit</a></li>
                                        <li><a class="dropdown-item py-2 text-danger" href="javascript:void(0)" onclick="deleteSection('${section.id}')"><i class="ti ti-trash"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark text-truncate">${section.title}</h6>
                            <div class="bg-light d-inline-block px-2 py-0 rounded text-muted" style="font-size: 10px;">SEC-${section.id}</div>
                        </div>
                    </div>
                `;
                container.appendChild(col);
            });
        } else {
            container.innerHTML = `
                <div class="col-12 text-center py-5 bg-light-subtle rounded-3 border-dashed border-2">
                    <img src="https://illustrations.popsy.co/gray/work-from-home.svg" alt="No Sections" style="height: 120px;" class="mb-3">
                    <h6 class="fw-bold">No Sections Found</h6>
                    <p class="text-muted small">Start by adding your first section to organize case studies.</p>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-4" onclick="openCreateSectionModal()">
                        <i class="ti ti-plus me-1"></i> Add My First Section
                    </button>
                </div>
            `;
        }
    } catch (err) {
        loading.style.display = 'none';
        console.error('Error loading sections:', err);
    }
}

function selectSection(id, title) {
    activeSectionId = id;
    document.getElementById('activeSectionTitle').textContent = title;
    const contentSection = document.getElementById('examContentManagementSection');
    contentSection.style.display = 'block';
    
    // Smooth scroll to content
    setTimeout(() => {
        contentSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);

    // Refresh sections to update active state
    loadExamSections();
    loadSectionContent(id);
}

async function loadSectionContent(sectionId) {
    const loading = document.getElementById('examContentLoading');
    const container = document.getElementById('examContentList');
    
    loading.style.display = 'block';
    container.innerHTML = '';

    try {
        // We'll need a new API endpoint for this or reuse existing ones
        // For now, let's assume we have one or I'll create it
        const response = await fetch(`/${rolePrefix}/api/sections/${sectionId}/content`);
        const data = await response.json();
        
        loading.style.display = 'none';
        
        if (data.success) {
            renderSectionContent(data.case_studies);
        }
    } catch (err) {
        loading.style.display = 'none';
        console.error('Error loading section content:', err);
    }
}

function renderSectionContent(caseStudies) {
    const container = document.getElementById('examContentList');
    if (!caseStudies || caseStudies.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5 border rounded-3 bg-light-subtle">
                <i class="ti ti-notes fs-1 text-muted mb-3 d-block"></i>
                <h6 class="fw-bold">No Case Studies Found</h6>
                <p class="text-muted small">This section is currently empty.</p>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4" onclick="openCreateCaseStudyModal()">
                    <i class="ti ti-plus me-1"></i> Add Case Study
                </button>
            </div>
        `;
        return;
    }

    caseStudies.forEach(cs => {
        const csDiv = document.createElement('div');
        csDiv.className = 'card case-study-card mb-4 animate-in';
        csDiv.innerHTML = `
            <div class="card-header bg-white border-bottom pt-3 pb-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="bg-primary-subtle p-2 rounded-3 me-3">
                        <i class="ti ti-folders text-primary fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">${cs.title}</h6>
                        <small class="text-muted" style="font-size: 11px;">Case ID: CS-${cs.id}</small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-icon btn-light-secondary rounded-circle" onclick="editCaseStudy(${cs.id})"><i class="ti ti-edit"></i></button>
                    <a href="/${rolePrefix}/case-studies-bank/${cs.id}" class="btn btn-sm btn-icon btn-light-info rounded-circle" title="View Case Study"><i class="ti ti-eye"></i></a>
                    <button class="btn btn-sm btn-icon btn-light-danger rounded-circle" onclick="deleteCaseStudy(${cs.id})"><i class="ti ti-trash"></i></button>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="visits-container vstack gap-3" id="visits-for-cs-${cs.id}">
                    <!-- Visits rendered here -->
                </div>
            </div>
        `;
        container.appendChild(csDiv);
        
        const visitsContainer = csDiv.querySelector('.visits-container');
        if (cs.visits && cs.visits.length > 0) {
            cs.visits.forEach(visit => {
                const visitDiv = document.createElement('div');
                visitDiv.className = 'visit-block animate-in';
                visitDiv.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-stethoscope text-info me-2 fs-5"></i>
                            <span class="fw-bold text-dark text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">${visit.title}</span>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-xs btn-outline-secondary" onclick="editVisit(${visit.id})">Edit</button>
                            <button class="btn btn-xs btn-primary px-3" onclick="addQuestion(${visit.id})"><i class="ti ti-plus me-1"></i> Question</button>
                            <button class="btn btn-xs btn-outline-danger" onclick="deleteVisit(${visit.id})">Delete</button>
                        </div>
                    </div>
                    <div class="questions-container vstack gap-2" id="questions-for-visit-${visit.id}">
                        <!-- Questions rendered here -->
                    </div>
                `;
                visitsContainer.appendChild(visitDiv);
                
                const questionsContainer = visitDiv.querySelector('.questions-container');
                if (visit.questions && visit.questions.length > 0) {
                    visit.questions.forEach(q => {
                        const qDiv = document.createElement('div');
                        qDiv.className = 'question-item d-flex justify-content-between align-items-center animate-in';
                        qDiv.innerHTML = `
                            <div class="pe-3 overflow-hidden">
                                <p class="mb-1 text-dark fw-medium text-truncate" style="font-size: 13px;">${q.question_text}</p>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-primary fw-bold" style="font-size: 11px;">${q.max_question_points} Pts</span>
                                    <span class="text-muted" style="font-size: 10px;">•</span>
                                    <span class="text-muted fw-normal" style="font-size: 11px;">${q.question_type.toUpperCase()}</span>
                                    <span class="text-muted" style="font-size: 10px;">•</span>
                                    <span class="text-secondary" style="font-size: 11px;">${q.tags_count} Tags</span>
                                </div>
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <button class="btn btn-xs btn-icon btn-light-primary" onclick="editQuestion(${q.id})"><i class="ti ti-pencil"></i></button>
                                <button class="btn btn-xs btn-icon btn-light-danger" onclick="deleteQuestion(${q.id})"><i class="ti ti-trash"></i></button>
                            </div>
                        `;
                        questionsContainer.appendChild(qDiv);
                    });
                } else {
                    questionsContainer.innerHTML = `<div class="text-center py-3 text-muted border border-dashed rounded-3 small">No questions.</div>`;
                }
            });
        }
    });
}

function confirmStatusChange() {
    const isActive = {{ $exam->is_active ? 'true' : 'false' }};
    const actionText = isActive ? 'Unpublish' : 'Publish';
    const btnColor = isActive ? '#d33' : '#28a745';

    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to ${actionText} this exam?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: btnColor,
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Yes, ${actionText} it!`
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('status-toggle-form').submit();
        }
    });
}

// Section CRUD
function openCreateSectionModal() {
    if (isExamActive) {
        showLockedModal('add sections');
        return;
    }
    window.location.href = `/${rolePrefix}/sections/create?exam_id=${currentExamId}&return_url=${getReturnUrl()}`;
}

function editSection(id) {
    if (isExamActive) {
        showLockedModal('edit sections');
        return;
    }
    window.location.href = `/${rolePrefix}/sections/${id}/edit?return_url=${getReturnUrl()}`;
}

async function deleteSection(id) {
    if (isExamActive) {
        showLockedModal('delete sections');
        return;
    }
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "Section and its contents will be hidden.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(`/${rolePrefix}/api/sections-ajax/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            if (response.ok) {
                loadExamSections();
                if (activeSectionId == id) {
                    document.getElementById('examContentManagementSection').style.display = 'none';
                    activeSectionId = null;
                }
                // If deleted panel is open, refresh it too
                const panel = document.getElementById('deletedSectionsPanel');
                if (panel && panel.style.display !== 'none') {
                    loadDeletedSections();
                }
                Swal.fire('Deleted!', 'Section has been deleted.', 'success');
            }
        } catch (err) {
            console.error(err);
        }
    }
}

let deletedPanelOpen = false;

function toggleDeletedSections() {
    const panel = document.getElementById('deletedSectionsPanel');
    const btn = document.getElementById('deletedSectionsToggleBtn');
    deletedPanelOpen = !deletedPanelOpen;
    if (deletedPanelOpen) {
        panel.style.display = 'block';
        btn.classList.remove('btn-outline-danger');
        btn.classList.add('btn-danger');
        loadDeletedSections();
    } else {
        panel.style.display = 'none';
        btn.classList.remove('btn-danger');
        btn.classList.add('btn-outline-danger');
    }
}

async function loadDeletedSections() {
    const loading = document.getElementById('deletedSectionsLoading');
    const container = document.getElementById('deletedSectionsList');
    const countBadge = document.getElementById('deletedSectionsCount');

    loading.style.display = 'block';
    container.innerHTML = '';

    try {
        const response = await fetch(`/${rolePrefix}/api/sections/${currentExamId}/deleted`);
        const data = await response.json();
        loading.style.display = 'none';

        if (data.success && data.sections.length > 0) {
            countBadge.textContent = data.sections.length;
            data.sections.forEach(section => {
                const col = document.createElement('div');
                col.className = 'col-md-4 col-sm-6';
                col.innerHTML = `
                    <div class="d-flex align-items-center justify-content-between p-3 border border-danger-subtle rounded-3 bg-danger-subtle">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:32px;height:32px;background:#fee2e2;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                <i class="ti ti-list-details text-danger fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark" style="font-size:13px;">${section.title}</div>
                                <div class="text-muted" style="font-size:10px;">SEC-${section.id} &bull; Deleted ${section.deleted_at}</div>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-success rounded-pill px-3" onclick="restoreSection(${section.id})">
                            <i class="ti ti-refresh me-1"></i> Restore
                        </button>
                    </div>
                `;
                container.appendChild(col);
            });
        } else {
            countBadge.textContent = '0';
            container.innerHTML = `
                <div class="col-12 text-center py-4 text-muted">
                    <i class="ti ti-check-circle fs-1 text-success mb-2 d-block"></i>
                    <p class="mb-0 small">No deleted sections found.</p>
                </div>
            `;
        }
    } catch (err) {
        loading.style.display = 'none';
        console.error('Error loading deleted sections:', err);
    }
}

async function restoreSection(id) {
    if (isExamActive) {
        showLockedModal('restore sections');
        return;
    }
    const result = await Swal.fire({
        title: 'Restore Section?',
        text: 'This section will be restored and visible again.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Yes, restore it!'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(`/${rolePrefix}/api/sections-ajax/${id}/restore`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (response.ok && data.success) {
                loadExamSections();
                loadDeletedSections();
                Swal.fire('Restored!', 'Section has been restored.', 'success');
            } else {
                Swal.fire('Error', data.message || 'Could not restore section.', 'error');
            }
        } catch (err) {
            console.error(err);
            Swal.fire('Error', 'Connection error. Please try again.', 'error');
        }
    }
}

// Case Study CRUD
function openCreateCaseStudyModal() {
    if (isExamActive) {
        showLockedModal('add case studies');
        return;
    }
    if (!activeSectionId) {
        Swal.fire('Select Section', 'Please select a section before adding a case study.', 'warning');
        return;
    }
    window.location.href = `/${rolePrefix}/case-studies-bank/create?exam_id=${currentExamId}&section_id=${activeSectionId}&return_url=${getReturnUrl()}`;
}

function editCaseStudy(id) {
    if (isExamActive) {
        showLockedModal('edit case studies');
        return;
    }
    window.location.href = `/${rolePrefix}/case-studies-bank/${id}/edit?return_url=${getReturnUrl()}`;
}

async function deleteCaseStudy(id) {
    if (isExamActive) {
        showLockedModal('delete case studies');
        return;
    }
    const result = await Swal.fire({
        title: 'Delete Case Study?',
        text: "This will remove all visits and questions inside.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(`/${rolePrefix}/api/case-studies/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Case Study has been deleted.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                const errorResult = await response.json();
                Swal.fire('Error', errorResult.message || 'Failed to delete case study', 'error');
            }
        } catch (err) {
            console.error('Delete case study error:', err);
            Swal.fire('Error', 'Connection error. Please try again.', 'error');
        }
    }
}

// Visit CRUD - handled in the second script block below

async function deleteVisit(id) {
    if (isExamActive) {
        showLockedModal('delete visits');
        return;
    }
    const result = await Swal.fire({
        title: 'Delete Visit?',
        text: "This will remove all questions inside.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(`/${rolePrefix}/api/visits/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            if (response.ok) {
                loadSectionContent(activeSectionId);
                Swal.fire('Deleted!', 'Visit has been deleted.', 'success');
            }
        } catch (err) {
            console.error(err);
        }
    }
}

</script>
@endif

@if(isset($exam) && $exam->is_active == 1)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const forceEditCheckbox = document.getElementById('forceEdit');
    // Select all inputs, selects, and textareas within the form, except the forceEdit checkbox itself
    const formInputs = document.querySelectorAll('form input:not(#forceEdit), form select, form textarea, form button:not([type="submit"]):not([data-bs-toggle="collapse"])');
    const submitBtn = document.getElementById('submitBtn');

    function updateFieldStates() {
        const isChecked = forceEditCheckbox.checked;
        
        formInputs.forEach(input => {
            // Skip header buttons, the back button, the status toggle button, and EXAM CODE
            if (input.closest('.page-header') || input.closest('#status-toggle-form') || input.id === 'addNewTypeBtn' || input.name === 'exam_code') {
                return;
            }

            input.disabled = !isChecked;
            if (isChecked) {
                input.style.opacity = '1';
                input.style.pointerEvents = 'auto';
                if (input.tagName === 'INPUT' || input.tagName === 'SELECT' || input.tagName === 'TEXTAREA') {
                    input.style.backgroundColor = '';
                }
            } else {
                input.style.opacity = '0.5';
                input.style.pointerEvents = 'none';
                if (input.tagName === 'INPUT' || input.tagName === 'SELECT' || input.tagName === 'TEXTAREA') {
                    input.style.backgroundColor = '#f0f0f0';
                }
            }
        });

        // Special handling for the Add New button to ensure it looks disabled/enabled
        const addNewBtn = document.getElementById('addNewTypeBtn');
        if (addNewBtn) {
            addNewBtn.disabled = !isChecked;
            addNewBtn.style.opacity = isChecked ? '1' : '0.5';
            addNewBtn.style.pointerEvents = isChecked ? 'auto' : 'none';
        }

        // Handle submit button
        submitBtn.disabled = !isChecked;
        if (!isChecked) {
            submitBtn.style.opacity = '0.5';
            submitBtn.style.pointerEvents = 'none';
        } else {
            submitBtn.style.opacity = '1';
            submitBtn.style.pointerEvents = 'auto';
        }
    }

    // Initial state
    updateFieldStates();

    // Listen to checkbox changes
    forceEditCheckbox.addEventListener('change', updateFieldStates);
});
</script>
@endif
@if(isset($exam))
<!-- Modals for AJAX operations -->
<div class="modal fade" id="visitModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="visitModalTitle">Add Visit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="visitForm">
                <div class="modal-body p-4">
                    <input type="hidden" name="case_study_id" id="modal_visit_cs_id">
                    <input type="hidden" name="visit_id" id="modal_visit_id">
                    <div class="mb-3">
                        <label class="form-label">Visit Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="modal_visit_title" class="form-control" placeholder="e.g., Physical Examination" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" id="modal_visit_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveVisitBtn">Save Visit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>

function addQuestion(visitId) {
    if (isExamActive) {
        showLockedModal('add questions');
        return;
    }
    window.location.href = `/${rolePrefix}/questions/create?visit_id=${visitId}&return_url=${getReturnUrl()}`;
}

function addModalOption(text = '', isCorrect = false) {
    const idx = qOptionsContainer.children.length;
    const type = document.getElementById('modal_q_type').value;
    const div = document.createElement('div');
    div.className = 'input-group animate-in';
    div.innerHTML = `
        <div class="input-group-text bg-white border-end-0">
            <input class="form-check-input mt-0" type="${type == 'single' ? 'radio' : 'checkbox'}" name="is_correct_toggle" ${isCorrect ? 'checked' : ''} value="${idx}">
        </div>
        <input type="text" class="form-control option-text border-start-0" placeholder="Option Text" value="${text}" required>
        <button class="btn btn-outline-danger" type="button" onclick="this.parentElement.remove()"><i class="ti ti-trash"></i></button>
    `;
    qOptionsContainer.appendChild(div);
}

function addTagRow(catId = '', areaId = '') {
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 tag-row animate-in';
    row.innerHTML = `
        <div class="col-md-5">
            <select class="form-select form-select-sm score-cat-select" onchange="loadContentAreasForTag(this)">
                <option value="">Select Category</option>
                @if(isset($exam) && $exam->examStandard)
                    @foreach($exam->examStandard->categories as $cat)
                        <option value="{{ $cat->id }}" ${catId == '{{ $cat->id }}' ? 'selected' : ''}>{{ $cat->name }}</option>
                    @endforeach
                @endif
            </select>
        </div>
        <div class="col-md-5">
            <select class="form-select form-select-sm area-select" ${!catId ? 'disabled' : ''}>
                <option value="">Select Area</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-sm btn-light-danger w-100" onclick="this.closest('.tag-row').remove()"><i class="ti ti-trash"></i></button>
        </div>
    `;
    qTagsContainer.appendChild(row);
    
    if (catId) {
        const select = row.querySelector('.score-cat-select');
        loadContentAreasForTag(select, areaId);
    }
}

function toggleOptionInputs() {
    const type = document.getElementById('modal_q_type').value;
    qOptionsContainer.querySelectorAll('.form-check-input').forEach((input, i) => {
        input.type = (type == 'single' ? 'radio' : 'checkbox');
    });
}

const standardData = @json(isset($exam) && $exam->examStandard ? $exam->examStandard->categories->map(fn($c) => ['id' => $c->id, 'areas' => $c->contentAreas->map(fn($a) => ['id' => $a->id, 'name' => $a->name])]) : []);

function loadContentAreasForTag(select, selectedAreaId = '') {
    const catId = select.value;
    const areaSelect = select.closest('.tag-row').querySelector('.area-select');
    areaSelect.innerHTML = '<option value="">Select Area</option>';
    areaSelect.disabled = !catId;
    
    if (catId) {
        const cat = standardData.find(c => c.id == catId);
        if (cat) {
            cat.areas.forEach(a => {
                const opt = document.createElement('option');
                opt.value = a.id;
                opt.textContent = a.name;
                if (selectedAreaId && selectedAreaId == a.id) opt.selected = true;
                areaSelect.appendChild(opt);
            });
        }
    }
}

const visitModal = new bootstrap.Modal(document.getElementById('visitModal'));
const visitForm = document.getElementById('visitForm');

function addVisit(csId) {
    if (isExamActive) {
        showLockedModal('add visits');
        return;
    }
    visitForm.reset();
    document.getElementById('modal_visit_cs_id').value = csId;
    document.getElementById('modal_visit_id').value = '';
    document.getElementById('visitModalTitle').textContent = 'Add Visit';
    visitModal.show();
}

async function editVisit(id) {
    if (isExamActive) {
        showLockedModal('edit visits');
        return;
    }
    try {
        const response = await fetch(`/${rolePrefix}/api/visits-detail/${id}`);
        const data = await response.json();
        if (data.success) {
            const v = data.visit;
            document.getElementById('modal_visit_id').value = v.id;
            document.getElementById('modal_visit_cs_id').value = v.case_study_id;
            document.getElementById('modal_visit_title').value = v.title;
            document.getElementById('modal_visit_description').value = v.description || '';
            document.getElementById('visitModalTitle').textContent = 'Edit Visit';
            visitModal.show();
        }
    } catch (err) {
        console.error(err);
    }
}

visitForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    const saveBtn = document.getElementById('saveVisitBtn');
    const id = document.getElementById('modal_visit_id').value;
    
    const formData = new FormData(visitForm);
    const data = Object.fromEntries(formData.entries());
    data._token = document.querySelector('meta[name="csrf-token"]').content;

    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

    const url = id ? `/${rolePrefix}/api/visits/${id}` : `/${rolePrefix}/api/visits`;
    const method = id ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': data._token,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        saveBtn.disabled = false;
        saveBtn.innerHTML = 'Save Visit';

        if (response.ok) {
            visitModal.hide();
            loadSectionContent(activeSectionId);
            Swal.fire('Success', id ? 'Visit updated' : 'Visit created', 'success');
        } else {
            Swal.fire('Error', result.message || 'Something went wrong', 'error');
        }
    } catch (err) {
        saveBtn.disabled = false;
        saveBtn.innerHTML = 'Save Visit';
        console.error(err);
    }
});

// Helper to construct return URL with current section state
function getReturnUrl() {
    const url = new URL(window.location.href);
    if (activeSectionId) {
        url.searchParams.set('section_id', activeSectionId);
    }
    return encodeURIComponent(url.toString());
}

function editQuestion(id) {
    if (isExamActive) {
        showLockedModal('edit questions');
        return;
    }
    window.location.href = `/${rolePrefix}/questions/${id}/edit?return_url=${getReturnUrl()}`;
}

async function deleteQuestion(id) {
    if (isExamActive) {
        showLockedModal('delete questions');
        return;
    }
    const result = await Swal.fire({
        title: 'Delete Question?',
        text: "This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(`/${rolePrefix}/api/questions/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            if (response.ok) {
                loadSectionContent(activeSectionId);
                Swal.fire('Deleted!', 'Question has been deleted.', 'success');
            }
        } catch (err) {
            console.error(err);
        }
    }
}
</script>
@endif
@endsection
