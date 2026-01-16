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
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-muted small mb-1">SEARCH EXAM CATEGORY</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                       placeholder="Category name..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">STATUS</label>
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="active" {{ request('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
            @if(request('search') || request('certification_type') || (request('exam_count') !== null && request('exam_count') !== '') || request('status') === 'inactive')
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

                    @if(request('exam_count') !== null && request('exam_count') !== '')
                        <span class="badge rounded-pill bg-info">
                            <i class="ti ti-clipboard-list me-1"></i>{{ request('exam_count') }} Exams
                        </span>
                    @endif

                    @if(request('status') === 'inactive')
                        <span class="badge rounded-pill bg-danger">
                            <i class="ti ti-trash-off me-1"></i>Inactive
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
                                    <span class="badge bg-light-info">{{ $category->exams_count ?? 0 }} exams</span>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn p-0 text-secondary bg-transparent border-0 shadow-none" type="button" 
                                                data-bs-toggle="dropdown" 
                                                data-bs-boundary="viewport" 
                                                data-bs-popper-config='{"strategy":"fixed"}'
                                                aria-expanded="false">
                                            <i class="ti ti-dots-vertical f-18"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @if($category->status == 1)
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.exam-categories.edit', $category->id) }}">
                                                        <i class="ti ti-edit me-2"></i>Edit Category
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('admin.exam-categories.destroy', $category->id) }}" method="POST" class="d-block" id="deleteForm{{ $category->id }}">
                                                        @csrf @method('DELETE')
                                                        <button type="button" class="dropdown-item text-danger" onclick="showDeleteModal(document.getElementById('deleteForm{{ $category->id }}'), 'Are you sure you want to delete this category?')">
                                                            <i class="ti ti-trash me-2"></i>Delete Category
                                                        </button>
                                                    </form>
                                                </li>
                                            @else
                                                <li>
                                                    <form action="{{ route('admin.exam-categories.activate', $category->id) }}" method="POST" class="d-block" id="activateForm{{ $category->id }}">
                                                        @csrf @method('PATCH')
                                                        <button type="button" class="dropdown-item text-success" onclick="showActivateModal(document.getElementById('activateForm{{ $category->id }}'))">
                                                            <i class="ti ti-check me-2"></i>Activate Category
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
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
    function showActivateModal(form) {
        if(typeof showAlert !== 'undefined' && showAlert.confirm) {
             showAlert.confirm(
                'Are you sure you want to activate this category?',
                'Activate Category',
                function() {
                    form.submit();
                }
            );
        } else {
            if(confirm('Are you sure you want to activate this category?')) {
                form.submit();
            }
        }
    }
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const searchInput = filterForm.querySelector('input[name="search"]');
    const certificationTypeSelect = filterForm.querySelector('select[name="certification_type"]');
    const examCountInput = filterForm.querySelector('input[name="exam_count"]');
    
    let searchTimeout;
    
    // Auto-submit on dropdown change (instant)

    
    // Auto-submit on search input (debounced - 1000ms delay)
    if (searchInput) {
        // Auto-focus if there is a value (restore state after reload)
        if (searchInput.value) {
           searchInput.focus();
           // Move cursor to end
           const val = searchInput.value;
           searchInput.value = '';
           searchInput.value = val;
        }

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                filterForm.submit();
            }, 1000);
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
<script>
// Initialize Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

@endsection
