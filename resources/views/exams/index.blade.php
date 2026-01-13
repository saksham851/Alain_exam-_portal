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

<div class="row">
    @forelse($exams as $exam)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge bg-light-primary text-primary">{{ $exam->category->name ?? 'General' }}</span>
                    <span class="text-muted text-sm"><i class="ti ti-clock me-1"></i>{{ $exam->duration_minutes }} mins</span>
                </div>
                
                <h4 class="card-title mb-2">{{ $exam->name }}</h4>
                <p class="card-text text-muted flex-fill">{{ Str::limit($exam->description ?? 'No description', 100) }}</p>
                
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <small class="text-muted"><i class="ti ti-list-numbers me-1"></i>Attempts: {{ $exam->attempts_left }}/{{ $exam->max_attempts }}</small>
                    <a href="{{ route('exams.show', $exam->id) }}" class="btn btn-primary btn-sm rounded-pill">View Details</a>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card text-center py-5">
            <div class="card-body">
                <i class="ti ti-folder-off f-40 text-muted mb-3 d-block"></i>
                <h5 class="card-title">No exams available</h5>
                <p class="card-text text-muted">Check back later for new assessments.</p>
            </div>
        </div>
    </div>
    @endforelse
</div>
@endsection
