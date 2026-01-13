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

      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Student Attempts</h5>
            </div>
            
            <!-- Filter Section -->
            <div class="card-body bg-light-subtle py-3 border-bottom">
              <form method="GET" action="{{ route('admin.attempts.index') }}" id="attemptsFilterForm">
                <div class="row g-2 align-items-end">
                  <!-- Student Name Search (Searchable Dropdown) -->
                  <div class="col-md-3">
                    <label class="form-label fw-bold text-muted small mb-1">SEARCH BY STUDENT</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text bg-white border-end-0"><i class="ti ti-user text-muted"></i></span>
                      <input type="text" 
                             name="student_search" 
                             class="form-control border-start-0 ps-0" 
                             placeholder="Type or select student..." 
                             value="{{ $studentSearch ?? '' }}" 
                             id="studentSearchInput"
                             list="studentsList"
                             autocomplete="off">
                      <datalist id="studentsList">
                        @foreach($students as $student)
                          <option value="{{ $student->first_name }} {{ $student->last_name }}">
                            {{ $student->first_name }} {{ $student->last_name }} ({{ $student->email }})
                          </option>
                        @endforeach
                      </datalist>
                    </div>
                  </div>
                  
                  <!-- Exam Name Search (Searchable Dropdown) -->
                  <div class="col-md-3">
                    <label class="form-label fw-bold text-muted small mb-1">SEARCH BY EXAM</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text bg-white border-end-0"><i class="ti ti-book text-muted"></i></span>
                      <input type="text" 
                             name="exam_search" 
                             class="form-control border-start-0 ps-0" 
                             placeholder="Type or select exam..." 
                             value="{{ $examSearch ?? '' }}" 
                             id="examSearchInput"
                             list="examsList"
                             autocomplete="off">
                      <datalist id="examsList">
                        @foreach($exams as $exam)
                          <option value="{{ $exam->name }}">{{ $exam->name }}</option>
                        @endforeach
                      </datalist>
                    </div>
                  </div>

                  <!-- Time Period Filter -->
                  <div class="col-md-3">
                    <label class="form-label fw-bold text-muted small mb-1">TIME PERIOD</label>
                    <select name="period" class="form-select form-select-sm" id="periodSelect">
                      <option value="" {{ !$selectedPeriod ? 'selected' : '' }}>All Time</option>
                      <option value="24h" {{ $selectedPeriod == '24h' ? 'selected' : '' }}>Last 24 Hours</option>
                      <option value="1week" {{ $selectedPeriod == '1week' ? 'selected' : '' }}>Last 1 Week</option>
                      <option value="1month" {{ $selectedPeriod == '1month' ? 'selected' : '' }}>Last 1 Month</option>
                      <option value="1year" {{ $selectedPeriod == '1year' ? 'selected' : '' }}>Last 1 Year</option>
                    </select>
                  </div>

                  <!-- Reset Button -->
                  <div class="col-md-3">
                    <div class="d-flex gap-1 justify-content-end">
                      @if($selectedPeriod || $studentSearch || $examSearch)
                        <a href="{{ route('admin.attempts.index') }}" class="btn btn-sm btn-light-secondary px-3" title="Clear All Filters">
                          <i class="ti ti-x me-1"></i> Clear Filters
                        </a>
                      @endif
                    </div>
                  </div>
                </div>
              </form>
            </div>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const filterForm = document.getElementById('attemptsFilterForm');
                const periodSelect = document.getElementById('periodSelect');
                const studentSearchInput = document.getElementById('studentSearchInput');
                const examSearchInput = document.getElementById('examSearchInput');
                
                let searchTimeout;
                
                // Store which field was last focused
                let lastFocusedField = localStorage.getItem('lastFocusedFilter');
                
                // Restore focus to the last active field
                if (lastFocusedField === 'student' && studentSearchInput && studentSearchInput.value) {
                    studentSearchInput.focus();
                    // Move cursor to end of text
                    studentSearchInput.setSelectionRange(studentSearchInput.value.length, studentSearchInput.value.length);
                } else if (lastFocusedField === 'exam' && examSearchInput && examSearchInput.value) {
                    examSearchInput.focus();
                    examSearchInput.setSelectionRange(examSearchInput.value.length, examSearchInput.value.length);
                }
                
                // Auto-submit on dropdown change (instant)
                if (periodSelect) {
                    periodSelect.addEventListener('change', function() {
                        localStorage.removeItem('lastFocusedFilter');
                        filterForm.submit();
                    });
                }
                
                // Auto-submit on student search input (debounced - 1500ms delay)
                if (studentSearchInput) {
                    studentSearchInput.addEventListener('focus', function() {
                        localStorage.setItem('lastFocusedFilter', 'student');
                    });
                    
                    studentSearchInput.addEventListener('input', function() {
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(function() {
                            filterForm.submit();
                        }, 1500); // Increased to 1.5 seconds
                    });
                }
                
                // Auto-submit on exam search input (debounced - 1500ms delay)
                if (examSearchInput) {
                    examSearchInput.addEventListener('focus', function() {
                        localStorage.setItem('lastFocusedFilter', 'exam');
                    });
                    
                    examSearchInput.addEventListener('input', function() {
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(function() {
                            filterForm.submit();
                        }, 1500); // Increased to 1.5 seconds
                    });
                }
                
                // Clear focus tracking when form is submitted via button
                const clearButton = document.querySelector('a[href*="admin.attempts.index"]');
                if (clearButton) {
                    clearButton.addEventListener('click', function() {
                        localStorage.removeItem('lastFocusedFilter');
                    });
                }
            });
            </script>
            
            <!-- Active Filter Indicator -->
            @php
              $hasFilters = $selectedPeriod || $studentSearch || $examSearch;
            @endphp
            
            @if($hasFilters)
            <div class="card-body border-bottom bg-light-subtle py-3">
              <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="text-muted small fw-semibold">
                  <i class="ti ti-filter-check me-1"></i>ACTIVE FILTERS:
                </span>
                @if($studentSearch)
                  <span class="badge rounded-pill bg-primary">
                    <i class="ti ti-user me-1"></i>{{ $studentSearch }}
                  </span>
                @endif
                @if($examSearch)
                  <span class="badge rounded-pill bg-success">
                    <i class="ti ti-book me-1"></i>{{ $examSearch }}
                  </span>
                @endif
                @if($selectedPeriod)
                  <span class="badge rounded-pill bg-info">
                    <i class="ti ti-clock me-1"></i>
                    @if($selectedPeriod == '24h')
                      Last 24 Hours
                    @elseif($selectedPeriod == '1week')
                      Last 1 Week
                    @elseif($selectedPeriod == '1month')
                      Last 1 Month
                    @elseif($selectedPeriod == '1year')
                      Last 1 Year
                    @endif
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
                </div>
                
                {{-- Custom Pagination --}}
                @if(method_exists($attempts, 'links'))
                    <x-custom-pagination :paginator="$attempts" />
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
