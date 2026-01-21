@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Exam Header -->
            <div class="mb-4">
                <h2 class="fw-bold text-dark mb-2">{{ $exam->name }}</h2>
                <p class="text-muted mb-0">{{ $exam->description ?? 'This exam is only for ' . ($exam->category->name ?? 'General') }}</p>
            </div>

            <!-- Exam Info Cards -->
            <div class="row g-3 mb-4">
                <!-- Duration -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center p-3">
                            <div class="bg-light-primary text-primary rounded-circle p-3 d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                                <i class="ti ti-clock f-24"></i>
                            </div>
                            <p class="text-muted small mb-1">Duration</p>
                            <h4 class="fw-bold mb-0">{{ $exam->duration_minutes }} minutes</h4>
                        </div>
                    </div>
                </div>

                <!-- Attempts Left -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center p-3">
                            <div class="bg-light-success text-success rounded-circle p-3 d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                                <i class="ti ti-refresh f-24"></i>
                            </div>
                            <p class="text-muted small mb-1">Attempts Left</p>
                            <h4 class="fw-bold mb-0">{{ $attemptsLeft }}/{{ $studentExam->attempts_allowed }}</h4>
                        </div>
                    </div>
                </div>

                <!-- Valid Until -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center p-3">
                            <div class="bg-light-warning text-warning rounded-circle p-3 d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                                <i class="ti ti-calendar f-24"></i>
                            </div>
                            <p class="text-muted small mb-1">Valid Until</p>
                            <h4 class="fw-bold mb-0" style="font-size: 1rem;">{{ \Carbon\Carbon::parse($studentExam->expiry_date)->format('M d, Y') }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructions Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light-info border-0 py-3">
                    <h5 class="mb-0 text-info fw-bold">
                        <i class="ti ti-info-circle me-2"></i>Instructions
                    </h5>
                </div>
                <div class="card-body p-4">
                    <ul class="mb-0 ps-3">
                        <li class="mb-2 text-dark">Read each question carefully before answering</li>
                        <li class="mb-2 text-dark">You have {{ $exam->duration_minutes }} minutes to complete the exam</li>
                        <li class="mb-2 text-dark">You can attempt this exam {{ $studentExam->attempts_allowed }} times</li>
                        <li class="mb-0 text-dark">This exam expires on {{ \Carbon\Carbon::parse($studentExam->expiry_date)->format('F d, Y') }}</li>
                    </ul>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-3 justify-content-center">
                <form action="{{ route('exams.confirm-start', $exam->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="ti ti-player-play me-2"></i>Start Exam
                    </button>
                </form>
                <a href="{{ route('exams.index') }}" class="btn btn-light-secondary btn-lg px-4">
                    <i class="ti ti-arrow-left me-2"></i>Back to Exams
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.bg-light-primary {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}
.bg-light-success {
    background-color: rgba(var(--bs-success-rgb), 0.1) !important;
}
.bg-light-warning {
    background-color: rgba(var(--bs-warning-rgb), 0.1) !important;
}
.bg-light-info {
    background-color: rgba(13, 202, 240, 0.1) !important;
}
</style>
@endsection
