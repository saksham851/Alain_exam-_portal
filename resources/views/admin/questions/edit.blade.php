@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">{{ isset($question) ? 'Edit Question' : 'Add Question' }}</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('admin.questions.index') }}">Questions</a></li>
          <li class="breadcrumb-item" aria-current="page">{{ isset($question) ? 'Edit' : 'Add' }}</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row" x-data="questionForm()">
    <div class="col-md-12">
        <form action="{{ isset($question) ? route('admin.questions.update', $question->id) : route('admin.questions.store') }}" method="POST" id="questionForm">
            @csrf
            @if(isset($question)) @method('PUT') @endif

            <!-- Cascading Dropdowns -->
            <div class="card">
                <div class="card-header">
                    <h5>Select Location</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Exam <span class="text-danger">*</span></label>
                            <select class="form-select" id="exam_id" @change="loadCaseStudies($event.target.value)" required>
                                <option value="">Select Exam</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}" {{ (isset($question) && $question->caseStudy->section->exam_id == $exam->id) ? 'selected' : '' }}>
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
                            <select class="form-select" name="sub_case_id" id="sub_case_id" :disabled="subCaseStudies.length === 0" required>
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

            <!-- Question Details -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Question Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Question Text <span class="text-danger">*</span></label>
                            <textarea name="question_text" id="question_text" class="form-control" rows="4">{{ old('question_text', $question->question_text ?? '') }}</textarea>
                            @error('question_text') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Question Type <span class="text-danger">*</span></label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="question_type" id="type_single" value="single" x-model="questionType" {{ (!isset($question) || $question->question_type == 'single') ? 'checked' : '' }}>
                                <label class="form-check-label" for="type_single">Single Choice (One Correct Answer)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="question_type" id="type_multiple" value="multiple" x-model="questionType" {{ (isset($question) && $question->question_type == 'multiple') ? 'checked' : '' }}>
                                <label class="form-check-label" for="type_multiple">Multiple Choice (Multiple Correct Answers)</label>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Question Category <span class="text-danger">*</span></label>
                            <select name="question_category" class="form-select" required>
                                <option value="">Select Category</option>
                                <option value="ig" {{ (isset($question) && $question->ig_weight > 0) ? 'selected' : '' }}>
                                    IG - Internal Governance
                                </option>
                                <option value="dm" {{ (isset($question) && $question->dm_weight > 0) ? 'selected' : '' }}>
                                    DM - Decision Making
                                </option>
                            </select>
                            <small class="text-muted">Select which category this question belongs to</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Options -->
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Answer Options</h5>
                    <button type="button" @click="addOption" class="btn btn-sm btn-light-primary">
                        <i class="ti ti-plus"></i> Add Option
                    </button>
                </div>
                <div class="card-body">
                    <template x-for="(option, index) in options" :key="index">
                        <div class="border rounded p-3 mb-3 bg-light">
                            <div class="row align-items-center">
                                <div class="col-md-1">
                                    <div class="text-center">
                                        <h4 class="mb-0 text-primary" x-text="String.fromCharCode(65 + index)"></h4>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" 
                                           :name="'options['+index+'][text]'" 
                                           x-model="option.text" 
                                           class="form-control" 
                                           placeholder="Enter option text" 
                                           required>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-check">
                                        <template x-if="questionType === 'single'">
                                            <div>
                                                <input class="form-check-input" 
                                                       type="radio" 
                                                       name="correct_option" 
                                                       :id="'correct_'+index"
                                                       :value="index"
                                                       x-model="singleCorrect">
                                                <input type="hidden" 
                                                       :name="'options['+index+'][is_correct]'" 
                                                       :value="singleCorrect == index ? '1' : '0'">
                                            </div>
                                        </template>
                                        <template x-if="questionType === 'multiple'">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   :name="'options['+index+'][is_correct]'" 
                                                   :id="'correct_'+index"
                                                   x-model="option.is_correct"
                                                   value="1">
                                        </template>
                                        <label class="form-check-label" :for="'correct_'+index">
                                            Correct
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-1 text-end">
                                    <button type="button" 
                                            @click="removeOption(index)" 
                                            class="btn btn-sm btn-outline-danger"
                                            x-show="options.length > 2">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div class="alert alert-info mt-3">
                        <i class="ti ti-info-circle me-2"></i>
                        <span x-show="questionType === 'single'">Select ONE correct answer using radio buttons.</span>
                        <span x-show="questionType === 'multiple'">Select MULTIPLE correct answers using checkboxes.</span>
                    </div>
                </div>
            </div>

            <div class="mt-3 text-end">
                <a href="{{ route('admin.questions.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-check me-1"></i> Save Question
                </button>
            </div>
        </form>
    </div>
