@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Exam Standards</h5>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<style>
.categories-collapse {
    display: none;
    margin-top: 8px;
}
.categories-collapse.show {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.toggle-categories-btn {
    cursor: pointer;
    border: none;
    background: none;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #5b73e8;
    background-color: #eef0fd;
}
.toggle-categories-btn:hover {
    background-color: #d8dcfb;
    color: #3d55d4;
}
.toggle-categories-btn .toggle-icon {
    transition: transform 0.25s ease;
    font-size: 13px;
}
.toggle-categories-btn.expanded .toggle-icon {
    transform: rotate(180deg);
}
.category-card {
    border: 1px solid #e0e3f0;
    border-radius: 8px;
    padding: 8px 12px;
    background-color: #f8f9ff;
    min-width: 150px;
    transition: box-shadow 0.2s;
}
.category-card:hover {
    box-shadow: 0 2px 8px rgba(91, 115, 232, 0.12);
}
.categories-count-badge {
    font-size: 11px;
    padding: 2px 7px;
    border-radius: 10px;
    background-color: #e8eafc;
    color: #5b73e8;
    font-weight: 600;
}
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    All Exam Standards 
                    <span class="badge bg-light-secondary ms-2">{{ $standards->count() }} Total</span>
                </h5>
                <a href="{{ route('admin.exam-standards.create') }}" class="btn btn-primary btn-sm">
                    <i class="ti ti-plus me-1"></i> Create Exam Standard
                </a>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive" style="overflow: visible;">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Standard Name</th>
                                <th>Score Categories</th>
                                <th>Exams Using</th>
                                <th class="text-end" style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($standards as $standard)
                            <tr>
                                <td>
                                    <h6 class="mb-0">{{ $standard->name }}</h6>
                                    @if($standard->description)
                                    <small class="text-muted">{{ Str::limit($standard->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @php $catCount = $standard->categories->count(); @endphp

                                    {{-- Toggle Button --}}
                                    <button 
                                        class="toggle-categories-btn" 
                                        onclick="toggleCategories(this, 'cats-{{ $standard->id }}')"
                                        title="Click to expand/collapse categories">
                                        <i class="ti ti-layout-list toggle-icon"></i>
                                        <span class="btn-label">Show {{ $catCount }} {{ Str::plural('Category', $catCount) }}</span>
                                        <i class="ti ti-chevron-down toggle-icon"></i>
                                    </button>

                                    {{-- Collapsible Categories --}}
                                    <div class="categories-collapse" id="cats-{{ $standard->id }}">
                                        @foreach($standard->categories as $category)
                                            <div class="category-card">
                                                <div class="fw-bold small text-uppercase text-muted">Category {{ $category->category_number }}</div>
                                                <div class="fw-bold small">{{ $category->name }}</div>
                                                <div class="small text-muted">
                                                    <i class="ti ti-layers-subtract" style="font-size:11px;"></i>
                                                    {{ $category->contentAreas->count() }} content areas
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light-info">{{ $standard->exams->count() }} exams</span>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown" style="position: static;">
                                        <button class="btn p-0 text-secondary bg-transparent border-0 shadow-none" type="button" 
                                                data-bs-toggle="dropdown" 
                                                aria-expanded="false">
                                            <i class="ti ti-dots-vertical f-18"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow" style="position: absolute; right: 0;">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.exam-standards.show', $standard->id) }}">
                                                    <i class="ti ti-eye me-2"></i>View Details
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.exam-standards.edit', $standard->id) }}">
                                                    <i class="ti ti-edit me-2"></i>Edit Standard
                                                </a>
                                            </li>
                                            @if($standard->exams->count() == 0)
                                            <li>
                                                <form action="{{ route('admin.exam-standards.destroy', $standard->id) }}" method="POST" class="d-block" id="deleteForm{{ $standard->id }}">
                                                    @csrf @method('DELETE')
                                                    <button type="button" class="dropdown-item text-danger" onclick="showDeleteModal(document.getElementById('deleteForm{{ $standard->id }}'), 'Are you sure you want to delete this standard?')">
                                                        <i class="ti ti-trash me-2"></i>Delete Standard
                                                    </button>
                                                </form>
                                            </li>
                                            @else
                                            <li>
                                                <span class="dropdown-item text-muted disabled">
                                                    <i class="ti ti-lock me-2"></i>Cannot Delete (In Use)
                                                </span>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="ti ti-file-off f-40 mb-2"></i>
                                        <p>No exam standards found. Create your first standard!</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleCategories(btn, targetId) {
    const target = document.getElementById(targetId);
    const isExpanded = btn.classList.contains('expanded');
    const label = btn.querySelector('.btn-label');
    const catCount = target.querySelectorAll('.category-card').length;

    if (isExpanded) {
        // Collapse
        target.classList.remove('show');
        btn.classList.remove('expanded');
        label.textContent = 'Show ' + catCount + ' ' + (catCount === 1 ? 'Category' : 'Categories');
    } else {
        // Expand
        target.classList.add('show');
        btn.classList.add('expanded');
        label.textContent = 'Hide ' + catCount + ' ' + (catCount === 1 ? 'Category' : 'Categories');
    }
}
</script>

@endsection
