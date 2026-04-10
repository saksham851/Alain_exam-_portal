@extends('layouts.app')

@section('content')

<div class="row mb-4">
    <!-- Stats Cards -->
    <div class="col-md-6">
        <a href="{{ route('manager.students.index') }}" class="text-decoration-none">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body pt-3 px-3 pb-2 text-center">
                    <div class="bg-light-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px;">
                        <i class="ti ti-users fs-3 text-primary"></i>
                    </div>
                    <h6 class="mb-1 f-w-400 text-muted">Total Students</h6>
                    <h4 class="mb-1 font-weight-bold">{{ number_format($totalStudents) }}</h4>
                    <p class="mb-0 text-muted small">Registered users</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6">
        <a href="{{ route('manager.exams.index') }}" class="text-decoration-none">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body pt-3 px-3 pb-2 text-center">
                    <div class="bg-light-success rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px;">
                        <i class="ti ti-book fs-3 text-success"></i>
                    </div>
                    <h6 class="mb-1 f-w-400 text-muted">Total Exams</h6>
                    <h4 class="mb-1 font-weight-bold">{{ $totalExams }}</h4>
                    <p class="mb-0 text-muted small">Created in the portal</p>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row">
    <!-- Recent Activity Table -->
    <div class="col-md-12">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold">Recently Created Exams</h5>
                <a href="{{ route('manager.exams.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="ti ti-eye me-1"></i> View All
                </a>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless mb-0">
                        <thead>
                            <tr class="bg-light">
                                <th class="ps-4">EXAM NAME</th>
                                <th>CATEGORY</th>
                                <th>STATUS</th>
                                <th class="text-end pe-4">CREATED</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentExams as $exam)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary-subtle rounded p-2 me-3">
                                            <i class="ti ti-file-text text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-weight-bold">{{ $exam->name }}</h6>
                                            <small class="text-muted">{{ $exam->exam_code }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $exam->category->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ $exam->is_active ? 'bg-success' : 'bg-secondary' }} rounded-pill px-2">
                                        {{ $exam->is_active ? 'Published' : 'Unpublished' }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">{{ $exam->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    No exams found yet. Start creating your first exam!
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

<div class="row mt-4">
    <!-- Quick Actions -->
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold">Management Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                         <a href="{{ route('manager.exams.create') }}" class="btn btn-primary w-100 py-2 rounded-3">
                            <i class="ti ti-plus me-2"></i> Create New Exam
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('manager.students.index') }}" class="btn btn-light-info w-100 py-2 rounded-3">
                            <i class="ti ti-users me-2"></i> View Students
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('manager.exams.index') }}" class="btn btn-light-secondary w-100 py-2 rounded-3">
                            <i class="ti ti-list me-2"></i> All Exams
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
