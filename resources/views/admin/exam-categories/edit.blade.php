@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">{{ isset($category) ? 'Edit Category' : 'Create Category' }}</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('admin.exam-categories.index') }}">Exam Categories</a></li>
          <li class="breadcrumb-item" aria-current="page">{{ isset($category) ? 'Edit' : 'Create' }}</li>
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
                <h5>Category Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ isset($category) ? route('admin.exam-categories.update', $category->id) : route('admin.exam-categories.store') }}" method="POST">
                    @csrf
                    @if(isset($category))
                        @method('PUT')
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', optional($category)->name) }}" required>
                            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Certification Type <span class="text-danger">*</span></label>
                            <input type="text" name="certification_type" class="form-control" value="{{ old('certification_type', optional($category)->certification_type) }}" required>
                            @error('certification_type') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            {{ isset($category) ? 'Update Category' : 'Create Category' }}
                        </button>
                        <a href="{{ route('admin.exam-categories.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
