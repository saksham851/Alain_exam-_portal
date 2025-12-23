@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom-0 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark fw-bold">My Exam History</h5>
                <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
            <div class="card-body">
                @if($attempts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light text-uppercase text-muted small">
                            <tr>
                                <th class="ps-4">Exam Name</th>
                                <th>Attempt Date</th>
                                <th>Status</th>
                                <th>Score</th>
                                <th class="text-end pe-4">Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attempts as $attempt)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avtar avtar-s bg-light-primary text-primary rounded-circle me-3">
                                            <i class="ti ti-history f-20"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">{{ $attempt->studentExam->exam->name }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span>{{ \Carbon\Carbon::parse($attempt->ended_at)->format('M d, Y h:i A') }}</span>
                                    <br>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($attempt->ended_at)->diffForHumans() }}</small>
                                </td>
                                <td>
                                    @if($attempt->is_passed)
                                        <span class="badge bg-light-success text-success">Pass</span>
                                    @else
                                        <span class="badge bg-light-danger text-danger">Fail</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-dark">
                                    {{ round($attempt->total_score) }}%
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('exams.result', $attempt->id) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                                        View Result <i class="ti ti-arrow-right ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    {{ $attempts->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="ti ti-history f-40 text-muted mb-3 d-block"></i>
                    <h5 class="text-dark">No exam history found</h5>
                    <p class="text-muted">You haven't attempted any exams yet.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
