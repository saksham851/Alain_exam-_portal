@extends('layouts.app')

@section('content')

<div class="row mb-4">
    <!-- Stats Cards -->
    <div class="col-md-4">
        <a href="{{ route('manager.students.index') }}" class="text-decoration-none">
            <div class="card h-100">
                <div class="card-body pt-3 px-3 pb-2">
                    <h6 class="mb-2 f-w-400 text-muted">Total Students</h6>
                    <h4 class="mb-2">{{ number_format($totalStudents) }} <span class="badge bg-light-primary border border-primary"><i class="ti ti-users"></i></span></h4>
                    <p class="mb-0 text-muted text-sm">Registered users</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body pt-3 px-3 pb-2">
                <h6 class="mb-2 f-w-400 text-muted">Total Exams</h6>
                <h4 class="mb-2">{{ $totalExams }} <span class="badge bg-light-success border border-success"><i class="ti ti-book"></i></span></h4>
                <p class="mb-0 text-muted text-sm">Available exams</p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <a href="{{ route('manager.attempts.index') }}" class="text-decoration-none">
            <div class="card h-100">
                <div class="card-body pt-3 px-3 pb-2">
                    <h6 class="mb-2 f-w-400 text-muted">Total Attempts</h6>
                    <h4 class="mb-2">{{ $totalAttempts }} <span class="badge bg-light-danger border border-danger"><i class="ti ti-trending-up"></i></span></h4>
                    <p class="mb-0 text-muted text-sm">All time</p>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row">
    <!-- Recent Activity Table -->
    <div class="col-md-12">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Activity</h5>
                <a href="{{ route('manager.attempts.index') }}" class="btn btn-sm btn-light-primary">
                    <i class="ti ti-eye me-1"></i> View All
                </a>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless mb-0">
                        <thead>
                            <tr>
                                <th>STUDENT</th>
                                <th>EXAM</th>
                                <th>SCORE</th>
                                <th>STATUS</th>
                                <th class="text-end">TIME</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAttempts as $attempt)
                            <tr>
                                <td>
                                    <a href="{{ route('manager.students.show', $attempt->studentExam->student->id) }}" class="text-muted">
                                        {{ $attempt->studentExam->student->first_name }} {{ $attempt->studentExam->student->last_name }}
                                    </a>
                                </td>
                                <td>{{ $attempt->studentExam->exam->name }}</td>
                                <td>{{ round($attempt->total_score, 1) }}%</td>
                                <td>
                                    <span class="d-flex align-items-center gap-2">
                                        <i class="fas fa-circle {{ $attempt->is_passed ? 'text-success' : 'text-danger' }} f-10 m-r-5"></i>
                                        {{ $attempt->is_passed ? 'Passed' : 'Failed' }}
                                    </span>
                                </td>
                                <td class="text-end">{{ $attempt->ended_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No recent attempts found. Students haven't attempted any exams yet.
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
        <div class="card">
            <div class="card-header">
                <h5>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('manager.students.index') }}" class="btn btn-light-primary">
                        <i class="ti ti-users me-2"></i> View Students
                    </a>
                    <a href="{{ route('manager.attempts.index') }}" class="btn btn-light-info">
                        <i class="ti ti-chart-bar me-2"></i> View All Results
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
