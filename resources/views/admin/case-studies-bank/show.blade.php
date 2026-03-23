@extends('layouts.app')

@section('content')

{{-- Page Header --}}
<div class="page-header d-print-none mb-4">
    <div class="row align-items-center w-100 m-0">
        <div class="col">
            <div class="page-pretitle text-muted small text-uppercase fw-bold tracking-wide">
                <a href="{{ route('admin.case-studies-bank.index') }}" class="text-muted text-decoration-none">Case Studies Bank</a>
                <span class="mx-1">/</span> Detailed View
            </div>
            <h2 class="page-title fw-bolder text-dark mb-0 py-1">{{ $caseStudy->title }}</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">
                <a href="{{ route('admin.case-studies-bank.edit', $caseStudy->id) }}" class="btn btn-primary">
                    <i class="ti ti-edit me-1"></i> Edit Case Study
                </a>
                <a href="{{ route('admin.case-studies-bank.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Back to Bank
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Stats Row --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="bg-warning-subtle text-warning rounded fs-3 p-3 me-3">
                    <i class="ti ti-notes"></i>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold tracking-wide" style="font-size: 10px;">Total Visits</div>
                    <div class="fs-4 fw-bold text-dark">{{ $caseStudy->visits->count() }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="bg-info-subtle text-info rounded fs-3 p-3 me-3">
                    <i class="ti ti-help"></i>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold tracking-wide" style="font-size: 10px;">Total Questions</div>
                    <div class="fs-4 fw-bold text-dark">{{ $caseStudy->visits->sum(fn($v) => $v->questions->count()) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="bg-purple-subtle text-purple rounded fs-3 p-3 me-3">
                    <i class="ti ti-tag"></i>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold tracking-wide" style="font-size: 10px;">Category</div>
                    <div class="fw-bold text-dark text-truncate" style="max-width: 150px;">{{ $caseStudy->section->exam->category->name ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Case Study Main Card --}}
<div class="card border border-secondary-subtle shadow-sm rounded-3 mb-4">
    {{-- Simple card header --}}
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center">
        <div class="bg-secondary-subtle text-secondary rounded p-1 me-3">
            <i class="ti ti-file-text"></i>
        </div>
        <div>
            <div class="text-muted small text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 1px;">Case Study Content</div>
            <h6 class="mb-0 fw-bold text-dark">{{ $caseStudy->title }}</h6>
        </div>
        <div class="ms-auto d-flex gap-2">
            <span class="badge {{ $caseStudy->status ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-3 py-2 rounded-pill fw-bold">
                {{ $caseStudy->status ? 'Active' : 'Inactive' }}
            </span>
            <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-pill fw-bold">
                {{ $caseStudy->visits->count() }} Visit(s) · {{ $caseStudy->visits->sum(fn($v) => $v->questions->count()) }} Questions
            </span>
        </div>
    </div>

    <div class="card-body p-4">
        {{-- Scenario Content --}}

        {{-- Meta details (Section/Exam) --}}
        <div class="row g-3">
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 rounded-3 bg-light border">
                    <div class="p-2 rounded-3 me-3 bg-white shadow-sm">
                        <i class="ti ti-layout-list text-primary fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 1px;">Section</div>
                        <div class="fw-bold text-dark">{{ $caseStudy->section->title ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 rounded-3 bg-light border">
                    <div class="p-2 rounded-3 me-3 bg-white shadow-sm">
                        <i class="ti ti-certificate text-primary fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 1px;">Exam</div>
                        <div class="fw-bold text-dark">{{ $caseStudy->section->exam->name ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Visits & Questions Section Header --}}
<div class="mb-3 d-flex align-items-center justify-content-between px-1">
    <h5 class="fw-bold text-dark mb-0">
        <i class="ti ti-stethoscope text-info me-2"></i>Visits & Questions
    </h5>
</div>

{{-- Visits Loop --}}
@forelse($caseStudy->visits as $vIndex => $visit)
    <div class="card border-0 shadow-sm mb-3 visit-card" id="visit-{{ $visit->id }}" style="border-radius: 10px; overflow: hidden;">

        {{-- Visit Header (Toggle) --}}
        <div class="card-header visit-toggle-btn d-flex align-items-center justify-content-between py-3 px-4 user-select-none"
             style="background: linear-gradient(90deg, #eef2ff 0%, #f5f7ff 100%); border-bottom: 2px solid #e0e7ff; cursor: pointer;"
             data-bs-toggle="collapse"
             data-bs-target="#visit-body-{{ $visit->id }}"
             aria-expanded="false">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-info-subtle p-2 rounded-3">
                    <i class="ti ti-stethoscope text-info fs-5"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 10px; text-transform: uppercase; letter-spacing: 1px; font-weight: 700;">
                        Visit {{ $vIndex + 1 }}
                    </div>
                    <div class="fw-bold text-dark" style="font-size: 14px;">{{ $visit->title }}</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fw-bold">
                    <i class="ti ti-help-circle me-1"></i> {{ $visit->questions->count() }} Q
                </span>
                <i class="ti ti-chevron-down text-muted visit-chevron fs-5"></i>
            </div>
        </div>

        {{-- Visit Body (Collapsible) --}}
        <div class="collapse" id="visit-body-{{ $visit->id }}">
            <div class="card-body p-0">

                {{-- Visit Description --}}

                {{-- Questions Table --}}
                @if($visit->questions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-body-tertiary">
                            <tr>
                                <th class="ps-4 py-3 text-uppercase text-secondary fw-bold" style="font-size: 11px; width: 4%;">#</th>
                                <th class="py-3 text-uppercase text-secondary fw-bold" style="font-size: 11px; width: 50%;">Question</th>
                                <th class="py-3 text-uppercase text-secondary fw-bold" style="font-size: 11px; width: 12%;">Type</th>
                                <th class="py-3 text-uppercase text-secondary fw-bold" style="font-size: 11px; width: 10%;">Points</th>
                                <th class="pe-4 py-3 text-uppercase text-secondary fw-bold" style="font-size: 11px; width: 24%;">Correct Answer</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($visit->questions as $qIndex => $question)
                            @php $correct = $question->options->firstWhere('is_correct', 1); @endphp
                            <tr class="{{ $loop->odd ? 'bg-white' : 'bg-light-subtle' }}">
                                <td class="ps-4 align-top pt-3">
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill fw-bold">{{ $qIndex + 1 }}</span>
                                </td>
                                <td class="align-top pt-3 pe-3">
                                    <div class="fw-semibold text-dark mb-2" style="font-size: 13px; line-height: 1.5;">
                                        {!! strip_tags($question->question_text) !!}
                                        @if($question->cloned_from_id)
                                            <span class="badge bg-warning-subtle text-warning ms-1" style="font-size: 10px;"><i class="ti ti-copy"></i> Cloned</span>
                                        @endif
                                    </div>
                                    @if($question->options->count() > 0)
                                    <div>
                                        <a class="btn btn-xs btn-light border rounded-pill px-3 py-1 text-muted text-decoration-none d-inline-flex align-items-center"
                                           style="font-size: 11px;"
                                           data-bs-toggle="collapse"
                                           href="#opts-{{ $question->id }}"
                                           role="button">
                                            <i class="ti ti-list me-1"></i> View {{ $question->options->count() }} Options
                                        </a>
                                        <div class="collapse mt-2" id="opts-{{ $question->id }}">
                                            <div class="bg-light rounded-3 p-3">
                                                <ul class="list-unstyled mb-0 vstack gap-2">
                                                    @foreach($question->options as $opt)
                                                    <li class="d-flex align-items-start gap-2">
                                                        @if($opt->is_correct)
                                                            <i class="ti ti-circle-check text-success fs-5 flex-shrink-0"></i>
                                                            <span class="text-success fw-bold" style="font-size: 13px;">{{ $opt->option_text }}</span>
                                                        @else
                                                            <i class="ti ti-circle text-muted fs-5 flex-shrink-0"></i>
                                                            <span class="text-secondary" style="font-size: 13px;">{{ $opt->option_text }}</span>
                                                        @endif
                                                    </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </td>
                                <td class="align-top pt-3">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-capitalize">
                                        {{ $question->question_type }}
                                    </span>
                                </td>
                                <td class="align-top pt-3">
                                    <span class="badge bg-info-subtle text-info border border-info-subtle fw-bold px-3 py-2" style="font-size: 12px;">
                                        {{ $question->max_question_points ?? 0 }}
                                    </span>
                                </td>
                                <td class="pe-4 align-top pt-3">
                                    @if($correct)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 d-inline-block text-wrap text-start" style="max-width: 250px; line-height: 1.4;">
                                            <i class="ti ti-circle-check me-1"></i> {{ $correct->option_text }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="ti ti-file-off fs-2 d-block mb-2 opacity-50"></i>
                    <span class="small">No questions in this visit yet.</span>
                </div>
                @endif
            </div>
        </div>
    </div>
@empty
    <div class="card border border-dashed rounded-3">
        <div class="card-body text-center py-5">
            <i class="ti ti-stethoscope fs-1 text-muted d-block mb-3 opacity-50"></i>
            <h5 class="fw-bold text-muted">No Visits Found</h5>
            <p class="text-secondary small mb-0">This case study has no visits added yet.</p>
        </div>
    </div>
@endforelse

<div class="pb-5 mb-5 mt-4">
    <a href="{{ route('admin.case-studies-bank.index') }}" class="btn btn-outline-secondary px-4">
        <i class="ti ti-arrow-left me-2"></i> Back to Bank
    </a>
</div>

<style>
    .visit-toggle-btn:hover { background: linear-gradient(90deg, #e0e7ff 0%, #eef2ff 100%) !important; }
    .visit-toggle-btn:focus { outline: none; box-shadow: none; }
    .visit-card { transition: box-shadow 0.2s; }
    .visit-card:hover { box-shadow: 0 4px 16px rgba(91,115,232,0.12) !important; }
    .visit-chevron { transition: transform 0.25s ease; }
    .visit-chevron.rotated { transform: rotate(180deg); }
    .tracking-wide { letter-spacing: 0.05em; }
    .fs-7 { font-size: 0.85rem; }
    .btn-xs { padding: 0.2rem 0.6rem; font-size: 11px; }

    .highlight-visit {
        animation: highlightPulse 2s ease-in-out;
        box-shadow: 0 0 0 4px rgba(70, 128, 255, 0.2) !important;
        border: 1px solid #4680ff !important;
    }
    
    @keyframes highlightPulse {
        0%, 100% { background-color: transparent; }
        50% { background-color: #f0f7ff; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[id^="visit-body-"]').forEach(function (collapseEl) {
        var btn = document.querySelector('[data-bs-target="#' + collapseEl.id + '"]');
        if (!btn) return;
        var chevron = btn.querySelector('.visit-chevron');
        collapseEl.addEventListener('show.bs.collapse', function () {
            if (chevron) chevron.classList.add('rotated');
        });
        collapseEl.addEventListener('hide.bs.collapse', function () {
            if (chevron) chevron.classList.remove('rotated');
        });
    });

    // Scroll to visit if hash is present
    if(window.location.hash) {
        const element = document.querySelector(window.location.hash);
        if(element) {
            setTimeout(() => {
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                element.classList.add('highlight-visit');
                // If it's a collapse, optionally show it? 
                // But visit body is what's collapsed. The header is always visible.
                setTimeout(() => element.classList.remove('highlight-visit'), 2000);
            }, 500);
        }
    }
});
</script>

@endsection
