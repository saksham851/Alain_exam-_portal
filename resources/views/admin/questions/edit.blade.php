@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">{{ isset($question) ? 'Edit Question' : 'Add Questions' }}</h5>
        </div>

      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row" x-data="questionForm()">
    <div class="col-md-12">
        @php
            $isActiveExam = false;
            // For edit mode check
            if(isset($question) && $question->caseStudy && $question->caseStudy->section && $question->caseStudy->section->exam) {
                $isActiveExam = $question->caseStudy->section->exam->is_active == 1;
            }
        @endphp

        <div id="activeExamWarning" class="alert alert-warning align-items-start gap-3 mb-4" role="alert" :class="isActiveExam ? 'd-flex' : 'd-none'" style="display: none;">
            <i class="ti ti-lock" style="font-size: 20px; margin-top: 3px;"></i>
            <div>
                <strong>This Exam is Active/Locked</strong>
                <p class="mb-0 mt-2">This exam is currently active. You cannot add or edit questions. Please deactivate the exam first.</p>
            </div>
        </div>

        <form action="{{ isset($question) ? route('admin.questions.update', $question->id) : route('admin.questions.store') }}" method="POST" id="questionForm">
            @csrf
            @if(isset($question)) @method('PUT') @endif

            <!-- Cascading Dropdowns (Location) -->
            <!-- This section is common for all questions being added -->
            <div class="card" :style="isActiveExam ? 'opacity:0.5;pointer-events:none' : ''">
                <div class="card-header">
                    <h5>Select Location</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Exam <span class="text-danger">*</span></label>
                            <select class="form-select" id="exam_id" @change="loadCaseStudies($event.target.value)" required {{ request('exam_id') ? 'style=pointer-events:none;background-color:#e9ecef;' : '' }}>
                                <option value="">Select Exam</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}" {{ (request('exam_id', (isset($question) && $question->caseStudy->section->exam_id == $exam->id) ? $exam->id : '') == $exam->id) ? 'selected' : '' }}>
                                        {{ $exam->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Section <span class="text-danger">*</span></label>
                            <select class="form-select" id="case_study_id" @change="loadSubCaseStudies($event.target.value)" :disabled="caseStudies.length === 0" required>
                                <option value="">Select Section</option>
                                <template x-for="cs in caseStudies" :key="cs.id">
                                    <option :value="cs.id" x-text="cs.title"></option>
                                </template>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Case Study <span class="text-danger">*</span></label>
                            <select class="form-select" name="sub_case_id" id="sub_case_id" :disabled="subCaseStudies.length === 0" @change="loadExistingQuestions($event.target.value)" required>
                                <option value="">Select Case Study</option>
                                <template x-for="scs in subCaseStudies" :key="scs.id">
                                    <option :value="scs.id" x-text="scs.title"></option>
                                </template>
                            </select>
                            @error('sub_case_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Existing Questions (Alpine) -->
            <template x-if="existingQuestions.length > 0">
                <div class="mt-4">
                    <h6 class="d-flex align-items-center mb-3 text-muted">
                        <i class="ti ti-edit me-2"></i> Existing Questions (Editable)
                    </h6>

                    <template x-for="(exQ, eqIndex) in existingQuestions" :key="exQ.id">
                        <div class="card border mb-3" :style="isActiveExam ? 'opacity:0.5;pointer-events:none' : 'background-color: #fcfcfc;'">
                            <div class="card-body position-relative">
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2" 
                                        @click="removeExistingQuestion(eqIndex, exQ.id)"
                                        title="Delete this question">
                                    <i class="ti ti-trash"></i>
                                </button>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <i class="ti ti-edit-circle me-1"></i> Existing Question #<span x-text="eqIndex + 1"></span>
                                    </h6>
                                </div>

                                <input type="hidden" :name="'existing_questions['+exQ.id+'][id]'" :value="exQ.id">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Question Text <span class="text-danger">*</span></label>
                                        <textarea :id="'existing_question_text_' + exQ.id" 
                                                  :name="'existing_questions['+exQ.id+'][question_text]'" 
                                                  class="form-control" 
                                                  rows="4"
                                                  x-model="exQ.question_text"></textarea>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Question Type <span class="text-danger">*</span></label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" 
                                                   :name="'existing_questions['+exQ.id+'][question_type]'"
                                                   :id="'existing_type_single_' + exQ.id" 
                                                   value="single" 
                                                   x-model="exQ.question_type"
                                                   @change="resetExistingCorrectAnswers(eqIndex)">
                                            <label class="form-check-label" :for="'existing_type_single_' + exQ.id">Single Choice</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" 
                                                   :name="'existing_questions['+exQ.id+'][question_type]'"
                                                   :id="'existing_type_multiple_' + exQ.id" 
                                                   value="multiple" 
                                                   x-model="exQ.question_type"
                                                   @change="resetExistingCorrectAnswers(eqIndex)">
                                            <label class="form-check-label" :for="'existing_type_multiple_' + exQ.id">Multiple Choice</label>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Group <span class="text-danger">*</span></label>
                                        <select :name="'existing_questions['+exQ.id+'][question_category]'" 
                                                class="form-select" 
                                                x-model="exQ.question_category"
                                                required>
                                            <option value="">Select group</option>
                                            <option value="ig">IG - Internal Governance</option>
                                            <option value="dm">DM - Decision Making</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="card mt-2">
                                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                                        <h6 class="mb-0">Answer Options</h6>
                                        <button type="button" @click="addExistingOption(eqIndex)" class="btn btn-sm btn-light-primary">
                                            <i class="ti ti-plus"></i> Add Option
                                        </button>
                                    </div>
                                    <div class="card-body bg-light">
                                        <template x-for="(option, oIndex) in exQ.options" :key="oIndex">
                                            <div class="border rounded p-3 mb-3 bg-white">
                                                <div class="row align-items-center">
                                                    <div class="col-md-1 text-center">
                                                        <h4 class="mb-0 text-primary" x-text="String.fromCharCode(65 + oIndex)"></h4>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" 
                                                               :name="'existing_questions['+exQ.id+'][options]['+oIndex+'][text]'"
                                                               x-model="option.text" 
                                                               class="form-control" 
                                                               required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-check">
                                                            <template x-if="exQ.question_type === 'single'">
                                                                <div>
                                                                    <input class="form-check-input" 
                                                                           type="radio" 
                                                                           :id="'eq'+exQ.id+'_correct_'+oIndex"
                                                                           :name="'existing_q_'+exQ.id+'_correct'" 
                                                                           :value="oIndex"
                                                                           x-model="exQ.singleCorrect">
                                                                    <input type="hidden" 
                                                                           :name="'existing_questions['+exQ.id+'][options]['+oIndex+'][is_correct]'"
                                                                           :value="exQ.singleCorrect == oIndex ? '1' : '0'">
                                                                </div>
                                                            </template>
                                                            <template x-if="exQ.question_type === 'multiple'">
                                                                <input class="form-check-input" 
                                                                       type="checkbox" 
                                                                       :name="'existing_questions['+exQ.id+'][options]['+oIndex+'][is_correct]'"
                                                                       :id="'eq'+exQ.id+'_correct_'+oIndex"
                                                                       x-model="option.is_correct"
                                                                       value="1">
                                                            </template>
                                                            <label class="form-check-label" :for="'eq'+exQ.id+'_correct_'+oIndex">Correct</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-1 text-end">
                                                        <button type="button" 
                                                                @click="removeExistingOption(eqIndex, oIndex)" 
                                                                class="btn btn-sm btn-outline-danger"
                                                                x-show="exQ.options.length > 2">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <hr class="my-4">
                </div>
            </template>

            <!-- Questions Loop -->
            <template x-for="(questionItem, qIndex) in questions" :key="questionItem.id">
                <div class="card mb-3 border" :style="isActiveExam ? 'opacity:0.5;pointer-events:none' : ''">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                           <i class="ti ti-help-circle me-1"></i> Question <span x-text="existingQuestions.length + qIndex + 1"></span>
                        </h6>
                        <button type="button" class="btn btn-sm btn-danger" 
                                @click="removeQuestion(qIndex)" 
                                x-show="!isEdit">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>
                    
                    <div class="card-body">
                        <div class="row">
                            <!-- Question Text -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Question Text <span class="text-danger">*</span></label>
                                <!-- Dynamic ID for CKEditor using Unique ID -->
                                <textarea :id="'question_text_' + questionItem.id" 
                                          :name="isEdit ? 'question_text' : 'questions['+qIndex+'][question_text]'" 
                                          class="form-control" 
                                          rows="4"
                                          x-model="questionItem.question_text"></textarea>
                            </div>

                            <!-- Question Type -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Question Type <span class="text-danger">*</span></label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" 
                                           :name="isEdit ? 'question_type' : 'questions['+qIndex+'][question_type]'"
                                           :id="'type_single_' + questionItem.id" 
                                           value="single" 
                                           x-model="questionItem.question_type"
                                           @change="resetCorrectAnswers(qIndex)">
                                    <label class="form-check-label" :for="'type_single_' + questionItem.id">Single Choice (One Correct Answer)</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" 
                                           :name="isEdit ? 'question_type' : 'questions['+qIndex+'][question_type]'"
                                           :id="'type_multiple_' + questionItem.id" 
                                           value="multiple" 
                                           x-model="questionItem.question_type"
                                           @change="resetCorrectAnswers(qIndex)">
                                    <label class="form-check-label" :for="'type_multiple_' + questionItem.id">Multiple Choice (Multiple Correct Answers)</label>
                                </div>
                            </div>

                            <!-- Category -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Group <span class="text-danger">*</span></label>
                                <select :name="isEdit ? 'question_category' : 'questions['+qIndex+'][question_category]'" 
                                        class="form-select" 
                                        x-model="questionItem.question_category"
                                        required>
                                    <option value="">Select group</option>
                                    <option value="ig">IG - Internal Governance</option>
                                    <option value="dm">DM - Decision Making</option>
                                </select>
                                <small class="text-muted">Select which category this question belongs to</small>
                            </div>
                        </div>

                        <!-- Options Section -->
                        <div class="card mt-2 bg-light">
                            <div class="card-header d-flex justify-content-between align-items-center py-2">
                                <h6 class="mb-0">Answer Options</h6>
                                <button type="button" @click="addOption(qIndex)" class="btn btn-sm btn-light-primary">
                                    <i class="ti ti-plus"></i> Add Option
                                </button>
                            </div>
                            <div class="card-body">
                                <template x-for="(option, oIndex) in questionItem.options" :key="oIndex">
                                    <div class="border rounded p-3 mb-3 bg-white">
                                        <div class="row align-items-center">
                                            <div class="col-md-1">
                                                <div class="text-center">
                                                    <h4 class="mb-0 text-primary" x-text="String.fromCharCode(65 + oIndex)"></h4>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <input type="text" 
                                                       :name="isEdit ? 'options['+oIndex+'][text]' : 'questions['+qIndex+'][options]['+oIndex+'][text]'"
                                                       x-model="option.text" 
                                                       class="form-control" 
                                                       placeholder="Enter option text" 
                                                       required>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-check">
                                                    <!-- Single Choice -->
                                                    <template x-if="questionItem.question_type === 'single'">
                                                        <div>
                                                            <input class="form-check-input" 
                                                                   type="radio" 
                                                                   :name="'question_'+qIndex+'_correct'" 
                                                                   :id="'q'+qIndex+'_correct_'+oIndex"
                                                                   :value="oIndex"
                                                                   x-model="questionItem.singleCorrect">
                                                            <!-- Hidden input to submit 1 or 0 for is_correct -->
                                                            <input type="hidden" 
                                                                   :name="isEdit ? 'options['+oIndex+'][is_correct]' : 'questions['+qIndex+'][options]['+oIndex+'][is_correct]'"
                                                                   :value="questionItem.singleCorrect == oIndex ? '1' : '0'">
                                                        </div>
                                                    </template>
                                                    <!-- Multiple Choice -->
                                                    <template x-if="questionItem.question_type === 'multiple'">
                                                        <input class="form-check-input" 
                                                               type="checkbox" 
                                                               :name="isEdit ? 'options['+oIndex+'][is_correct]' : 'questions['+qIndex+'][options]['+oIndex+'][is_correct]'"
                                                               :id="'q'+qIndex+'_correct_'+oIndex"
                                                               x-model="option.is_correct"
                                                               value="1">
                                                    </template>
                                                    <label class="form-check-label" :for="'q'+qIndex+'_correct_'+oIndex">
                                                        Correct
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-1 text-end">
                                                <button type="button" 
                                                        @click="removeOption(qIndex, oIndex)" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        x-show="questionItem.options.length > 2">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <div class="alert alert-info mt-3 mb-0">
                                    <i class="ti ti-info-circle me-2"></i>
                                    <span x-show="questionItem.question_type === 'single'">Select ONE correct answer using radio buttons.</span>
                                    <span x-show="questionItem.question_type === 'multiple'">Select MULTIPLE correct answers using checkboxes.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <div class="mt-4 p-3 bg-light rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <!-- Add Another Question Button (Left) - Only in Create Mode -->
                    <button type="button" class="btn btn-sm btn-primary" @click="addQuestion()" x-show="!isEdit">
                        <i class="ti ti-plus me-1"></i> Add Another Question
                    </button>
                    <div x-show="isEdit"></div> <!-- Empty div to maintain layout in edit mode -->
                    
                    <!-- Action Buttons (Right) -->
                    <div>
                        <a href="{{ route('admin.questions.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary" :disabled="isActiveExam">
                            <i class="ti ti-check me-1"></i> Save {{ isset($question) ? 'Question' : 'Questions' }}
                        </button>
                    </div>
                </div>
            </div>
        </form>


    </div>
</div>

@php
    $isEdit = false;
    // Initial data structure for Alpine
    // If Editing, we populate ONE question
    $initialQuestions = [];
    
    $selectedSubCaseId = null;
    $selectedCaseStudyId = null;
    $selectedExamId = null;

    if(isset($question)) {
        $isEdit = true;
        // Build options array
        $opts = $question->options->map(function($opt, $index) {
            return [
                'text' => $opt->option_text,
                'is_correct' => $opt->is_correct ? true : false
            ];
        })->toArray();

        $singleCorrect = 0;
        if($question->question_type == 'single') {
            $correctIndex = $question->options->search(function($opt) {
                return $opt->is_correct == 1;
            });
            $singleCorrect = $correctIndex !== false ? $correctIndex : 0;
        }

        $cat = 'ig'; // Default
        if($question->ig_weight > 0) $cat = 'ig';
        if($question->dm_weight > 0) $cat = 'dm';

        $initialQuestions[] = [
            'question_text' => $question->question_text,
            'question_type' => $question->question_type,
            'question_category' => $cat,
            'options' => $opts,
            'singleCorrect' => $singleCorrect
        ];

        $selectedSubCaseId = $question->case_study_id;
        $selectedCaseStudyId = $question->caseStudy->section_id;
        $selectedExamId = $question->caseStudy->section->exam_id;
    } else {
        // Create Mode - Start with one empty question
        $initialQuestions[] = [
            'question_text' => '',
            'question_type' => 'single',
            'question_category' => '',
            'options' => [
                ['text' => '', 'is_correct' => false],
                ['text' => '', 'is_correct' => false]
            ],
            'singleCorrect' => 0
        ];
    }
@endphp

<script>
function questionForm() {
    return {
        isEdit: {{ $isEdit ? 'true' : 'false' }},
        isActiveExam: {{ $isActiveExam ? 'true' : 'false' }},
        currentQuestionId: {{ isset($question) ? $question->id : 'null' }},
        // Add random unique ID to each question for stable DOM tracking
        questions: @json($initialQuestions).map(q => ({ ...q, id: 'q_' + Math.random().toString(36).substr(2, 9) })),
        existingQuestions: [], // Array to hold existing questions
        caseStudies: [],
        subCaseStudies: [],
        selectedExamId: {{ request('exam_id') ?? ($selectedExamId ?? 'null') }},
        selectedCaseStudyId: {{ request('section_id') ?? ($selectedCaseStudyId ?? 'null') }},
        selectedSubCaseId: {{ request('case_study_id') ?? ($selectedSubCaseId ?? 'null') }},
        editors: {}, // Map of uniqueID -> editor instance
        existingEditors: {}, // Map of ID -> editor instance

        init() {
            // Load initial location data
            if(this.selectedExamId) {
                document.getElementById('exam_id').value = this.selectedExamId;
                this.loadCaseStudies(this.selectedExamId);
            }
            
            // If sub case is already selected (e.g. from validation error back), trigger load
            if(this.selectedSubCaseId) {
                 this.$nextTick(() => {
                     // We need to wait for subCaseStudies dropdown to populate potentially, handled in loadSubCaseStudies
                 });
            }

            // Initialize editors for existing questions (if any predefined)
            this.$nextTick(() => {
                this.questions.forEach((q) => {
                    this.initEditor(q.id);
                });
            });

            // Handle form submission
            document.getElementById('questionForm').addEventListener('submit', (e) => {
                let isValid = true;
                
                // Sync New Questions CKEditors
                this.questions.forEach((q) => {
                    if(this.editors[q.id]) {
                        const data = this.editors[q.id].getData();
                        q.question_text = data;
                        
                        const el = document.getElementById('question_text_' + q.id);
                        if(el) el.value = data;

                        if(!data || data.trim() === '') isValid = false;
                    }
                });

                // Sync Existing Questions CKEditors
                this.existingQuestions.forEach(exQ => {
                    if(this.existingEditors[exQ.id]) {
                        const data = this.existingEditors[exQ.id].getData();
                        exQ.question_text = data;

                        const el = document.getElementById('existing_question_text_' + exQ.id);
                        if(el) el.value = data;
                         // Less strict on existing questions validation? Assume they were valid.
                         // But if user cleared it, it should act as valid or error?
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Please enter text for all new questions');
                    return false;
                }
            });
        },

        addQuestion() {
            const newId = 'q_' + Math.random().toString(36).substr(2, 9);
            this.questions.push({
                id: newId,
                question_text: '',
                question_type: 'single',
                question_category: '',
                singleCorrect: 0,
                options: [
                    { text: '', is_correct: false },
                    { text: '', is_correct: false }
                ]
            });
            
            this.$nextTick(() => {
                this.initEditor(newId);
            });
        },

        removeQuestion(index) {
            const q = this.questions[index];
            const qId = q.id; 
            this.questions.splice(index, 1);
            
            setTimeout(() => {
                if(qId && this.editors[qId]) {
                    this.editors[qId].destroy().then(() => { delete this.editors[qId]; }).catch(e => { delete this.editors[qId]; });
                }
            }, 50);
        },

        addOption(qIndex) {
            this.questions[qIndex].options.push({ text: '', is_correct: false });
        },

        removeOption(qIndex, oIndex) {
            if(this.questions[qIndex].options.length > 2) {
                this.questions[qIndex].options.splice(oIndex, 1);
                if(this.questions[qIndex].question_type === 'single' && 
                   this.questions[qIndex].singleCorrect >= this.questions[qIndex].options.length) {
                    this.questions[qIndex].singleCorrect = 0;
                }
            }
        },

        resetCorrectAnswers(qIndex) {
            this.questions[qIndex].singleCorrect = 0;
            this.questions[qIndex].options.forEach(opt => {
                opt.is_correct = false;
            });
        },

        // Existing Question Methods
        addExistingOption(eqIndex) {
            this.existingQuestions[eqIndex].options.push({ text: '', is_correct: false });
        },

        removeExistingOption(eqIndex, oIndex) {
             if(this.existingQuestions[eqIndex].options.length > 2) {
                this.existingQuestions[eqIndex].options.splice(oIndex, 1);
                // Adjust index if needed
                 if(this.existingQuestions[eqIndex].question_type === 'single' && 
                   this.existingQuestions[eqIndex].singleCorrect >= this.existingQuestions[eqIndex].options.length) {
                    this.existingQuestions[eqIndex].singleCorrect = 0;
                }
            }
        },

        resetExistingCorrectAnswers(eqIndex) {
            this.existingQuestions[eqIndex].singleCorrect = 0;
            this.existingQuestions[eqIndex].options.forEach(opt => {
                opt.is_correct = false;
            });
        },

        async removeExistingQuestion(index, id) {
            window.showAlert.confirm('Are you sure you want to delete this question? This action cannot be undone.', 'Delete Question?', async () => {
                try {
                    const response = await fetch(`/admin/questions/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
                    }

                    let result;
                    try {
                        result = await response.json();
                    } catch (e) {
                        const text = await response.text();
                        console.error('Server returned non-JSON:', text);
                        throw new Error('Server returned invalid response. Check console.');
                    }

                    if (result.success) {
                        // Destroy editor
                        if(this.existingEditors[id]) {
                            this.existingEditors[id].destroy()
                                .then(() => { delete this.existingEditors[id]; })
                                .catch(e => {
                                    console.error(e);
                                    delete this.existingEditors[id];
                                });
                        }
                        
                        // Remove from view
                        this.existingQuestions.splice(index, 1);
                        
                        window.showAlert.toast('Question deleted successfully');
                    } else {
                        window.showAlert.error(result.message || 'Error deleting question');
                    }
                } catch (error) {
                    console.error('Deletion Error:', error);
                    window.showAlert.error('Deletion failed: ' + error.message);
                }
            });
        },

        initEditor(uniqueId) {
            const id = 'question_text_' + uniqueId;
            this.$nextTick(() => { this.createEditor(id, uniqueId, false); });
        },

        initExistingEditor(questionId) {
             const id = 'existing_question_text_' + questionId;
             this.$nextTick(() => { this.createEditor(id, questionId, true); });
        },

        createEditor(elementId, key, isExisting) {
            const el = document.getElementById(elementId);
            const editorMap = isExisting ? this.existingEditors : this.editors;
            
            if(editorMap[key] || !el) return;

             if(el.nextSibling && el.nextSibling.classList && el.nextSibling.classList.contains('ck-editor')) {
                   el.nextSibling.remove();
                   el.style.display = 'block';
             }

            ClassicEditor.create(el)
                .then(editor => {
                    editorMap[key] = editor;
                    editor.model.document.on('change:data', () => {
                        const data = editor.getData();
                        if(isExisting) {
                             const q = this.existingQuestions.find(i => i.id === key);
                             if(q) q.question_text = data;
                        } else {
                             const q = this.questions.find(i => i.id === key);
                             if(q) q.question_text = data;
                        }
                    });
                    
                    // Set initial data
                    let initialContent = '';
                    if(isExisting) {
                         const q = this.existingQuestions.find(i => i.id === key);
                         if(q) initialContent = q.question_text;
                    } else {
                         const q = this.questions.find(i => i.id === key);
                         if(q) initialContent = q.question_text;
                    }

                    if(initialContent) {
                        editor.setData(initialContent);
                    }
                })
                .catch(error => { console.error('CKEditor Init Error:', error); });
        },

        async loadCaseStudies(examId) {
            if(!examId) {
                this.caseStudies = [];
                this.subCaseStudies = [];
                this.isActiveExam = false;
                return;
            }

            try {
                const response = await fetch(`/admin/questions-ajax/case-studies/${examId}`);
                const data = await response.json();
                this.isActiveExam = data.some(cs => cs.exam_is_active === 1);
                this.caseStudies = data;
                
                if(this.selectedCaseStudyId) {
                    this.$nextTick(() => {
                        document.getElementById('case_study_id').value = this.selectedCaseStudyId;
                        this.loadSubCaseStudies(this.selectedCaseStudyId);
                    });
                }
            } catch(error) {
                console.error('Error loading case studies:', error);
            }
        },

        async loadSubCaseStudies(caseStudyId) {
            if(!caseStudyId) {
                this.subCaseStudies = [];
                this.existingQuestions = [];
                return;
            }

            try {
                const response = await fetch(`/admin/questions-ajax/sub-case-studies/${caseStudyId}`);
                this.subCaseStudies = await response.json();
                
                if(this.selectedSubCaseId) {
                    this.$nextTick(() => {
                        document.getElementById('sub_case_id').value = this.selectedSubCaseId;
                         // Load existing questions for the selected sub case
                        this.loadExistingQuestions(this.selectedSubCaseId);
                    });
                }
            } catch(error) {
                console.error('Error loading sub case studies:', error);
            }
        },

        async loadExistingQuestions(subCaseId) {
            if(!subCaseId) {
                this.existingQuestions = [];
                return;
            }
            
            try {
                const response = await fetch(`/admin/questions-ajax/questions/${subCaseId}`);
                let data = await response.json();

                // If in Edit mode, filter out the current question from the existing list
                if (this.isEdit && this.currentQuestionId) {
                    data = data.filter(q => q.id != this.currentQuestionId);
                }

                // Process data to match Vue/Alpine structure (e.g. options handling)
                this.existingQuestions = data.map(q => {
                    let cat = 'ig';
                    if(q.dm_weight > 0) cat = 'dm';
                    
                    // Handle options
                    const opts = q.options.map(o => ({
                        text: o.option_text,
                        is_correct: o.is_correct == 1
                    }));

                    // Find single correct index
                    let singleIdx = 0;
                    if(q.question_type === 'single') {
                         const idx = opts.findIndex(o => o.is_correct);
                         if(idx > -1) singleIdx = idx;
                    }

                    return {
                        ...q,
                        question_text: q.question_text || '',
                        question_category: cat,
                        options: opts,
                        singleCorrect: singleIdx
                    };
                });

                // Init editors
                this.$nextTick(() => {
                    this.existingQuestions.forEach(q => {
                        this.initExistingEditor(q.id);
                    });
                });

            } catch(error) {
                console.error('Error loading existing questions:', error);
            }
        }
    }
}
</script>

<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

@endsection
