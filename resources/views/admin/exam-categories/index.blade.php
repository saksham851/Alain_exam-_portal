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
