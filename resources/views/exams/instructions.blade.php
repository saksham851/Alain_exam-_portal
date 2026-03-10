@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <!-- Exam Header -->
            <div class="mb-4 text-center">
                <h2 class="fw-bold text-dark mb-1">{{ $exam->name }}</h2>
                <p class="text-muted mb-0">{{ $exam->description ?? 'This exam is for ' . ($exam->category->name ?? 'General') }}</p>
            </div>

            <!-- Exam Info Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center p-3">
                            <i class="ti ti-clock text-primary f-24 mb-1 d-block"></i>
                            <p class="text-muted small mb-0">Duration</p>
                            <h5 class="fw-bold mb-0 text-dark">{{ $exam->duration_minutes }} mins</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center p-3">
                            <i class="ti ti-refresh text-success f-24 mb-1 d-block"></i>
                            <p class="text-muted small mb-0">Attempts Left</p>
                            <h5 class="fw-bold mb-0 text-dark">{{ $attemptsLeft }}/{{ $studentExam->attempts_allowed }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center p-3">
                            <i class="ti ti-calendar text-warning f-24 mb-1 d-block"></i>
                            <p class="text-muted small mb-0">Valid Until</p>
                            <h5 class="fw-bold mb-0 text-dark">{{ \Carbon\Carbon::parse($studentExam->expiry_date)->format('M d, Y') }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructions Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light-info border-0 py-3 px-4">
                    <h5 class="mb-0 text-info fw-bold">
                        <i class="ti ti-info-circle me-1"></i>Exam Instructions
                    </h5>
                </div>
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3">Please review the following before starting your exam:</h6>
                    
                    <div class="instruction-list">
                        <div class="d-flex align-items-start mb-3">
                            <i class="ti ti-circle-check text-success mt-1 me-2"></i>
                            <p class="text-dark mb-0">Once you start the exam, the timer cannot be paused or stopped.</p>
                        </div>
                        
                        <div class="d-flex align-items-start mb-3">
                            <i class="ti ti-circle-check text-success mt-1 me-2"></i>
                            <p class="text-dark mb-0">If you exit or cancel the exam after starting, the attempt will still be counted as completed.</p>
                        </div>

                        <div class="d-flex align-items-start mb-3">
                            <i class="ti ti-circle-check text-success mt-1 me-2"></i>
                            <p class="text-dark mb-0">Read each case study and question carefully before selecting your answer.</p>
                        </div>

                        <div class="d-flex align-items-start">
                            <i class="ti ti-circle-check text-success mt-1 me-2"></i>
                            <p class="text-dark mb-0">Make sure you are ready and have sufficient uninterrupted time before beginning. When you are ready, click <strong>Start Exam</strong>.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-3 justify-content-center">
                <form action="{{ route('exams.confirm-start', $exam->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary px-5 py-2">
                        <i class="ti ti-player-play me-1"></i>Start Exam
                    </button>
                </form>
                <a href="{{ route('exams.index') }}" class="btn btn-outline-secondary px-4 py-2">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.f-24 { font-size: 24px; }
.bg-light-primary { background-color: rgba(var(--bs-primary-rgb), 0.1) !important; }
.bg-light-success { background-color: rgba(var(--bs-success-rgb), 0.1) !important; }
.bg-light-warning { background-color: rgba(var(--bs-warning-rgb), 0.1) !important; }
.bg-light-info { background-color: rgba(13, 202, 240, 0.1) !important; }
</style>
@endsection
