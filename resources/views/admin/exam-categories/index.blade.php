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

      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    All Exam Categories 
                    <span class="badge bg-light-secondary ms-2">{{ $categories->total() }} Total</span>
                </h5>
                <a href="{{ route('admin.exam-categories.create') }}" class="btn btn-primary btn-sm">
                    <i class="ti ti-plus me-1"></i> Create Category
                </a>
            </div>
            
            <!-- Compact Filters Section -->
            <div class="card-body bg-light-subtle py-3 border-bottom">
                <form method="GET" action="{{ route('admin.exam-categories.index') }}" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <!-- Search -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small mb-1">SEARCH EXAM CATEGORY</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                       placeholder="Category name..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <!-- Certification Type -->
                        <div class="col-md-3">
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

                        <!-- Exam Count -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">EXAM COUNT</label>
                            <input type="number" name="exam_count" class="form-control form-control-sm" 
                                   placeholder="Count" min="0" value="{{ request('exam_count') }}">
                        </div>

                        <!-- Buttons -->
                        <div class="col-md-3">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('admin.exam-categories.index') }}" class="btn btn-sm btn-light-secondary px-3" title="Reset">
                                    <i class="ti ti-rotate"></i>
                                </a>
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
                                <th>Exam Category Name</th>
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
                </div>
                
                {{-- Custom Pagination --}}
                <x-custom-pagination :paginator="$categories" />
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const searchInput = filterForm.querySelector('input[name="search"]');
    const certificationTypeSelect = filterForm.querySelector('select[name="certification_type"]');
    const examCountInput = filterForm.querySelector('input[name="exam_count"]');
    
    let searchTimeout;
    
    // Auto-submit on dropdown change (instant)
    if (certificationTypeSelect) {
        certificationTypeSelect.addEventListener('change', function() {
            filterForm.submit();
        });
    }
    
    // Auto-submit on search input (debounced - 500ms delay)
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                filterForm.submit();
            }, 500);
        });
    }
    
    // Auto-submit on exam count input (debounced - 500ms delay)
    if (examCountInput) {
        examCountInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                filterForm.submit();
            }, 500);
        });
    }
});
</script>
@endsection
