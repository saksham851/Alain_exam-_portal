@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="page-header d-print-none mb-4">
    <div class="row align-items-center w-100 m-0">
        <div class="col">
            <div class="page-pretitle text-muted small text-uppercase fw-bold tracking-wide">Question Management</div>
            <h2 class="page-title fw-bolder text-dark mb-0 py-1">Question Details</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">
                <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Back to Questions
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Question Overview Card -->
<div class="card mb-4 shadow-sm border-0 rounded-3">
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-md-12">
                 <div class="d-flex align-items-center mb-4 border-bottom pb-3">
                    <span class="badge {{ $question->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-3 py-2 rounded-pill fw-bold text-uppercase me-3">
                        <i class="ti {{ $question->is_active ? 'ti-circle-check' : 'ti-alert-circle' }} me-1"></i>
                        {{ $question->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    @if($question->cloned_from_id)
                         <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill fw-bold text-uppercase me-3">
                            <i class="ti ti-copy me-1"></i> Cloned
                        </span>
                    @endif
                    <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-bold text-uppercase me-3">
                        {{ $question->question_type }}
                    </span>
                 </div>
                 
                 <!-- Parent Hierarchy -->
                 <div class="bg-light-subtle rounded p-3 mb-4 border border-dashed">
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted text-uppercase fw-bold">Exam</small>
                            <div class="fw-bold">{{ $question->caseStudy->section->exam->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted text-uppercase fw-bold">Section</small>
                            <div class="fw-bold">{{ $question->caseStudy->section->title ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted text-uppercase fw-bold">Case Study</small>
                            <div class="fw-bold">{{ $question->caseStudy->title ?? 'N/A' }}</div>
                        </div>
                    </div>
                 </div>
                
                 <div class="text-secondary fs-5 mb-5 text-break" style="line-height: 1.8;">
                     <h6 class="text-uppercase text-muted small fw-bold mb-3">Question Text</h6>
                    {!! $question->question_text !!}
                 </div>

                 <!-- Options List -->
                 <h6 class="text-uppercase text-muted small fw-bold mb-3">Answer Options</h6>
                 <div class="card border border-secondary-subtle shadow-sm rounded-3">
                    <ul class="list-group list-group-flush">
                        @foreach($question->options as $option)
                            <li class="list-group-item p-3 {{ $option->is_correct ? 'bg-success-subtle' : '' }}">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        @if($option->is_correct)
                                            <i class="ti ti-circle-check-filled text-success fs-3"></i>
                                        @else
                                            <i class="ti ti-circle text-muted fs-3"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold {{ $option->is_correct ? 'text-success' : 'text-dark' }}">
                                            {{ $option->option_text }}
                                        </div>
                                         @if($option->is_correct)
                                            <small class="text-success fw-bold text-uppercase">Correct Answer (Key: {{ $option->option_key }})</small>
                                        @else
                                             <small class="text-muted">Key: {{ $option->option_key }}</small>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                 </div>
            </div>
        </div>
    </div>
</div>
@endsection
