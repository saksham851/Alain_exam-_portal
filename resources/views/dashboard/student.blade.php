@extends('layouts.app')

@section('content')

<!-- Compact Student Profile Header -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-0 overflow-hidden" style="border-radius: 12px;">
            <div class="card-body p-0">
                <div class="d-flex flex-column flex-md-row align-items-stretch">
                    <!-- Name & ID Section -->
                    <div class="d-flex align-items-center px-4 py-2" style="min-width: 220px; background-color: #01284E !important;">
                        <div class="avtar avtar-m bg-white bg-opacity-25 text-white rounded-circle me-3">
                            <i class="ti ti-user fs-4"></i>
                        </div>
                        <div>
                            <h5 class="text-white fw-bold mb-0 lh-1">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</h5>
                            <span class="text-white text-opacity-75" style="font-size: 0.65rem; letter-spacing: 1px; text-transform: uppercase; font-weight: 700;">Student Portal</span>
                        </div>
                    </div>
                    
                    <!-- Info Section -->
                    <div class="flex-grow-1 bg-white d-flex align-items-center px-4 py-2 border-start">
                        <div class="row w-100 g-3">
                            <div class="col-md-6 col-lg-4">
                                <div class="d-flex align-items-center">
                                    <div class="text-primary me-2">
                                        <i class="ti ti-mail fs-5"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="text-muted mb-0" style="font-size: 0.65rem; font-weight: 600; text-uppercase;">Email</p>
                                        <h6 class="mb-0 fw-bold text-truncate" style="font-size: 0.85rem;">{{ auth()->user()->email }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 border-start d-none d-md-block">
                                <div class="d-flex align-items-center ms-md-3">
                                    <div class="text-success me-2">
                                        <i class="ti ti-phone fs-5"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted mb-0" style="font-size: 0.65rem; font-weight: 600; text-uppercase;">Phone</p>
                                        <h6 class="mb-0 fw-bold" style="font-size: 0.85rem;">{{ auth()->user()->phone ?? 'N/A' }}</h6>
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

<div class="row mb-3">
    <!-- Stats Cards - Three Column Mode -->
    <div class="col-6 col-lg-4 mb-3 mb-lg-0">
        <div class="card mb-0 shadow-sm border-0" style="border-radius: 12px; height: 100%;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="mb-0 fw-bold text-muted small text-uppercase">Total Enrolled</h6>
                    <div class="bg-light text-primary rounded p-1"><i class="ti ti-book fs-5"></i></div>
                </div>
                <h3 class="mb-0 fw-bold text-dark">{{ $stats['enrolled'] }}</h3>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-4 mb-3 mb-lg-0">
        <div class="card mb-0 shadow-sm border-0" style="border-radius: 12px; height: 100%;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="mb-0 fw-bold text-muted small text-uppercase">Success Rate</h6>
                    <div class="bg-light text-info rounded p-1"><i class="ti ti-activity fs-5"></i></div>
                </div>
                <h3 class="mb-0 fw-bold text-dark">{{ round($stats['success_rate']) }}%</h3>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card mb-0 shadow-sm border-0" style="border-radius: 12px; height: 100%;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="mb-0 fw-bold text-muted small text-uppercase">Passed Exams</h6>
                    <div class="bg-light text-success rounded p-1"><i class="ti ti-trophy fs-5"></i></div>
                </div>
                <h3 class="mb-0 fw-bold text-dark">{{ $stats['passed_exams'] }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Active Exams Section -->
    <div class="col-lg-8 d-flex flex-column">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold text-dark">My Active Exams</h5>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th class="px-4 py-3 border-0 text-muted small fw-bold">EXAM NAME</th>
                                <th class="px-4 py-3 border-0 text-center text-muted small fw-bold">ATTEMPTS</th>
                                <th class="px-4 py-3 border-0 text-muted small fw-bold">PROGRESS</th>
                                <th class="px-4 py-3 border-0 text-end text-muted small fw-bold">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchasedExams as $exam)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avtar bg-primary-subtle text-primary rounded-circle me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                            <i class="ti ti-notes fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">{{ $exam->title }}</h6>
                                            <span class="text-muted" style="font-size: 0.75rem;">{{ $exam->duration }}m | {{ $exam->exam_code }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="fw-bold text-slate-700">{{ $exam->attempts_taken }}</span><span class="text-muted">/{{ $exam->max_attempts }}</span>
                                </td>
                                <td class="px-4 py-3" style="width: 150px;">
                                    @php
                                        $percent = ($exam->attempts_taken / $exam->max_attempts) * 100;
                                        $color = $percent >= 100 ? 'bg-danger' : ($percent >= 66 ? 'bg-warning' : 'bg-success');
                                    @endphp
                                    <div class="progress" style="height: 5px; border-radius: 10px;">
                                        <div class="progress-bar {{ $color }}" style="width: {{ $percent }}%"></div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    @if($exam->can_attempt)
                                        <a href="{{ route('exams.start', $exam->id) }}" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold d-inline-flex align-items-center gap-2 shadow-sm" style="white-space: nowrap;">
                                            Start <i class="ti ti-chevron-right fs-6"></i>
                                        </a>
                                    @else
                                        <span class="badge bg-light text-muted border px-2 py-1">LOCKED</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="py-4">
                                        <div class="mb-3">
                                            <div class="avtar avtar-xl bg-light-primary text-primary mx-auto shadow-sm">
                                                <i class="ti ti-notes fs-1"></i>
                                            </div>
                                        </div>
                                        <h5 class="fw-bold text-slate-800 mb-1">No Active Exams</h5>
                                        <p class="text-muted mb-0 px-4 mx-auto" style="max-width: 320px;">You don't have any exams assigned to you yet. Please check back later or contact your administrator.</p>
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

    <!-- Recent History Sidebar -->
    <div class="col-lg-4 d-flex flex-column">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-slate-800">History</h5>
                <a href="{{ route('student.history') }}" class="text-primary fw-bold" style="font-size: 0.75rem; text-decoration: none;">
                    VIEW ALL <i class="ti ti-arrow-right"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless align-middle mb-0">
                        <tbody>
                            @forelse($attempts as $attempt)
                            <tr>
                                <td class="px-3 py-3">
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-slate-800 text-truncate" style="max-width: 130px; line-height: 1.2;">{{ $attempt->exam_title }}</span>
                                        <span class="text-muted small">{{ $attempt->date ? $attempt->date->format('d M') : 'N/A' }} • {{ $attempt->duration }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-end">
                                    <div class="d-flex flex-column align-items-end">
                                        <span class="fw-bold {{ $attempt->status == 'Pass' ? 'text-success' : 'text-danger' }}" style="font-size: 0.95rem;">{{ $attempt->score }} <small class="text-muted" style="font-size: 0.6rem;">PTS</small></span>
                                        <span class="badge {{ $attempt->status == 'Pass' ? 'bg-success' : 'bg-danger' }} rounded-pill" style="font-size: 0.6rem; padding: 2px 8px;">{{ strtoupper($attempt->status) }}</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center py-5">
                                    <div class="py-3">
                                        <div class="mb-2">
                                            <i class="ti ti-history-off fs-1 text-muted opacity-50"></i>
                                        </div>
                                        <h6 class="text-slate-600 fw-bold mb-1">No Recent Activity</h6>
                                        <p class="text-muted small px-3 mb-0">Complete an exam to see your history here.</p>
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
