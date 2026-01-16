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

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Exam Details</h5>
                @if(isset($exam))
                    <div class="d-flex gap-2">
                    <div class="d-flex gap-2">
                        <form action="{{ route('admin.exams.toggle-status', $exam->id) }}" method="POST" id="status-toggle-form">
                            @csrf
                            @method('PUT')
                            <button type="button" class="btn btn-sm {{ $exam->is_active ? 'btn-danger' : 'btn-success' }}" onclick="confirmStatusChange()">
                                <i class="ti {{ $exam->is_active ? 'ti-lock' : 'ti-lock-open' }} me-1"></i>
                                {{ $exam->is_active ? 'Unpublish Exam' : 'Publish Exam' }}
                            </button>
                        </form>
                    </div>

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
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            {{ isset($exam) ? 'Update Exam' : 'Create Exam' }}
                        </button>
                        <a href="{{ route('admin.exams.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
