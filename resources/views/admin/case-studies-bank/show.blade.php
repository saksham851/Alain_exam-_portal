
@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="page-header d-print-none mb-4">
    <div class="row align-items-center w-100 m-0">
        <div class="col">
            <div class="page-pretitle text-muted small text-uppercase fw-bold tracking-wide">Case Study Management</div>
            <h2 class="page-title fw-bolder text-dark mb-0 py-1">{{ $caseStudy->title }}</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">
                <a href="{{ route('admin.case-studies-bank.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Back to Bank
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Case Study Overview Card -->
<div class="card mb-4 shadow-sm border-0 rounded-3">
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-md-12">
                 <div class="d-flex align-items-center mb-4 border-bottom pb-3">
                    <span class="badge {{ $caseStudy->status ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-3 py-2 rounded-pill fw-bold text-uppercase me-3">
                        <i class="ti {{ $caseStudy->status ? 'ti-circle-check' : 'ti-alert-circle' }} me-1"></i>
                        {{ $caseStudy->status ? 'Active' : 'Inactive' }}
                    </span>
                    @if($caseStudy->cloned_from_id)
                         <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill fw-bold text-uppercase me-3">
                            <i class="ti ti-copy me-1"></i> Cloned
                        </span>
                    @endif
                     <span class="text-muted d-flex align-items-center border-start ps-3 me-3">
                        <i class="ti ti-folder me-1"></i>
                        <span class="text-uppercase small fw-bold letter-spacing-1">Section: {{ $caseStudy->section->title ?? 'N/A' }}</span>
                    </span>
                    <span class="text-muted d-flex align-items-center border-start ps-3">
                        <i class="ti ti-file-description me-1"></i>
                        <span class="text-uppercase small fw-bold letter-spacing-1">Exam: {{ $caseStudy->section->exam->name ?? 'N/A' }}</span>
                    </span>
                </div>
                
                 <div class="text-secondary fs-5 mb-5 text-break" style="line-height: 1.8;">
                     <h6 class="text-uppercase text-muted small fw-bold mb-3">Scenario Content</h6>
                    {!! $caseStudy->content ?? 'No content provided for this case study.' !!}
                 </div>

                 <!-- Stats Row -->
                 <div class="row g-4 justify-content-between">
                     <div class="col-auto">
                        <div class="d-flex align-items-center">
                            <div class="bg-info-subtle text-info rounded fs-3 p-3 me-3">
                                <i class="ti ti-help"></i>
                            </div>
                            <div>
                                <div class="text-muted small text-uppercase fw-bold tracking-wide">Total Questions</div>
                                <div class="fs-4 fw-bold text-dark">{{ $caseStudy->questions->count() }}</div>
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
                                <div class="fs-4 fw-bold text-dark">{{ $caseStudy->section->exam->category->name ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                 </div>
            </div>
        </div>
    </div>
</div>

<!-- Questions List -->
<div class="row">
    <div class="col-12">
        <h4 class="text-uppercase text-muted small fw-bold tracking-wide mb-3">Case Study Questions</h4>
         <div class="card border border-secondary-subtle shadow-sm rounded-3">
            <div class="card-body p-4">
                 @if($caseStudy->questions->count() > 0)
                    <div class="table-responsive border rounded-3 mb-3">
                        <table class="table table-hover table-vcenter mb-0 table-fixed">
                            <thead class="bg-body-tertiary">
                                <tr>
                                    <th class="ps-4 py-3 text-uppercase text-secondary fs-7 fw-bold" style="width: 5%;">#</th>
                                    <th class="py-3 text-uppercase text-secondary fs-7 fw-bold" style="width: 50%;">Question Details</th>
                                    <th class="py-3 text-uppercase text-secondary fs-7 fw-bold" style="width: 25%;">Type</th>
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
                                                <div class="fw-bold text-dark mb-2 text-break">
                                                    {!! strip_tags($question->question_text) !!}
                                                    @if($question->cloned_from_id)
                                                        <span class="badge bg-warning text-dark ms-2"><i class="ti ti-copy"></i> Cloned</span>
                                                    @endif
                                                </div>
                                                <!-- Options Logic -->
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
                                            <span class="badge bg-light-primary text-primary">{{ $question->question_type }}</span>
                                        </td>
                                        <td class="pe-4 align-top pt-3">
                                            @php
                                                $correctOption = $question->options->where('is_correct', 1)->first();
                                            @endphp
                                            <span class="badge bg-success-subtle text-success">{{ $correctOption->option_key ?? 'N/A' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                         <div class="mb-3">
                            <i class="ti ti-file-off fs-1 text-muted opacity-50"></i>
                        </div>
                        <h5 class="text-muted fw-bold">No Questions Found</h5>
                        <p class="text-secondary small">This case study doesn't have any questions yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
