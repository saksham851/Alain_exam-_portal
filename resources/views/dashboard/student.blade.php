@extends('layouts.app')

@section('content')
<div class="row">
    {{-- User Profile Card --}}
    <div class="col-md-12 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="avtar avtar-lg bg-white text-primary rounded-circle me-3">
                        <i class="ti ti-user f-24"></i>
                    </div>
                    <div>
                        <h4 class="text-white mb-0">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</h4>
                        <p class="text-white-50 mb-0">{{ auth()->user()->email }}</p>
                        <span class="badge bg-light-success text-success mt-1">{{ auth()->user()->status == 1 ? 'Active' : 'Inactive' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Purchased Exams --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Purchased Exams</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Exam Title</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Attempts</th>
                                <th>Action</th>
                                <th>Answer</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchasedExams as $exam)
                            <tr>
                                <td>
                                    <h6 class="mb-0">{{ $exam->title }}</h6>
                                </td>
                                <td>
                                    <i class="ti ti-clock me-1"></i>{{ $exam->duration }} mins
                                </td>
                                <td>
                                    @if($exam->status === 'active')
                                        <span class="badge bg-light-success text-success">Active</span>
                                    @else
                                        <span class="badge bg-light-danger text-danger">Expired</span>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-bold text-primary">{{ $exam->attempts_left }}</span> left
                                    </div>
                                    <small class="text-muted">{{ $exam->attempts_taken }}/{{ $exam->max_attempts }} used</small>
                                </td>
                                <td>
                                    @if($exam->can_attempt)
                                        <a href="{{ route('exams.show', $exam->id) }}" class="btn btn-primary btn-sm rounded-pill">
                                            <i class="ti ti-play "></i>Start
                                        </a>
                                    @elseif($exam->status === 'expired')
                                        <button class="btn btn-outline-secondary btn-sm rounded-pill" disabled>
                                            <i class="ti ti-clock-x me-1"></i>Expired
                                        </button>
                                    @else
                                        <button class="btn btn-outline-secondary btn-sm rounded-pill" disabled>
                                            <i class="ti ti-lock me-1"></i>No Attempts
                                        </button>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('exams.answer-key', $exam->id) }}" class="btn btn-sm btn-outline-success rounded-pill" title="Download Answer Key">
                                        <i class="ti ti-download"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Past Attempts --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Recent History</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($attempts as $attempt)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ $attempt->exam_title }}</h6>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($attempt->date)->diffForHumans() }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge {{ $attempt->status == 'Pass' ? 'bg-light-success text-success' : 'bg-light-danger text-danger' }}">{{ $attempt->status }}</span>
                                <div class="f-12 fw-bold mt-1">{{ $attempt->score }} pts</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="p-3 text-center">
                    <a href="{{ route('student.history') }}" class="link-primary">View All History</a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Change Password Modal --}}
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="alert('Password changed successfully (Mock)')">Save changes</button>
            </div>
        </div>
    </div>
</div>
@endsection
