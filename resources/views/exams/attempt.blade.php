<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam In Progress - {{ $exam->title }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Using same assets as app layout -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link" >
    <link rel="stylesheet" href="{{ asset('assets/css/style-preset.css') }}" >
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/animate.min.css') }}" >
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}" >
    
    <style>
        body { background-color: #f8f9fa; }
        .exam-sidebar { 
            height: 100vh; 
            overflow-y: auto; 
            border-right: 1px solid #e1e1e1; 
            background: white;
            position: fixed;
            width: 300px;
            top: 0; left: 0;
            z-index: 100;
        }
        .exam-content { 
            margin-left: 300px; 
            padding: 2rem;
            min-height: 100vh;
        }
        .timer-badge {
            font-size: 1.5rem;
            font-family: monospace;
            font-weight: bold;
        }
        .step-locked { opacity: 0.5; pointer-events: none; }
        .step-completed { color: green; }
        .step-active { font-weight: bold; color: var(--bs-primary); border-left: 3px solid var(--bs-primary); background: #f0f7ff; }
        
        .question-card { margin-bottom: 2rem; border: 1px solid #e3e3e3; border-radius: 8px; overflow: hidden; background: white; }
        .question-header { background: #f8f9fa; padding: 1rem; border-bottom: 1px solid #e3e3e3; display: flex; justify-content: space-between; }
        .question-body { padding: 1.5rem; }
        
        .option-label { display: block; padding: 10px; border: 1px solid #ddd; border-radius: 6px; margin-bottom: 10px; cursor: pointer; transition: all 0.2s; }
        .option-label:hover { background-color: #f1f1f1; }
        .option-radio:checked + span { font-weight: bold; color: var(--bs-primary); }
        .option-radio { margin-right: 10px; }
        
        .subcase-card { margin-bottom: 2rem; }
        .cs-desc { font-size: 0.9rem; color: #666; margin-bottom: 1rem; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <div class="exam-sidebar shadow-sm">
        <div class="p-4 border-bottom">
            <h5 class="mb-0">Exam Navigator</h5>
            <small class="text-muted">{{ $exam->title }}</small>
        </div>
        <div class="p-0">
            <div class="accordion" id="caseStudyAccordion">
                @foreach($exam->structure as $index => $cs)
                <div class="accordion-item border-0">
                    <h2 class="accordion-header" id="heading{{ $index }}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="false">
                            <i class="ti ti-folder me-2"></i> {{ $cs->title }}
                        </button>
                    </h2>
                    <div id="collapse{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#caseStudyAccordion">
                        <div class="accordion-body p-0">
                            <ul class="list-group list-group-flush">
                                @foreach($cs->subCases as $sIndex => $sub)
                                <li class="list-group-item ps-5 py-2 cursor-pointer" id="nav-item-{{ $sub->id }}">
                                    <small>{{ $sub->title }}</small>
                                    <i class="ti ti-lock float-end text-muted lock-icon" id="lock-{{ $sub->id }}"></i>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="exam-content">
        <!-- TOP BAR -->
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm sticky-top">
            <div>
                <h4 class="mb-0" id="current-title">Loading...</h4>
                <small class="text-muted">Part of <span id="current-parent"></span></small>
            </div>
            <div class="text-end">
                <div class="text-muted small mb-1">Time Remaining</div>
                <div class="timer-badge text-primary" id="timer-display">00:00:00</div>
            </div>
        </div>

        <!-- ALERT AREA -->
        <div id="alert-area"></div>

        <!-- CASE STUDY INFO -->
        <div class="card mb-4 border-primary border-start border-4" id="cs-info-card">
            <div class="card-body">
                <h5 class="card-title text-primary">Case Study Context</h5>
                <p class="card-text" id="cs-description">...</p>
            </div>
        </div>

        <!-- SUB CASE CONTENT -->
        <div id="sub-case-container">
            <!-- Dynamic Content Injected Here -->
        </div>

        <!-- ACTIONS -->
        <div class="d-flex justify-content-between mt-5 pt-3 border-top">
            <button class="btn btn-secondary disabled" id="prev-btn" style="visibility: hidden;">Previous</button>
            <button class="btn btn-primary btn-lg px-5 display-none" id="next-btn" onclick="nextSubCase()">
                Next Section <i class="ti ti-arrow-right ms-2"></i>
            </button>
            <button class="btn btn-success btn-lg px-5 d-none" id="submit-btn" onclick="submitExam()">
                Submit Exam <i class="ti ti-check ms-2"></i>
            </button>
        </div>
    </div>

    <!-- DATA INJECTION -->
    <script>
        const EXAM_DATA = @json($exam->structure);
        const EXAM_ID = "{{ $exam->id }}";
        const TOTAL_TIME_SEC = {{ $exam->duration_minutes * 60 }};
        const SUBMIT_URL = "{{ route('exams.submit', $exam->id) }}";
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // --- LOGIC ---
        
        let currentState = {
            csIndex: 0,
            scIndex: 0,
            answers: {}, // { 'q_id': { option_id: 1, type: 'IG' } }
            completedSubCases: [] // IDs
        };

        let timerInterval;

        document.addEventListener('DOMContentLoaded', () => {
            initTimer();
            loadState();
            renderCurrentView();
        });

        // --- TIMER ---
        function initTimer() {
            let timeLeft = sessionStorage.getItem(`exam_timer_${EXAM_ID}`);
            if (!timeLeft) {
                timeLeft = TOTAL_TIME_SEC;
            } else {
                timeLeft = parseInt(timeLeft);
            }

            const display = document.getElementById('timer-display');

            timerInterval = setInterval(() => {
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    autoSubmit();
                    return;
                }

                timeLeft--;
                sessionStorage.setItem(`exam_timer_${EXAM_ID}`, timeLeft);
                
                // Format
                const h = Math.floor(timeLeft / 3600);
                const m = Math.floor((timeLeft % 3600) / 60);
                const s = timeLeft % 60;
                display.innerText = `${h.toString().padStart(2,'0')}:${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;

                if (timeLeft === 300) { // 5 mins
                    alert("Warning: 5 minutes remaining!");
                    display.classList.add('text-danger');
                }
            }, 1000);
        }

        // --- NAVIGATION & STATE ---
        function getCurrentSubCase() {
            return EXAM_DATA[currentState.csIndex].subCases[currentState.scIndex];
        }

        function getCurrentCaseStudy() {
            return EXAM_DATA[currentState.csIndex];
        }

        function renderCurrentView() {
            const cs = getCurrentCaseStudy();
            const sc = getCurrentSubCase();

            // headers
            document.getElementById('current-title').innerText = sc.title;
            document.getElementById('current-parent').innerText = cs.title;
            
            // CS Info
            document.getElementById('cs-description').innerText = cs.description;

            // Render Questions
            const container = document.getElementById('sub-case-container');
            container.innerHTML = `
                <div class="subcase-card">
                    <h4>${sc.title}</h4>
                    <p class="cs-desc">${sc.description}</p>
                </div>
            `;

            sc.questions.forEach((q, idx) => {
                const isSelected = currentState.answers[q.id] ? currentState.answers[q.id].value : null;
                
                let optionsHtml = '';
                q.options.forEach(opt => {
                    optionsHtml += `
                        <label class="option-label">
                            <input type="radio" 
                                name="${q.id}" 
                                class="option-radio" 
                                value="${opt.id}" 
                                ${isSelected == opt.id ? 'checked' : ''} 
                                onchange="saveAnswer('${q.id}', '${q.type}', '${opt.id}')">
                            <span>${opt.text}</span>
                        </label>
                    `;
                });

                const badgeClass = q.type === 'IG' ? 'bg-primary' : 'bg-warning text-dark';
                
                container.innerHTML += `
                    <div class="question-card">
                        <div class="question-header">
                            <strong>Question ${idx + 1}</strong>
                            <span class="badge ${badgeClass}">${q.type}</span>
                        </div>
                        <div class="question-body">
                            <p class="mb-3">${q.text}</p>
                            ${optionsHtml}
                        </div>
                    </div>
                `;
            });

            // Update Navigation UI
            updateSidebar();
            checkCompletion();
        }

        function saveAnswer(qId, type, optId) {
            currentState.answers[qId] = { value: optId, type: type };
            sessionStorage.setItem(`exam_answers_${EXAM_ID}`, JSON.stringify(currentState.answers));
        }

        function nextSubCase() {
            // Lock current
            const sc = getCurrentSubCase();
            if (!currentState.completedSubCases.includes(sc.id)) {
                currentState.completedSubCases.push(sc.id);
            }

            // Move pointer
            const cs = EXAM_DATA[currentState.csIndex];
            if (currentState.scIndex < cs.subCases.length - 1) {
                currentState.scIndex++;
            } else {
                // Next CS
                if (currentState.csIndex < EXAM_DATA.length - 1) {
                    currentState.csIndex++;
                    currentState.scIndex = 0;
                } else {
                    // FINISH
                    showSubmitButton();
                    return;
                }
            }
            
            saveState();
            renderCurrentView();
            window.scrollTo(0,0);
        }
        
        function showSubmitButton() {
            document.getElementById('sub-case-container').innerHTML = `
                <div class="text-center py-5">
                    <h3>All Sections Completed</h3>
                    <p>You have answered all questions. Please submit your exam.</p>
                </div>
            `;
            document.getElementById('next-btn').classList.add('d-none');
            document.getElementById('submit-btn').classList.remove('d-none');
        }

        function updateSidebar() {
            // Very simple visual update
            EXAM_DATA.forEach((cs, cIdx) => {
                cs.subCases.forEach((sub, sIdx) => {
                    const el = document.getElementById(`nav-item-${sub.id}`);
                    const icon = document.getElementById(`lock-${sub.id}`);
                    if(!el) return;

                    el.classList.remove('step-active', 'step-completed', 'step-locked');
                    icon.className = 'ti float-end'; // reset icon

                    // Logic
                    if (currentState.completedSubCases.includes(sub.id)) {
                        el.classList.add('step-completed');
                        icon.classList.add('ti-check', 'text-success');
                    } else if (cIdx === currentState.csIndex && sIdx === currentState.scIndex) {
                        el.classList.add('step-active');
                        icon.classList.add('ti-pencil', 'text-primary');
                    } else {
                        // Future
                        el.classList.add('step-locked');
                        icon.classList.add('ti-lock', 'text-muted');
                    }
                });
            });
        }

        function checkCompletion() {
            // Show next button always? Or validation?
            // "User answers all questions... clicks Next"
            // We'll allow next for now, but in real app we might validate
            document.getElementById('next-btn').style.visibility = 'visible';
            
            // Check if last one
            const isLast = (currentState.csIndex === EXAM_DATA.length - 1) && (currentState.scIndex === EXAM_DATA[currentState.csIndex].subCases.length - 1);
            if(isLast) {
                 document.getElementById('next-btn').innerText = "Finish Section";
            }
        }

        // --- STORAGE ---
        function saveState() {
             sessionStorage.setItem(`exam_state_${EXAM_ID}`, JSON.stringify({
                 csIndex: currentState.csIndex,
                 scIndex: currentState.scIndex,
                 completedSubCases: currentState.completedSubCases
             }));
        }

        function loadState() {
            const saved = sessionStorage.getItem(`exam_state_${EXAM_ID}`);
            const savedAns = sessionStorage.getItem(`exam_answers_${EXAM_ID}`);
            if (saved) {
                const parsed = JSON.parse(saved);
                currentState.csIndex = parsed.csIndex;
                currentState.scIndex = parsed.scIndex;
                currentState.completedSubCases = parsed.completedSubCases;
            }
            if(savedAns) {
                currentState.answers = JSON.parse(savedAns);
            }
        }

        // --- SUBMISSION ---
        function submitExam() {
            if(!confirm("Are you sure you want to submit?")) return;
            performSubmit();
        }

        function autoSubmit() {
            alert("Time is up! Submitting exam automatically.");
            performSubmit();
        }

        function performSubmit() {
            sessionStorage.removeItem(`exam_state_${EXAM_ID}`);
            sessionStorage.removeItem(`exam_timer_${EXAM_ID}`);
            sessionStorage.removeItem(`exam_answers_${EXAM_ID}`);
            
            // Create hidden form to submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = SUBMIT_URL;
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrf);

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'answers';
            input.value = JSON.stringify(currentState.answers);
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>