</div>

@php
    $initialOptions = [];
    $initialQuestionType = 'single';
    $initialSingleCorrect = 0;
    $selectedSubCaseId = null;
    $selectedCaseStudyId = null;
    $selectedExamId = null;

    if(isset($question)) {
        $initialOptions = $question->options->map(function($opt, $index) {
            return [
                'text' => $opt->option_text,
                'is_correct' => $opt->is_correct ? true : false
            ];
        })->toArray();
        $initialQuestionType = $question->question_type;
        
        if($question->question_type == 'single') {
            $correctIndex = $question->options->search(function($opt) {
                return $opt->is_correct == 1;
            });
            $initialSingleCorrect = $correctIndex !== false ? $correctIndex : 0;
        }
        
        $selectedSubCaseId = $question->case_study_id;
        $selectedCaseStudyId = $question->caseStudy->section_id;
        $selectedExamId = $question->caseStudy->section->exam_id;
    }

    if(empty($initialOptions)) {
        $initialOptions = [
            ['text' => '', 'is_correct' => false],
            ['text' => '', 'is_correct' => false],
        ];
    }
@endphp

<script>
function questionForm() {
    return {
        questionType: '{{ $initialQuestionType }}',
        options: @json($initialOptions),
        singleCorrect: {{ $initialSingleCorrect }},
        caseStudies: [],
        subCaseStudies: [],
        selectedExamId: {{ $selectedExamId ?? 'null' }},
        selectedCaseStudyId: {{ $selectedCaseStudyId ?? 'null' }},
        selectedSubCaseId: {{ $selectedSubCaseId ?? 'null' }},

        init() {
            // Load initial data if editing
            if(this.selectedExamId) {
                this.loadCaseStudies(this.selectedExamId);
            }

            // Handle form submission
            document.getElementById('questionForm').addEventListener('submit', (e) => {
                // Sync CKEditor data to textarea
                if (window.questionEditor) {
                    const editorData = window.questionEditor.getData();
                    document.querySelector('#question_text').value = editorData;
                    
                    // Validate that question text is not empty
                    if (!editorData || editorData.trim() === '') {
                        e.preventDefault();
                        alert('Please enter question text');
                        return false;
                    }
                }
                
                // For single choice, set is_correct based on radio selection
                if(this.questionType === 'single') {
                    this.options.forEach((opt, index) => {
                        opt.is_correct = (index === parseInt(this.singleCorrect));
                    });
                }
            });
        },

        addOption() {
            this.options.push({ text: '', is_correct: false });
        },

        removeOption(index) {
            if(this.options.length > 2) {
                this.options.splice(index, 1);
                // Adjust singleCorrect if needed
                if(this.questionType === 'single' && this.singleCorrect >= this.options.length) {
                    this.singleCorrect = 0;
                }
            }
        },

        async loadCaseStudies(examId) {
            if(!examId) {
                this.caseStudies = [];
                this.subCaseStudies = [];
                return;
            }

            try {
                const response = await fetch(`/admin/questions-ajax/case-studies/${examId}`);
                this.caseStudies = await response.json();
                
                // Auto-select if editing
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
                return;
            }

            try {
                const response = await fetch(`/admin/questions-ajax/sub-case-studies/${caseStudyId}`);
                this.subCaseStudies = await response.json();
                
                // Auto-select if editing
                if(this.selectedSubCaseId) {
                    this.$nextTick(() => {
                        document.getElementById('sub_case_id').value = this.selectedSubCaseId;
                    });
                }
            } catch(error) {
                console.error('Error loading sub case studies:', error);
            }
        }
    }
}
</script>

<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    ClassicEditor.create(document.querySelector('#question_text'))
        .then(editor => {
            window.questionEditor = editor;
        })
        .catch(error => { console.error(error); });
});
</script>

<!-- AlpineJS for dynamic form handling -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

@endsection
