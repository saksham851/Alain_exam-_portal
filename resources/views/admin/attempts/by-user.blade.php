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
                        @php
                            $headers = [];
                            if($attempts->count() > 0) {
                                foreach($attempts as $a) {
                                    if(is_array($a->category_breakdown)) {
                                        foreach($a->category_breakdown as $c) {
                                            $headers[$c['name']] = $c['name'];
                                        }
                                    }
                                }
                            }
                        @endphp
                        <thead>
                            <tr>
                                <th>Exam Name</th>
                                <th>Duration</th>
                                <th>Started At</th>
                                @foreach($headers as $headerName)
                                    <th>{{ $headerName }}</th>
                                @endforeach
                                <th>Total Points</th>
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
                                <td>{{ $attempt->formatted_start_time }}</td>
                                @foreach($headers as $headerName)
                                    @php
                                        $earned = 0;
                                        if(is_array($attempt->category_breakdown)) {
                                            foreach($attempt->category_breakdown as $c) {
                                                if($c['name'] == $headerName) {
                                                    $earned = $c['earned_points'] ?? 0;
                                                    break;
                                                }
                                            }
                                        }
                                    @endphp
                                    <td>{{ $earned }} pts</td>
                                @endforeach
                                <td><strong>{{ $attempt->total_score }} pts</strong></td>
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
            <x-custom-pagination :paginator="$attempts" label="attempts" />
        </div>
    </div>
</div>
@endsection
