@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Exam Result</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">My Exams</a></li>
          <li class="breadcrumb-item"><a href="{{ route('exams.show', $attempt->studentExam->exam->id) }}">{{ $attempt->studentExam->exam->name }}</a></li>
          <li class="breadcrumb-item" aria-current="page">Result</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row">
    <!-- Score Summary Card -->
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-body p-0">
                <div class="row g-0">
                    <!-- Left: Pass/Fail Status -->
                    <div class="col-md-4 {{ $attempt->is_passed ? 'bg-success' : 'bg-danger' }} text-white p-4 d-flex align-items-center justify-content-center">
                        <div class="text-center">
                            <i class="ti {{ $attempt->is_passed ? 'ti-circle-check' : 'ti-circle-x' }} display-3 mb-3"></i>
                            <h2 class="fw-bold mb-2">{{ $attempt->is_passed ? 'PASSED' : 'FAILED' }}</h2>
                            <p class="mb-0 opacity-75">{{ $attempt->studentExam->exam->name }}</p>
                        </div>
                    </div>
                    
                    <!-- Right: Score Details -->
                    <div class="col-md-8 p-4">
                        <div class="row g-4">
                            <!-- Total Score -->
                            <div class="col-md-4 text-center">
                                <small class="text-muted text-uppercase d-block mb-2">Total Score</small>
                                <h1 class="fw-bold mb-0 {{ $attempt->is_passed ? 'text-success' : 'text-danger' }}">
                                    {{ round($attempt->total_score) }}%
                                </h1>
                            </div>
                            
                            <!-- IG Score -->
                            <div class="col-md-4 text-center border-start">
                                <small class="text-muted text-uppercase d-block mb-2">IG Score</small>
                                <h2 class="fw-bold mb-0 text-primary">{{ round($attempt->ig_score ?? 0) }}%</h2>
                                <small class="text-muted">Information Gathering</small>
                            </div>
                            
                            <!-- DM Score -->
                            <div class="col-md-4 text-center border-start">
                                <small class="text-muted text-uppercase d-block mb-2">DM Score</small>
                                <h2 class="fw-bold mb-0 text-warning">{{ round($attempt->dm_score ?? 0) }}%</h2>
                                <small class="text-muted">Decision Making</small>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Attempt Details -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ti ti-calendar text-muted"></i>
                                    <div>
                                        <small class="text-muted d-block">Completed On</small>
                                        <strong>{{ $attempt->ended_at->format('M d, Y h:i A') }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ti ti-clock text-muted"></i>
                                    <div>
                                        <small class="text-muted d-block">Duration</small>
                                        <strong>{{ $attempt->started_at->diffInMinutes($attempt->ended_at) }} minutes</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Actions -->
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <a href="{{ route('exams.show', $attempt->studentExam->exam->id) }}" class="btn btn-primary">
                        <i class="ti ti-arrow-left me-2"></i>Back to Exam Details
                    </a>
                    <a href="{{ route('exams.download', $attempt->id) }}" class="btn btn-outline-secondary">
                        <i class="ti ti-download me-2"></i>Download Answer Sheet
                    </a>
                    @if($attempt->studentExam->attempts_allowed - $attempt->studentExam->attempts_used > 0)
                        <form action="{{ route('exams.start', $attempt->studentExam->exam->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="ti ti-refresh me-2"></i>Attempt Again
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Performance Message -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                @if($attempt->is_passed)
                    <i class="ti ti-trophy text-warning display-4 mb-3"></i>
                    <h4 class="fw-bold mb-3">Congratulations!</h4>
                    <p class="text-muted mb-0">
                        You have successfully passed the exam with a score of <strong>{{ round($attempt->total_score) }}%</strong>. 
                        Great job! Keep up the excellent work.
                    </p>
                @else
                    <i class="ti ti-info-circle text-info display-4 mb-3"></i>
                    <h4 class="fw-bold mb-3">Keep Trying!</h4>
                    <p class="text-muted mb-0">
                        You scored <strong>{{ round($attempt->total_score) }}%</strong> on this attempt. 
                        @if($attempt->studentExam->attempts_allowed - $attempt->studentExam->attempts_used > 0)
                            Don't give up! You have <strong>{{ $attempt->studentExam->attempts_allowed - $attempt->studentExam->attempts_used }}</strong> attempt(s) remaining. 
                            Review your answers and try again.
                        @else
                            You have used all your attempts. Please contact your administrator for more information.
                        @endif
                    </p>
                @endif
    </div>
    
    <!-- Detailed Exam Review -->
    <div class="col-12 mt-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="mb-0 fw-bold"><i class="ti ti-file-text me-2"></i>Detailed Exam Review</h5>
            </div>
            <div class="card-body p-4">
                @php
                    $questionNumber = 1;
                @endphp
                
                @foreach($attempt->studentExam->exam->sections as $sectionIndex => $section)
                    <!-- Section Header -->
                    <div class="section-header mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="bg-primary text-white rounded px-3 py-1">
                                <strong>Section {{ $sectionIndex + 1 }}</strong>
                            </div>
                            <h4 class="mb-0 fw-bold">{{ $section->title }}</h4>
                        </div>
                        @if($section->content)
                            <div class="text-muted ms-2">{!! $section->content !!}</div>
                        @endif
                    </div>
                    
                    @foreach($section->caseStudies as $caseStudyIndex => $caseStudy)
                        <!-- Case Study Card -->
                        <div class="case-study-card mb-4 border rounded p-4 bg-light">
                            <div class="d-flex align-items-start gap-3 mb-3">
                                <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="min-width: 40px; height: 40px;">
                                    <i class="ti ti-file-description"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="fw-bold mb-2">{{ $caseStudy->title }}</h5>
                                    @if($caseStudy->content)
                                        <div class="case-study-text bg-white border rounded p-3 mb-3">
                                            <div class="text-dark" style="line-height: 1.6;">{!! $caseStudy->content !!}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Questions for this Case Study -->
                            @foreach($caseStudy->questions as $question)
                                @php
                                    $userAnswer = $userAnswers[$question->id] ?? null;
                                    $isAnswered = $userAnswer !== null;
                                    $isCorrect = $isAnswered ? $userAnswer['is_correct'] : false;
                                    $selectedOptionIds = $isAnswered ? $userAnswer['selected_options'] : [];
                                @endphp
                                
                                <div class="question-card mb-4 bg-white border rounded p-4 {{ $isAnswered ? ($isCorrect ? 'border-success' : 'border-danger') : 'border-warning' }}">
                                    <!-- Question Header -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="d-flex gap-2 align-items-start flex-grow-1">
                                            <span class="badge bg-secondary">Q{{ $questionNumber }}</span>
                                            <div class="flex-grow-1">
                                                <p class="mb-2 fw-semibold">{{ $question->question_text }}</p>
                                                <div class="d-flex gap-2 flex-wrap">
                                                    <span class="badge bg-light text-dark border">
                                                        <i class="ti ti-category me-1"></i>{{ ucfirst($question->category) }}
                                                    </span>
                                                    <span class="badge bg-light text-dark border">
                                                        <i class="ti ti-star me-1"></i>{{ $question->marks }} {{ $question->marks == 1 ? 'Mark' : 'Marks' }}
                                                    </span>
                                                    @if($isAnswered)
                                                        @if($isCorrect)
                                                            <span class="badge bg-success">
                                                                <i class="ti ti-check me-1"></i>Correct
                                                            </span>
                                                        @else
                                                            <span class="badge bg-danger">
                                                                <i class="ti ti-x me-1"></i>Incorrect
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-warning">
                                                            <i class="ti ti-alert-circle me-1"></i>Not Answered
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Options -->
                                    <div class="options-list">
                                        @foreach($question->options as $option)
                                            @php
                                                $isSelected = in_array($option->id, $selectedOptionIds);
                                                $isCorrectOption = $option->is_correct;
                                                
                                                // Determine the styling
                                                if ($isSelected && $isCorrectOption) {
                                                    $optionClass = 'border-success bg-success bg-opacity-10';
                                                    $iconClass = 'ti-check text-success';
                                                } elseif ($isSelected && !$isCorrectOption) {
                                                    $optionClass = 'border-danger bg-danger bg-opacity-10';
                                                    $iconClass = 'ti-x text-danger';
                                                } else {
                                                    $optionClass = 'border-secondary bg-light';
                                                    $iconClass = '';
                                                }
                                            @endphp
                                            
                                            <div class="option-item d-flex align-items-start gap-2 p-3 mb-2 border rounded {{ $optionClass }}">
                                                <div class="d-flex align-items-center gap-2 flex-grow-1">
                                                    <span class="badge bg-secondary">{{ $option->option_key }}</span>
                                                    <span class="flex-grow-1">{{ $option->option_text }}</span>
                                                </div>
                                                @if($isSelected)
                                                    <i class="ti {{ $iconClass }} fs-5"></i>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                
                                @php
                                    $questionNumber++;
                                @endphp
                            @endforeach
                        </div>
                    @endforeach
                    
                    @if(!$loop->last)
                        <hr class="my-5">
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
    .display-3 {
        font-size: 4rem;
    }
    .display-4 {
        font-size: 3rem;
    }
    
    /* Exam Review Styles */
    .section-header {
        border-left: 4px solid var(--bs-primary);
        padding-left: 1rem;
    }
    
    .case-study-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        transition: all 0.3s ease;
    }
    
    .case-study-text {
        font-size: 0.95rem;
        max-height: 400px;
        overflow-y: auto;
    }
    
    .question-card {
        transition: all 0.3s ease;
        border-width: 2px !important;
    }
    
    .question-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .option-item {
        transition: all 0.2s ease;
    }
    
    .option-item:hover {
        transform: translateX(4px);
    }
    
    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }
    
    /* Scrollbar styling for case study text */
    .case-study-text::-webkit-scrollbar {
        width: 6px;
    }
    
    .case-study-text::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .case-study-text::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }
    
    .case-study-text::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>

@endsection
