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
                                       placeholder="Enter Standard Name" value="{{ old('name', $standard->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Description (Optional)</label>
                                <input type="text" name="description" class="form-control" 
                                       placeholder="Brief description" value="{{ old('description', $standard->description) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories Container -->
            <div id="categoriesContainer"></div>

            <div class="mb-4">
                <button type="button" class="btn btn-outline-primary" onclick="addCategory()">
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
                        <button type="submit" class="btn btn-primary">
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
            'name' => $c->name, 
            'areas' => $c->contentAreas->map(function($a){ 
                return ['name'=>$a->name, 'percentage'=>$a->percentage, 'max_points'=>$a->max_points]; 
            })->values()
        ]; 
    })->values());
@endphp

<script>
    // Initialize data from Old Input or Database
    const initialCategories = @json($initialData);

    let categoryCount = 0;

    document.addEventListener('DOMContentLoaded', function() {
        // Render initial categories
        if(initialCategories.length > 0) {
            initialCategories.forEach(cat => addCategory(cat));
        } else {
             addCategory(); // Default empty one
        }
    });

    function addCategory(data = null) {
        const index = categoryCount;
        const container = document.getElementById('categoriesContainer');
        const card = document.createElement('div');
        card.className = 'card category-card mb-4';
        card.dataset.index = index;

        // Default Values
        const catName = data ? data.name : '';
        const areas = data && data.areas ? data.areas : [{name:'', percentage:'', max_points: ''}];

        card.innerHTML = `
            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                <h5 class="mb-0">Score Category <span class="category-number">${index + 1}</span></h5>
                ${index > 0 ? `<button type="button" class="btn btn-danger btn-sm" onclick="removeCategory(this)"><i class="ti ti-trash"></i></button>` : ''}
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Enter Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="categories[${index}][name]" class="form-control" 
                           placeholder="Enter Category Name" value="${catName}" required>
                </div>
                
                <label class="form-label">Content Areas <span class="text-danger">*</span></label>
                <div class="areas-container" id="areas-${index}"></div>
                
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addArea(${index})">
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
        const areaIndex = container.children.length;
        
        const name = data ? data.name : '';
        const percentage = data ? data.percentage : '';
        const maxPoints = data ? (data.max_points || 0) : 0;

        const row = document.createElement('div');
        row.className = 'row area-row mb-2 align-items-center';
        row.innerHTML = `
            <div class="col-md-7">
                <input type="text" name="categories[${catIndex}][areas][${areaIndex}][name]" 
                       class="form-control" placeholder="Area Name" value="${name}" required>
            </div>
            <div class="col-md-4">
                 <div class="input-group">
                    <span class="input-group-text">Pts</span>
                    <input type="number" name="categories[${catIndex}][areas][${areaIndex}][max_points]" 
                           class="form-control max-points-input" placeholder="Max" min="0" 
                           value="${maxPoints}" required oninput="updateTotal(${catIndex})">
                 </div>
            </div>
            <div class="col-md-1">
                 <button type="button" class="btn btn-danger w-100" onclick="removeArea(this, ${catIndex})">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
        updateTotal(catIndex);
    }

    function removeCategory(btn) {
        btn.closest('.category-card').remove();
        updateCategoryNumbers();
    }

    function removeArea(btn, catIndex) {
        const container = btn.closest('.areas-container');
        if(container.children.length <= 1) {
            Swal.fire({
                icon: 'warning',
                title: 'Cannot Remove',
                text: 'At least one content area is required.'
            });
            return;
        }
        btn.closest('.area-row').remove();
        reindexAreas(catIndex);
        updateTotal(catIndex);
    }

    function reindexAreas(catIndex) {
        const container = document.getElementById(`areas-${catIndex}`);
        Array.from(container.children).forEach((row, idx) => {
            row.querySelector('input[placeholder="Area Name"]').name = `categories[${catIndex}][areas][${idx}][name]`;
            row.querySelector('input.max-points-input').name = `categories[${catIndex}][areas][${idx}][max_points]`;
        });
    }

    function updateCategoryNumbers() {
        const cards = document.querySelectorAll('.category-card');
        cards.forEach((card, idx) => {
            card.querySelector('.category-number').textContent = idx + 1;
            const index = idx;
            const nameInput = card.querySelector('input[placeholder*="Category Name"], input[name*="[name]"]'); // Safe selector
            if(nameInput) nameInput.name = `categories[${index}][name]`;
            
            const areasDiv = card.querySelector('.areas-container');
            areasDiv.id = `areas-${index}`;
            
            const addBtn = card.querySelector('button[onclick*="addArea"]');
            addBtn.setAttribute('onclick', `addArea(${index})`);
            
            const pInputs = card.querySelectorAll('.max-points-input');
            pInputs.forEach(input => input.setAttribute('oninput', `updateTotal(${index})`));
            
            const areaRemoveBtns = card.querySelectorAll('.area-row button.btn-danger');
            areaRemoveBtns.forEach(btn => btn.setAttribute('onclick', `removeArea(this, ${index})`));

            reindexAreas(index);
        });
        categoryCount = cards.length;
    }

    function updateTotal(catIndex) {
        const container = document.getElementById(`areas-${catIndex}`);
        let total = 0;
        container.querySelectorAll('.max-points-input').forEach(inp => {
            total += parseInt(inp.value) || 0;
        });
        
        const card = container.closest('.card-body');
        const totalSpan = card.querySelector('.total-points');
        totalSpan.textContent = total;
    }

    document.getElementById('examStandardForm').addEventListener('submit', function(e) {
        // No strict check
    });

</script>

@endsection
