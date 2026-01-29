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
        background-color: #0F5EF7;
        border-color: #0F5EF7;
        color: white;
        transform: scale(1.1);
        box-shadow: 0 4px 10px rgba(15, 94, 247, 0.2);
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
        background: #eff6ff;
        color: #3b82f6;
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
        border-color: #0F5EF7 !important;
        background-color: #f0f7ff;
        box-shadow: 0 2px 8px rgba(15, 94, 247, 0.05);
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
            foreach($caseStudy->questions as $question) {
                $questionsList[] = [
                    'question' => $question,
                    'section_id' => $section->id, 
                    'section_title' => $section->title,
                    'case_title' => $caseStudy->title,
                    'case_id' => $caseStudy->id,
                    'case_content' => $caseStudy->content, 
                    // Previously 'case_content' was section content and 'sub_content' was case study content in old Loop
                    // Mapping correctly to visual expectation:
                    // Top Card: Section Content (Scenario) -> $section->content
                    // Inner Card: Case Study Content -> $caseStudy->content
                    'scenario_content' => $section->content, // The main scenario text
                    'sub_content' => $caseStudy->content     // The specific sub-case text
                ];
            }
        }
    }
@endphp

<div x-data="examWizard({{ count($questionsList) }})" x-init="initTimer()" x-cloak>
    
    <!-- START OVERLAY -->
    <div id="startOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #fff; z-index: 10000; display: flex; justify-content: center; align-items: center; flex-direction: column;">
        <div class="text-center p-4">
            <img src="{{ asset('assets/images/logo-new.png') }}" alt="Logo" class="mb-5" style="height: 60px;">
            <h2 class="mb-3 fw-bold text-dark">Ready to Begin?</h2>
            <p class="mb-5 text-muted lead">The exam environment is optimized for a focus-driven experience.</p>
            <button class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow-lg fw-bold" @click="startFullscreen()">
                START EXAM
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
                    
                    @if(!empty(strip_tags($slide['sub_content'] ?? '')))
                    <div class="case-study-box">
                        <div class="case-title-area" @click="isCaseExpanded = !isCaseExpanded">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-file-text text-primary fs-5 me-2"></i>
                                <h6 class="mb-0 fw-bold text-slate-700">{{ $slide['case_title'] }}</h6>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;" x-text="isCaseExpanded ? 'COLLAPSE' : 'EXPAND'"></span>
                                <i class="ti fs-5 transition-all" :class="isCaseExpanded ? 'ti-chevron-up' : 'ti-chevron-down'"></i>
                            </div>
                        </div>
                        <div x-show="isCaseExpanded" x-transition.opacity>
                            <div class="card-body p-4 bg-white">
                                <div class="text-slate-700" style="font-size: 1.05rem; line-height: 1.7;">
                                    {!! $slide['sub_content'] !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="text-center py-5" x-show="!viewedCases.includes('{{ $slide['case_id'] }}')">
                        <button type="button" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm fw-bold" @click="viewedCases.push('{{ $slide['case_id'] }}')">
                            VIEW QUESTION & OPTIONS
                        </button>
                    </div>

                    <div class="question-card" x-show="viewedCases.includes('{{ $slide['case_id'] }}')" x-transition.opacity>
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
                    <button type="button" class="btn btn-light btn-action" x-show="currentIndex > 0" @click="prevSlide()">
                       <i class="ti ti-chevron-left me-1"></i> Previous
                    </button>
                    <div x-show="currentIndex === 0"></div>
                    
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary btn-action" x-show="currentIndex < totalSlides - 1" @click="nextSlide()">
                            Next <i class="ti ti-chevron-right ms-1"></i>
                        </button>
                        <button type="button" class="btn btn-success btn-action" x-show="currentIndex === totalSlides - 1" @click="confirmSubmit('submit')">
                            Finish Exam <i class="ti ti-check ms-1"></i>
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
                        <button type="button" class="btn btn-danger btn-lg py-3 rounded-3 fw-bold shadow-sm" @click="submitForm()">
                            TERMINATE & SUBMIT
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

    <!-- BLACK SCREEN BLOCKER -->
    <div id="screenshotBlocker" style="opacity: 0; pointer-events: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: black; z-index: 2147483647; transition: none;"></div>

    <!-- AlpineJS Logic -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        function examWizard(totalSlides) {
            return {
                currentIndex: 0,
                totalSlides: totalSlides,
                expiryTimestamp: {{ \Carbon\Carbon::parse($attempt->started_at)->addMinutes($exam->duration_minutes)->timestamp }} * 1000,
                now: new Date().getTime(),
                
                pendingAction: null,
                isSubmitting: false, 
                boundHandleBeforeUnload: null,
                isCaseExpanded: true,
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
                        this.currentIndex++;
                        this.isCaseExpanded = true;
                        this.$nextTick(() => { this.handleScrollLogic(oldSecId); });
                    }
                },

                prevSlide() {
                    if (this.currentIndex > 0) {
                        const oldSecId = this.currentSectionId;
                        this.currentIndex--;
                        this.isCaseExpanded = true;
                        this.$nextTick(() => { this.handleScrollLogic(oldSecId); });
                    }
                },

                submitForm() {
                    this.isSubmitting = true;
                    if (this.boundHandleBeforeUnload) {
                        try { window.removeEventListener('beforeunload', this.boundHandleBeforeUnload); } catch(e) {}
                    }
                    const form = document.getElementById('examForm');
                    if (form) form.submit();
                },

                handleBeforeUnload(e) {
                    if (this.isSubmitting) return;
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
