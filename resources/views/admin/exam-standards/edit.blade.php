@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header"> 
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Edit Exam Standard</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('admin.exam-standards.index') }}">Exam Standards</a></li>
          <li class="breadcrumb-item active">Edit</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row">
    <div class="col-md-12">
        <form action="{{ route('admin.exam-standards.update', $standard->id) }}" method="POST" id="examStandardForm">
            @csrf
            @method('PUT')

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($hasActiveExams)
                <div class="alert alert-warning border-warning d-flex align-items-center mb-4" role="alert">
                    <i class="ti ti-alert-triangle f-24 me-3"></i>
                    <div>
                        <h6 class="alert-heading mb-1">Standard Locked</h6>
                        <p class="mb-0 small">This standard is currently being used by <strong>active exams</strong>. You cannot add, remove, or modify categories/areas until those exams are deactivated.</p>
                    </div>
                </div>
            @endif
            
            <!-- Basic Information -->
            <div class="card">
                <div class="card-header">
                    <h5>Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Enter Standard Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       placeholder="Enter Standard Name" value="{{ old('name', $standard->name) }}" required
                                       {{ $hasActiveExams ? 'readonly' : '' }}>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Description (Optional)</label>
                                <input type="text" name="description" class="form-control" 
                                       placeholder="Brief description" value="{{ old('description', $standard->description) }}"
                                       {{ $hasActiveExams ? 'readonly' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories Container -->
            <div id="categoriesContainer"></div>

            <div class="mb-4">
                <button type="button" class="btn btn-outline-primary" onclick="addCategory()" {{ $hasActiveExams ? 'disabled' : '' }}>
                    <i class="ti ti-folder-plus me-1"></i> Add Another Category
                </button>
            </div>

            <!-- Submit Buttons -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.exam-standards.index') }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" {{ $hasActiveExams ? 'disabled' : '' }}>
                            <i class="ti ti-check me-1"></i> Update Exam Standard
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@php
    $initialData = old('categories', $standard->categories->map(function($c) { 
        return [
            'id' => $c->id,
            'name' => $c->name, 
            'areas' => $c->contentAreas->map(function($a){ 
                return ['id'=>$a->id, 'name'=>$a->name, 'percentage'=>$a->percentage, 'max_points'=>$a->max_points]; 
            })->values()
        ]; 
    })->values());
@endphp

<script>
    // Initialize data from Old Input or Database
    const initialCategories = @json($initialData);
    const hasActiveExams = @json($hasActiveExams);

    let categoryCount = 0;

    document.addEventListener('DOMContentLoaded', function() {
        // Render initial categories
        if(initialCategories && initialCategories.length > 0) {
            initialCategories.forEach(cat => addCategory(cat));
        } else {
             addCategory(); // Default empty one
        }
    });

    function addCategory(data = null) {
        const index = categoryCount;
        const container = document.getElementById('categoriesContainer');
        if (!container) return;

        const card = document.createElement('div');
        card.className = 'card category-card mb-4';
        card.dataset.index = index;

        // Default Values
        const catId = data && data.id ? data.id : '';
        const catName = data ? data.name : '';
        const areas = (data && data.areas && data.areas.length > 0) ? data.areas : [{name:'', max_points: 0}];

        card.innerHTML = `
            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                <h5 class="mb-0">Score Category <span class="category-number">${index + 1}</span></h5>
                ${index > 0 ? `<button type="button" class="btn btn-danger btn-sm remove-category-btn" onclick="removeCategory(this)"><i class="ti ti-trash"></i></button>` : ''}
            </div>
            <div class="card-body">
                <input type="hidden" name="categories[${index}][id]" class="category-id-input" value="${catId}">
                <div class="mb-3">
                    <label class="form-label">Enter Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="categories[${index}][name]" class="form-control category-name-input" 
                           placeholder="Enter Category Name" value="${catName}" required ${hasActiveExams ? 'readonly' : ''}>
                </div>
                
                <label class="form-label">Content Areas <span class="text-danger">*</span></label>
                <div class="areas-container" id="areas-${index}"></div>
                
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2 add-area-btn" onclick="addArea(${index})" ${hasActiveExams ? 'disabled' : ''}>
                    <i class="ti ti-plus"></i> Add Content Area
                </button>

                <div class="mt-3">
                     <strong>Total Points: <span class="total-points text-primary">0</span></strong>
                     <span class="status-badge ms-2"></span>
                </div>
            </div>
        `;

        container.appendChild(card);
        
        // Render Areas
        areas.forEach(area => addArea(index, area));

        categoryCount++;
        updateCategoryNumbers();
    }

    function addArea(catIndex, data = null) {
        const container = document.getElementById(`areas-${catIndex}`);
        if (!container) return;

        const areaIndex = container.children.length;
        
        const name = data ? data.name : '';
        const maxPoints = data ? (data.max_points !== undefined ? data.max_points : 0) : 0;

        const areaId = data && data.id ? data.id : '';

        const row = document.createElement('div');
        row.className = 'row area-row mb-2 align-items-center';
        row.innerHTML = `
            <input type="hidden" name="categories[${catIndex}][areas][${areaIndex}][id]" class="area-id-input" value="${areaId}">
            <div class="col-md-7">
                <input type="text" name="categories[${catIndex}][areas][${areaIndex}][name]" 
                       class="form-control area-name-input" placeholder="Area Name" value="${name}" required ${hasActiveExams ? 'readonly' : ''}>
            </div>
            <div class="col-md-4">
                 <div class="input-group">
                    <span class="input-group-text">Pts</span>
                    <input type="number" name="categories[${catIndex}][areas][${areaIndex}][max_points]" 
                           class="form-control max-points-input" placeholder="Max" min="0" 
                           value="${maxPoints}" required oninput="updateTotal(${catIndex})" ${hasActiveExams ? 'readonly' : ''}>
                 </div>
            </div>
            <div class="col-md-1">
                 <button type="button" class="btn btn-danger w-100 remove-area-btn" onclick="removeArea(this, ${catIndex})">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
        updateTotal(catIndex);
    }

    function removeCategory(btn) {
        if (hasActiveExams) {
            Swal.fire({
                icon: 'error',
                title: 'Action Blocked',
                text: 'This standard is being used by an active exam and cannot be modified. Please deactivate the associated exam(s) first.'
            });
            return;
        }
        Swal.fire({
            title: 'Are you sure?',
            text: "This will remove the category and all its content areas. You'll need to save the standard to apply changes.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const card = btn.closest('.category-card');
                if (card) {
                    card.remove();
                    updateCategoryNumbers();
                }
            }
        });
    }

    function removeArea(btn, catIndex) {
        if (hasActiveExams) {
            Swal.fire({
                icon: 'error',
                title: 'Action Blocked',
                text: 'This standard is being used by an active exam and cannot be modified. Please deactivate the associated exam(s) first.'
            });
            return;
        }

        const container = btn.closest('.areas-container');
        if(!container) return;

        if(container.children.length <= 1) {
            Swal.fire({
                icon: 'warning',
                title: 'Cannot Remove',
                text: 'At least one content area is required.'
            });
            return;
        }

        Swal.fire({
            title: 'Remove Content Area?',
            text: "Are you sure you want to remove this content area?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, remove'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.closest('.area-row').remove();
                reindexAreas(catIndex);
                updateTotal(catIndex);
            }
        });
    }

    function reindexAreas(catIndex) {
        const container = document.getElementById(`areas-${catIndex}`);
        if (!container) return;

        Array.from(container.children).forEach((row, idx) => {
            const idInput = row.querySelector('.area-id-input');
            const nameInput = row.querySelector('.area-name-input');
            const pointsInput = row.querySelector('.max-points-input');
            const removeBtn = row.querySelector('.remove-area-btn');

            if (idInput) idInput.name = `categories[${catIndex}][areas][${idx}][id]`;
            if (nameInput) nameInput.name = `categories[${catIndex}][areas][${idx}][name]`;
            if (pointsInput) {
                pointsInput.name = `categories[${catIndex}][areas][${idx}][max_points]`;
                pointsInput.setAttribute('oninput', `updateTotal(${catIndex})`);
            }
            if (removeBtn) removeBtn.setAttribute('onclick', `removeArea(this, ${catIndex})`);
        });
    }

    function updateCategoryNumbers() {
        const cards = document.querySelectorAll('.category-card');
        cards.forEach((card, idx) => {
            const index = idx;
            card.dataset.index = index;
            
            const numSpan = card.querySelector('.category-number');
            if (numSpan) numSpan.textContent = index + 1;

            const nameInput = card.querySelector('.category-name-input');
            if(nameInput) nameInput.name = `categories[${index}][name]`;

            const idInput = card.querySelector('.category-id-input');
            if(idInput) idInput.name = `categories[${index}][id]`;
            
            const areasDiv = card.querySelector('.areas-container');
            if (areasDiv) areasDiv.id = `areas-${index}`;
            
            const addBtn = card.querySelector('.add-area-btn');
            if (addBtn) addBtn.setAttribute('onclick', `addArea(${index})`);
            
            const removeBtn = card.querySelector('.remove-category-btn');
            if (removeBtn) removeBtn.setAttribute('onclick', `removeCategory(this)`);

            reindexAreas(index);
            updateTotal(index);
        });
        categoryCount = cards.length;
    }

    function updateTotal(catIndex) {
        const container = document.getElementById(`areas-${catIndex}`);
        if (!container) return;

        let total = 0;
        container.querySelectorAll('.max-points-input').forEach(inp => {
            total += parseInt(inp.value) || 0;
        });
        
        const cardBody = container.closest('.card-body');
        if (cardBody) {
            const totalSpan = cardBody.querySelector('.total-points');
            if (totalSpan) totalSpan.textContent = total;
        }
    }


    document.getElementById('examStandardForm').addEventListener('submit', function(e) {
        // No strict check
    });

</script>

@endsection
