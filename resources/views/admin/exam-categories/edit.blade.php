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
                            <div class="d-flex gap-2 align-items-center">
                                <div class="flex-grow-1">
                                    <select name="certification_type" id="certificationTypeSelect" class="form-select" required>
                                        <option value="">Select Certification Type</option>
                                        <option value="NIMCA" {{ old('certification_type', optional($category)->certification_type) == 'NIMCA' ? 'selected' : '' }}>NIMCA</option>
                                    </select>
                                    <input type="text" name="new_certification_type" id="newCertificationTypeInput" class="form-control" placeholder="Enter new certification type" style="display: none;">
                                    @error('certification_type') <small class="text-danger">{{ $message }}</small> @enderror
                                    @error('new_certification_type') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <button type="button" id="addNewTypeBtn" class="btn btn-primary" style="white-space: nowrap;">
                                    <i class="ti ti-plus me-1"></i> Add New
                                </button>
                            </div>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectElement = document.getElementById('certificationTypeSelect');
    const newInputElement = document.getElementById('newCertificationTypeInput');
    const addNewBtn = document.getElementById('addNewTypeBtn');
    let isAddingNew = false;
    
    addNewBtn.addEventListener('click', function() {
        if (!isAddingNew) {
            // Show input field for new certification type
            selectElement.style.display = 'none';
            selectElement.required = false;
            newInputElement.style.display = 'block';
            newInputElement.required = true;
            newInputElement.focus();
            addNewBtn.innerHTML = '<i class="ti ti-x me-1"></i> Cancel';
            addNewBtn.classList.remove('btn-primary');
            addNewBtn.classList.add('btn-secondary');
            isAddingNew = true;
        } else {
            // Hide input field and show dropdown
            selectElement.style.display = 'block';
            selectElement.required = true;
            newInputElement.style.display = 'none';
            newInputElement.required = false;
            newInputElement.value = '';
            addNewBtn.innerHTML = '<i class="ti ti-plus me-1"></i> Add New';
            addNewBtn.classList.remove('btn-secondary');
            addNewBtn.classList.add('btn-primary');
            isAddingNew = false;
        }
    });
});
</script>
@endpush

@endsection
