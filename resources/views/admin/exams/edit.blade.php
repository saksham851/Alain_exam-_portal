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
            <div class="card-header">
                <h5>Exam Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ isset($exam) ? route('admin.exams.update', $exam->id) : route('admin.exams.store') }}" method="POST">
                    @csrf
                    @if(isset($exam))
                        @method('PUT')
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
                        <button type="submit" class="btn btn-primary">
                            {{ isset($exam) ? 'Update Exam' : 'Create Exam' }}
                        </button>
                        <a href="{{ route('admin.exams.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
