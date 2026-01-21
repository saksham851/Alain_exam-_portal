@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <!-- Welcome Section matches Admin header style implicitly but adds personalization -->


    <!-- Stats Cards - Matching Admin Dashboard Style -->
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body pt-3 px-3 pb-2">
                <h6 class="mb-2 f-w-400 text-muted">Enrolled Exams</h6>
                <h4 class="mb-2">{{ $stats['enrolled'] }} <span class="badge bg-light-primary border border-primary"><i class="ti ti-book"></i></span></h4>
                <p class="mb-0 text-muted text-sm">Total active enrollments</p>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
             <div class="card-body pt-3 px-3 pb-2">
                <h6 class="mb-2 f-w-400 text-muted">Passed Exams</h6>
                <h4 class="mb-2">{{ $stats['passed_exams'] }} <span class="badge bg-light-success border border-success"><i class="ti ti-trophy"></i></span></h4>
                <p class="mb-0 text-muted text-sm">Successfully completed</p>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body pt-3 px-3 pb-2">
                <h6 class="mb-2 f-w-400 text-muted">Average Score</h6>
                <h4 class="mb-2">{{ $stats['average_score'] }}% <span class="badge bg-light-warning border border-warning"><i class="ti ti-chart-bar"></i></span></h4>
                <p class="mb-0 text-muted text-sm">Across all attempts</p>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body pt-3 px-3 pb-2">
                <h6 class="mb-2 f-w-400 text-muted">Success Rate</h6>
                <h4 class="mb-2">{{ $stats['success_rate'] }}% <span class="badge bg-light-info border border-info"><i class="ti ti-activity"></i></span></h4>
                <p class="mb-0 text-muted text-sm">Pass vs Fail ratio</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Active Exams Section - Can use Cards but styled consistently -->
    <div class="col-lg-8">
        <div class="card h-100" style="min-height: 500px;">
            <div class="card-header">
                <h5>My Exams</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>EXAM NAME</th>
                                <th>STATUS</th>
                                <th>ATTEMPTS</th>
                                <th>PROGRESS</th>
                                <th class="text-end">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchasedExams as $exam)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avtar avtar-s bg-light-primary text-primary rounded-circle me-3">
                                            <i class="ti ti-book"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">
                                                {{ $exam->title }}
                                                <span class="badge bg-light-secondary text-secondary ms-1">{{ $exam->exam_code }}</span>
                                            </h6>
                                            <small class="text-muted">{{ $exam->duration }} mins | {{ $exam->max_attempts }} Attempts Allowed</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($exam->status == 'active')
                                        <span class="badge bg-light-success border border-success">Active</span>
                                    @else
                                        <span class="badge bg-light-danger border border-danger">Expired</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold">{{ $exam->attempts_taken }}</span> / {{ $exam->max_attempts }}
                                </td>
                                <td style="width: 20%;">
                                    <div class="progress" style="height: 6px;">
                                        @php
                                            $percent = ($exam->attempts_taken / $exam->max_attempts) * 100;
                                            $color = $percent >= 100 ? 'bg-danger' : ($percent >= 66 ? 'bg-warning' : 'bg-primary');
                                        @endphp
                                        <div class="progress-bar {{ $color }}" role="progressbar" style="width: {{ $percent }}%"></div>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        @if($exam->can_attempt)
                                            <a href="{{ route('exams.show', $exam->id) }}" class="btn btn-sm btn-primary">
                                                Start <i class="ti ti-player-play ms-1"></i>
                                            </a>
                                        @else
                                            <button class="btn btn-sm btn-light-secondary" disabled>
                                                Locked
                                            </button>
                                        @endif
                                        
                                        @if($exam->attempts_taken > 0)
                                        <a href="{{ route('exams.answer-key', $exam->id) }}" class="btn btn-sm btn-light-success ms-1" data-bs-toggle="tooltip" title="Download Answer Key">
                                            <i class="ti ti-download"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-center">
                                        <img src="{{ asset('assets/images/no-data.svg') }}" alt="No Data" class="img-fluid mb-3" style="max-height: 100px; opacity: 0.5;">
                                        <p class="text-muted mb-0">No exams assigned to you yet.</p>
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

    <!-- Recent History Sidebar - Matching Admin's Recent Activity Card -->
    <div class="col-lg-4">
        <div class="card h-100" style="min-height: 500px;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent History</h5>
                <a href="{{ route('student.history') }}" class="btn btn-sm btn-light-primary">
                    <i class="ti ti-eye me-1"></i> View All
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless mb-0">
                        <thead>
                            <tr>
                                <th>EXAM</th>
                                <th>SCORE</th>
                                <th>IG</th>
                                <th>DM</th>
                                <th class="text-end">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attempts as $attempt)
                            <tr>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold text-truncate" style="max-width: 120px;" title="{{ $attempt->exam_title }}">{{ $attempt->exam_title }}</span>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($attempt->date)->diffForHumans() }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ $attempt->score }}%</span>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ $attempt->ig_score }}%</span>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ $attempt->dm_score }}%</span>
                                </td>
                                <td class="text-end">
                                    @if($attempt->status == 'Pass')
                                        <span class="badge bg-light-success border border-success">Pass</span>
                                    @else
                                        <span class="badge bg-light-danger border border-danger">Fail</span>
                                    @endif
                                    <div class="mt-1">
                                        <a href="{{ route('exams.result', $attempt->id) }}" class="f-12 link-primary">Result</a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No recent history.
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
