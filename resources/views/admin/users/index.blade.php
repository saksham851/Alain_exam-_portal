@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Student Management</h5>
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
                <h5>
                    All Students 
                    <span class="badge bg-light-secondary ms-2">{{ $users->total() }} Total</span>
                </h5>
            </div>
            
            <!-- Compact Filters Section -->
            <div class="card-body bg-light-subtle py-3 border-bottom">
                <form method="GET" action="{{ route('admin.users.index') }}" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <!-- Search -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted small mb-1">SEARCH STUDENT</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                       placeholder="Name or email..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <!-- Exam Name -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">EXAM</label>
                            <select name="exam_id" class="form-select form-select-sm">
                                <option value="">All Exams</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                                        {{ $exam->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Exam Category -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">EXAM CATEGORY</label>
                            <select name="category_id" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Attempts -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1">TOTAL ATTEMPTS</label>
                            <input type="number" name="attempts" class="form-control form-control-sm" 
                                   placeholder="Attempts" min="0" value="{{ request('attempts') }}">
                        </div>

                        <!-- Buttons -->
                        <div class="col-md-3">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-light-secondary px-3" title="Reset">
                                    <i class="ti ti-rotate"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Active Filters Indicator -->
            @if(request()->hasAny(['search', 'exam_id', 'category_id', 'attempts']))
            <div class="card-body border-top border-bottom bg-light-subtle py-3">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="text-muted small fw-semibold">
                        <i class="ti ti-filter-check me-1"></i>ACTIVE FILTERS:
                    </span>
                    @if(request('search'))
                        <span class="badge rounded-pill bg-dark">
                            <i class="ti ti-search me-1"></i>{{ request('search') }}
                        </span>
                    @endif
                    @if(request('exam_id'))
                        <span class="badge rounded-pill bg-primary">
                            <i class="ti ti-book me-1"></i>{{ $exams->firstWhere('id', request('exam_id'))->name ?? 'Unknown' }}
                        </span>
                    @endif
                    @if(request('category_id'))
                        <span class="badge rounded-pill bg-info">
                            <i class="ti ti-category me-1"></i>{{ $categories->firstWhere('id', request('category_id'))->name ?? 'Unknown' }}
                        </span>
                    @endif
                    @if(request('attempts'))
                        <span class="badge rounded-pill bg-success">
                            <i class="ti ti-clipboard-list me-1"></i>{{ request('attempts') }} Attempts
                        </span>
                    @endif
                </div>
            </div>
            @endif
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Last Login</th>
                                <th>Last Submission</th>
                                <th>Total Attempts</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>
                                    <div>
                                        <h6 class="mb-0">{{ $user->first_name }} {{ $user->last_name }}</h6>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?? 'N/A' }}</td>
                                <td>
                                    @if($user->last_login_at)
                                        <div class="d-flex flex-column">
                                            <span class="text-muted small">{{ $user->last_login_at->format('M d, Y') }}</span>
                                            <span class="text-muted" style="font-size: 0.75rem;">{{ $user->last_login_at->format('h:i A') }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">Never</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        // Show the absolute latest submission (ended_at) for the user
                                        $lastAttempt = $user->examAttempts()
                                            ->whereNotNull('ended_at')
                                            ->orderBy('ended_at', 'desc')
                                            ->with('studentExam.exam')
                                            ->first();
                                    @endphp
                                    
                                    @if($lastAttempt && $lastAttempt->ended_at)
                                        <div class="d-flex flex-column">
                                            @if($lastAttempt->studentExam && $lastAttempt->studentExam->exam)
                                                <span class="fw-bold text-dark mb-1">
                                                    {{ $lastAttempt->studentExam->exam->name ?? 'Exam Name' }}
                                                </span>
                                                <span class="text-muted small mb-1" style="font-size: 0.75rem;">
                                                    {{ $lastAttempt->studentExam->exam->category->name ?? 'Cat' }} & 
                                                    {{ $lastAttempt->studentExam->exam->exam_code }} & 
                                                    {{ $lastAttempt->studentExam->exam->certification_type ?? 'Type' }}
                                                </span>
                                            @endif
                                            <span class="text-dark small">{{ $lastAttempt->ended_at->format('M d, Y') }}</span>
                                            <span class="text-muted" style="font-size: 0.75rem;">{{ $lastAttempt->ended_at->format('H:i') }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">No submissions</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" 
                                            class="badge bg-light-info text-info border-0" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#manageAttemptsModal"
                                            onclick="openManageAttemptsModal({{ $user->id }}, '{{ $user->first_name }} {{ $user->last_name }}', '{{ $user->email }}')"
                                            style="cursor: pointer;">
                                        {{ $user->studentExams->sum('attempts_allowed') - $user->studentExams->sum('attempts_used') }} / {{ $user->studentExams->sum('attempts_allowed') }} left
                                    </button>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn p-0 text-secondary bg-transparent border-0 shadow-none" type="button" 
                                                data-bs-toggle="dropdown" 
                                                data-bs-boundary="viewport" 
                                                data-bs-popper-config='{"strategy":"fixed"}'
                                                aria-expanded="false">
                                            <i class="ti ti-dots-vertical f-18"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.users.show', $user->id) }}">
                                                    <i class="ti ti-eye me-2"></i>View Profile
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.attempts.by-user', $user->id) }}">
                                                    <i class="ti ti-trophy me-2"></i>View Result
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            {{-- Custom Pagination --}}
            @if(method_exists($users, 'links'))
                <x-custom-pagination :paginator="$users" />
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const searchInput = filterForm.querySelector('input[name="search"]');
    const examSelect = filterForm.querySelector('select[name="exam_id"]');
    const categorySelect = filterForm.querySelector('select[name="category_id"]');
    const attemptsInput = filterForm.querySelector('input[name="attempts"]');
    
    let searchTimeout;
    
    // Auto-submit on dropdown change (instant)
    if (examSelect) {
        examSelect.addEventListener('change', function() {
            filterForm.submit();
        });
    }
    
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            filterForm.submit();
        });
    }
    
    // Auto-submit on search input (debounced - 1000ms delay)
    if (searchInput) {
        if (searchInput.value) {
            searchInput.focus();
            const val = searchInput.value;
            searchInput.value = '';
            searchInput.value = val;
        }

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                filterForm.submit();
            }, 1000);
        });
    }
    
    // Auto-submit on attempts input (debounced - 500ms delay)
    if (attemptsInput) {
        attemptsInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                filterForm.submit();
            }, 500);
        });
    }
});
</script>

<script>
// Quick adjust attempts function
function quickAdjustAttempts(studentId, examId, adjustment, examName) {
    if (!confirm(`Adjust attempts for "${examName}" by ${adjustment > 0 ? '+' : ''}${adjustment}?`)) {
        return;
    }

    fetch('/admin/users/manage-attempts', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            student_id: studentId,
            exam_id: examId,
            attempts_adjustment: adjustment
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update attempts');
    });
}
</script>

@include('admin.users.partials.manage_attempts_modal')

<script>
// Initialize Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

@endsection
