@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="page-header d-print-none mb-4">
    <div class="row align-items-center w-100 m-0">
        <div class="col">
            <div class="page-pretitle text-muted small text-uppercase fw-bold tracking-wide">Section Management</div>
            <h2 class="page-title fw-bolder text-dark mb-0 py-1">{{ $section->title }}</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">
                <a href="{{ route('admin.sections.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Back to Sections
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Section Overview Card -->
<div class="card mb-4 shadow-sm border-0 rounded-3">
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-md-12">
                 <div class="d-flex align-items-center mb-4 border-bottom pb-3">
                    <span class="badge {{ $section->status ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-3 py-2 rounded-pill fw-bold text-uppercase me-3">
                        <i class="ti {{ $section->status ? 'ti-circle-check' : 'ti-alert-circle' }} me-1"></i>
                        {{ $section->status ? 'Active' : 'Inactive' }}
                    </span>
                    @if($section->cloned_from_id)
                         <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill fw-bold text-uppercase me-3">
                            <i class="ti ti-copy me-1"></i> Cloned
                        </span>
                    @endif
                     <span class="text-muted d-flex align-items-center border-start ps-3">
                        <i class="ti ti-file-description me-1"></i>
                        <span class="text-uppercase small fw-bold letter-spacing-1">Parent Exam: {{ $section->exam->name ?? 'N/A' }}</span>
                    </span>
                </div>

                 <div class="text-secondary fs-5 mb-5 text-break" style="line-height: 1.8;">
                    {!! $section->content ?? 'No content provided for this section.' !!}
                 </div>

                 <!-- Stats Row -->
                 <div class="row g-4 justify-content-between">
                     <div class="col-auto">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary-subtle text-primary rounded fs-3 p-3 me-3">
                                <i class="ti ti-files"></i>
                            </div>
                            <div>
                                <div class="text-muted small text-uppercase fw-bold tracking-wide">Total Case Studies</div>
                                <div class="fs-4 fw-bold text-dark">{{ $section->caseStudies->count() }}</div>
                            </div>
                        </div>
                    </div>
                     <div class="col-auto">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning-subtle text-warning rounded fs-3 p-3 me-3">
                                <i class="ti ti-notes"></i>
                            </div>
                            <div>
                                <div class="text-muted small text-uppercase fw-bold tracking-wide">Total Visits</div>
                                <div class="fs-4 fw-bold text-dark">{{ $section->caseStudies->sum(function($cs) { return $cs->visits->count(); }) }}</div>
                            </div>
                        </div>
                    </div>
                     <div class="col-auto">
                        <div class="d-flex align-items-center">
                            <div class="bg-info-subtle text-info rounded fs-3 p-3 me-3">
                                <i class="ti ti-help"></i>
                            </div>
                            <div>
                                <div class="text-muted small text-uppercase fw-bold tracking-wide">Total Questions</div>
                                <div class="fs-4 fw-bold text-dark">{{ $section->caseStudies->sum(function($cs) { return $cs->questions->count(); }) }}</div>
                            </div>
                        </div>
                    </div>
                    <!-- Exam Category Info -->
                     <div class="col-auto">
                        <div class="d-flex align-items-center">
                             <div class="bg-purple-subtle text-purple rounded fs-3 p-3 me-3">
                                <i class="ti ti-tag"></i>
                            </div>
                            <div>
                                <div class="text-muted small text-uppercase fw-bold tracking-wide">Category</div>
                                <div class="fs-4 fw-bold text-dark">{{ $section->exam->category->name ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                 </div>
            </div>
        </div>
    </div>
</div>

<!-- Case Studies List -->
<div class="row">
    <div class="col-12">
        <h4 class="text-uppercase text-muted small fw-bold tracking-wide mb-3">Section Content</h4>
        <div class="d-flex flex-column gap-3">
            @forelse($section->caseStudies as $csIndex => $caseStudy)
                <div class="card border border-secondary-subtle shadow-sm rounded-3">

                    {{-- Case Study Header --}}
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center">
                        <div class="bg-secondary-subtle text-secondary rounded p-1 me-3">
                            <i class="ti ti-file-text"></i>
                        </div>
                        <h6 class="mb-0 fw-bold text-dark">Case Study {{ $csIndex + 1 }}: {{ $caseStudy->title }}</h6>
                        @if($caseStudy->cloned_from_id)
                            <span class="badge bg-warning text-dark ms-2"><i class="ti ti-copy"></i> Cloned</span>
                        @endif
                        <span class="badge {{ $caseStudy->status ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} ms-2">
                            {{ $caseStudy->status ? 'Active' : 'Inactive' }}
                        </span>
                        <span class="badge bg-info-subtle text-info ms-auto">
                            {{ $caseStudy->visits->count() }} Visit(s) &nbsp;·&nbsp; {{ $caseStudy->visits->sum(fn($v) => $v->questions->count()) }} Q
                        </span>
                    </div>

                    <div class="card-body p-4">

                        {{-- Scenario Content --}}
                        @if($caseStudy->content)
                            <div class="p-3 mb-4 bg-light-subtle border-start border-4 border-primary rounded-end">
                                <h6 class="text-uppercase text-muted fs-7 fw-bold mb-2">Case Study Scenario</h6>
                                <div class="text-dark text-break">{!! $caseStudy->content !!}</div>
                            </div>
                        @endif

                        {{-- Visits --}}
                        @forelse($caseStudy->visits as $vIndex => $visit)
                            <div class="card border-0 shadow-sm mb-3 visit-card" style="border-radius: 10px; overflow: hidden;">

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
                                            @if($visit->description)
                                                <div class="text-muted text-truncate" style="max-width: 450px; font-size: 12px;">
                                                    {{ Str::limit(strip_tags($visit->description), 70) }}
                                                </div>
                                            @endif
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
                                        @if($visit->description)
                                        <div class="px-4 py-3 border-bottom bg-light-subtle">
                                            <div class="text-muted small fw-bold text-uppercase mb-1" style="letter-spacing: 1px; font-size: 10px;">
                                                <i class="ti ti-notes me-1"></i> Session Context / Description
                                            </div>
                                            <div class="text-secondary" style="line-height: 1.7; font-size: 14px;">{!! $visit->description !!}</div>
                                        </div>
                                        @endif

                                        {{-- Questions Table --}}
                                        @if($visit->questions->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead class="bg-body-tertiary">
                                                    <tr>
                                                        <th class="ps-4 py-3 text-uppercase text-secondary fw-bold" style="font-size: 11px; width: 4%;">#</th>
                                                        <th class="py-3 text-uppercase text-secondary fw-bold" style="font-size: 11px; width: 48%;">Question</th>
                                                        <th class="py-3 text-uppercase text-secondary fw-bold" style="font-size: 11px; width: 12%;">Type</th>
                                                        <th class="py-3 text-uppercase text-secondary fw-bold" style="font-size: 11px; width: 10%;">Points</th>
                                                        <th class="pe-4 py-3 text-uppercase text-secondary fw-bold" style="font-size: 11px; width: 26%;">Correct Answer</th>
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
                                                                    <span class="badge bg-warning-subtle text-warning ms-1"><i class="ti ti-copy"></i> Cloned</span>
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
                                                            <span class="badge bg-info-subtle text-info border border-info-subtle fw-bold px-3 py-2" style="font-size: 13px;">
                                                                {{ $question->max_question_points ?? 0 }}
                                                            </span>
                                                        </td>
                                                        <td class="pe-4 align-top pt-3">
                                                            @if($correct)
                                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2" style="white-space:normal; text-align:left; line-height:1.5;">
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
                            <div class="text-center py-4 bg-light rounded border">
                                <i class="ti ti-stethoscope fs-2 text-muted d-block mb-2 opacity-50"></i>
                                <span class="text-muted small">No visits added to this case study yet.</span>
                            </div>
                        @endforelse

                    </div>
                </div>
            @empty
                <div class="card p-5 text-center border-dashed bg-light-subtle">
                    <div class="mb-3"><i class="ti ti-folder-off fs-1 text-muted opacity-50"></i></div>
                    <h5 class="text-muted fw-bold">No Case Studies Found</h5>
                    <p class="text-secondary small">This section doesn't have any case studies yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Bottom Back Button --}}
<div class="pb-5 mb-5 mt-4">
    <a href="{{ route('admin.sections.index') }}" class="btn btn-outline-secondary px-4">
        <i class="ti ti-arrow-left me-1"></i> Back to Sections
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
    .letter-spacing-1 { letter-spacing: 1px; }
    .fs-7 { font-size: 0.85rem; }
    .btn-xs { padding: 0.2rem 0.6rem; font-size: 11px; }
    .bg-purple-subtle { background-color: #f3e5f5 !important; }
    .text-purple { color: #7b1fa2 !important; }
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
});
</script>

@endsection