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

      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Exam Details</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">My Exams</a></li>
          <li class="breadcrumb-item" aria-current="page">{{ $exam->name }}</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row">
    <!-- Left Column: Details & History -->
    <div class="col-lg-8">
        <!-- Exam Header Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                             <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">{{ $exam->category->name ?? 'General' }}</span>
                             <span class="badge bg-light-secondary text-secondary">{{ $exam->exam_code }}</span>
                        </div>
                        <h2 class="fw-bold text-dark mb-2">{{ $exam->name }}</h2>
                        @if($exam->certification_type)
                            <p class="text-muted mb-0"><i class="ti ti-certificate me-1"></i>{{ $exam->certification_type }}</p>
                        @endif
                    </div>
                    <div class="text-end">
                        <h3 class="mb-0 text-primary">{{ $exam->duration_minutes }} <span class="fs-6 text-muted">mins</span></h3>
                        <small class="text-muted">Duration</small>
                    </div>
                </div>
                
                <h5 class="fw-semibold mt-4 mb-2">Description</h5>
                <p class="text-muted mb-0">{{ $exam->description ?? 'No detailed description available for this exam.' }}</p>

                @php
                    $totalSections = $exam->sections->count();
                    $totalCaseStudies = 0;
                    $totalQuestions = 0;
                    foreach($exam->sections as $section) {
                        $totalCaseStudies += $section->caseStudies->count();
                        foreach($section->caseStudies as $caseStudy) {
                            $totalQuestions += $caseStudy->questions->count();
                        }
                    }
                @endphp

                <div class="row g-0 mt-4 pt-4 border-top">
                    <div class="col-4 border-end">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <div class="bg-light-primary text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="ti ti-folders f-20"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">{{ $totalSections }}</h5>
                                <small class="text-muted d-block" style="line-height: 1;">Sections</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-4 border-end">
                         <div class="d-flex align-items-center justify-content-center gap-2">
                            <div class="bg-light-info text-info rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="ti ti-files f-20"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">{{ $totalCaseStudies }}</h5>
                                <small class="text-muted d-block" style="line-height: 1;">Case Studies</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                         <div class="d-flex align-items-center justify-content-center gap-2">
                            <div class="bg-light-warning text-warning rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="ti ti-help f-20"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">{{ $totalQuestions }}</h5>
                                <small class="text-muted d-block" style="line-height: 1;">Questions</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attempts History -->
        <div class="card border-0 shadow-sm" style="min-height: 406px;">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="mb-0 fw-bold"><i class="ti ti-history me-2"></i>Attempt History</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>Scores (IG / DM)</th>
                                <th>Total Score</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attempts as $attempt)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold text-dark">{{ $attempt->created_at->format('M d, Y') }}</span>
                                        <small class="text-muted">{{ $attempt->created_at->format('h:i A') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="d-flex flex-column align-items-center">
                                            <small class="text-muted text-uppercase" style="font-size: 0.65rem;">IG Score</small>
                                            <span class="fw-semibold">{{ round($attempt->ig_score ?? 0) }}%</span>
                                        </div>
                                        <div class="vr opacity-25"></div>
                                        <div class="d-flex flex-column align-items-center">
                                            <small class="text-muted text-uppercase" style="font-size: 0.65rem;">DM Score</small>
                                            <span class="fw-semibold">{{ round($attempt->dm_score ?? 0) }}%</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold fs-6 {{ $attempt->is_passed ? 'text-success' : 'text-danger' }}">
                                        {{ round($attempt->total_score) }}%
                                    </span>
                                </td>
                                <td>
                                    @if($attempt->is_passed)
                                        <span class="badge bg-light-success text-success border border-success">Passed</span>
                                    @else
                                        <span class="badge bg-light-danger text-danger border border-danger">Failed</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="input-group input-group-sm justify-content-end">
                                        <a href="{{ route('exams.result', $attempt->id) }}" class="btn btn-light-primary" data-bs-toggle="tooltip" title="View Detailed Analysis">
                                            <i class="ti ti-chart-bar me-1"></i> Result
                                        </a>

                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="ti ti-history-toggle text-muted f-30"></i>
                                    </div>
                                    <h6 class="text-muted">No attempts yet</h6>
                                    <p class="text-muted small mb-0">Start the exam to see your history here.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Actions & Stats -->
    <div class="col-lg-4">
        <!-- Action Card -->
        <div class="card border-0 shadow-sm mb-4 bg-primary text-white overflow-hidden position-relative">
            <!-- Decorative circle -->
            <div style="position: absolute; top:-50px; right:-50px; width:150px; height:150px; background:rgba(255,255,255,0.1); border-radius:50%;"></div>
            
            <div class="card-body p-4 position-relative">
                <h5 class="text-white mb-3">Ready to start?</h5>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between text-white-50 small mb-1">
                        <span>Attempts Remaining</span>
                        <span>{{ $exam->attempts_left }} of {{ $exam->max_attempts }}</span>
                    </div>
                    @php
                        $used = $exam->max_attempts - $exam->attempts_left;
                        $percent = ($used / $exam->max_attempts) * 100;
                    @endphp
                    <div class="progress" style="height: 6px; background: rgba(255,255,255,0.2);">
                        <div class="progress-bar bg-white" role="progressbar" style="width: {{ $percent }}%"></div>
                    </div>
                </div>

                @if($exam->attempts_left > 0)
                    @if(now() > $exam->expiry_date)
                        <button class="btn btn-light w-100 disabled" disabled>
                            <i class="ti ti-clock-off me-2"></i> Exam Expired
                        </button>
                    @else
                        <form action="{{ route('exams.start', $exam->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-light w-100 fw-bold text-primary shadow-sm py-2">
                                <i class="ti ti-player-play me-2"></i> Start New Attempt
                            </button>
                        </form>
                    @endif
                @else
                    <button class="btn btn-light w-100 disabled opacity-75" disabled>
                        <i class="ti ti-lock me-2"></i> No Attempts Left
                    </button>
                @endif
                
                <div class="mt-3 text-center">
                    <small class="text-white-50">Valid until {{ \Carbon\Carbon::parse($exam->expiry_date)->format('M d, Y') }}</small>
                </div>
            </div>
        </div>

        <!-- Performance Summary -->
        @if($attempts->count() > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom py-3">
                <h6 class="mb-0 fw-bold">Performance Summary</h6>
            </div>
            <div class="card-body">
                <div class="row g-0">
                    <div class="col-6 border-end pe-3">
                        <small class="text-muted d-block mb-1">Best Score</small>
                        <h3 class="mb-0 fw-bold text-success">{{ round($attempts->max('total_score')) }}%</h3>
                    </div>
                    <div class="col-6 ps-3">
                        <small class="text-muted d-block mb-1">Average</small>
                        <h3 class="mb-0 fw-bold text-primary">{{ round($attempts->avg('total_score')) }}%</h3>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Instructions -->
        <div class="card border-0 shadow-sm" style="min-height: 323px;">
            <div class="card-header bg-transparent border-bottom py-3">
                <h6 class="mb-0 fw-bold"><i class="ti ti-info-circle me-2"></i>Important Instructions</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                    <li class="d-flex align-items-start text-muted">
                        <i class="ti ti-point me-2 mt-1 text-primary"></i>
                        <span>Read each question carefully before answering.</span>
                    </li>
                    <li class="d-flex align-items-start text-muted">
                        <i class="ti ti-point me-2 mt-1 text-primary"></i>
                        <span>Total duration is {{ $exam->duration_minutes }} minutes. Timer starts immediately.</span>
                    </li>
                    <li class="d-flex align-items-start text-muted">
                        <i class="ti ti-point me-2 mt-1 text-primary"></i>
                        <span>Ensure you have a stable internet connection.</span>
                    </li>
                    <li class="d-flex align-items-start text-muted">
                        <i class="ti ti-point me-2 mt-1 text-primary"></i>
                        <span>Do not refresh the page during the exam.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

@endsection
