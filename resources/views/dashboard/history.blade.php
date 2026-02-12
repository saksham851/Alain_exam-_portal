@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom-0 py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 text-dark fw-bold">My Exam History</h5>
                    <p class="text-muted small mb-0">Track all your previous exam attempts and performance.</p>
                </div>
                <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="ti ti-arrow-left me-1"></i> Back
                </a>
            </div>
            <div class="card-body pt-0">
                @if($attempts->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($attempts as $attempt)
                    <div class="list-group-item px-0 py-4">
                        <div class="row align-items-center">
                            <!-- Icon & Title -->
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <div class="avtar avtar-s bg-light-primary text-primary rounded-circle me-3 shadow-sm">
                                        <i class="ti ti-history f-20"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $attempt->studentExam->exam->name }}</h6>
                                        <small class="text-muted">{{ $attempt->studentExam->exam->exam_code }}</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Date & Time -->
                            <div class="col-md-3">
                                <div class="text-dark">
                                    <i class="ti ti-calendar-event me-1 text-muted"></i>
                                    {{ \Carbon\Carbon::parse($attempt->ended_at)->format('M d, Y h:i A') }}
                                </div>
                                <div class="small text-muted ps-4 d-flex align-items-center">
                                    {{ \Carbon\Carbon::parse($attempt->ended_at)->diffForHumans() }}
                                    <span class="mx-2">•</span>
                                    <i class="ti ti-clock me-1 text-primary"></i>
                                    <span class="text-primary fw-semibold">{{ $attempt->formatted_duration }}</span>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div class="col-md-2 text-center">
                                @if($attempt->is_passed)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                                        <i class="ti ti-circle-check me-1"></i> Pass
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2">
                                        <i class="ti ti-circle-x me-1"></i> Fail
                                    </span>
                                @endif
                            </div>

                            <!-- Score Card -->
                            <div class="col-md-2 text-center">
                                <h5 class="mb-0 fw-bold text-dark">
                                    {{ round($attempt->total_score) }}
                                </h5>
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Total Points</small>
                            </div>

                            <!-- Action -->
                            <div class="col-md-2 text-end">
                                <a href="{{ route('exams.result', $attempt->id) }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                    View Result <i class="ti ti-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="ti ti-history f-60 text-muted opacity-25"></i>
                    </div>
                    <h5 class="text-dark fw-bold">No exam history found</h5>
                    <p class="text-muted mb-4">You haven't attempted any exams yet. Start your first exam today!</p>
                    <a href="{{ route('exams.index') }}" class="btn btn-primary rounded-pill px-4">
                        Browse My Exams
                    </a>
                </div>
                @endif
            </div>
            
            @if($attempts->count() > 0)
                <x-custom-pagination :paginator="$attempts" />
            @endif
        </div>
    </div>
</div>

<style>
    .list-group-item {
        transition: all 0.2s ease;
    }
    .list-group-item:hover {
        background-color: #f8faff;
    }
    .badge {
        font-weight: 600;
        letter-spacing: 0.3px;
    }
    .bg-success-subtle { background-color: rgba(30, 212, 145, 0.1) !important; }
    .bg-danger-subtle { background-color: rgba(255, 76, 81, 0.1) !important; }
    .border-success-subtle { border-color: rgba(30, 212, 145, 0.2) !important; }
    .border-danger-subtle { border-color: rgba(255, 76, 81, 0.2) !important; }
</style>
@endsection
