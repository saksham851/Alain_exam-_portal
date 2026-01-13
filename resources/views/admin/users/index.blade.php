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
                                    <button type="button" class="btn btn-icon btn-link-primary btn-sm" 
                                            title="Manage Attempts" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#manageAttemptsModal"
                                            onclick="openManageAttemptsModal({{ $user->id }}, '{{ $user->first_name }} {{ $user->last_name }}', '{{ $user->email }}')">
                                        <i class="ti ti-edit"></i>
                                    </button>
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

<!-- Manage Attempts Modal - Compact Version -->
<div class="modal fade" id="manageAttemptsModal" tabindex="-1" aria-labelledby="manageAttemptsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 rounded">
            <div class="modal-header bg-primary text-white border-0">
                <h6 class="modal-title mb-0" id="manageAttemptsModalLabel">
                    <i class="ti ti-adjustments me-1"></i> Manage Attempts
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-3">
                <form id="manageAttemptsForm">
                    @csrf
                    <input type="hidden" id="studentId" name="student_id">
                    
                    <!-- Student Name -->
                    <div class="mb-3">
                        <small class="text-muted">Student:</small>
                        <div class="fw-semibold" id="studentName"></div>
                    </div>

                    <!-- Select Exam -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1">Select Exam</label>
                        <select class="form-select form-select-sm" id="examSelect" name="exam_id" required>
                            <option value="">Choose exam...</option>
                            <!-- Options will be populated dynamically based on user's assigned exams -->
                        </select>
                    </div>

                    <!-- Current Stats -->
                    <div id="currentAttemptsInfo" class="d-none mb-3">
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <small class="text-muted d-block">Total</small>
                                <strong class="text-primary" id="currentAttemptsAllowed">0</strong>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Used</small>
                                <strong class="text-secondary" id="currentAttemptsUsed">0</strong>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Left</small>
                                <strong class="text-success" id="currentAttemptsRemaining">0</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Adjust -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1">Adjust</label>
                        <div class="input-group">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="adjustAttempts(-1)">
                                <i class="ti ti-minus"></i>
                            </button>
                            <input type="number" class="form-control form-control-sm text-center fw-bold" 
                                   id="attemptsAdjustment" value="0" readonly>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="adjustAttempts(1)">
                                <i class="ti ti-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div id="newAttemptsPreview" class="d-none">
                        <div class="alert alert-success alert-sm py-2 mb-0">
                            <small><strong>New Total:</strong></small>
                            <span id="newAttemptsTotal" class="badge bg-success ms-2">0</span>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer p-2">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="saveAttempts()">
                    <i class="ti ti-check me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>


<script>
let currentStudentId = null;
let currentExamId = null;
let currentAttemptsData = {};

function openManageAttemptsModal(studentId, studentName, studentEmail) {
    currentStudentId = studentId;
    document.getElementById('studentId').value = studentId;
    document.getElementById('studentName').textContent = studentName;
    document.getElementById('attemptsAdjustment').value = 0;
    document.getElementById('currentAttemptsInfo').classList.add('d-none');
    document.getElementById('newAttemptsPreview').classList.add('d-none');
    
    // Clear and populate exam dropdown with only assigned exams
    const examSelect = document.getElementById('examSelect');
    examSelect.innerHTML = '<option value="">Loading...</option>';
    
    // Fetch user's assigned exams
    fetch(`/admin/users/${studentId}/assigned-exams`)
        .then(response => response.json())
        .then(data => {
            examSelect.innerHTML = '<option value="">Choose exam...</option>';
            
            if (data.success && data.exams.length > 0) {
                data.exams.forEach(exam => {
                    const option = document.createElement('option');
                    option.value = exam.exam_id;
                    option.textContent = exam.exam_name;
                    examSelect.appendChild(option);
                });
            } else {
                examSelect.innerHTML = '<option value="">No exams assigned</option>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            examSelect.innerHTML = '<option value="">Error loading exams</option>';
        });
}

// When exam is selected, fetch current attempts
document.getElementById('examSelect').addEventListener('change', function() {
    const examId = this.value;
    currentExamId = examId;
    
    if (!examId || !currentStudentId) {
        document.getElementById('currentAttemptsInfo').classList.add('d-none');
        document.getElementById('newAttemptsPreview').classList.add('d-none');
        return;
    }

    // Fetch current attempts via AJAX
    fetch(`/admin/users/${currentStudentId}/exam/${examId}/attempts`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentAttemptsData = data.data;
                document.getElementById('currentAttemptsAllowed').textContent = data.data.attempts_allowed;
                document.getElementById('currentAttemptsUsed').textContent = data.data.attempts_used;
                document.getElementById('currentAttemptsRemaining').textContent = data.data.attempts_remaining;
                document.getElementById('currentAttemptsInfo').classList.remove('d-none');
                document.getElementById('attemptsAdjustment').value = 0;
                updatePreview();
            } else {
                // Exam not assigned yet
                currentAttemptsData = {
                    attempts_allowed: 0,
                    attempts_used: 0,
                    attempts_remaining: 0,
                    is_assigned: false
                };
                document.getElementById('currentAttemptsInfo').classList.add('d-none');
                document.getElementById('attemptsAdjustment').value = 0;
                alert('This exam is not assigned to this student yet. You can assign it by adding attempts.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to fetch current attempts');
        });
});

function adjustAttempts(change) {
    const input = document.getElementById('attemptsAdjustment');
    let currentValue = parseInt(input.value) || 0;
    input.value = currentValue + change;
    updatePreview();
}

function updatePreview() {
    const adjustment = parseInt(document.getElementById('attemptsAdjustment').value) || 0;
    const currentAllowed = currentAttemptsData.attempts_allowed || 0;
    const newTotal = currentAllowed + adjustment;
    
    if (adjustment !== 0) {
        document.getElementById('newAttemptsTotal').textContent = newTotal;
        document.getElementById('newAttemptsPreview').classList.remove('d-none');
    } else {
        document.getElementById('newAttemptsPreview').classList.add('d-none');
    }
}

function saveAttempts() {
    const studentId = document.getElementById('studentId').value;
    const examId = document.getElementById('examSelect').value;
    const adjustment = parseInt(document.getElementById('attemptsAdjustment').value) || 0;

    if (!examId) {
        showAlert.warning('Please select an exam first');
        return;
    }

    if (adjustment === 0) {
        showAlert.warning('Please adjust the attempts using + or - buttons');
        return;
    }

    // Send AJAX request to update attempts
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
        // Close modal first
        const modal = bootstrap.Modal.getInstance(document.getElementById('manageAttemptsModal'));
        modal.hide();
        
        // Wait for modal to close, then show alert
        setTimeout(() => {
            if (data.success) {
                showAlert.success(data.message);
                // Reload after alert shows
                setTimeout(() => {
                    location.reload();
                }, 3000);
            } else {
                showAlert.error(data.message);
            }
        }, 300);
    })
    .catch(error => {
        console.error('Error:', error);
        // Close modal first
        const modal = bootstrap.Modal.getInstance(document.getElementById('manageAttemptsModal'));
        modal.hide();
        
        // Show error after modal closes
        setTimeout(() => {
            showAlert.error('Failed to update attempts. Please try again.');
        }, 300);
    });
}
</script>
@endsection
