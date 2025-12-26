@extends('layouts.exam')

@section('content')
<style>
    .sticky-timer { position: sticky; top: 0; z-index: 1000; background: white; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
    .no-select { user-select: none; }
    
    /* Section Stepper Styles with Connecting Line */
    .section-stepper-container {
        position: relative;
        display: flex;
        justify-content: center;
        margin-bottom: 1.5rem;
    }
    .section-stepper {
        display: flex;
        align-items: center;
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 900px; /* Limit width so it doesn't overflow */
        justify-content: space-between;
        margin: 0 auto;
    }
    .step-connector {
        position: absolute;
        top: 50%;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: #e9ecef;
        z-index: 0;
        transform: translateY(-50%);
        max-width: 900px;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    .section-step {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #fff;
        border: 2px solid #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1rem;
        cursor: default;
        transition: all 0.3s;
        position: relative;
        z-index: 2;
    }
    .section-step.active {
        background-color: #0F5EF7;
        border-color: #0F5EF7;
        color: white;
        box-shadow: 0 0 0 4px rgba(15, 94, 247, 0.2);
    }
    .section-step.passed {
        background-color: #d1e7dd;
        border-color: #198754;
        color: #198754;
    }
    .animate-label {
        animation: fadeInDown 0.3s ease-out;
    }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
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

<div x-data="examWizard({{ count($questionsList) }})" x-init="initTimer()">
    
    <!-- START OVERLAY -->
    <div id="startOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #fff; z-index: 10000; display: flex; justify-content: center; align-items: center; flex-direction: column;">
        <div class="text-center">
            <h2 class="mb-4 text-primary">Ready to Begin?</h2>
            <p class="mb-4 text-muted">The exam runs in Fullscreen mode. Please minimize distractions.</p>
            <button class="btn btn-primary btn-lg px-5 rounded-pill shadow-lg" @click="startFullscreen()">
                <i class="ti ti-maximize me-2"></i> Enter Fullscreen & Start
            </button>
        </div>
    </div>

    <!-- STICKY HEADER -->
    <div class="card sticky-timer mb-4 overflow-hidden border-0 shadow-sm">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 text-primary fw-bold">{{ $exam->name }}</h5>
                    <div class="text-muted small mt-1">
                        Question <span x-text="currentIndex + 1"></span> of <span x-text="totalSlides"></span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-4">
                    <div class="text-end">
                        <p class="mb-0 text-muted extra-small text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Time Remaining</p>
                        <h3 class="mb-0 text-danger fw-bold" x-text="formattedTime" style="letter-spacing: -1px;">--:--:--</h3>
                    </div>
                    <div>
                         <button type="button" class="btn btn-outline-danger btn-sm px-3" @click="confirmSubmit('quit')">
                            <i class="ti ti-power me-1"></i> Quit
                         </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- TOTAL PROGRESS BAR AT THE BOTTOM -->
        <div class="progress rounded-0" style="height: 6px; background-color: #f0f4f8;">
            <div class="progress-bar bg-primary transition-width" role="progressbar" 
                 :style="`width: ${progressPercentage}%`"
                 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    <!-- SECTION STEPPER BELOW HEADER -->
    <div class="container mb-4 mt-5">
        <div class="section-stepper-container">
            <div class="step-connector"></div>
            <div class="section-stepper">
                @foreach($sectionsMap as $secId => $secData)
                    <div class="section-step" 
                         :class="{ 
                            'active': currentSectionId === {{ $secId }},
                            'passed': currentSectionId > {{ $secId }}
                         }"
                         title="{{ $secData['title'] }}">
                        <template x-if="currentSectionId === {{ $secId }}">
                            <div class="position-absolute" style="top: -28px; left: 50%; transform: translateX(-50%); white-space: nowrap;">
                                <span class="text-uppercase fw-bold text-primary animate-label" style="font-size: 0.7rem; letter-spacing: 1px;">Section</span>
                            </div>
                        </template>
                        {{ $secData['index'] }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- MAIN EXAM FORM -->
    <form action="{{ route('exams.submit', $exam->id) }}" method="POST" id="examForm" @submit.prevent="submitForm" novalidate>
        @csrf

        {{-- Loop through the flattened 'Questions List' --}}
        @foreach($questionsList as $index => $slide)
            <div x-show="currentIndex === {{ $index }}" class="slide-container" data-section-id="{{ $slide['section_id'] }}">
                
                <!-- 1. ORIGINAL STYLE TOP CARD: Scenario/Section Context -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-light-primary">
                        <h5 class="mb-0 text-primary">
                            <i class="ti ti-notebook me-2"></i> {{ $slide['section_title'] }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-light border text-dark mb-0" style="font-size: 1.05rem; line-height: 1.6;">
                            {!! $slide['scenario_content'] !!}
                        </div>
                    </div>
                </div>

                <!-- 2. ORIGINAL STYLE BOTTOM CARD: Sub-Case & Question -->
                <div class="card mb-4 border-start border-4 border-info case-study-card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 text-uppercase fw-bold text-info">
                            <i class="ti ti-arrow-right me-2"></i> {{ $slide['case_title'] }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Sub Case Content -->
                        @if(!empty(strip_tags($slide['sub_content'] ?? '')))
                        <div class="alert alert-secondary border-0 text-dark mb-4" style="font-size: 1.0rem; line-height: 1.6;">
                            {!! $slide['sub_content'] !!}
                        </div>
                        @endif
                        
                        <!-- SINGLE QUESTION -->
                        <div class="card mb-4 border shadow-none bg-white">
                            <div class="card-body">
                                <p class="fw-bold mb-3" style="font-size: 1.1rem;">
                                    Q{{ $index + 1 }}. {!! $slide['question']->question_text !!}
                                </p>

                                <div class="options-list">
                                    @php $q = $slide['question']; @endphp
                                    @foreach($q->options as $option)
                                        <div class="form-check mb-2 p-3 rounded border hover-bg-light">
                                            @if($q->question_type === 'multiple')
                                                <input class="form-check-input mt-1" type="checkbox" 
                                                    name="answers[{{ $q->id }}][]" 
                                                    id="opt_{{ $option->id }}" 
                                                    value="{{ $option->option_text }}">
                                            @else
                                                <input class="form-check-input mt-1" type="radio" 
                                                    name="answers[{{ $q->id }}]" 
                                                    id="opt_{{ $option->id }}" 
                                                    value="{{ $option->option_text }}">
                                            @endif
                                            
                                            <label class="form-check-label w-100 ps-2 cursor-pointer" for="opt_{{ $option->id }}">
                                                {{ $option->option_text }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        @endforeach

        <!-- NAVIGATION ACTIONS -->
        <div style="height: 100px;"></div> 
        <div class="fixed-bottom border-top shadow-lg bg-white" style="z-index: 999; bottom: 0;">
            <div class="py-3">
                <div class="container d-flex justify-content-between">
                    
                    <button type="button" class="btn btn-outline-secondary btn-lg px-4" 
                            x-show="currentIndex > 0" 
                            @click="prevSlide()">
                        <i class="ti ti-arrow-left me-2"></i> Previous
                    </button>
                    
                    <div class="ms-auto">
                        <button type="button" class="btn btn-primary btn-lg px-5" 
                                x-show="currentIndex < totalSlides - 1" 
                                @click="nextSlide()">
                            Next Question <i class="ti ti-arrow-right ms-2"></i>
                        </button>

                        <button type="button" class="btn btn-success btn-lg px-5" 
                                x-show="currentIndex === totalSlides - 1"
                                @click="confirmSubmit('submit')">
                            Submit Exam <i class="ti ti-check ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </form>
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
                        this.$nextTick(() => { this.handleScrollLogic(oldSecId); });
                    }
                },

                prevSlide() {
                    if (this.currentIndex > 0) {
                        const oldSecId = this.currentSectionId;
                        this.currentIndex--;
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
