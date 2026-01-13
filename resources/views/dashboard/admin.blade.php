@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Admin Dashboard</h5>
        </div>

      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row mb-4">
    <!-- Stats Cards -->
    <div class="col-md-6 col-xl-2">
      <div class="card h-100">
        <div class="card-body pt-3 px-3 pb-2">
          <h6 class="mb-2 f-w-400 text-muted">Total Students</h6>
          <h4 class="mb-2">{{ number_format($stats['total_students']) }} <span class="badge bg-light-primary border border-primary"><i class="ti ti-users"></i></span></h4>
          <p class="mb-0 text-muted text-sm">Registered users</p>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-2">
      <div class="card h-100">
        <div class="card-body pt-3 px-3 pb-2">
          <h6 class="mb-2 f-w-400 text-muted">Total Exams</h6>
          <h4 class="mb-2">{{ $stats['active_exams'] }} <span class="badge bg-light-success border border-success"><i class="ti ti-book"></i></span></h4>
          <p class="mb-0 text-muted text-sm">Available exams</p>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-2">
      <div class="card h-100">
        <div class="card-body pt-3 px-3 pb-2">
          <h6 class="mb-2 f-w-400 text-muted">Total Exam Categories</h6>
          <h4 class="mb-2">{{ $stats['exam_categories'] }} <span class="badge bg-light-info border border-info"><i class="ti ti-file-text"></i></span></h4>
          <p class="mb-0 text-muted text-sm">Total categories</p>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-2">
      <div class="card h-100">
        <div class="card-body pt-3 px-3 pb-2">
          <h6 class="mb-2 f-w-400 text-muted">Total Case Studies</h6>
          <h4 class="mb-2">{{ $stats['case_studies'] }} <span class="badge bg-light-secondary border border-secondary"><i class="ti ti-file-text"></i></span></h4>
          <p class="mb-0 text-muted text-sm">Total case studies</p>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-2">
      <div class="card h-100">
        <div class="card-body pt-3 px-3 pb-2">
          <h6 class="mb-2 f-w-400 text-muted">Questions Bank</h6>
          <h4 class="mb-2">{{ number_format($stats['total_questions']) }} <span class="badge bg-light-warning border border-warning"><i class="ti ti-question-mark"></i></span></h4>
          <p class="mb-0 text-muted text-sm">Total questions</p>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-2">
      <div class="card h-100">
        <div class="card-body pt-3 px-3 pb-2">
          <h6 class="mb-2 f-w-400 text-muted">Recent Attempts</h6>
          <h4 class="mb-2">{{ $stats['recent_attempts_count'] }} <span class="badge bg-light-danger border border-danger"><i class="ti ti-trending-up"></i></span></h4>
          <p class="mb-0 text-muted text-sm">Last 24 hours</p>
        </div>
      </div>
    </div>
</div>

