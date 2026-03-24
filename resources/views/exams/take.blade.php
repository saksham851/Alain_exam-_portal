@extends('layouts.exam')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
        color: #1e293b;
        margin: 0;
        padding: 0;
    }

    /* Edge-to-edge layout optimization */
    .exam-container {
        width: 100%;
        max-width: 100%;
        margin: 0;
        padding: 0;
    }
    
    .content-inner {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 24px;
    }

    /* Fluid full-width header */
    .premium-header { 
        position: sticky; 
        top: 0; 
        z-index: 1050; 
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        width: 100%;
    }

    .header-content {
        height: 72px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 24px;
    }

    /* Polished Stepper on fluid background */
    .stepper-section {
        background-color: #fff;
        border-bottom: 1px solid #f1f5f9;
        padding: 16px 0;
        margin-bottom: 32px;
        width: 100%;
    }
    .section-stepper-container {
        position: relative;
        display: flex;
        justify-content: center;
        max-width: 800px;
        margin: 0 auto;
    }
    .step-connector {
        position: absolute;
        top: 50%;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: #f1f5f9;
        z-index: 1;
        transform: translateY(-50%);
    }
    .section-stepper {
        display: flex;
        justify-content: space-between;
        width: 100%;
        position: relative;
        z-index: 2;
    }
    .section-step {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #fff;
        border: 2px solid #e2e8f0;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.8rem;
        transition: all 0.3s ease;
    }
    .section-step.active {
        background-color: #01284E;
        border-color: #01284E;
        color: white;
        transform: scale(1.1);
        box-shadow: 0 4px 10px rgba(1, 40, 78, 0.2);
    }
    .section-step.passed {
        background-color: #f0f7ff;
        border-color: #3b82f6;
        color: #3b82f6;
    }

    /* Lean & Professional Content Cards */
    .case-study-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 24px;
    }
    .case-title-area {
        background-color: #fcfdfe;
        padding: 1rem 1.5rem;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #f1f5f9;
    }

    .question-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 2.5rem; /* Added generous internal padding */
        margin-bottom: 32px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
    }
    .question-head {
        margin-bottom: 1.5rem;
    }
    .q-badge {
        display: inline-block;
        background: #f0f7ff;
        color: #01284E;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        margin-bottom: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .question-text {
        font-size: 1.15rem; /* Reduced from 1.25rem */
        font-weight: 700;
        line-height: 1.6; /* More readable line height */
        color: #0f172a;
    }

    /* Smart Full-Width Options List */
    .options-container {
        display: flex;
        flex-direction: column;
        gap: 12px; /* Balanced spacing */
    }
    .option-row {
        cursor: pointer;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        padding: 1.25rem 1.5rem !important; /* Balanced padding */
        transition: all 0.2s ease;
        display: flex;
        align-items: flex-start;
        margin-bottom: 0px;
        background: #fff;
    }
    .option-row:hover {
        background-color: #f8fafc;
        border-color: #cbd5e1 !important;
    }
    .option-row:has(input:checked) {
        border-color: #01284E !important;
        background-color: #f0f7ff;
        box-shadow: 0 2px 8px rgba(1, 40, 78, 0.05);
    }
    .option-radio-ui {
        min-width: 20px;
        margin-top: 4px;
    }
    .option-text {
        font-size: 0.95rem; /* Reduced from 1.05rem for a cleaner look */
        font-weight: 500;
        color: #475569;
        margin-left: 1rem;
        line-height: 1.5;
    }
    .option-row:has(input:checked) .option-text {
        color: #0F5EF7;
        font-weight: 600;
    }

    /* Responsive fluid action bar */
    .action-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #fff;
        border-top: 1px solid #e2e8f0;
        padding: 16px 24px;
        z-index: 1040;
    }
    
    .action-inner {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .btn-action {
        border-radius: 8px;
        padding: 10px 24px;
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.2s;
    }

    [x-cloak] { display: none !important; }
</style>

{{-- 
    Flatten case studies components to Questions as the primary unit ("Slides").
--}}
@php
    $questionsList = [];
    $sectionsMap = [];
    
    foreach($exam->sections as $secIndex => $section) {
        $sectionsMap[$section->id] = [
            'id' => $section->id,
            'title' => $section->title,
            'index' => $secIndex + 1 
        ];

        foreach($section->caseStudies as $caseStudy) {
            foreach($caseStudy->visits as $visit) {
                foreach($visit->questions as $question) {
                    $questionsList[] = [
                        'question' => $question,
                        'section_id' => $section->id, 
                        'visit_id' => $visit->id,
                        // ... other fields
                        'section_title' => $section->title,
                        'case_title' => $caseStudy->title,
                        'case_id' => $caseStudy->id,
                        'case_content' => $caseStudy->content, 
                        'visit_title' => $visit->title,
                        'visit_content' => $visit->description,
                        'scenario_content' => $section->content, 
                        'sub_content' => $caseStudy->content
                    ];
                }
            }
        }
    }
@endphp

<div x-data="examWizard(
    {{ count($questionsList) }}, 
    {{ json_encode(array_column($questionsList, 'section_id')) }},
    {{ json_encode(array_column($questionsList, 'visit_id')) }}
)" x-init="initTimer()" x-cloak>
    
    <!-- START OVERLAY -->
    <div id="startOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #fff; z-index: 10000; display: flex; justify-content: center; align-items: center; flex-direction: column;">
        <div class="text-center p-4">
            <img src="{{ asset('assets/images/logo-new.png') }}" alt="Logo" class="mb-5" style="height: 60px;">
            <h2 class="mb-4 fw-bold text-dark">Ready to Begin?</h2>
            
            <div class="text-start mx-auto mb-5" style="max-width: 600px;">
                <h6 class="fw-bold text-dark mb-3 text-center">Please review the following before starting your exam:</h6>
                <div class="d-flex align-items-start mb-3">
                    <i class="ti ti-circle-check text-success mt-1 me-2 f-18"></i>
                    <p class="text-dark mb-0">Once you start the exam, the timer cannot be paused or stopped.</p>
                </div>
                <div class="d-flex align-items-start mb-3">
                    <i class="ti ti-circle-check text-success mt-1 me-2 f-18"></i>
                    <p class="text-dark mb-0">If you exit or cancel the exam after starting, the attempt will still be counted as completed.</p>
                </div>
                <div class="d-flex align-items-start mb-3">
                    <i class="ti ti-circle-check text-success mt-1 me-2 f-18"></i>
                    <p class="text-dark mb-0">Read each case study and question carefully before selecting your answer.</p>
                </div>
                <div class="d-flex align-items-start mb-3">
                    <i class="ti ti-circle-check text-success mt-1 me-2 f-18"></i>
                    <p class="text-dark mb-0">Make sure you are ready and have sufficient uninterrupted time before beginning. When you are ready, click <strong>Start Exam</strong>.</p>
                </div>
            </div>
            
            <button class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow-lg fw-bold d-inline-flex align-items-center gap-2" @click="startFullscreen()">
                Start Exam <i class="ti ti-chevron-right fs-4"></i>
            </button>
        </div>
    </div>

    <!-- CLEAN PROFESSIONAL HEADER (FLUID) -->
    <header class="premium-header">
        <div class="header-content">
            <div class="d-flex align-items-center">
                <img src="{{ asset('assets/images/logo-new.png') }}" alt="Logo" style="height: 32px;">
                <div class="vr mx-3 bg-slate-200" style="height: 24px; opacity: 1;"></div>
                <div>
                    <h6 class="mb-0 fw-bold text-slate-800" style="font-size: 0.9rem;">{{ $exam->name }}</h6>
                    <small class="text-muted">Question <span x-text="currentIndex + 1"></span> of <span x-text="totalSlides"></span></small>
                </div>
            </div>

            <div class="d-flex align-items-center gap-4">
                <div class="text-end">
                    <span class="text-slate-400 text-uppercase fw-bold" style="font-size: 0.55rem; display: block;">Time Left</span>
                    <h5 class="mb-0 text-slate-800 fw-bold tabular-nums" x-text="formattedTime">--:--:--</h5>
                </div>
                
                <div class="vr bg-slate-200" style="height: 32px; opacity: 1;"></div>

                <div class="d-flex align-items-center d-none d-md-flex">
                    <div class="avtar avtar-xs bg-light-primary text-primary rounded-circle me-2">
                        <i class="ti ti-user fs-6"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold" style="font-size: 0.8rem;">{{ auth()->user()->first_name }}</h6>
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-link text-danger fw-bold text-decoration-none p-0" data-bs-toggle="modal" data-bs-target="#quitModal">
                    QUIT
                </button>
            </div>
        </div>
        <!-- Ultra slim progress bar -->
        <div style="height: 2px; background: #f1f5f9; width: 100%;">
            <div class="bg-primary transition-all" :style="`height: 100%; width: ${progressPercentage}%`"></div>
        </div>
    </header>

    <!-- STEPPER (FLUID BG) -->
    <div class="stepper-section">
        <div class="section-stepper-container">
            <div class="step-connector"></div>
            <div class="section-stepper px-3">
                @foreach($sectionsMap as $secId => $secData)
                    <div class="section-step" 
                         :class="{ 
                            'active': currentSectionId === {{ $secId }},
                            'passed': currentSectionId > {{ $secId }}
                         }">
                        {{ $secData['index'] }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- CONTENT AREA (CENTERED INNER) -->
    <main class="content-inner pb-5">
        <form action="{{ route('exams.submit', $exam->id) }}" method="POST" id="examForm" @submit.prevent="submitForm" novalidate>
            @csrf

            @foreach($questionsList as $index => $slide)
                <div x-show="currentIndex === {{ $index }}" class="slide-container" data-section-id="{{ $slide['section_id'] }}">
                    
                    @if(!empty(strip_tags($slide['case_title'] ?? '')))
                    <div class="case-study-box">
                        <div class="case-title-area">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-file-text text-primary fs-5 me-2"></i>
                                <h6 class="mb-0 fw-bold text-slate-700">{{ $slide['case_title'] }}</h6>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(!empty(strip_tags($slide['visit_title'] ?? '')))
                    <div class="case-study-box mt-3">
                        <div class="case-title-area bg-light" @click="isVisitExpanded = !isVisitExpanded">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-notes text-secondary fs-5 me-2"></i>
                                <h6 class="mb-0 fw-bold text-slate-700">{{ $slide['visit_title'] }}</h6>
                            </div>
                            <i class="ti fs-5 text-muted transition-transform" :class="isVisitExpanded ? 'ti-chevron-up' : 'ti-chevron-down'"></i>
                        </div>
                        <div x-show="isVisitExpanded" x-collapse>
                            @if(!empty(strip_tags($slide['visit_content'] ?? '')))
                                <div class="p-4 border-top bg-white">
                                    <div class="text-slate-600 leading-relaxed">{!! $slide['visit_content'] !!}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="text-center py-5" x-show="!viewedCases.includes({{ $slide['visit_id'] }}) && !completedVisitIds.includes({{ $slide['visit_id'] }})">
                        <button type="button" class="btn btn-primary btn-lg rounded-pill px-5 py-3 shadow-sm fw-bold d-inline-flex align-items-center gap-2" @click="viewedCases.push({{ $slide['visit_id'] }})">
                            View Questions <i class="ti ti-chevron-right"></i>
                        </button>
                    </div>

                    <div class="question-card" x-show="viewedCases.includes({{ $slide['visit_id'] }}) && !completedVisitIds.includes({{ $slide['visit_id'] }})" x-transition.opacity>
                        <div class="question-head">
                            <span class="q-badge">Question {{ $index + 1 }}</span>
                            <h2 class="question-text">{!! $slide['question']->question_text !!}</h2>
                        </div>

                        <div class="options-container">
                            @php $q = $slide['question']; @endphp
                            @foreach($q->options as $option)
                                <label class="option-row" for="opt_{{ $option->id }}">
                                    <div class="option-radio-ui">
                                        @if($q->question_type === 'multiple')
                                            <input class="form-check-input" type="checkbox" name="answers[{{ $q->id }}][]" id="opt_{{ $option->id }}" value="{{ $option->option_text }}">
                                        @else
                                            <input class="form-check-input" type="radio" name="answers[{{ $q->id }}]" id="opt_{{ $option->id }}" value="{{ $option->option_text }}">
                                        @endif
                                    </div>
                                    <span class="option-text">{{ $option->option_text }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Action Bar (FLUID) -->
            <div class="action-bar shadow-sm">
                <div class="action-inner">
                    <!-- Can only go back if the previous question is in the same section -->
                    <button type="button" class="btn btn-light btn-action" 
                            x-show="currentIndex > 0" 
                            @click="prevSlide()">
                       <i class="ti ti-chevron-left me-1"></i> Go Back
                    </button>
                    
                    <div class="ms-auto d-flex gap-2">
                        <!-- Next button (for non-last question of visit) -->
                        <button type="button" class="btn btn-primary btn-action" 
                                x-show="currentIndex < totalSlides - 1 && !isLastQuestionOfVisit()" 
                                @click="nextSlide()">
                            Continue <i class="ti ti-chevron-right ms-1"></i>
                        </button>
                        
                        <!-- Submit button (for last question of visit, except final question) -->
                        <button type="button" class="btn btn-success btn-action" 
                                x-show="currentIndex < totalSlides - 1 && isLastQuestionOfVisit()" 
                                data-bs-toggle="modal" 
                                data-bs-target="#visitSubmitModal">
                            Submit <i class="ti ti-check ms-1"></i>
                        </button>
                        
                        <!-- Finish exam button (final question) -->
                        <button type="button" class="btn btn-success btn-action" 
                                x-show="currentIndex === totalSlides - 1" 
                                data-bs-toggle="modal" 
                                data-bs-target="#visitSubmitModal"
                                @click="isLastQuestionOfExam = true">
                            Submit <i class="ti ti-check ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </main>

    <!-- QUIT MODAL -->
    <div class="modal fade" id="quitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content overflow-hidden border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-body text-center p-5">
                    <div class="avtar avtar-xl bg-light-danger text-danger mb-4" style="width: 80px; height: 80px; margin: 0 auto; font-size: 2rem;">
                        <i class="ti ti-alert-triangle"></i>
                    </div>
                    <h3 class="fw-bold text-slate-800 mb-2">Quit Examination?</h3>
                    <p class="text-muted mb-4 lead" style="font-size: 1rem;">Are you sure you want to end your exam session? Your current progress will be submitted and you won't be able to return.</p>
                    <div class="d-grid gap-3 mt-4">
                        <button type="button" class="btn btn-danger btn-lg py-3 rounded-3 fw-bold shadow-sm" @click="if(!isSubmitting) { isSubmitting = true; $el.disabled = true; showLoadingAndSubmit(); }">
                            TERMINATE &amp; SUBMIT
                        </button>
                        <button type="button" class="btn btn-light btn-lg py-3 rounded-3 fw-bold text-slate-500" data-bs-dismiss="modal">
                            CANCEL, CONTINUE EXAM
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- VISIT SUBMISSION MODAL -->
    <div class="modal fade" id="visitSubmitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content overflow-hidden border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-body text-center p-5">
                    <h3 class="fw-bold text-slate-800 mb-3">Submit Answers For This Session</h3>
                    <p class="text-muted mb-4 lead" style="font-size: 1rem;">You are about to submit your answers for this session. Once submitted, your answers are final and you will not be able to return to this portion of the exam.</p>
                    <div class="d-grid gap-3 mt-4">
                        <button type="button" class="btn btn-success btn-lg py-3 rounded-3 fw-bold shadow-sm" onclick="handleVisitSubmit()">
                            <i class="ti ti-check me-2"></i> SUBMIT
                        </button>
                        <button type="button" class="btn btn-light btn-lg py-3 rounded-3 fw-bold text-slate-600" onclick="handleVisitGoBack()">
                            GO BACK
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SUBMIT CONFIRMATION MODAL -->
    <div class="modal fade" id="submitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content overflow-hidden border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-body text-center p-5">
                    <div class="avtar avtar-xl bg-light-success text-success mb-4" style="width: 80px; height: 80px; margin: 0 auto; font-size: 2rem;">
                        <i class="ti ti-clipboard-check"></i>
                    </div>
                    <h3 class="fw-bold text-slate-800 mb-2">Submit Exam?</h3>
                    <p class="text-muted mb-4 lead" style="font-size: 1rem;">Are you sure you want to submit your exam? Once submitted, you cannot make any changes.</p>
                    <div class="d-grid gap-3 mt-4">
                        <button type="button" class="btn btn-success btn-lg py-3 rounded-3 fw-bold shadow-sm" onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm me-2\'></span> SUBMITTING...'; showLoadingAndSubmitGlobal()">
                            <i class="ti ti-check me-2"></i> YES, SUBMIT EXAM
                        </button>
                        <button type="button" class="btn btn-light btn-lg py-3 rounded-3 fw-bold text-slate-500" data-bs-dismiss="modal">
                            CANCEL, CONTINUE EXAM
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RESULT LOADING SCREEN -->
    <div id="resultLoadingOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%); z-index:99999; justify-content:center; align-items:center; flex-direction:column;">
        <div style="text-align:center; color:#fff; padding: 2rem;">
            <!-- Animated Logo -->
            <div style="margin-bottom: 2.5rem;">
                <img src="{{ asset('assets/images/logo-new.png') }}" alt="Logo" style="height: 50px; filter: brightness(0) invert(1); opacity: 0.9;">
            </div>
            <!-- Spinner -->
            <div style="position:relative; width:100px; height:100px; margin: 0 auto 2rem;">
                <svg viewBox="0 0 100 100" style="width:100px; height:100px; animation: spin 1.8s linear infinite;">
                    <circle cx="50" cy="50" r="42" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="8"/>
                    <circle cx="50" cy="50" r="42" fill="none" stroke="#3b82f6" stroke-width="8"
                            stroke-dasharray="264" stroke-dashoffset="180" stroke-linecap="round"/>
                </svg>
                <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center;">
                    <i class="ti ti-file-analytics" style="font-size:2rem; color:#60a5fa;"></i>
                </div>
            </div>
            <!-- Text -->
            <h2 style="font-weight:800; font-size:1.6rem; margin-bottom:0.5rem; color:#fff;">Generating Your Result</h2>
            <p style="color:rgba(255,255,255,0.6); font-size:1rem; margin-bottom:2rem;">Please wait while we evaluate your responses...</p>
            <!-- Progress dots -->
            <div style="display:flex; justify-content:center; gap:8px;">
                <div style="width:10px; height:10px; background:#3b82f6; border-radius:50%; animation: bounce 1.2s ease-in-out 0s infinite;"></div>
                <div style="width:10px; height:10px; background:#3b82f6; border-radius:50%; animation: bounce 1.2s ease-in-out 0.2s infinite;"></div>
                <div style="width:10px; height:10px; background:#3b82f6; border-radius:50%; animation: bounce 1.2s ease-in-out 0.4s infinite;"></div>
            </div>
            <!-- Steps -->
            <div style="margin-top:2.5rem; display:flex; flex-direction:column; gap:12px; max-width:300px; margin-left:auto; margin-right:auto;">
                <div id="step1" style="display:flex; align-items:center; gap:12px; opacity:0.4; transition: opacity 0.5s;">
                    <div style="width:28px; height:28px; border-radius:50%; background:rgba(59,130,246,0.3); display:flex; align-items:center; justify-content:center;"><i class="ti ti-check" style="color:#60a5fa; font-size:0.85rem;"></i></div>
                    <span style="font-size:0.85rem; color:rgba(255,255,255,0.8);">Submitting answers</span>
                </div>
                <div id="step2" style="display:flex; align-items:center; gap:12px; opacity:0.4; transition: opacity 0.5s;">
                    <div style="width:28px; height:28px; border-radius:50%; background:rgba(59,130,246,0.3); display:flex; align-items:center; justify-content:center;"><i class="ti ti-calculator" style="color:#60a5fa; font-size:0.85rem;"></i></div>
                    <span style="font-size:0.85rem; color:rgba(255,255,255,0.8);">Calculating scores</span>
                </div>
                <div id="step3" style="display:flex; align-items:center; gap:12px; opacity:0.4; transition: opacity 0.5s;">
                    <div style="width:28px; height:28px; border-radius:50%; background:rgba(59,130,246,0.3); display:flex; align-items:center; justify-content:center;"><i class="ti ti-chart-bar" style="color:#60a5fa; font-size:0.85rem;"></i></div>
                    <span style="font-size:0.85rem; color:rgba(255,255,255,0.8);">Building your report</span>
                </div>
            </div>
        </div>
        <style>
            @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
            @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        </style>
    </div>

    <!-- BLACK SCREEN BLOCKER -->
    <div id="screenshotBlocker" style="opacity: 0; pointer-events: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: black; z-index: 2147483647; transition: none;"></div>

    <!-- AlpineJS Logic -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        // Global functions for modal buttons (outside Alpine context)
        function handleVisitSubmit() {
            // Hide the modal
            const visitModal = document.getElementById('visitSubmitModal');
            if (visitModal) {
                const modal = bootstrap.Modal.getInstance(visitModal);
                if (modal) {
                    modal.hide();
                }
            }
            
            // Get Alpine component and navigate
            setTimeout(() => {
                const examEl = document.querySelector('[x-data*="examWizard"]');
                if (examEl && window.Alpine) {
                    const alpineData = Alpine.$data(examEl);
                    if (alpineData) {
                        // Check if this is the last question of exam
                        // Mark current visit as completed
                        const currentVisitId = alpineData.visitIds[alpineData.currentIndex];
                        if (!alpineData.completedVisitIds.includes(currentVisitId)) {
                            alpineData.completedVisitIds.push(currentVisitId);
                        }

                        if (alpineData.isLastQuestionOfExam) {
                            alpineData.isLastQuestionOfExam = false;
                            // Submit entire exam
                            alpineData.submitForm();
                        } else {
                            // Just move to next slide (next visit)
                            alpineData.nextSlide();
                        }
                    }
                }
            }, 200);
        }

        function handleVisitGoBack() {
            // Hide the modal
            const visitModal = document.getElementById('visitSubmitModal');
            if (visitModal) {
                const modal = bootstrap.Modal.getInstance(visitModal);
                if (modal) {
                    modal.hide();
                }
            }
            
            // Reset the flag
            const examEl = document.querySelector('[x-data*="examWizard"]');
            if (examEl && window.Alpine) {
                const alpineData = Alpine.$data(examEl);
                if (alpineData) {
                    alpineData.isLastQuestionOfExam = false;
                }
            }
            
            // Get Alpine component and navigate back
            setTimeout(() => {
                const examEl = document.querySelector('[x-data*="examWizard"]');
                if (examEl && window.Alpine) {
                    const alpineData = Alpine.$data(examEl);
                    if (alpineData) {
                        alpineData.prevSlide();
                    }
                }
            }, 200);
        }

        function examWizard(totalSlides, questionSections, visitIds) {
            return {
                currentIndex: 0,
                totalSlides: totalSlides,
                questionSections: questionSections,
                visitIds: visitIds,
                completedVisitIds: [],
                isLastQuestionOfExam: false,
                expiryTimestamp: {{ \Carbon\Carbon::parse($attempt->started_at)->addMinutes($exam->duration_minutes)->timestamp }} * 1000,
                now: new Date().getTime(),
                
                pendingAction: null,
                isSubmitting: false, 
                boundHandleBeforeUnload: null,
                isCaseExpanded: true,
                isVisitExpanded: true,
                viewedCases: [],
                
                currentSectionId: null,
                
                updateCurrentSection() {
                    this.$nextTick(() => {
                        const slide = document.querySelector(`.slide-container[x-show="currentIndex === ${this.currentIndex}"]`);
                        if(slide) {
                           this.currentSectionId = parseInt(slide.getAttribute('data-section-id'));
                        }
                    });
                },

                isLastQuestionOfVisit() {
                    // Check if current question is the last one in its visit
                    if (this.currentIndex >= this.totalSlides - 1) return false; // Last question of exam
                    
                    const currentVisitId = this.visitIds[this.currentIndex];
                    const nextVisitId = this.visitIds[this.currentIndex + 1];
                    
                    // If next question is in a different visit, current is last of its visit
                    return currentVisitId !== nextVisitId;
                },

                visitSubmit() {
                    // Close modal and move to next slide
                    try {
                        const visitModal = bootstrap.Modal.getInstance(document.getElementById('visitSubmitModal'));
                        if (visitModal) visitModal.hide();
                    } catch(e) {}
                    
                    setTimeout(() => { this.nextSlide(); }, 300);
                },

                init() {
                    this.updateCurrentSection();
                    this.initTimer();
                },
                
                confirmSubmit(action) {
                    let text = "Are you sure you want to finish and submit?";
                    if (action === 'quit') {
                        text = "This will submit your exam as is. Cannot be undone. Quit now?";
                    }
                    
                    if (confirm(text)) {
                        this.submitForm();
                    }
                },
                
                get progressPercentage() {
                    return ((this.currentIndex + 1) / this.totalSlides) * 100;
                },

                get formattedTime() {
                    const distance = this.expiryTimestamp - this.now;
                    
                    if (distance < 0) { 
                        return "00:00:00"; 
                    }

                    const h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const s = Math.floor((distance % (1000 * 60)) / 1000);

                    return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
                },

                handleScrollLogic(oldSecId) {
                    const slide = document.querySelector(`.slide-container[x-show="currentIndex === ${this.currentIndex}"]`);
                    if(!slide) return;
                    
                    const newSecId = parseInt(slide.getAttribute('data-section-id'));
                    this.currentSectionId = newSecId;

                    if (newSecId !== oldSecId) {
                        // Section changed: Scroll to very top to read the new scenario
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } else {
                        // Same section: Focus on the Question/Case Study card
                        const targetCard = slide.querySelector('.case-study-card');
                        if (targetCard) {
                            const offset = 160; // Extra room for sticky timer and header
                            const targetTop = targetCard.getBoundingClientRect().top + window.pageYOffset - offset;
                            window.scrollTo({ top: targetTop, behavior: 'smooth' });
                        }
                    }
                },

                nextSlide() {
                    if (this.currentIndex < this.totalSlides - 1) {
                        const oldSecId = this.currentSectionId;
                        const currentVisitId = this.visitIds[this.currentIndex];
                        
                        // If we are on a completed visit, jump to the first question of the NEXT visit
                        if (this.completedVisitIds.includes(currentVisitId)) {
                            let idx = this.currentIndex;
                            while (idx < this.totalSlides && this.visitIds[idx] === currentVisitId) {
                                idx++;
                            }
                            if (idx < this.totalSlides) {
                                this.currentIndex = idx;
                            } else {
                                // Fallback just in case
                                this.currentIndex++;
                            }
                        } else {
                            this.currentIndex++;
                        }
                        
                        // Expansion state for the new current slide
                        // If it's a completed visit, keep it collapsed (initially hidden)
                        this.isVisitExpanded = !this.completedVisitIds.includes(this.visitIds[this.currentIndex]);
                        this.isCaseExpanded = true;
                        
                        this.$nextTick(() => { this.handleScrollLogic(oldSecId); });
                    }
                },

                prevSlide() {
                    if (this.currentIndex > 0) {
                        const oldSecId = this.currentSectionId;
                        const currentVisitId = this.visitIds[this.currentIndex];
                        const prevVisitId = this.visitIds[this.currentIndex - 1];
                        
                        if (currentVisitId === prevVisitId) {
                            // Moving within the same visit, just go back one question
                            this.currentIndex--;
                        } else {
                            // Moving to a previous visit. Jump to the first question of that visit.
                            let idx = this.currentIndex - 1;
                            const targetVisitId = this.visitIds[idx];
                            while (idx > 0 && this.visitIds[idx - 1] === targetVisitId) {
                                idx--;
                            }
                            this.currentIndex = idx;
                        }
                        
                        // If we returned to a completed visit, it should be initially hidden (collapsed)
                        this.isVisitExpanded = !this.completedVisitIds.includes(this.visitIds[this.currentIndex]);
                        this.isCaseExpanded = true;
                        this.$nextTick(() => { this.handleScrollLogic(oldSecId); });
                    }
                },

                submitForm() {
                    this.isSubmitting = true;
                    if (this.boundHandleBeforeUnload) {
                        try { window.removeEventListener('beforeunload', this.boundHandleBeforeUnload); } catch(e) {}
                    }
                    // Show loading overlay
                    const overlay = document.getElementById('resultLoadingOverlay');
                    if (overlay) {
                        overlay.style.display = 'flex';
                        // Animate steps sequentially
                        setTimeout(() => { const s = document.getElementById('step1'); if(s) s.style.opacity = '1'; }, 300);
                        setTimeout(() => { const s = document.getElementById('step2'); if(s) s.style.opacity = '1'; }, 900);
                        setTimeout(() => { const s = document.getElementById('step3'); if(s) s.style.opacity = '1'; }, 1500);
                    }
                    // Close any open modals first
                    try {
                        const modals = document.querySelectorAll('.modal.show');
                        modals.forEach(m => { const bsModal = bootstrap.Modal.getInstance(m); if(bsModal) bsModal.hide(); });
                    } catch(e) {}
                    // Submit after brief delay so loading screen shows
                    setTimeout(() => {
                        const form = document.getElementById('examForm');
                        if (form) form.submit();
                    }, 800);
                },

                showLoadingAndSubmit() {
                    // Close quit modal first, then show loading
                    try {
                        const quitModal = bootstrap.Modal.getInstance(document.getElementById('quitModal'));
                        if (quitModal) quitModal.hide();
                    } catch(e) {}
                    setTimeout(() => { this.submitForm(); }, 300);
                },

                handleBeforeUnload(e) {
                    if (this.isSubmitting || window._examSubmitting) return;
                    e.preventDefault();
                    e.returnValue = ''; 
                },

                startFullscreen() {
                    const elem = document.documentElement;
                    if (elem.requestFullscreen) { elem.requestFullscreen(); }
                    else if (elem.webkitRequestFullscreen) { elem.webkitRequestFullscreen(); }
                    else if (elem.msRequestFullscreen) { elem.msRequestFullscreen(); }
                    document.getElementById('startOverlay').style.display = 'none';
                },

                initTimer() {
                    history.pushState(null, null, location.href);
                    window.onpopstate = function () { history.go(1); };
                    
                    setInterval(() => { 
                        this.now = new Date().getTime();
                        if (this.expiryTimestamp - this.now < 0) {
                            if (!this.isSubmitting) this.submitForm();
                        }
                    }, 1000);

                    document.addEventListener('contextmenu', event => event.preventDefault());
                    document.addEventListener('selectstart', event => event.preventDefault());
                    document.addEventListener('copy', event => event.preventDefault());
                    document.addEventListener('paste', event => event.preventDefault());
                    document.addEventListener('cut', event => event.preventDefault());

                    const blockKeys = (e) => {
                        const isFunctionKey = /^F[0-9]+$/.test(e.key);
                        if (e.altKey || e.ctrlKey || e.metaKey || e.key === 'Tab' || e.key === 'Escape' || e.key === 'PrintScreen' || isFunctionKey) {
                            e.preventDefault();
                            e.stopPropagation();
                            if (e.key === 'PrintScreen') {
                                document.getElementById('screenshotBlocker').style.opacity = '1';
                                try { navigator.clipboard.writeText(''); } catch(err) {}
                                setTimeout(() => { document.getElementById('screenshotBlocker').style.opacity = '0'; }, 500);
                            }
                            return false;
                        }
                    };

                    window.addEventListener('keydown', blockKeys, true);
                    window.addEventListener('keyup', (e) => {
                        if (e.key === 'PrintScreen') { try { navigator.clipboard.writeText(''); } catch(err) {} }
                    });
                    
                    if (navigator.webdriver) { try { this.submitForm(); } catch(e) {} }
                    
                    document.addEventListener("visibilitychange", () => {
                       if (document.hidden && !this.isSubmitting) this.showViolationOverlay();
                    });
                    window.addEventListener("blur", () => {
                        if (!this.isSubmitting) this.showViolationOverlay();
                    });
                    
                    const onFullscreenChange = () => {
                        if (!document.fullscreenElement && !document.webkitIsFullScreen && !document.mozFullScreen && !document.msFullscreenElement) {
                             if (!this.isSubmitting) this.showViolationOverlay();
                        }
                    };
                    document.addEventListener('fullscreenchange', onFullscreenChange);
                    document.addEventListener('webkitfullscreenchange', onFullscreenChange);
                    document.addEventListener('mozfullscreenchange', onFullscreenChange);
                    document.addEventListener('MSFullscreenChange', onFullscreenChange);

                    this.boundHandleBeforeUnload = this.handleBeforeUnload.bind(this);
                    window.addEventListener('beforeunload', this.boundHandleBeforeUnload);
                },
                
                showViolationOverlay() {
                    if (this.isSubmitting || this.pendingAction) return;
                    document.getElementById('violationOverlay').style.display = 'flex';
                },
                
                resumeExam() {
                    if (!document.hidden) {
                        document.getElementById('violationOverlay').style.display = 'none';
                        try { document.documentElement.requestFullscreen().catch((e) => {}); } catch (e) {}
                    }
                }
            }
        }
    </script>

    <!-- QUIT CONFIRMATION MODAL (Moved out of here) -->

    <!-- VIOLATION OVERLAY -->
    <div id="violationOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 9990; justify-content: center; align-items: center; flex-direction: column; text-align: center;">
        <div class="card border-danger border-top border-4 shadow-lg p-5" style="max-width: 500px;">
            <div class="mb-4">
                <i class="ti ti-alert-triangle text-danger f-50"></i>
            </div>
            <h3 class="text-danger fw-bold mb-3">Exam Paused!</h3>
            <p class="mb-4 fs-5">You are not allowed to switch tabs or leave the exam window. This action has been recorded.</p>
            <button class="btn btn-danger btn-lg px-5 rounded-pill" onclick="document.documentElement.dispatchEvent(new CustomEvent('resume-exam'))">
                I Understand, Resume Exam
            </button>
        </div>
    </div>



    <script>
        document.documentElement.addEventListener('resume-exam', () => {
            document.getElementById('violationOverlay').style.display = 'none';
            try { document.documentElement.requestFullscreen().catch((e) => {}); } catch (e) {}
        });

        // Global function for submit modal confirm button (outside Alpine context)
        function showLoadingAndSubmitGlobal() {
            // Close submit modal
            try {
                const submitModal = bootstrap.Modal.getInstance(document.getElementById('submitModal'));
                if (submitModal) submitModal.hide();
            } catch(e) {}

            setTimeout(() => {
                // Call Alpine component's submitForm() — it handles overlay + beforeunload removal + submit
                try {
                    const examEl = document.querySelector('[x-data]');
                    if (examEl && window.Alpine) {
                        Alpine.$data(examEl).submitForm();
                        return;
                    }
                } catch(e) {
                    console.warn('Alpine fallback used', e);
                }

                // Fallback: set global flag so beforeunload won't fire, then submit
                window._examSubmitting = true;
                const overlay = document.getElementById('resultLoadingOverlay');
                if (overlay) {
                    overlay.style.display = 'flex';
                    setTimeout(() => { const s = document.getElementById('step1'); if(s) s.style.opacity = '1'; }, 300);
                    setTimeout(() => { const s = document.getElementById('step2'); if(s) s.style.opacity = '1'; }, 900);
                    setTimeout(() => { const s = document.getElementById('step3'); if(s) s.style.opacity = '1'; }, 1500);
                }
                setTimeout(() => {
                    const form = document.getElementById('examForm');
                    if (form) form.submit();
                }, 800);
            }, 300);
        }
    </script>

<style>
    .hover-bg-light:hover { background-color: #f8f9fa; }
    .cursor-pointer { cursor: pointer; }
    .transition-width { transition: width 0.3s ease; }
    @media print {
        html, body { display: none !important; height: 0 !important; overflow: hidden; }
    }
</style>
@endsection
