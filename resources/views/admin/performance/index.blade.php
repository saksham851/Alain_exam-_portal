@extends('layouts.app')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: 48px !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            background-color: #fff !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 48px !important;
            padding-left: 16px !important;
            color: #1e293b !important;
            font-weight: 400;
            font-size: 0.88rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px !important;
            width: 40px !important;
            right: 4px !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: center;
            background-size: 18px;
            transition: transform 0.3s ease;
        }

        .select2-container--default.select2-container--open .select2-selection__arrow {
            transform: rotate(180deg);
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            display: none !important;
            /* Hide old arrow */
        }

        .select2-container--default .select2-selection--single .select2-selection__clear {
            display: none !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
        }

        .select2-dropdown {
            border: 1px solid #e2e8f0 !important;
            border-radius: 14px !important;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1) !important;
            overflow: hidden;
            margin-top: 8px;
            background-color: #fff;
        }

        .select2-results__option {
            padding: 12px 16px !important;
            font-size: 14px;
            color: #475569;
            transition: all 0.2s ease;
        }

        .select2-results__option--highlighted[aria-selected] {
            background-color: #f1f5f9 !important;
            color: #001427 !important;
        }

        .select2-results__option--selected {
            background-color: #f8fafc !important;
            color: #001427 !important;
            font-weight: 600;
        }

        /* Checkbox styles for Specific Exam dropdown only */
        .select2-exams-dropdown .select2-results__option {
            display: flex;
            align-items: center;
        }

        .select2-exams-dropdown .select2-results__option:before {
            content: "";
            display: inline-block;
            width: 16px;
            height: 16px;
            margin-right: 12px;
            border: 1.5px solid #cbd5e1;
            border-radius: 4px;
            background-color: #fff;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .select2-exams-dropdown .select2-results__option--highlighted:before {
            border-color: #3b82f6;
        }

        .select2-exams-dropdown .select2-results__option--selected:before {
            background-color: #3b82f6 !important;
            border-color: #3b82f6 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='4' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='20 6 9 17 4 12'%3e%3c/polyline%3e%3c/svg%3e") !important;
            background-size: 10px !important;
            background-repeat: no-repeat !important;
            background-position: center !important;
        }

        .select2-container--default .select2-selection--multiple {
            min-height: 48px !important;
            height: 48px !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            padding: 0 40px 0 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            background-color: #fff !important;
            position: relative;
            transition: all 0.3s ease;
        }

        .select2-container--default .select2-selection--multiple:after {
            content: "";
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: center;
            background-size: 18px;
            transition: transform 0.3s ease;
            pointer-events: none;
        }

        .select2-container--default.select2-container--open .select2-selection--multiple:after {
            transform: translateY(-50%) rotate(180deg);
        }

        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            padding: 0 0 0 16px !important;
            margin: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            flex-wrap: nowrap;
            overflow: hidden;
            width: 100%;
        }

        .select2-container--default .select2-selection--multiple .select2-search--inline {
            display: flex;
            align-items: center;
            margin: 0 !important;
            padding: 0 !important;
            flex-grow: 1;
        }

        .select2-container--default .select2-selection--multiple .select2-search__field {
            margin: 0 !important;
            height: 48px !important;
            line-height: 48px !important;
            font-family: inherit;
            font-size: 0.88rem !important;
            color: #1e293b !important;
            background: transparent !important;
            width: 100% !important;
            text-align: left !important;
            padding: 0 !important;
            display: block !important;
        }

        /* Hide search field placeholder when items are selected */
        .select2-container--default .select2-selection--multiple:not(:has(.select2-selection__choice)) .select2-search__field {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--multiple:has(.select2-selection__choice) .select2-search__field {
            width: 0 !important;
            opacity: 0 !important;
        }

        .select2-container--default .select2-selection--multiple .select2-search__field::placeholder {
            text-align: left !important;
            color: #94a3b8 !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: transparent !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            font-size: 0.88rem !important;
            color: #1e293b !important;
            font-weight: 400 !important;
            display: none !important;
            align-items: center;
            flex-shrink: 0;
            width: auto !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice:first-child {
            display: inline-flex !important; /* Show only the first one as a summary */
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            display: none !important;
        }

        .select2-search--dropdown .select2-search__field {
            border-radius: 10px !important;
            border: 1px solid #e2e8f0 !important;
            padding: 10px 14px !important;
            margin: 10px !important;
            width: calc(100% - 20px) !important;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex align-items-center justify-content-between">
                <h3 class="mb-0 fw-bold">Student Performance Analytics</h3>
                <div class="d-flex gap-2">
                    <a href="{{ route($routePrefix . '.performance.export', request()->query()) }}" class="btn btn-light-success d-flex align-items-center">
                        <i class="ti ti-download me-2"></i> Download CSV
                    </a>
                    <button class="btn btn-light-primary d-flex align-items-center" onclick="window.print()">
                        <i class="ti ti-printer me-2"></i> Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route($routePrefix . '.performance.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Exam Standard</label>
                        <select name="standard_id" class="form-select select2">
                            <option value="">All Standards</option>
                            @foreach($standards as $standard)
                                <option value="{{ $standard->id }}" {{ $selectedStandard == $standard->id ? 'selected' : '' }}>
                                    {{ $standard->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Specific Exam</label>
                        <select name="exam_id[]" class="form-select select2-exams" multiple data-placeholder="Exams">
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}" {{ (is_array($selectedExam) && in_array($exam->id, $selectedExam)) || $selectedExam == $exam->id ? 'selected' : '' }}>
                                    {{ $exam->name }} {{ $exam->exam_code ? "($exam->exam_code)" : "" }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Search Student</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i
                                    class="ti ti-search text-muted"></i></span>
                            <input type="text" name="student_search" class="form-control border-start-0 ps-0"
                                placeholder="Name or email..." value="{{ $studentSearch }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block text-white d-none d-md-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">
                            Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm overflow-hidden"
                style="background: linear-gradient(135deg, #001427 0%, #002345 100%);">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0 bg-white bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="ti ti-users text-white fs-4"></i>
                        </div>
                    </div>
                    <h6 class="text-white text-opacity-75 mb-1 small text-uppercase fw-bold">Total Attempts</h6>
                    <h2 class="text-white mb-0 fw-bold">{{ number_format($totalAttempts) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm overflow-hidden bg-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0 bg-light-success p-3 rounded-circle me-3">
                            <i class="ti ti-circle-check text-success fs-4"></i>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1 small text-uppercase fw-bold">Passed Attempts</h6>
                    <h2 class="text-dark mb-0 fw-bold">{{ number_format($passedAttempts) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm overflow-hidden bg-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0 bg-light-danger p-3 rounded-circle me-3">
                            <i class="ti ti-circle-x text-danger fs-4"></i>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1 small text-uppercase fw-bold">Failed Attempts</h6>
                    <h2 class="text-dark mb-0 fw-bold">{{ number_format($failedAttempts) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm overflow-hidden bg-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0 bg-light-warning p-3 rounded-circle me-3">
                            <i class="ti ti-chart-line text-warning fs-4"></i>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1 small text-uppercase fw-bold">Average Score</h6>
                    <h2 class="text-dark mb-0 fw-bold">{{ number_format($avgScore, 0) }}</h2>
                </div>
            </div>
        </div>
    </div>


    <!-- Detailed Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <h5 class="mb-0 fw-bold">Recent Student Attempts</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">Student</th>
                            <th class="py-3">Exam</th>
                            <th class="py-3">Standard</th>
                            <th class="py-3 text-center">Score</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3">Date</th>
                            <th class="pe-4 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attempts as $attempt)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $attempt->studentExam->student->first_name }}
                                        {{ $attempt->studentExam->student->last_name }}
                                    </div>
                                    <div class="small text-muted">{{ $attempt->studentExam->student->email }}</div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark">{{ $attempt->studentExam->exam->name }}</div>
                                    <div class="small text-muted opacity-75">{{ $attempt->studentExam->exam->exam_code }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light-info text-info rounded-pill px-3">
                                        {{ $attempt->studentExam->exam->examStandard->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div
                                        class="fw-bold {{ $attempt->total_score >= ($attempt->studentExam->exam->passing_score_overall ?? 70) ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($attempt->total_score, 0) }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($attempt->is_passed)
                                        <span class="badge bg-success px-3 rounded-pill">Passed</span>
                                    @else
                                        <span class="badge bg-danger px-3 rounded-pill">Failed</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="small text-dark fw-medium">{{ $attempt->created_at->format('M d, Y') }}</div>
                                    <div class="smaller text-muted">{{ $attempt->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route($routePrefix . '.attempts.show', $attempt->id) }}"
                                        class="btn btn-sm btn-icon btn-light-primary rounded-circle shadow-sm">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted opacity-50">
                                        <i class="ti ti-notes fs-1 mb-2 d-block"></i>
                                        No performance data found for the selected filters.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Custom Pagination --}}
            <x-custom-pagination :paginator="$attempts" />
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2({
                placeholder: $(this).data('placeholder'),
                width: '100%'
            });

            $('.select2-exams').select2({
                placeholder: "Exams",
                width: '100%',
                closeOnSelect: false,
                dropdownCssClass: 'select2-exams-dropdown',
                templateSelection: function (data) {
                    if (!data.id) return data.text;
                    var selected = $('.select2-exams').val();
                    if (selected && selected.length > 1) {
                        return selected.length + " Exams Selected";
                    }
                    return data.text;
                }
            });

            // Clear exams when standard changes
            $('select[name="standard_id"]').on('change', function() {
                $('.select2-exams').val(null).trigger('change');
                $('#filterForm').submit();
            });
        });
    </script>
@endpush