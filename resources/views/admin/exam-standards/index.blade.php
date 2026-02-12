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
                                <th>Score Category 1</th>
                                <th>Score Category 2</th>
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
                                    <strong>{{ $standard->category1->name ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $standard->category1 ? $standard->category1->contentAreas->count() : 0 }} content areas</small>
                                </td>
                                <td>
                                    <strong>{{ $standard->category2->name ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $standard->category2 ? $standard->category2->contentAreas->count() : 0 }} content areas</small>
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
                                <td colspan="5" class="text-center py-4">
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

@endsection
