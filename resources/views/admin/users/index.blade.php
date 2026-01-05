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
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item" aria-current="page">Students</li>
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
                            <label class="form-label fw-bold text-muted small mb-1">EXAM ATTEMPTS</label>
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
                                <th>Last Activity</th>
                                <th>Attempts</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($user->profile_picture)
                                            <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                                 alt="{{ $user->first_name }}" 
                                                 class="rounded-circle" 
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="avtar avtar-s bg-light-primary text-primary">
                                                {{ strtoupper(substr($user->first_name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="ms-3">
                                            <h6 class="mb-0">{{ $user->first_name }} {{ $user->last_name }}</h6>
                                        </div>
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
                                        // Always show the absolute latest activity for the user
                                        // regardless of the current filters
                                        $lastAttempt = $user->examAttempts()
                                            ->whereNotNull('started_at')
                                            ->orderBy('started_at', 'desc')
                                            ->with('studentExam.exam')
                                            ->first();
                                    @endphp
                                    
                                    @if($lastAttempt && $lastAttempt->started_at)
                                        <div class="d-flex flex-column">
                                            @if($lastAttempt->studentExam && $lastAttempt->studentExam->exam)
                                                <span class="fw-semibold text-dark mb-1" style="font-size: 0.85rem;">
                                                    {{ Str::limit($lastAttempt->studentExam->exam->name, 25) }}
                                                </span>
                                            @endif
                                            <span class="text-muted small">{{ $lastAttempt->started_at->format('M d, Y') }}</span>
                                            <span class="text-muted" style="font-size: 0.75rem;">{{ $lastAttempt->started_at->format('h:i A') }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">No attempts</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.attempts.by-user', $user->id) }}" class="badge bg-light-info text-info">
                                        {{ $user->studentExams->sum('attempts_allowed') - $user->studentExams->sum('attempts_used') }} / {{ $user->studentExams->sum('attempts_allowed') }} left
                                    </a>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.attempts.by-user', $user->id) }}" class="btn btn-icon btn-link-info btn-sm" title="View Attempts">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this student?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-link-danger btn-sm" title="Delete Student">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
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
    
    // Auto-submit on search input (debounced - 500ms delay)
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                filterForm.submit();
            }, 500);
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
@endsection
