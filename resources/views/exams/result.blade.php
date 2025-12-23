@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card text-center py-5">
            <div class="card-body">
                @if($attempt->is_passed)
                    <div class="mb-4">
                        <div class="avtar avtar-xl bg-light-success rounded-circle mx-auto">
                            <i class="ti ti-trophy f-40 text-success"></i>
                        </div>
                    </div>
                    <h2 class="text-success mb-2">Congratulations!</h2>
                    <p class="text-muted lead">You have successfully passed the exam.</p>
                @else
                    <div class="mb-4">
                        <div class="avtar avtar-xl bg-light-danger rounded-circle mx-auto">
                            <i class="ti ti-mood-sad f-40 text-danger"></i>
                        </div>
                    </div>
                    <h2 class="text-danger mb-2">Better Luck Next Time</h2>
                    <p class="text-muted lead">Unfortunately, you did not meet the passing criteria.</p>
                @endif

                <div class="row justify-content-center my-5">
                    <!-- Box 1: Score Breakdown -->
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light-secondary shadow-none border-0 h-100">
                            <div class="card-body">
                                <h6 class="text-muted mb-3">Score Breakdown</h6>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small">IG Score</span>
                                        <span class="fw-bold">{{ $attempt->ig_score }}%</span>
                                    </div>
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-primary" style="width: {{ $attempt->ig_score }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small">DM Score</span>
                                        <span class="fw-bold">{{ $attempt->dm_score }}%</span>
                                    </div>
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-warning" style="width: {{ $attempt->dm_score }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Box 2: Status -->
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light-secondary shadow-none border-0 h-100">
                            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                                <h6 class="text-muted">Status</h6>
                                <h3 class="mb-0 {{ $attempt->is_passed ? 'text-success' : 'text-danger' }}">
                                    {{ $attempt->is_passed ? 'PASSED' : 'FAILED' }}
                                </h3>
                            </div>
                        </div>
                    </div>

                    <!-- Box 3: Completed On -->
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light-secondary shadow-none border-0 h-100">
                            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                                <h6 class="text-muted">Completed On</h6>
                                <p class="mb-0 fs-5 fw-bold">{{ $attempt->ended_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="{{ route('exams.index') }}" class="btn btn-primary btn-lg px-4">
                    <i class="ti ti-arrow-left me-2"></i> Back to Exams
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
