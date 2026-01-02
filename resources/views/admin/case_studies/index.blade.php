@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Sections</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item" aria-current="page">Sections</li>
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
                <h5 class="mb-0">All Sections</h5>
                <a href="{{ route('admin.case-studies.create') }}" class="btn btn-primary btn-sm">
                    <i class="ti ti-plus me-1"></i> Add New Section
                </a>
            </div>
            
            <!-- Compact Filters Section -->
            <div class="card-body bg-light-subtle py-3 border-bottom">
                <form method="GET" action="{{ route('admin.case-studies.index') }}" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <!-- Search -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted small mb-1">SEARCH BY SECTION NAME</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                       placeholder="Section name..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">EXAM CATEGORY</label>
                            <select name="category_id" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Certification Type -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">CERTIFICATION TYPE</label>
                            <select name="certification_type" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                @foreach($certificationTypes as $type)
                                    <option value="{{ $type }}" {{ request('certification_type') == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Exam -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">EXAM</label>
                            <select name="exam_id" class="form-select form-select-sm">
                                <option value="">All Exams</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                                        {{ $exam->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-md-1">
                            <label class="form-label fw-bold text-muted small mb-1">EXAM STATUS</label>
                            <select name="is_active" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="col-md-2">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('admin.case-studies.index') }}" class="btn btn-sm btn-light-secondary px-3" title="Reset">
                                    <i class="ti ti-rotate"></i>
                                </a>
                                <button type="submit" class="btn btn-sm btn-primary px-3">
                                    <i class="ti ti-filter me-1"></i> Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Active Filters Indicator -->
            @php
                $hasActiveFilters = request('search') || 
                                  request('exam_id') || 
                                  request('category_id') || 
                                  request('certification_type') ||
                                  request()->filled('is_active');
            @endphp
            
            @if($hasActiveFilters)
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
                    @if(request()->filled('is_active'))
                        <span class="badge rounded-pill {{ request('is_active') == 1 ? 'bg-success' : 'bg-danger' }}">
                            <i class="ti ti-toggle-left me-1"></i>{{ request('is_active') == 1 ? 'Active Exams' : 'Inactive Exams' }}
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
                    @if(request('exam_id'))
                        <span class="badge rounded-pill bg-primary">
                            <i class="ti ti-book me-1"></i>{{ $exams->firstWhere('id', request('exam_id'))->name ?? 'Unknown' }}
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
                                <th>Category</th>
                                <th>Certification Type</th>
                                <th>Exam</th>
                                <th>Section Name</th>
                                <th>Total Case Studies</th>
                                <th>Total Questions</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($caseStudies as $cs)
                            <tr>
                                <td>
                                    @if($cs->exam && $cs->exam->category)
                                        <span class="badge bg-light-info text-info">{{ $cs->exam->category->name }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($cs->exam && $cs->exam->category)
                                        <span class="badge bg-light-success text-success">{{ $cs->exam->category->certification_type }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $cs->exam->name ?? 'N/A' }}</td>
                                <td>
                                    <h6 class="mb-0">{{ $cs->title }}</h6>
                                    <small class="text-muted">{{ Str::limit(strip_tags($cs->content), 50) }}</small>
                                </td>
                                <td><span class="badge bg-light-primary text-primary">{{ $cs->caseStudies->count() }}</span></td>
                                <td><span class="badge bg-light-info text-info">{{ $cs->caseStudies->sum(function($sc) { return $sc->questions()->where('status', 1)->count(); }) }}</span></td>
                                <td class="text-end">
                                    @php
                                        $isActiveExam = $cs->exam && $cs->exam->is_active == 1;
                                    @endphp
                                    @if($isActiveExam)
                                        <button class="btn btn-icon btn-link-secondary btn-sm" style="opacity: 0.5; background: transparent; border: none;" title="Exam is active - cannot edit" disabled>
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <div class="d-inline-block">
                                            <button class="btn btn-icon btn-link-secondary btn-sm" style="opacity: 0.5; background: transparent; border: none;" title="Exam is active - cannot delete" disabled>
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    @else
                                        <a href="{{ route('admin.case-studies.edit', $cs->id) }}" class="btn btn-icon btn-link-primary btn-sm"><i class="ti ti-edit"></i></a>
                                        <form action="{{ route('admin.case-studies.destroy', $cs->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure?');">
                                            @csrf @method('DELETE')
                                            <button type="button" class="btn btn-icon btn-link-danger btn-sm" onclick="this.closest('form').submit()"><i class="ti ti-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No sections found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($caseStudies->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $caseStudies->firstItem() }} to {{ $caseStudies->lastItem() }} of {{ $caseStudies->total() }} entries
                        </div>
                        <div>
                            {{ $caseStudies->links() }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
