@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Exam Categories</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item" aria-current="page">Exam Categories</li>
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
                <h5 class="mb-0">All Exam Categories</h5>
                <a href="{{ route('admin.exam-categories.create') }}" class="btn btn-primary btn-sm">
                    <i class="ti ti-plus me-1"></i> Create Category
                </a>
            </div>
            
            <!-- Filters Section -->
            <div class="card-body">
                <form method="GET" action="{{ route('admin.exam-categories.index') }}" id="filterForm">
                    <!-- Search Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-muted small mb-2">
                                <i class="ti ti-search me-1"></i>SEARCH CATEGORIES
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="ti ti-search text-muted"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                       placeholder="Search by category name..." value="{{ request('search') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Filters Grid -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted small mb-2">
                                <i class="ti ti-certificate me-1"></i>CERTIFICATION TYPE
                            </label>
                            <select name="certification_type" class="form-select">
                                <option value="">All Types</option>
                                @foreach($certificationTypes as $type)
                                    <option value="{{ $type }}" {{ request('certification_type') == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted small mb-2">
                                <i class="ti ti-clipboard-list me-1"></i>EXAM COUNT
                            </label>
                            <input type="number" name="exam_count" class="form-control" 
                                   placeholder="Enter exact number of exams" min="0" value="{{ request('exam_count') }}">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('admin.exam-categories.index') }}" class="btn btn-light px-4">
                                    <i class="ti ti-refresh me-1"></i> Reset
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="ti ti-filter me-1"></i> Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Active Filters Indicator -->
            @if(request()->hasAny(['search', 'certification_type', 'exam_count']))
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
                    @if(request('certification_type'))
                        <span class="badge rounded-pill bg-success">
                            <i class="ti ti-certificate me-1"></i>{{ request('certification_type') }}
                        </span>
                    @endif
                    @if(request('exam_count') !== null && request('exam_count') !== '')
                        <span class="badge rounded-pill bg-info">
                            <i class="ti ti-clipboard-list me-1"></i>{{ request('exam_count') }} Exams
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
                                <th>Category Name</th>
                                <th>Certification Type</th>
                                <th>Exams Count</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                            <tr>
                                <td>
                                    <h6 class="mb-0">{{ $category->name }}</h6>
                                </td>
                                <td>
                                    <span class="badge bg-light-success">{{ $category->certification_type }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light-info">{{ $category->exams_count ?? 0 }} exams</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.exam-categories.edit', $category->id) }}" class="btn btn-icon btn-link-primary btn-sm" title="Edit Category">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.exam-categories.destroy', $category->id) }}" method="POST" class="d-inline-block" id="deleteForm{{ $category->id }}">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-icon btn-link-danger btn-sm" title="Delete Category" onclick="showDeleteModal(document.getElementById('deleteForm{{ $category->id }}'), 'Are you sure you want to delete this category?')">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">No categories found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($categories->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $categories->firstItem() }} to {{ $categories->lastItem() }} of {{ $categories->total() }} entries
                        </div>
                        <div>
                            {{ $categories->links() }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
