@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Admin Dashboard</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item" aria-current="page">Dashboard</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row">
    <!-- Stats Cards -->
    <div class="col-md-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <h6 class="mb-2 f-w-400 text-muted">Total Students</h6>
          <h4 class="mb-3">{{ number_format($stats['total_students']) }} <span class="badge bg-light-primary border border-primary"><i class="ti ti-users"></i></span></h4>
          <p class="mb-0 text-muted text-sm">Registered users on platform</p>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <h6 class="mb-2 f-w-400 text-muted">Active Exams</h6>
          <h4 class="mb-3">{{ $stats['active_exams'] }} <span class="badge bg-light-success border border-success"><i class="ti ti-book"></i></span></h4>
          <p class="mb-0 text-muted text-sm">Currently available for attempts</p>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <h6 class="mb-2 f-w-400 text-muted">Questions Bank</h6>
          <h4 class="mb-3">{{ number_format($stats['total_questions']) }} <span class="badge bg-light-warning border border-warning"><i class="ti ti-question-mark"></i></span></h4>
          <p class="mb-0 text-muted text-sm">Total MCQs in database</p>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <h6 class="mb-2 f-w-400 text-muted">Recent Attempts</h6>
          <h4 class="mb-3">{{ $stats['recent_attempts_count'] }} <span class="badge bg-light-danger border border-danger"><i class="ti ti-trending-up"></i></span></h4>
          <p class="mb-0 text-muted text-sm">Submissions in last 24h</p>
        </div>
      </div>
    </div>

    <!-- Recent Activity Table -->
    <div class="col-md-12 col-xl-8">
      <div class="card tbl-card">
        <div class="card-header">
            <h5>Recent Activity</h5>
        </div>
        <div class="card-body">
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
                    <a href="{{ route('admin.attempts.by-user', $attempt->student_id) }}" class="text-muted">
                      {{ $attempt->student_name }}
                    </a>
                  </td>
                  <td>{{ $attempt->exam_name }}</td>
                  <td>{{ round($attempt->total_score, 1) }}%</td>
                  <td>
                    <span class="d-flex align-items-center gap-2">
                      <i class="fas fa-circle {{ $attempt->is_passed ? 'text-success' : 'text-danger' }} f-10 m-r-5"></i>
                      {{ $attempt->is_passed ? 'Passed' : 'Failed' }}
                    </span>
                  </td>
                  <td class="text-end">{{ $attempt->time_ago }}</td>
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

    <!-- Quick Stats -->
    <div class="col-md-12 col-xl-4">
      <div class="card">
        <div class="card-header">
          <h5>Quick Actions</h5>
        </div>
        <div class="card-body">
          <div class="d-grid gap-2">
            <a href="{{ route('admin.users.index') }}" class="btn btn-light-primary">
              <i class="ti ti-users me-2"></i> Manage Students
            </a>
            <a href="{{ route('admin.exams.index') }}" class="btn btn-light-success">
              <i class="ti ti-book me-2"></i> Manage Exams
            </a>
            <a href="{{ route('admin.questions.index') }}" class="btn btn-light-warning">
              <i class="ti ti-question-mark me-2"></i> Manage Questions
            </a>
            <a href="{{ route('admin.attempts.index') }}" class="btn btn-light-info">
              <i class="ti ti-chart-bar me-2"></i> View All Results
            </a>
          </div>
        </div>
      </div>
    </div>
</div>
@endsection
