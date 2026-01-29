@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Available Exams</h5>
        </div>

      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row g-4">
    @forelse($exams as $exam)
    <div class="col-md-6 col-lg-4 col-xl-4">
        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
            <div class="card-body p-4 d-flex flex-column">
                <!-- Header: Category & Status -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge bg-light-info text-info border border-info px-2 py-1 rounded-pill">
                        {{ $exam->category->name ?? 'General' }}
                    </span>
                    @if(now() > $exam->expiry_date)
                        <span class="badge bg-light-danger text-danger"><i class="ti ti-clock-off me-1"></i>Expired</span>
                    @else
                        <span class="badge bg-light-success text-success"><i class="ti ti-circle-check me-1"></i>Active</span>
                    @endif
                </div>

                <!-- Title & Code -->
                <div class="mb-3">
                    <h5 class="fw-bold text-dark mb-1">{{ $exam->name }}</h5>
                    <div class="d-flex align-items-center gap-2">
                         <span class="badge bg-light-secondary text-secondary small">{{ $exam->exam_code }}</span>
                         @if($exam->certification_type)
                            <span class="text-muted small border-start ps-2">{{ $exam->certification_type }}</span>
                         @endif
                    </div>
                </div>

                <!-- Description -->
                <p class="text-muted small flex-fill mb-4">
                    {{ Str::limit($exam->description ?? 'No description available for this exam.', 90) }}
                </p>

                <!-- Key Metrics Grid -->
                <div class="row g-2 mb-4 bg-light-subtle rounded p-2 mx-0">
                    <div class="col-6">
                        <div class="d-flex align-items-center mb-1">
                            <i class="ti ti-clock text-primary me-2 f-18"></i>
                            <span class="text-muted small">Duration</span>
                        </div>
                        <span class="fw-semibold">{{ $exam->duration_minutes }} mins</span>
                    </div>
                    <div class="col-6 border-start ps-3">
                        <div class="d-flex align-items-center mb-1">
                            <i class="ti ti-list-check text-warning me-2 f-18"></i>
                            <span class="text-muted small">Attempts</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="fw-semibold">{{ $exam->attempts_used ?? ($exam->max_attempts - $exam->attempts_left) }}</span>
                            <span class="text-muted small mx-1">/</span>
                            <span class="fw-semibold">{{ $exam->max_attempts }}</span>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mb-4">
                    @php
                        $used = $exam->attempts_used ?? ($exam->max_attempts - $exam->attempts_left);
                        $percent = ($used / $exam->max_attempts) * 100;
                        $barColor = $percent >= 100 ? 'bg-danger' : 'bg-primary';
                    @endphp
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Usage</span>
                        <span class="text-{{ $percent >= 100 ? 'danger' : 'primary' }} fw-bold">{{ round($percent) }}%</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar {{ $barColor }}" role="progressbar" style="width: {{ $percent }}%"></div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-grid gap-2 mt-auto">
                    @if(now() > $exam->expiry_date)
                        <button class="btn btn-light-secondary disabled" disabled>
                            <i class="ti ti-lock me-1"></i> Exam Expired
                        </button>
                    @elseif($exam->attempts_left <= 0)
                         <div class="btn-group">
                            <button class="btn btn-light-warning disabled w-100" disabled>
                                <i class="ti ti-lock me-1"></i> Attempts Exhausted
                            </button>
                            <a href="{{ route('exams.show', $exam->id) }}" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="View Details">
                                <i class="ti ti-eye"></i>
                            </a>
                        </div>
                    @else
                        <div class="d-flex gap-2">
                            <a href="{{ route('exams.start', $exam->id) }}" class="btn btn-primary flex-grow-1">
                                <i class="ti ti-player-play me-1"></i> Start Exam
                            </a>
                            <a href="{{ route('exams.show', $exam->id) }}" class="btn btn-light-secondary icon-btn" data-bs-toggle="tooltip" title="View Details">
                                <i class="ti ti-eye"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm py-5 text-center">
            <div class="card-body">
                <img src="{{ asset('assets/images/no-data.svg') }}" alt="No Exams" class="img-fluid mb-4" style="max-height: 150px; opacity: 0.6;">
                <h5 class="fw-bold text-dark">No Active Exams Found</h5>
                <p class="text-muted mb-0">It seems there are no exams assigned to you at the moment.</p>
                <p class="text-muted small">Please contact your administrator if you believe this is an error.</p>
            </div>
        </div>
    </div>
    @endforelse
</div>

<style>
.hover-shadow:hover {
    box_shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
.transition-all {
    transition: all 0.3s ease;
}
.icon-btn {
    width: 42px;
    padding-left: 0;
    padding-right: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endsection
