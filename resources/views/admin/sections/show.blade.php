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

@if($section->exam->exam_standard_id)
<!-- Exam Standard Progress Tracker -->
<div class="card mb-4 shadow-sm border-primary">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0">
            <i class="ti ti-target me-2"></i>
            Exam Standard Progress - {{ $section->exam->examStandard->name }}
        </h6>
    </div>
    <div class="card-body">
        @php
            $validation = $section->exam->validateStandardCompliance();
            $totalQuestions = $validation['total_questions'];
            $expectedTotal = $section->exam->total_questions ?? 0;
            
            // Get current section's questions by content area
            $sectionQuestions = [];
            foreach($section->caseStudies as $cs) {
                foreach($cs->questions as $q) {
                    if($q->content_area_id) {
                        if(!isset($sectionQuestions[$q->content_area_id])) {
                            $sectionQuestions[$q->content_area_id] = 0;
                        }
                        $sectionQuestions[$q->content_area_id]++;
                    }
                }
            }
        @endphp

        <!-- Overall Exam Progress -->
        <div class="alert alert-info mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="ti ti-info-circle me-2"></i>
                    <strong>Overall Exam Progress:</strong> {{ $totalQuestions }}/{{ $expectedTotal }} questions
                </div>
                <span class="badge {{ $validation['valid'] ? 'bg-success' : 'bg-warning' }}">
                    {{ round(($totalQuestions / max($expectedTotal, 1)) * 100) }}%
                </span>
            </div>
        </div>

        <!-- This Section's Contribution -->
        <h6 class="text-primary mb-3">
            <i class="ti ti-chart-bar me-2"></i>
            This Section's Contribution
        </h6>
        
        <div class="row g-3">
            @php
                $hasContribution = false;
            @endphp
            
            @foreach($validation['content_areas'] as $area)
                @if(isset($sectionQuestions[$area['id']]) && $sectionQuestions[$area['id']] > 0)
                    @php $hasContribution = true; @endphp
                    <div class="col-md-6">
                        <div class="card border-{{ $area['valid'] ? 'success' : 'warning' }}">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">{{ $area['name'] }}</h6>
                                        <small class="text-muted">{{ $area['percentage'] }}% of exam</small>
                                    </div>
                                    <span class="badge bg-primary">
                                        {{ $sectionQuestions[$area['id']] ?? 0 }} questions here
                                    </span>
                                </div>
                                <div class="progress mb-2" style="height: 20px;">
                                    <div class="progress-bar bg-{{ $area['valid'] ? 'success' : 'warning' }}" 
                                         style="width: {{ min(($area['current'] / max($area['required'], 1)) * 100, 100) }}%;">
                                        {{ $area['current'] }}/{{ $area['required'] }}
                                    </div>
                                </div>
                                @if(!$area['valid'])
                                    <small class="text-warning">
                                        <i class="ti ti-alert-circle"></i>
                                        Exam needs {{ $area['required'] - $area['current'] > 0 ? ($area['required'] - $area['current']) . ' more' : 'to remove ' . ($area['current'] - $area['required']) }}
                                    </small>
                                @else
                                    <small class="text-success">
                                        <i class="ti ti-circle-check"></i>
                                        Complete!
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
            
            @if(!$hasContribution)
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-2"></i>
                        This section has no questions yet. Add case studies and questions to contribute to the exam standard.
                    </div>
                </div>
            @endif
        </div>

        <!-- Suggestions -->
        @if(!$validation['valid'])
        <div class="alert alert-warning mt-3 mb-0">
            <h6 class="alert-heading">
                <i class="ti ti-bulb me-2"></i>
                Suggestions to Complete Exam
            </h6>
            <ul class="mb-0 small">
                @foreach($validation['errors'] as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
@endif

<!-- Case Studies List -->
<div class="row">
    <div class="col-12">
        <h4 class="text-uppercase text-muted small fw-bold tracking-wide mb-3">Section Content</h4>
         <div class="d-flex flex-column gap-3">
            @forelse($section->caseStudies as $csIndex => $caseStudy)
                <div class="card border border-secondary-subtle shadow-sm rounded-3">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center">
                        <div class="bg-secondary-subtle text-secondary rounded p-1 me-3">
                            <i class="ti ti-file-text"></i>
                        </div>
                         <h6 class="mb-0 fw-bold text-dark">Case Study {{ $csIndex + 1 }}: {{ $caseStudy->title }}</h6>
                         @if($caseStudy->cloned_from_id)
                            <span class="badge bg-warning text-dark ms-2"><i class="ti ti-copy"></i> Cloned</span>
                        @endif
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
                            <div class="text-center py-4 bg-light rounded border border-dashed">
                                <span class="text-muted">No questions added to this case study yet.</span>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="card p-5 text-center border-dashed bg-light-subtle">
                     <div class="mb-3">
                        <i class="ti ti-folder-off fs-1 text-muted opacity-50"></i>
                    </div>
                    <h5 class="text-muted fw-bold">No Case Studies Found</h5>
                    <p class="text-secondary small">This section doesn't have any case studies yet.</p>
                </div>
            @endforelse
         </div>
    </div>
</div>
@endsection