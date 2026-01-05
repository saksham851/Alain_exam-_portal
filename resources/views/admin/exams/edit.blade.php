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
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('admin.exams.index') }}">Exams</a></li>
          <li class="breadcrumb-item" aria-current="page">{{ isset($exam) ? 'Edit' : 'Create' }}</li>
        </ul>
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
                        <form action="{{ route('admin.exams.toggle-status', $exam->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to change the status of this exam?');">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-sm {{ $exam->is_active ? 'btn-danger' : 'btn-success' }}">
                                <i class="ti {{ $exam->is_active ? 'ti-lock' : 'ti-lock-open' }} me-1"></i>
                                {{ $exam->is_active ? 'Deactivate / Unlock Exam' : 'Activate / Lock Exam' }}
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
                            <strong>This Exam is Active/Locked</strong>
                            <p class="mb-0 mt-2">This exam is currently active and locked for editing. To make changes, please check the "Force Edit" checkbox below to confirm you want to edit this active exam.</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="force_edit" id="forceEdit" value="1">
                            <label class="form-check-label" for="forceEdit">
                                I understand this exam is active. <strong>Force Edit this exam</strong>
                            </label>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Exam Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', optional($exam)->name) }}" required>
                            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Exam Code <span class="text-danger">*</span></label>
                            <input type="text" name="exam_code" class="form-control" value="{{ old('exam_code', optional($exam)->exam_code) }}" required>
                            @error('exam_code') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Exam Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', optional($exam)->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }} ({{ $category->certification_type }})
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Duration (Minutes) <span class="text-danger">*</span></label>
                            <input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', optional($exam)->duration_minutes ?? 180) }}" required min="1">
                            @error('duration_minutes') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

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
