@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">{{ $exam->name }}</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">Exams</a></li>
          <li class="breadcrumb-item" aria-current="page">{{ $exam->name }}</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h3>{{ $exam->name }}</h3>
                        <p class="text-muted">{{ $exam->description }}</p>
                    </div>
                    <span class="badge bg-light-primary text-primary fs-6">{{ $exam->duration }} mins</span>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="avtar avtar-s bg-light-primary rounded me-3">
                                <i class="ti ti-clock text-primary"></i>
                            </div>
                            <div>
                                <p class="mb-0 text-muted small">Duration</p>
                                <h6 class="mb-0">{{ $exam->duration }} minutes</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="avtar avtar-s bg-light-success rounded me-3">
                                <i class="ti ti-circle-check text-success"></i>
                            </div>
                            <div>
                                <p class="mb-0 text-muted small">Attempts Left</p>
                                <h6 class="mb-0">{{ $exam->attempts_left }}/{{ $exam->max_attempts }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="avtar avtar-s bg-light-warning rounded me-3">
                                <i class="ti ti-calendar text-warning"></i>
                            </div>
                            <div>
                                <p class="mb-0 text-muted small">Valid Until</p>
                                <h6 class="mb-0">{{ \Carbon\Carbon::parse($exam->expiry_date)->format('M d, Y') }}</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <h6 class="alert-heading"><i class="ti ti-info-circle me-1"></i> Instructions</h6>
                    <ul class="mb-0">
                        <li>Read each question carefully before answering</li>
                        <li>You have {{ $exam->duration }} minutes to complete the exam</li>
                        <li>You can attempt this exam {{ $exam->max_attempts }} times</li>
                        <li>This exam expires on {{ \Carbon\Carbon::parse($exam->expiry_date)->format('M d, Y') }}</li>
                    </ul>
                </div>

                <div class="text-center mt-4">
                    @if($exam->attempts_left > 0)
                        <form action="{{ route('exams.start', $exam->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="ti ti-play me-2"></i> Start Exam
                            </button>
                        </form>
                    @else
                        <button class="btn btn-secondary btn-lg" disabled>
                            <i class="ti ti-lock me-2"></i> No Attempts Left
                        </button>
                    @endif
                    <a href="{{ route('exams.index') }}" class="btn btn-outline-secondary btn-lg ms-2">
                        <i class="ti ti-arrow-left me-2"></i> Back to Exams
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
