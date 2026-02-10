@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">{{ isset($exam) ? 'Edit Exam' : 'Create Exam' }}</h5>
        </div>

      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

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
                <button class="btn btn-sm btn-icon btn-light-secondary rounded-circle" type="button" data-bs-toggle="collapse" data-bs-target="#progressDetails" aria-expanded="true">
                    <i class="ti ti-chevron-down"></i>
                </button>
            </div>
            <div class="card-body pt-0">
                @php
                    $validation = $exam->validateStandardCompliance();
                    $totalQuestions = $validation['total_questions'];
                    $expectedTotal = $exam->total_questions ?? 0;
                    $progressPercent = $expectedTotal > 0 ? round(($totalQuestions / $expectedTotal) * 100) : 0;
                    $isValidStatus = $validation['valid'];
                @endphp

                <!-- Overall Progress -->
                <div class="bg-light-subtle rounded-3 p-3 mb-4 border border-dashed border-primary-subtle">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold text-dark">Overall Exam Capacity</span>
                                <span class="badge {{ $isValidStatus ? 'bg-success' : 'bg-warning' }} rounded-pill px-3">
                                    {{ $totalQuestions }} / {{ $expectedTotal }} Questions ({{ $progressPercent }}%)
                                </span>
                            </div>
                            <div class="progress rounded-pill overflow-hidden shadow-none mb-2" style="height: 12px; background: rgba(0,0,0,0.05);">
                                <div class="progress-bar progress-bar-striped progress-bar-animated {{ $isValidStatus ? 'bg-success' : 'bg-warning' }}" 
                                     role="progressbar" 
                                     style="width: {{ min($progressPercent, 100) }}%;" 
                                     aria-valuenow="{{ $progressPercent }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                @if(!$isValidStatus)
                                    <i class="ti ti-alert-triangle text-warning me-2 fs-5"></i>
                                    <span class="small text-warning">
                                        {{ $expectedTotal - $totalQuestions > 0 ? 'Action required: Add ' . ($expectedTotal - $totalQuestions) . ' more questions' : 'Action required: Remove ' . ($totalQuestions - $expectedTotal) . ' questions' }}
                                    </span>
                                @else
                                    <i class="ti ti-circle-check text-success me-2 fs-5"></i>
                                    <span class="small text-success fw-medium">All requirements met! This exam is ready to be published.</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('admin.questions.index', ['exam_id' => $exam->id]) }}" class="btn btn-sm btn-primary px-3 rounded-pill">
                                <i class="ti ti-settings me-1"></i> Manage Questions
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Detailed Breakdown (Collapsible) -->
                <div class="collapse show" id="progressDetails">
                    <div class="row">
                        @php
                            $groupedAreas = collect($validation['content_areas'])->groupBy('category');
                        @endphp

                        @foreach($groupedAreas as $categoryName => $areas)
                        <div class="col-md-6 mb-4">
                            <div class="h-100 p-3 bg-white border rounded-3 border-light-subtle">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="ti ti-category-2 text-primary me-2 f-18"></i>
                                    <h6 class="mb-0 fw-bold">{{ $categoryName }}</h6>
                                </div>
                                <div class="vstack gap-3">
                                    @foreach($areas as $area)
                                    <div class="content-area-item">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="small text-secondary fw-semibold">{{ $area['name'] }}</span>
                                            <span class="small {{ $area['valid'] ? 'text-success' : 'text-danger' }} fw-bold">
                                                {{ $area['current'] }} / {{ $area['required'] }}
                                            </span>
                                        </div>
                                        <div class="progress rounded-pill" style="height: 6px; background: rgba(0,0,0,0.03);">
                                            <div class="progress-bar {{ $area['valid'] ? 'bg-success' : 'bg-warning' }}" 
                                                 style="width: {{ $area['required'] > 0 ? min(($area['current'] / $area['required']) * 100, 100) : 0 }}%;"></div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                            <small class="text-muted opacity-75" style="font-size: 10px;">{{ $area['percentage'] }}% requirement</small>
                                            @if(!$area['valid'])
                                                <small class="text-warning fw-medium" style="font-size: 10px;">
                                                    <i class="ti ti-arrow-move-right"></i> {{ $area['required'] - $area['current'] > 0 ? 'Need ' . ($area['required'] - $area['current']) : 'Remove ' . ($area['current'] - $area['required']) }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
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
@endif

<div class="row">
    <div class="col-md-12">
        <div class="card">
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
                @if(isset($exam))
                    <div class="d-flex gap-2">
                        <form action="{{ route('admin.exams.toggle-status', $exam->id) }}" method="POST" id="status-toggle-form">
                            @csrf
                            @method('PUT')
                            <button type="button" class="btn btn-sm {{ $exam->is_active ? 'btn-light-danger' : 'btn-light-success' }} px-3 rounded-pill" onclick="confirmStatusChange()">
                                <i class="ti {{ $exam->is_active ? 'ti-lock' : 'ti-lock-open' }} me-1"></i>
                                {{ $exam->is_active ? 'Unpublish' : 'Publish' }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>
            <div class="card-body">
                <form action="{{ isset($exam) ? route('admin.exams.update', $exam->id) : route('admin.exams.store') }}" method="POST">
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
                            <input type="text" name="name" class="form-control" value="{{ old('name', optional($exam)->name) }}" required pattern="^[a-zA-Z0-9\s]+$" title="Only letters, numbers, and spaces are allowed.">
                            @error('name') 
                                <small class="text-danger">{{ $message }}</small> 
                            @else
                                <small class="text-muted">Only letters, numbers, and spaces are allowed.</small>
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
                                    <option value="{{ $category->id }}" {{ old('category_id', optional($exam)->category_id) == $category->id ? 'selected' : '' }}>
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
                                <input type="text" name="new_certification_type" id="newCertificationTypeInput" class="form-control" placeholder="Enter new certification type" style="display: none;" pattern="^[a-zA-Z0-9\s]+$" title="Only letters, numbers, and spaces are allowed.">
                                <button type="button" id="addNewTypeBtn" class="btn btn-primary" style="white-space: nowrap;">
                                    <i class="ti ti-plus me-1"></i> Add New
                                </button>
                            </div>
                            @error('certification_type') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                            @error('new_certification_type') 
                                <small class="text-danger d-block mt-1">{{ $message }}</small> 
                            @else
                                <small class="text-muted d-none mt-1" id="certTypeHelper">Only letters, numbers, and spaces are allowed.</small>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3" style="display: none;">
                            <label class="form-label">Total Questions <span class="text-danger">*</span></label>
                            <input type="number" name="total_questions" class="form-control" value="{{ old('total_questions', optional($exam)->total_questions ?? 0) }}" min="0">
                            <small class="text-muted">Required number of questions for this exam.</small>
                            @error('total_questions') <small class="text-danger">{{ $message }}</small> @enderror
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
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded-3 bg-white h-100 shadow-sm border-light-subtle">
                                            <label class="form-label fw-bold text-muted small text-uppercase">Overall Passing Score (%)</label>
                                            <div class="input-group">
                                                <input type="number" name="passing_score_overall" class="form-control border-end-0" 
                                                       value="{{ old('passing_score_overall', optional($exam)->passing_score_overall ?? 65) }}" 
                                                       min="0" max="100">
                                                <span class="input-group-text bg-white border-start-0 text-muted">%</span>
                                            </div>
                                            @error('passing_score_overall') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                    </div>
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
                                                <div class="p-3 border rounded-3 bg-white h-100 shadow-sm border-light-subtle">
                                                    <label class="form-label fw-bold text-muted small text-uppercase">${cat.name} Passing (%)</label>
                                                    <div class="input-group">
                                                        <input type="number" name="passing_scores[${cat.id}]" class="form-control border-end-0" 
                                                            value="${value}" 
                                                            min="0" max="100">
                                                        <span class="input-group-text bg-white border-start-0 text-muted">%</span>
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
                    </div>

                    <div class="mt-4 pt-4 border-top d-flex justify-content-between align-items-center mb-3 px-3">
                        <a href="{{ route('admin.exams.index') }}" class="btn btn-link text-secondary text-decoration-none">
                            <i class="ti ti-arrow-left me-1"></i> Back to List
                        </a>
                        <div class="d-flex gap-2">
                             <a href="{{ route('admin.exams.index') }}" class="btn btn-light-secondary px-4">Cancel</a>
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

@if(isset($exam))
<script>
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
</script>
@endif

@if(isset($exam) && $exam->is_active == 1)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const forceEditCheckbox = document.getElementById('forceEdit');
    const formInputs = document.querySelectorAll('input[name="name"], input[name="exam_code"], select[name="category_id"], input[name="duration_minutes"], textarea[name="description"]');
    const submitBtn = document.getElementById('submitBtn');

    function updateFieldStates() {
        const isChecked = forceEditCheckbox.checked;
        
        formInputs.forEach(input => {
            input.disabled = !isChecked;
            if (isChecked) {
                input.style.opacity = '1';
                input.style.pointerEvents = 'auto';
            } else {
                input.style.opacity = '0.5';
                input.style.pointerEvents = 'none';
                input.style.backgroundColor = '#f0f0f0';
            }
        });

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
@endsection