<!-- Student Details Table -->
<div class="row mb-4">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
            <h5>Student Details</h5>
        </div>
        
        <!-- Compact Filters Section -->
        <div class="card-body bg-light-subtle py-3 border-bottom">
          <form method="GET" action="{{ route('admin.dashboard') }}" id="studentDetailsFilterForm">
            <!-- Preserve exam overview filters -->
            @if(request('exam_search'))
              <input type="hidden" name="exam_search" value="{{ request('exam_search') }}">
            @endif
            @if(request('exam_category_id'))
              <input type="hidden" name="exam_category_id" value="{{ request('exam_category_id') }}">
            @endif
            @if(request('certification_type'))
              <input type="hidden" name="certification_type" value="{{ request('certification_type') }}">
            @endif
            
            <div class="row g-2 align-items-end">
              <!-- Search -->
              <div class="col-md-6">
                <label class="form-label fw-bold text-muted small mb-1">SEARCH BY NAME OR EMAIL</label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
                  <input type="text" name="student_search" class="form-control border-start-0 ps-0" 
                         placeholder="Student name or email..." value="{{ request('student_search') }}" id="studentSearchInput">
                </div>
              </div>

              <!-- Buttons -->
              <div class="col-md-6">
                <div class="d-flex gap-1 justify-content-end">
                  <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-light-secondary px-3" title="Reset">
                    <i class="ti ti-rotate"></i>
                  </a>
                </div>
              </div>
            </div>
          </form>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const studentFilterForm = document.getElementById('studentDetailsFilterForm');
            const studentSearchInput = document.getElementById('studentSearchInput');
            
            let studentSearchTimeout;
            
            // Auto-submit on search input (debounced - 500ms delay)
            if (studentSearchInput) {
                studentSearchInput.addEventListener('input', function() {
                    clearTimeout(studentSearchTimeout);
                    studentSearchTimeout = setTimeout(function() {
                        studentFilterForm.submit();
                    }, 500);
                });
            }
        });
        </script>
        
        <!-- Active Filters Indicator -->
        @php
          $hasStudentFilters = request('student_search');
        @endphp
        
        @if($hasStudentFilters)
        <div class="card-body border-bottom bg-light-subtle py-3">
          <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="text-muted small fw-semibold">
              <i class="ti ti-filter-check me-1"></i>ACTIVE FILTERS:
            </span>
            @if(request('student_search'))
              <span class="badge rounded-pill bg-dark">
                <i class="ti ti-search me-1"></i>{{ request('student_search') }}
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
                  <th>STUDENT NAME</th>
                  <th>EMAIL</th>
                  <th>ENROLLED EXAMS</th>
                  <th>TOTAL ATTEMPTS</th>
                  <th>AVERAGE SCORE</th>
                  <th>STATUS</th>
                  <th>JOINED DATE</th>
                  <th class="text-end">ACTIONS</th>
                </tr>
              </thead>
              <tbody>
                @forelse($studentDetails as $student)
                <tr>
                  <td>
                    <h6 class="mb-0">{{ $student->name }}</h6>
                  </td>
                  <td>
                    <span class="text-muted">{{ $student->email }}</span>
                  </td>
                  <td>
                    <span class="badge bg-light-primary">{{ $student->enrolled_exams }}</span>
                  </td>
                  <td>
                    <span class="badge bg-light-info">{{ $student->total_attempts }}</span>
                  </td>
                  <td>
                    @if($student->average_score > 0)
                      <span class="badge {{ $student->average_score >= 70 ? 'bg-light-success' : 'bg-light-warning' }}">
                        {{ $student->average_score }}%
                      </span>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td>
                    @if($student->status == 1)
                      <span class="badge bg-success">Active</span>
                    @else
                      <span class="badge bg-danger">Inactive</span>
                    @endif
                  </td>
                  <td>
                    <span class="text-muted">{{ $student->joined_date }}</span>
                  </td>
                  <td class="text-end">
                    <a href="{{ route('admin.attempts.by-user', $student->id) }}" 
                       class="btn btn-sm btn-light-primary" 
                       title="View Attempts">
                      <i class="ti ti-eye"></i>
                    </a>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">
                    @if($hasStudentFilters)
                      No students found matching your filters.
                    @else
                      No students registered yet.
                    @endif
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
</div>

