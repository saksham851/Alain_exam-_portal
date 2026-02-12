@extends('layouts.app')

@section('content')

<!-- Premium Student Profile Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-0 overflow-hidden" style="border-radius: 15px;">
            <div class="card-body p-0">
                <div class="d-flex align-items-stretch">
                    <!-- Name & ID Section -->
                    <div class="bg-primary d-flex align-items-center px-4 py-3" style="min-width: 250px; background: linear-gradient(45deg, #0d6efd, #3e8ef7);">
                        <div class="avtar avtar-lg bg-white bg-opacity-25 text-white rounded-circle me-3">
                            <i class="ti ti-user fs-2"></i>
                        </div>
                        <div>
                            <h4 class="text-white fw-bold mb-0 lh-1">{{ auth()->user()->name }}</h4>
                            <span class="text-white text-opacity-75 small fw-medium text-uppercase ls-1">Verified Student</span>
                        </div>
                    </div>
                    
                    <!-- Info Section -->
                    <div class="flex-grow-1 bg-white d-flex align-items-center px-4 py-3 border-start">
                        <div class="row w-100 g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="avtar avtar-m rounded-2 me-3" style="background: rgba(13, 110, 253, 0.1); color: #0d6efd; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ti ti-mail fs-4"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="text-uppercase fw-bold mb-0" style="font-size: 0.72rem; letter-spacing: 0.5px; color: #5b6b79;">EMAIL ADDRESS</p>
                                        <h6 class="mb-0 fw-bold text-truncate" style="color: #121926;">{{ auth()->user()->email }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 border-start d-none d-md-block">
                                <div class="d-flex align-items-center ms-md-4">
                                    <div class="avtar avtar-m rounded-2 me-3" style="background: rgba(40, 167, 69, 0.1); color: #28a745; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ti ti-phone fs-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-uppercase fw-bold mb-0" style="font-size: 0.72rem; letter-spacing: 0.5px; color: #5b6b79;">PHONE NUMBER</p>
                                        <h6 class="mb-0 fw-bold" style="color: #121926;">{{ auth()->user()->phone ?? 'N/A' }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                <h4 class="mb-2">{{ $stats['average_score'] }} <span class="badge bg-light-warning border border-warning"><i class="ti ti-chart-bar"></i></span></h4>
                <p class="mb-0 text-muted text-sm">Across all attempts (points)</p>
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
                                            <a href="{{ route('exams.start', $exam->id) }}" class="btn btn-sm btn-primary">
                                                Start <i class="ti ti-player-play ms-1"></i>
                                            </a>
                                        @else
                                            <button class="btn btn-sm btn-light-secondary" disabled>
                                                Locked
                                            </button>
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
                <h5 class="mb-0">Recent Attempts History</h5>
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
                                <th>DATE</th>
                                <th>DURATION</th>
                                <th>SCORE</th>
                                <th class="text-end">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attempts as $attempt)
                            <tr>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold text-truncate" style="max-width: 150px;" title="{{ $attempt->exam_title }}">{{ $attempt->exam_title }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ $attempt->date ? $attempt->date->format('M d, Y') : 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ $attempt->duration }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ $attempt->score }}</span> <small class="text-muted">pts</small>
                                </td>
                                <td class="text-end">
                                    @if($attempt->status == 'Pass')
                                        <span class="badge bg-light-success border border-success" style="padding: 2px 6px; font-size: 0.75rem;">Pass</span>
                                    @else
                                        <span class="badge bg-light-danger border border-danger" style="padding: 2px 6px; font-size: 0.75rem;">Fail</span>
                                    @endif
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
