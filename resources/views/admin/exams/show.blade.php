@extends('layouts.app')

@section('content')
<!-- Page Header -->
<!-- Page Header -->
<div class="page-header d-print-none mb-4">
    <div class="row align-items-center w-100 m-0">
        <div class="col">
            <div class="page-pretitle text-muted small text-uppercase fw-bold tracking-wide">Exam Management</div>
            <h2 class="page-title fw-bolder text-dark mb-0 py-1">{{ $exam->name }}</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">
                <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Back to Exams
                </a>
                <a href="{{ route('admin.exams.edit', $exam->id) }}" class="btn btn-primary d-none d-sm-inline-block">
                    <i class="ti ti-edit me-1"></i> Edit Exam
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Exam Overview Card -->
<div class="card mb-4 shadow-sm border-0 rounded-3">
    <div class="card-body p-4">
        <div class="row g-4">
            <!-- Left Column: Details -->
            <div class="col-md-12">
                <div class="d-flex align-items-center mb-4 border-bottom pb-3">
                    <span class="badge {{ $exam->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-3 py-2 rounded-pill fw-bold text-uppercase me-3">
                        <i class="ti {{ $exam->is_active ? 'ti-circle-check' : 'ti-alert-circle' }} me-1"></i>
                        {{ $exam->is_active ? 'Published' : 'Unpublished' }}
                    </span>
                    <span class="text-muted d-flex align-items-center border-start ps-3">
                        <i class="ti ti-code me-1"></i>
                        <span class="text-uppercase small fw-bold letter-spacing-1">Code: {{ $exam->exam_code }}</span>
                    </span>
                </div>
                
                <p class="text-secondary fs-5 mb-5 text-break" style="line-height: 1.8;">
                    {{ $exam->description ?? 'No description provided for this exam.' }}
                </p>
                
                <!-- Stats Row -->
                <div class="row g-4 justify-content-between">
                    <div class="col-auto">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary-subtle text-primary rounded fs-3 p-3 me-3">
                                <i class="ti ti-clock"></i>
                            </div>
                            <div>
                                <div class="text-muted small text-uppercase fw-bold tracking-wide">Duration</div>
                                <div class="fs-4 fw-bold text-dark">{{ $exam->duration_minutes }} <span class="fs-6 text-muted fw-normal">mins</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="d-flex align-items-center">
                             <div class="bg-info-subtle text-info rounded fs-3 p-3 me-3">
                                <i class="ti ti-tag"></i>
                            </div>
                            <div>
                                <div class="text-muted small text-uppercase fw-bold tracking-wide">Category</div>
                                <div class="fs-4 fw-bold text-dark">{{ $exam->category->name ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                         <div class="d-flex align-items-center">
                             <div class="bg-purple-subtle text-purple rounded fs-3 p-3 me-3">
                                <i class="ti ti-certificate"></i>
                            </div>
                            <div>
                                <div class="text-muted small text-uppercase fw-bold tracking-wide">Certification</div>
                                <div class="fs-4 fw-bold text-dark">{{ $exam->certification_type }}</div>
                            </div>
                        </div>
                    </div>
                     <div class="col-auto">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning-subtle text-warning rounded fs-3 p-3 me-3">
                                <i class="ti ti-layers-intersect"></i>
                            </div>
                            <div>
                                <div class="text-muted small text-uppercase fw-bold tracking-wide">Total Sections</div>
                                <div class="fs-4 fw-bold text-dark">{{ $exam->sections->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hierarchy Content -->
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="text-uppercase text-muted small fw-bold tracking-wide mb-0">Exam Curriculum</h4>
            <button class="btn btn-sm btn-link text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target=".multi-collapse" aria-expanded="false" aria-controls="examAccordion">
                Expand/Collapse All
            </button>
        </div>

        <div class="accordion" id="examAccordion">
            @forelse($exam->sections as $index => $section)
                <div class="accordion-item mb-3 border-0 shadow-sm rounded-3 overflow-hidden">
                    <h2 class="accordion-header" id="heading{{ $section->id }}">
                        <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }} bg-white py-3 px-4 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $section->id }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $section->id }}">
                            <div class="d-flex align-items-center w-100">
                                <div class="me-3">
                                    <span class="fw-bold text-muted small">{{ $index + 1 }}</span>
                                </div>
                                <div class="flex-grow-1 border-start ps-3 ms-2">
                                    <h5 class="mb-0 fw-bold text-dark">{{ $section->title }}</h5>
                                </div>
                                <div class="me-3 text-end d-none d-sm-block">
                                    <span class="badge bg-secondary-subtle text-secondary border fw-normal">{{ $section->caseStudies->count() }} Case Studies</span>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse{{ $section->id }}" class="accordion-collapse collapse multi-collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $section->id }}">
                        <div class="accordion-body bg-light-subtle p-4">
                            @if($section->content)
                                <div class="card border-0 bg-white shadow-sm mb-4">
                                    <div class="card-body p-4 position-relative overflow-hidden">
                                        <div class="position-absolute top-0 start-0 w-1 pt-4 h-100 bg-primary opacity-25"></div>
                                        <h6 class="text-uppercase text-muted small fw-bold mb-3 d-flex align-items-center">
                                            <i class="ti ti-file-description me-2"></i>Section Instructions
                                        </h6>
                                        <div class="opacity-75">{!! $section->content !!}</div>
                                    </div>
                                </div>
                            @endif

                            <!-- Case Studies Timeline/List -->
                            <div class="d-flex flex-column gap-3">
                                @foreach($section->caseStudies as $csIndex => $caseStudy)
                                    <div class="card border border-secondary-subtle shadow-sm rounded-3">
                                        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center">
                                            <div class="bg-secondary-subtle text-secondary rounded p-1 me-3">
                                                <i class="ti ti-file-text"></i>
                                            </div>
                                            <h6 class="mb-0 fw-bold text-dark">Case Study {{ $csIndex + 1 }}: {{ $caseStudy->title }}</h6>
                                        </div>
                                        <div class="card-body p-4">
                                            @if($caseStudy->content)
                                                <div class="p-3 mb-4 bg-light-subtle border-start border-4 border-primary rounded-end">
                                                    <h6 class="text-uppercase text-muted fs-7 fw-bold mb-2">Case Study Scenario</h6>
                                                    <div class="text-dark opacity-100 text-break">{!! $caseStudy->content !!}</div>
                                                </div>
                                            @endif

                                            <!-- Questions Table -->
                                            @if($caseStudy->questions->count() > 0)
                                                <div class="table-responsive border rounded-3 mb-3">
                                                    <table class="table table-hover table-vcenter mb-0 table-fixed">
                                                        <thead class="bg-body-tertiary">
                                                            <tr>
                                                                <th class="ps-4 py-3 text-uppercase text-secondary fs-7 fw-bold" style="width: 5%;">#</th>
                                                                <th class="py-3 text-uppercase text-secondary fs-7 fw-bold" style="width: 50%;">Question Details</th>
                                                                <th class="py-3 text-uppercase text-secondary fs-7 fw-bold" style="width: 10%;">Type</th>
                                                                <th class="py-3 text-uppercase text-secondary fs-7 fw-bold" style="width: 15%;">Weights</th>
                                                                <th class="pe-4 py-3 text-uppercase text-secondary fs-7 fw-bold" style="width: 20%;">Correct Answer</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($caseStudy->questions as $qIndex => $question)
                                                                <tr>
                                                                    <td class="ps-4 align-top pt-3">
                                                                        <span class="text-secondary fw-medium">{{ $qIndex + 1 }}</span>
                                                                    </td>
                                                                    <td class="align-top pt-3 text-wrap">
                                                                        <div class="d-flex flex-column">
                                                                            <div class="fw-bold text-dark mb-2 text-break">{!! strip_tags($question->question_text) !!}</div>
                                                                            <div>
                                                                                <a class="btn btn-sm btn-light border btn-pill fs-7 py-1 px-3 text-decoration-none text-muted" data-bs-toggle="collapse" href="#qOptions{{ $question->id }}" role="button" aria-expanded="false">
                                                                                    <i class="ti ti-list me-1"></i> View {{ $question->options->count() }} Options
                                                                                </a>
                                                                                <div class="collapse mt-2" id="qOptions{{ $question->id }}">
                                                                                    <div class="card card-body bg-light-subtle border-0 p-3 small">
                                                                                        <ul class="list-unstyled mb-0">
                                                                                            @foreach($question->options as $opt)
                                                                                                <li class="mb-2 d-flex align-items-start">
                                                                                                    <div class="d-flex align-items-center justify-content-center me-2" style="height: 1.5em; min-width: 1.5em;">
                                                                                                        @if($opt->is_correct)
                                                                                                            <i class="ti ti-circle-check text-success fs-5"></i>
                                                                                                        @else
                                                                                                            <i class="ti ti-circle text-muted fs-5"></i>
                                                                                                        @endif
                                                                                                    </div>
                                                                                                    <span class="{{ $opt->is_correct ? 'text-success fw-bold' : 'text-secondary' }} text-break" style="line-height: 1.5;">
                                                                                                        {{ $opt->option_text }}
                                                                                                    </span>
                                                                                                </li>
                                                                                            @endforeach
                                                                                        </ul>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="align-top pt-3">
                                                                        <span class="badge bg-secondary-subtle text-secondary border fw-medium px-2 py-1">
                                                                            {{ ucfirst($question->question_type) }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="align-top pt-3">
                                                                        <div class="row g-2" style="width: 120px;">
                                                                            <div class="col-6">
                                                                                <div class="p-1 border rounded text-center bg-white">
                                                                                    <div class="text-xs text-muted text-uppercase fw-bold">IG</div>
                                                                                    <div class="fw-bold text-dark">{{ $question->ig_weight }}</div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-6">
                                                                                <div class="p-1 border rounded text-center bg-white">
                                                                                    <div class="text-xs text-muted text-uppercase fw-bold">DM</div>
                                                                                    <div class="fw-bold text-dark">{{ $question->dm_weight }}</div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="pe-4 align-top pt-3">
                                                                        @php $correct = $question->options->firstWhere('is_correct', 1); @endphp
                                                                        @if($correct)
                                                                            <div class="p-2 bg-success-subtle text-success-emphasis rounded border border-success-subtle">
                                                                                <div class="d-flex align-items-start">
                                                                                    <i class="ti ti-check me-2 mt-1"></i>
                                                                                    <span class="fw-medium small">{{ $correct->option_text }}</span>
                                                                                </div>
                                                                            </div>
                                                                        @else
                                                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="ti ti-alert-circle me-1"></i> Set Answer</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-center py-4 bg-light rounded border border-dashed text-muted">
                                                    <i class="ti ti-list-details fs-2 mb-2 d-block opacity-50"></i>
                                                    <span class="small">No questions added yet.</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                                
                                @if($section->caseStudies->isEmpty())
                                    <div class="text-center py-5">
                                        <div class="bg-white p-4 rounded-circle shadow-sm d-inline-block mb-3">
                                            <i class="ti ti-files text-secondary opacity-50 display-6"></i>
                                        </div>
                                        <h6 class="text-muted">No case studies in this section</h6>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <img src="{{ asset('assets/images/no-data.svg') }}" alt="No Data" style="max-width: 200px;" class="mb-4 opacity-75">
                    <h4 class="text-muted">This exam has no content yet</h4>
                    <a href="{{ route('admin.exams.edit', $exam->id) }}" class="btn btn-primary mt-3">
                        <i class="ti ti-plus me-1"></i> Add Content
                    </a>
                </div>
            @endforelse
        </div>
        
        <!-- Bottom Actions -->
        @if($exam->sections->count() > 0)
        <div class="d-flex justify-content-center mt-5 mb-5">
            <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-primary btn-pill px-4">
                Back to Exam List
            </a>
        </div>
        @endif
    </div>
</div>

<style>
    .letter-spacing-1 { letter-spacing: 1px; }
    .tracking-wide { letter-spacing: 0.05em; }
    .bg-purple-subtle { background-color: #f3e5f5 !important; }
    .text-purple { color: #7b1fa2 !important; }
    .accordion-button:not(.collapsed) { background-color: #fff; color: var(--bs-primary); box-shadow: inset 0 -1px 0 rgba(0,0,0,.125); }
    .accordion-button:not(.collapsed) .avatar { background-color: var(--bs-primary) !important; color: white !important; }
    .fs-7 { font-size: 0.85rem; }
    .text-xs { font-size: 0.75em; }
</style>
@endsection
