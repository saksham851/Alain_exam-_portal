@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Student Details</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Students</a></li>
          <li class="breadcrumb-item" aria-current="page">View Student</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row align-items-stretch">
    <!-- Student Profile Card -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="mb-3">
                    <!-- Avatar removed -->
                </div>
                <h5 class="mb-1">{{ $user->first_name }} {{ $user->last_name }}</h5>
                <p class="text-muted mb-3">{{ $user->email }}</p>
                
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary" 
                            onclick="openManageAttemptsModal({{ $user->id }}, '{{ $user->first_name }} {{ $user->last_name }}', '{{ $user->email }}')">
                        <i class="ti ti-adjustments me-1"></i> Manage Attempts
                    </button>
                </div>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item px-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted">Phone</span>
                        <span class="fw-semibold">{{ $user->phone ?? 'N/A' }}</span>
                    </div>
                </li>
                <li class="list-group-item px-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted">Joined</span>
                        <span class="fw-semibold">{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                </li>
                <li class="list-group-item px-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted">Status</span>
                        @if($user->status)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <!-- Assigned Exams Column -->
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header">
                <h5>Assigned Exams</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Exam Name</th>
                                <th>Category</th>
                                <th class="text-center">Attempts</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->studentExams as $studentExam)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avtar avtar-xs bg-light-primary text-primary me-2">
                                            <i class="ti ti-book"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $studentExam->exam->name ?? 'Unknown Exam' }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($studentExam->exam && $studentExam->exam->category)
                                        <span class="badge bg-light-secondary text-secondary border border-secondary">
                                            {{ $studentExam->exam->category->name }}
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light-info text-info">
                                        {{ $studentExam->attempts_used }} / {{ $studentExam->attempts_allowed }} Used
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if(($studentExam->attempts_allowed - $studentExam->attempts_used) > 0)
                                        <span class="badge bg-success">Available</span>
                                    @else
                                        <span class="badge bg-secondary">Exhausted</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <div class="mb-2"><i class="ti ti-folder-off f-24"></i></div>
                                    <p class="mb-0">No exams assigned to this student.</p>
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

<!-- Manage Attempts Modal (Recycled from Index) -->
<!-- We need to include the same modal logic here or ensure it's accessible. 
     Since it's highly coupled with the index page via JS, 
     I will duplicate the minimal modal structure and JS here for standalone functionality. 
-->
@include('admin.users.partials.manage_attempts_modal')

@endsection

@push('scripts')
<script>
// Include the same JS logic for managing attempts here
// Ideally this should be in a separate JS file, but for now we'll duplicate inline for speed 
// or if we created a partial we can just include it.

// Let's assume we'll just copy the necessary JS into the partial or here.
</script>
@endpush
