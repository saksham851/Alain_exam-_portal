@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Attempt Results</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item" aria-current="page">Attempts</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Student Attempts</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Exam</th>
                                <th>Score</th>
                                <th>Date</th>
                                <th>Warnings</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attempts as $attempt)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avtar avtar-s bg-light-secondary text-secondary">
                                            {{ strtoupper(substr($attempt->student_name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-0">{{ $attempt->student_name ?? 'Unknown User' }}</h6>
                                            <small class="text-muted">{{ $attempt->student_email ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $attempt->exam_name ?? 'N/A' }}</td>
                                <td>
                                    @php
                                        // Pass threshold is 65%
                                        $isPassed = $attempt->total_score >= 65;
                                    @endphp
                                    <span class="badge {{ $isPassed ? 'bg-light-success text-success' : 'bg-light-danger text-danger' }}">
                                        {{ round($attempt->percentage, 1) }}%
                                    </span>
                                </td>
                                <td>{{ $attempt->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    @if($attempt->tab_switch_count > 0)
                                        <span class="badge bg-light-warning text-warning">{{ $attempt->tab_switch_count }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.attempts.show', $attempt->id) }}" class="btn btn-icon btn-link-primary btn-sm">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No attempts recorded yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                 <div class="p-3">
                     @if(method_exists($attempts, 'links'))
                        {{ $attempts->links() }}
                     @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