<!-- Exam Overview Table -->
<div class="row mb-4">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
            <h5>Exam Overview</h5>
        </div>
        
        <!-- Compact Filters Section -->
        <div class="card-body bg-light-subtle py-3 border-bottom">
          <form method="GET" action="{{ route('admin.dashboard') }}" id="examOverviewFilterForm">
            <div class="row g-2 align-items-end">
              <!-- Search -->
              <div class="col-md-4">
                <label class="form-label fw-bold text-muted small mb-1">SEARCH BY NAME OR CODE</label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
                  <input type="text" name="exam_search" class="form-control border-start-0 ps-0" 
                         placeholder="Name or code..." value="{{ request('exam_search') }}" id="examSearchInput">
                </div>
              </div>

              <!-- Exam Category -->
              <div class="col-md-3">
                <label class="form-label fw-bold text-muted small mb-1">EXAM CATEGORY</label>
                <select name="exam_category_id" class="form-select form-select-sm" id="examCategorySelect">
                  <option value="">All Categories</option>
                  @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('exam_category_id') == $category->id ? 'selected' : '' }}>
                      {{ $category->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              <!-- Certification Type -->
              <div class="col-md-2">
                <label class="form-label fw-bold text-muted small mb-1">CERTIFICATION TYPE</label>
                <select name="certification_type" class="form-select form-select-sm" id="certificationTypeSelect">
                  <option value="">All Types</option>
                  @foreach($certificationTypes as $type)
                    <option value="{{ $type }}" {{ request('certification_type') == $type ? 'selected' : '' }}>
                      {{ $type }}
                    </option>
                  @endforeach
                </select>
              </div>

              <!-- Buttons -->
              <div class="col-md-3">
                <div class="d-flex gap-1 justify-content-end">
                  <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-light-secondary px-3" title="Reset">
                    <i class="ti ti-rotate"></i>
                  </a>
                </div>
              </div>
            </div>
          </form>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('examOverviewFilterForm');
            const searchInput = document.getElementById('examSearchInput');
            const categorySelect = document.getElementById('examCategorySelect');
            const certificationTypeSelect = document.getElementById('certificationTypeSelect');
            
            let searchTimeout;
            
            // Auto-submit on dropdown change (instant)
            if (categorySelect) {
                categorySelect.addEventListener('change', function() {
                    filterForm.submit();
                });
            }
            
            if (certificationTypeSelect) {
                certificationTypeSelect.addEventListener('change', function() {
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
        });
        </script>
        
        <!-- Active Filters Indicator -->
        @php
          $hasExamFilters = request('exam_search') || 
                            request('exam_category_id') || 
                            request('certification_type');
        @endphp
        
        @if($hasExamFilters)
        <div class="card-body border-bottom bg-light-subtle py-3">
          <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="text-muted small fw-semibold">
              <i class="ti ti-filter-check me-1"></i>ACTIVE FILTERS:
            </span>
            @if(request('exam_search'))
              <span class="badge rounded-pill bg-dark">
                <i class="ti ti-search me-1"></i>{{ request('exam_search') }}
              </span>
            @endif
            @if(request('exam_category_id'))
              <span class="badge rounded-pill bg-info">
                <i class="ti ti-category me-1"></i>{{ $categories->firstWhere('id', request('exam_category_id'))->name ?? 'Unknown' }}
              </span>
            @endif
            @if(request('certification_type'))
              <span class="badge rounded-pill bg-success">
                <i class="ti ti-certificate me-1"></i>{{ request('certification_type') }}
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
                  <th>EXAM CATEGORY</th>
                  <th>CERTIFICATION TYPE</th>
                  <th>EXAM NAME</th>
                  <th>EXAM CODE</th>
                  <th>EXAM STATUS</th>
                  <th>STUDENTS</th>
                  <th>QUESTIONS</th>
                  <th>CASE STUDIES</th>
                  <th>ATTEMPTS</th>
                </tr>
              </thead>
              <tbody>
                @forelse($examOverview as $exam)
                <tr>
                  <td>
                    <span class="badge bg-light-info">{{ $exam->category }}</span>
                  </td>
                  <td>
                    <span class="badge bg-light-success">{{ $exam->certification_type }}</span>
                  </td>
                  <td>
                    <h6 class="mb-0">{{ $exam->name }}</h6>
                  </td>
                  <td>
                    @if($exam->exam_code)
                      <span class="badge bg-light-secondary">{{ $exam->exam_code }}</span>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td>
                    @if($exam->is_active == 1)
                      <span class="badge bg-success">Active</span>
                    @else
                      <span class="badge bg-danger">Inactive</span>
                    @endif
                  </td>
                  <td>
                    <span class="badge bg-light-primary">{{ $exam->student_count }}</span>
                  </td>
                  <td>
                    <span class="badge bg-light-warning">{{ $exam->question_count }}</span>
                  </td>
                  <td>
                    <span class="badge bg-light-secondary">{{ $exam->case_study_count }}</span>
                  </td>
                  <td>
                    <span class="badge bg-light-danger">{{ $exam->attempt_count }}</span>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="10" class="text-center text-muted py-4">
                    No exams available.
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
</div>

<div class="row">
    <!-- Recent Activity Table -->
    <div class="col-md-12 col-xl-8">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Activity</h5>
            <a href="{{ route('admin.attempts.index') }}" class="btn btn-sm btn-light-primary">
              <i class="ti ti-eye me-1"></i> View All
            </a>
        </div>
        
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless mb-0">
              <thead>
                <tr>
                  <th>STUDENT</th>
                  <th>EXAM</th>
                  <th>SCORE</th>
                  <th>STATUS</th>
                  <th class="text-end">TIME</th>
                </tr>
              </thead>
              <tbody>
                @forelse($recentAttempts as $attempt)
                <tr>
                  <td>
                    <a href="{{ route('admin.attempts.by-user', $attempt->student_id) }}" class="text-muted">
                      {{ $attempt->student_name }}
                    </a>
                  </td>
                  <td>{{ $attempt->exam_name }}</td>
                  <td>{{ round($attempt->total_score, 1) }}%</td>
                  <td>
                    <span class="d-flex align-items-center gap-2">
                      <i class="fas fa-circle {{ $attempt->is_passed ? 'text-success' : 'text-danger' }} f-10 m-r-5"></i>
                      {{ $attempt->is_passed ? 'Passed' : 'Failed' }}
                    </span>
                  </td>
                  <td class="text-end">{{ $attempt->time_ago }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">
                    No recent attempts found. Students haven't attempted any exams yet.
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="col-md-12 col-xl-4">
      <div class="card h-100">
        <div class="card-header">
          <h5>Quick Actions</h5>
        </div>
        <div class="card-body">
          <div class="d-grid gap-2">
            <a href="{{ route('admin.users.index') }}" class="btn btn-light-primary">
              <i class="ti ti-users me-2"></i> Manage Students
            </a>
            <a href="{{ route('admin.exams.index') }}" class="btn btn-light-success">
              <i class="ti ti-book me-2"></i> Manage Exams
            </a>
            <a href="{{ route('admin.questions.index') }}" class="btn btn-light-warning">
              <i class="ti ti-question-mark me-2"></i> Manage Questions
            </a>
            <a href="{{ route('admin.attempts.index') }}" class="btn btn-light-info">
              <i class="ti ti-chart-bar me-2"></i> View All Results
            </a>
          </div>
        </div>
      </div>
    </div>
</div>
@endsection
