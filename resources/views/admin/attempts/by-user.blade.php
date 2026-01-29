@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Student Attempts - {{ $student->first_name }} {{ $student->last_name }}</h5>
        </div>

      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Exam Attempts</h5>
                        <p class="text-muted mb-0">{{ $student->email }}</p>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                        <i class="ti ti-arrow-left me-1"></i> Back to Students
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Exam Name</th>
                                <th>Duration</th>
                                <th>Started At</th>
                                <th>IG Score</th>
                                <th>DM Score</th>
                                <th>Total Score</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attempts as $attempt)
                            <tr>
                                <td>{{ $attempt->exam_name }}</td>
                                <td>
                                    <span class="text-muted fw-semibold">{{ $attempt->formatted_duration }}</span>
                                </td>
                                <td>{{ $attempt->started_at->format('M d, Y H:i') }}</td>
                                <td>{{ round($attempt->ig_score, 1) }}%</td>
                                <td>{{ round($attempt->dm_score, 1) }}%</td>
                                <td><strong>{{ round($attempt->percentage, 1) }}%</strong></td>
                                <td>
                                    <span class="badge {{ $attempt->is_passed ? 'bg-success' : 'bg-danger' }}">
                                        {{ $attempt->is_passed ? 'Passed' : 'Failed' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.attempts.show', $attempt->id) }}" class="btn btn-icon btn-link-primary btn-sm">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No attempts found for this student.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($attempts->hasPages())
                <div class="p-3">
                    {{ $attempts->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
