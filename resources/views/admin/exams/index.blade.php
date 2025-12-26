@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Exam Management</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item" aria-current="page">Exams</li>
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
                <h5 class="mb-0">All Exams</h5>
                <a href="{{ route('admin.exams.create') }}" class="btn btn-primary btn-sm">
                    <i class="ti ti-plus me-1"></i> Create Exam
                </a>
            </div>
            
            <!-- Filters Section -->
            <div class="card-body">
                <form method="GET" action="{{ route('admin.exams.index') }}" id="filterForm">
                    <!-- Search Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-muted small mb-2">
                                <i class="ti ti-search me-1"></i>SEARCH EXAMS
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="ti ti-search text-muted"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                       placeholder="Search by exam name or code..." value="{{ request('search') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Filters Grid -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-muted small mb-2">
                                <i class="ti ti-category me-1"></i>CATEGORY
                            </label>
                            <select name="category_id" class="form-select">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-4">
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
                        
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-muted small mb-2">
                                <i class="ti ti-clock me-1"></i>DURATION (MINUTES)
                            </label>
                            <input type="number" name="duration" class="form-control" 
                                   placeholder="Enter exact duration" min="0" value="{{ request('duration') }}">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('admin.exams.index') }}" class="btn btn-light px-4">
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
            @if(request()->hasAny(['search', 'category_id', 'certification_type', 'duration']))
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
                    @if(request('category_id'))
                        <span class="badge rounded-pill bg-info">
                            <i class="ti ti-category me-1"></i>{{ $categories->firstWhere('id', request('category_id'))->name ?? 'Unknown' }}
                        </span>
                    @endif
                    @if(request('certification_type'))
                        <span class="badge rounded-pill bg-success">
                            <i class="ti ti-certificate me-1"></i>{{ request('certification_type') }}
                        </span>
                    @endif
                    @if(request('duration') !== null && request('duration') !== '')
                        <span class="badge rounded-pill bg-primary">
                            <i class="ti ti-clock me-1"></i>{{ request('duration') }} mins
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
                                <th>Name</th>
                                <th>Code</th>
                                <th>Category</th>
                                <th>Certification Type</th>
                                <th>Duration</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($exams as $exam)
                            <tr>
                                <td style="width: 25%;">
                                    <h5 class="mb-1 fw-bold">{{ $exam->name }}</h5>
                                    @if($exam->description)
                                        <small class="text-muted d-block">{{ Str::limit($exam->description, 80) }}</small>
                                    @endif
                                </td>
                                <td style="width: 12%;">
                                    @if($exam->exam_code)
                                        <span class="badge bg-light-secondary">{{ $exam->exam_code }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td style="width: 15%;">
                                    @if($exam->category)
                                        <span class="badge bg-light-info">{{ $exam->category->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td style="width: 13%;">
                                    @if($exam->category)
                                        <span class="badge bg-light-success">{{ $exam->category->certification_type }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td style="width: 12%;">
                                    <span class="badge bg-light-primary">
                                        <i class="ti ti-clock me-1"></i>{{ $exam->duration_minutes }} mins
                                    </span>
                                </td>
                                <td class="text-end" style="width: 30%;">
                                    <a href="{{ route('admin.case-studies.index', ['exam_id' => $exam->id]) }}" class="btn btn-icon btn-link-success btn-sm" title="Manage Case Studies">
                                        <i class="ti ti-file-text"></i>
                                    </a>
                                    <a href="{{ route('admin.exams.edit', $exam->id) }}" class="btn btn-icon btn-link-primary btn-sm" title="Edit Exam">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.exams.destroy', $exam->id) }}" method="POST" class="d-inline-block" id="deleteForm{{ $exam->id }}">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-icon btn-link-danger btn-sm" title="Delete Exam" onclick="showDeleteModal(document.getElementById('deleteForm{{ $exam->id }}'), 'Are you sure you want to delete this exam?')">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No exams found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($exams->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $exams->firstItem() }} to {{ $exams->lastItem() }} of {{ $exams->total() }} entries
                        </div>
                        <div>
                            {{ $exams->links() }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
