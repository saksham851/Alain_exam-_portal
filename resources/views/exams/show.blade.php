@extends('layouts.app')

@section('content')
<div class="pt-2">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Exam Title Card -->
            <div class="card border mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-light-primary text-primary rounded p-3 me-3">
                            <i class="ti ti-notebook f-30"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold text-dark mb-1">{{ $exam->name }}</h2>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary text-white px-2 py-1">{{ $exam->category->name ?? 'General' }}</span>
                                <span class="text-muted small fw-bold">ID: {{ $exam->exam_code }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <div class="card border text-center h-100">
                        <div class="card-body py-4">
                            <i class="ti ti-clock text-muted f-24 mb-2"></i>
                            <h6 class="text-muted mb-1 small text-uppercase fw-bold">Duration</h6>
                            <h4 class="fw-bold mb-0 text-dark">{{ $exam->duration_minutes }} Mins</h4>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card border text-center h-100">
                        <div class="card-body py-4">
                            <i class="ti ti-repeat text-muted f-24 mb-2"></i>
                            <h6 class="text-muted mb-1 small text-uppercase fw-bold">Attempts Left</h6>
                            <h4 class="fw-bold mb-0 text-dark">{{ $exam->attempts_left }} / {{ $exam->max_attempts }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="card border mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold">Exam Description</h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-0 lh-lg">
                        {{ $exam->description ?? 'This examination session is designed to evaluate your comprehensive understanding of the subject matter. Please ensure you have reviewed all study materials before starting.' }}
                    </p>
                    @if($exam->certification_type)
                        <div class="mt-3 p-2 px-3 border rounded d-inline-block bg-light">
                            <i class="ti ti-certificate me-1"></i> <strong>Certification:</strong> {{ $exam->certification_type }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Instructions -->
            <div class="card border">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold">Important Instructions</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6 border-end">
                            <div class="mb-3">
                                <h6 class="fw-bold mb-1">1. Time Management</h6>
                                <p class="text-muted mb-0 small">The exam timer cannot be paused once started. If you close the browser, the timer continues to run.</p>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">2. Network Stability</h6>
                                <p class="text-muted mb-0 small">Ensure a stable internet connection. Use a laptop or desktop for the best experience.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="fw-bold mb-1">3. Navigation</h6>
                                <p class="text-muted mb-0 small">Do not use the browser back button or refresh the page during the exam.</p>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">4. Proctored Session</h6>
                                <p class="text-muted mb-0 small">Your session is monitored. Any switch to other browser tabs may be logged.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Actions -->
        <div class="col-lg-4">
            <div class="card border shadow-sm sticky-top" style="top: 20px;">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="ti ti-shield-check text-success f-40 mb-2"></i>
                        <h4 class="fw-bold mb-1">Ready to Start</h4>
                        <p class="text-muted small">Please verify all details before clicking the button below.</p>
                    </div>

                    <div class="border rounded p-3 mb-4 text-center bg-light">
                        <span class="text-muted small d-block mb-1">VALID UNTIL</span>
                        <h6 class="mb-0 fw-bold">{{ \Carbon\Carbon::parse($exam->expiry_date)->format('M d, Y') }}</h6>
                    </div>

                    @if($exam->attempts_left > 0)
                        @if(now() > $exam->expiry_date)
                            <button class="btn btn-secondary w-100 py-3 disabled" disabled>Exam Expired</button>
                        @else
                            <a href="{{ route('exams.start', $exam->id) }}" class="btn btn-primary w-100 py-3 fw-bold">
                                START NEW ATTEMPT
                            </a>
                        @endif
                    @else
                        <div class="alert alert-danger px-3 py-3 border-0 rounded text-center mb-0">
                            <p class="mb-0 small fw-bold">No attempts remaining for this exam.</p>
                        </div>
                    @endif

                    <div class="mt-4 text-center p-2 border-top">
                        <span class="text-muted x-small"><i class="ti ti-lock me-1"></i> Secure exam session active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .f-30 { font-size: 30px; }
    .f-24 { font-size: 24px; }
    .f-40 { font-size: 40px; }
    .x-small { font-size: 0.75rem; }
    .bg-light-primary { background-color: rgba(70, 128, 255, 0.1); }
   
    .card {
        border-radius: 8px;
        transition: none !important;
    }
</style>
@endsection
