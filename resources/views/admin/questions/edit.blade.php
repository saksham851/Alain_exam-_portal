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
    <div class="col-md-8">
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
                            <select class="form-select" id="exam_id" name="exam_id" x-model="selectedExamId" @change="loadCaseStudies($event.target.value)" :disabled="isEdit" required>
                                <option value="">Select Exam</option>
                                <template x-for="e in allExams" :key="e.id">
                                    <option :value="e.id" :selected="String(e.id) === String(selectedExamId)" x-text="e.name"></option>
                                </template>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Section <span class="text-danger">*</span></label>
                            <select class="form-select" id="case_study_id" name="section_id" x-model="selectedCaseStudyId" @change="loadSubCaseStudies($event.target.value)" :disabled="caseStudies.length === 0" required>
                                <option value="">Select Section</option>
                                <template x-for="cs in caseStudies" :key="cs.id">
                                    <option :value="cs.id" :selected="String(cs.id) === String(selectedCaseStudyId)"
                                            :data-category-id="cs.exam_standard_category_id"
                                            x-text="cs.title + (cs.category_name ? ' [' + cs.category_name + ']' : '')"></option>
                                </template>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Case Study <span class="text-danger">*</span></label>
                            <select class="form-select" name="sub_case_id" id="sub_case_id" x-model="selectedSubCaseId" :disabled="subCaseStudies.length === 0" @change="!isEdit && loadExistingQuestions($event.target.value)" required>
                                <option value="">Select Case Study</option>
                                <template x-for="scs in subCaseStudies" :key="scs.id">
                                    <option :value="scs.id" :selected="String(scs.id) === String(selectedSubCaseId)" x-text="scs.title"></option>
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


                                    <!-- Content Area for Existing Questions -->
                                    <!-- Tags / Content Areas for Existing Questions -->
                                    <div class="col-md-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label text-primary fw-bold mb-0">
                                                <i class="ti ti-tags me-1"></i> Question Tags (Category Mapping)
                                            </label>
                                            <button type="button" class="btn btn-sm btn-outline-primary" @click="addExistingTag(eqIndex)">
                                                <i class="ti ti-plus me-1"></i> Add Tag
                                            </button>
                                        </div>
                                        
                                        <template x-if="exQ.tags.length === 0">
                                            <div class="alert alert-warning py-2 mb-2">
                                                <small><i class="ti ti-alert-triangle me-1"></i> No content area assigned. Please add at least one tag.</small>
                                            </div>
                                        </template>

                                        <template x-for="(tag, tIndex) in exQ.tags" :key="tIndex">
                                            <div class="row align-items-center mb-2">
                                                <div class="col-md-5">
                                                    <select :name="'existing_questions['+exQ.id+'][tags]['+tIndex+'][score_category_id]'" 
                                                            class="form-select form-select-sm"
                                                            x-model="tag.score_category_id">
                                                        <option value="">Select Category</option>
                                                        <template x-for="cat in examStandardCategories" :key="cat.id">
                                                            <option :value="cat.id" x-text="cat.name" :selected="tag.score_category_id == cat.id"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <div class="col-md-5">
                                                    <select :name="'existing_questions['+exQ.id+'][tags]['+tIndex+'][content_area_id]'" 
                                                            class="form-select form-select-sm"
                                                            x-model="tag.content_area_id"
                                                            :disabled="!tag.score_category_id">
                                                        <option value="">Select Content Area</option>
                                                        <template x-for="area in getContentAreas(tag.score_category_id)" :key="area.id">
                                                            <option :value="area.id" x-text="area.name + (area.max_points ? ' (Max: ' + area.max_points + ')' : '')" :selected="tag.content_area_id == area.id"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-sm btn-outline-danger w-100" @click="removeExistingTag(eqIndex, tIndex)">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                     </div>

                                    <!-- Max Points Field (0 to 3) -->
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold"><i class="ti ti-star me-1 text-warning"></i> Question Points (0-3)</label>
                                        <div class="input-group">
                                            <input type="number" 
                                                   :name="'existing_questions['+exQ.id+'][max_question_points]'"
                                                   class="form-control" 
                                                   min="0" 
                                                   max="3"
                                                   step="1"
                                                   x-model="exQ.max_question_points">
                                            <span class="input-group-text">pts</span>
                                        </div>
                                        <small class="text-muted">Weight for this question.</small>
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
                                <!-- Removed required to prevent hidden tooltip issues, handled by server & alert -->
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

                            <!-- Tags for New Questions -->
                            <div class="col-md-12 mb-3" x-show="examStandardCategories.length > 0">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label text-primary fw-bold mb-0">
                                        <i class="ti ti-tags me-1"></i> Question Tags
                                    </label>
                                    <button type="button" class="btn btn-sm btn-outline-primary" @click="addTag(qIndex)">
                                        <i class="ti ti-plus me-1"></i> Add Tag
                                    </button>
                                </div>
                                <template x-for="(tag, tIndex) in questionItem.tags" :key="tIndex">
                                    <div class="row align-items-center mb-2">
                                        <div class="col-md-5">
                                            <select :name="isEdit ? 'tags['+tIndex+'][score_category_id]' : 'questions['+qIndex+'][tags]['+tIndex+'][score_category_id]'"
                                                    class="form-select form-select-sm"
                                                    x-model="tag.score_category_id">
                                                <option value="">Select Category</option>
                                                <template x-for="cat in examStandardCategories" :key="cat.id">
                                                    <option :value="cat.id" x-text="cat.name" :selected="tag.score_category_id == cat.id"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <select :name="isEdit ? 'tags['+tIndex+'][content_area_id]' : 'questions['+qIndex+'][tags]['+tIndex+'][content_area_id]'" 
                                                    class="form-select form-select-sm"
                                                    x-model="tag.content_area_id"
                                                    :disabled="!tag.score_category_id">
                                                <option value="">Select Content Area</option>
                                                <template x-for="area in getContentAreas(tag.score_category_id)" :key="area.id">
                                                    <option :value="area.id" x-text="area.name + (area.max_points ? ' (Max: ' + area.max_points + ')' : '')" :selected="tag.content_area_id == area.id"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger w-100" @click="removeTag(qIndex, tIndex)">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Max Points (0 to 3) -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold"><i class="ti ti-star me-1 text-warning"></i> Question Points (0-3)</label>
                                <div class="input-group">
                                    <input type="number" 
                                           :name="isEdit ? 'max_question_points' : 'questions['+qIndex+'][max_question_points]'"
                                           class="form-control" 
                                           min="0" 
                                           max="3"
                                           step="1"
                                           x-model="questionItem.max_question_points">
                                    <span class="input-group-text">pts</span>
                                </div>
                                <small class="text-muted">Weight for this question.</small>
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
    
    <!-- Sidebar: Real-Time Compliance Check -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 sticky-top" style="top: 20px; z-index: 100;">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white"><i class="ti ti-chart-pie me-2"></i> Standard Status</h5>
                <button class="btn btn-sm btn-light text-primary" @click="loadComplianceData()" title="Refresh Status">
                    <i class="ti ti-refresh"></i>
                </button>
            </div>
            <div class="card-body p-0" style="max-height: 80vh; overflow-y: auto;">
                
                <!-- Loading State -->
                <div x-show="isLoadingCompliance" class="p-4 text-center text-muted">
                    <div class="spinner-border spinner-border-sm mb-2" role="status"></div>
                    <p class="small mb-0">Checking compliance requirements...</p>
                </div>

                <!-- Empty State -->
                <div x-show="!isLoadingCompliance && !complianceData.content_areas" class="p-4 text-center text-muted">
                    <i class="ti ti-alert-circle display-6 mb-3 opacity-50"></i>
                    <p>Select an Exam to view Standard Requirements and Progress.</p>
                </div>

                <!-- Data Content -->
                <template x-if="!isLoadingCompliance && complianceData.content_areas">
                    <div>
                        <!-- Overall Status -->
                        <div class="p-3 border-bottom bg-light">
                             <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-dark">Overall Status</span>
                                <span class="badge" :class="complianceHasErrors ? 'bg-danger' : 'bg-success'">
                                    <i class="ti" :class="complianceHasErrors ? 'ti-alert-triangle' : 'ti-check'"></i>
                                    <span x-text="complianceHasErrors ? 'Needs Attention' : 'Compliant'"></span>
                                </span>
                             </div>
                             <small class="text-muted d-block">Add questions with tags to meet the goals below.</small>
                        </div>

                        <!-- Categories List -->
                        <ul class="list-group list-group-flush">
                            <template x-for="area in complianceData.content_areas" :key="area.id">
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold small text-dark text-truncate" style="max-width: 65%;" :title="area.name" x-text="area.name"></span>
                                        <div class="text-end">
                                            <span class="badge" 
                                                  :class="area.valid ? 'bg-light-success text-success' : 'bg-light-danger text-danger'"
                                                  x-text="area.valid ? 'Pass' : 'Low'"></span>
                                        </div>
                                    </div>
                                    
                                    <!-- Progress Bar -->
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar" 
                                             :class="area.valid ? 'bg-success' : 'bg-danger'"
                                             role="progressbar" 
                                             :style="'width: ' + Math.min((area.current / (area.required || 1)) * 100, 100) + '%'"></div>
                                    </div>
                                    
                                    <!-- Stats Text -->
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted" style="font-size: 11px;">Current: <strong x-text="area.current"></strong></small>
                                        <small class="text-muted" style="font-size: 11px;">Required: <strong x-text="area.required"></strong> pts</small>
                                    </div>
                                    
                                    <!-- Helper Hint -->
                                    <template x-if="!area.valid">
                                        <div class="mt-1">
                                            <span class="badge bg-light-warning text-warning border border-warning" style="font-size: 10px;">
                                                Add <span x-text="(area.required - area.current)"></span> pts tagged to this area
                                            </span>
                                        </div>
                                    </template>
                                </li>
                            </template>
                        </ul>
                    </div>
                </template>
            </div>
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

    $selectedSectionCategoryId = null;
    $initialAllowedContentAreas = [];

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

        $initialQuestions[] = [
            'question_text' => $question->question_text,
            'question_type' => $question->question_type,
            'max_question_points' => $question->max_question_points,
            'tags' => $question->tags->map(fn($t) => [
                'score_category_id' => (string)$t->score_category_id, 
                'content_area_id' => (string)$t->content_area_id
            ])->toArray(),
            'options' => $opts,
            'singleCorrect' => $singleCorrect
        ];

        $selectedSubCaseId = $question->case_study_id;
        $selectedCaseStudyId = $question->caseStudy->section_id;
        $selectedExamId = $question->caseStudy->section->exam_id;
    } else {
        $oldQuestions = old('questions');
        if ($oldQuestions && is_array($oldQuestions)) {
            foreach ($oldQuestions as $q) {
                // Alpine needs singleCorrect for radioactivity
                $options = array_values($q['options'] ?? [['text' => '', 'is_correct' => '0'], ['text' => '', 'is_correct' => '0']]);
                $singleCorrect = 0;
                foreach($options as $idx => $opt) {
                    if (isset($opt['is_correct']) && $opt['is_correct'] == '1') {
                        $singleCorrect = $idx;
                        break;
                    }
                }
                
                $initialQuestions[] = [
                    'question_text' => $q['question_text'] ?? '',
                    'question_type' => $q['question_type'] ?? 'single',
                    'max_question_points' => $q['max_question_points'] ?? 1,
                    'tags' => $q['tags'] ?? [],
                    'options' => $options,
                    'singleCorrect' => $singleCorrect
                ];
            }
        } else {
            // Create Mode - Start with one empty question
            $initialQuestions[] = [
                'question_text' => '',
                'question_type' => 'single',
                'max_question_points' => 1,
                'tags' => [],
                'options' => [
                    ['text' => '', 'is_correct' => '0'],
                    ['text' => '', 'is_correct' => '0']
                ],
                'singleCorrect' => 0
            ];
        }
    }

    // Pre-calculate Standard Categories for tag dropdowns
    $initialStandardCategories = [];
    $targetExamId = old('exam_id', request('exam_id') ?? ($selectedExamId ?? null));
    if ($targetExamId) {
        $foundExam = $exams->where('id', $targetExamId)->first();
        if ($foundExam) {
            $standard = $foundExam->examStandard ?? $foundExam->exam_standard;
            if ($standard) {
                foreach($standard->categories as $cat) {
                    $initialStandardCategories[] = [
                        'id' => (string)$cat->id,
                        'name' => $cat->name,
                        'content_areas' => $cat->contentAreas->map(fn($a) => [
                            'id' => (string)$a->id,
                            'name' => $a->name,
                            'max_points' => $a->max_points
                        ])->toArray()
                    ];
                }
            }
        }
    }
@endphp
<script>
function questionForm() {
    return {
        isEdit: {{ $isEdit ? 'true' : 'false' }},
        isActiveExam: {{ $isActiveExam ? 'true' : 'false' }},
        currentQuestionId: {{ isset($question) ? $question->id : 'null' }},
        
        // Data Models
        questions: @json($initialQuestions).map(q => ({ 
            ...q, 
            id: 'q_' + Math.random().toString(36).substr(2, 9),
            tags: (q.tags || []).map(t => ({ 
                score_category_id: String(t.score_category_id || ''), 
                content_area_id: String(t.content_area_id || '') 
            })),
            max_question_points: q.max_question_points || 1
        })),
        existingQuestions: [],
        
        // Select Options
        caseStudies: [],
        subCaseStudies: [],
        allExams: @json($exams),
        selectedExamId: {{ old('exam_id', request('exam_id') ?? ($selectedExamId ?? 'null')) }},
        selectedCaseStudyId: {{ old('section_id', request('section_id') ?? ($selectedCaseStudyId ?? 'null')) }},
        selectedSubCaseId: {{ old('sub_case_id', request('case_study_id') ?? ($selectedSubCaseId ?? 'null')) }},
        
        // Exam Standard Data for Tagging
        examStandardCategories: @json($initialStandardCategories), // [{id, name, content_areas: []}]
        
        examQuestionLimit: 0,
        currentExamQuestionCount: 0,

        get totalQuestionsCount() {
            return (this.existingQuestions ? this.existingQuestions.length : 0) + (this.questions ? this.questions.length : 0);
        },
        editors: {},
        existingEditors: {},
        
        // Compliance Widget Data
        complianceData: {},
        complianceHasErrors: false,
        isLoadingCompliance: false,

        init() {
            if(this.selectedExamId) {
                // Ensure the variable is set for internal methods
                this.loadCaseStudies(this.selectedExamId);
            }
            if(this.selectedSubCaseId) {
                 this.$nextTick(() => { });
            }
            this.$nextTick(() => {
                if(typeof ClassicEditor !== 'undefined') {
                    this.questions.forEach((q) => {
                        this.initEditor(q.id);
                    });
                } else {
                    console.warn('ClassicEditor not loaded yet. Editors will be initialized on first demand.');
                }
            });

            // Form Submit Handler
            document.getElementById('questionForm').addEventListener('submit', (e) => {
                let isValid = true;
                this.questions.forEach((q) => {
                    if(this.editors[q.id]) {
                        const data = this.editors[q.id].getData();
                        q.question_text = data;
                        const el = document.getElementById('question_text_' + q.id);
                        if(el) el.value = data;
                        if(!data || data.trim() === '') isValid = false;
                    }
                });
                this.existingQuestions.forEach(exQ => {
                    if(this.existingEditors[exQ.id]) {
                        const data = this.existingEditors[exQ.id].getData();
                        exQ.question_text = data;
                        const el = document.getElementById('existing_question_text_' + exQ.id);
                        if(el) el.value = data;
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Please enter text for all new questions');
                    return false;
                }
            });
        },

        // --- Standard & Tagging Helpers ---
        loadExamStandardData(standard) {
             if(!standard) {
                 this.examStandardCategories = [];
                 return;
             }
             // Map standard categories
             this.examStandardCategories = (standard.categories || []).map(cat => ({
                 id: String(cat.id),
                 name: cat.name,
                 content_areas: (cat.content_areas || cat.contentAreas || []).map(area => ({
                     id: String(area.id),
                     name: area.name,
                     max_points: area.max_points
                 }))
             }));
        },

        getContentAreas(categoryId) {
            if(!categoryId) return [];
            const cat = this.examStandardCategories.find(c => String(c.id) === String(categoryId));
            return cat ? cat.content_areas : [];
        },

        // --- Question Actions ---
        addQuestion() {
            if (this.examQuestionLimit > 0 && this.totalQuestionsCount >= this.examQuestionLimit) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Capacity Reached',
                    text: `This exam has a limit of ${this.examQuestionLimit} questions. You already have ${this.totalQuestionsCount}.` 
                });
                return;
            }

            const newId = 'q_' + Math.random().toString(36).substr(2, 9);
            this.questions.push({
                id: newId,
                question_text: '',
                question_type: 'single',
                max_question_points: 1,
                tags: [], // Start with empty tags
                singleCorrect: 0,
                options: [{ text: '', is_correct: false }, { text: '', is_correct: false }]
            });
            
            this.$nextTick(() => { this.initEditor(newId); });
        },

        removeQuestion(index) {
            const q = this.questions[index];
            this.questions.splice(index, 1);
            setTimeout(() => {
                if(q.id && this.editors[q.id]) {
                    try { this.editors[q.id].destroy(); } catch(e){}
                    delete this.editors[q.id];
                }
            }, 50);
        },
        
        // --- Tag Actions (New Questions) ---
        addTag(qIndex) {
            this.questions[qIndex].tags.push({ score_category_id: '', content_area_id: '' });
        },
        removeTag(qIndex, tIndex) {
            this.questions[qIndex].tags.splice(tIndex, 1);
        },

        // --- Tag Actions (Existing Questions) ---
        addExistingTag(eqIndex) {
             this.existingQuestions[eqIndex].tags.push({ score_category_id: '', content_area_id: '' });
        },
        removeExistingTag(eqIndex, tIndex) {
             this.existingQuestions[eqIndex].tags.splice(tIndex, 1);
        },

        // --- Options Actions ---
        addOption(qIndex) {
            this.questions[qIndex].options.push({ text: '', is_correct: false });
        },
        removeOption(qIndex, oIndex) {
            if(this.questions[qIndex].options.length > 2) {
                this.questions[qIndex].options.splice(oIndex, 1);
                if(this.questions[qIndex].question_type === 'single' && this.questions[qIndex].singleCorrect >= this.questions[qIndex].options.length) {
                    this.questions[qIndex].singleCorrect = 0;
                }
            }
        },
        resetCorrectAnswers(qIndex) {
            this.questions[qIndex].singleCorrect = 0;
            this.questions[qIndex].options.forEach(opt => opt.is_correct = false);
        },
        
        // Existing Options
        addExistingOption(eqIndex) {
            this.existingQuestions[eqIndex].options.push({ text: '', is_correct: false });
        },
        removeExistingOption(eqIndex, oIndex) {
             if(this.existingQuestions[eqIndex].options.length > 2) {
                this.existingQuestions[eqIndex].options.splice(oIndex, 1);
                 if(this.existingQuestions[eqIndex].question_type === 'single' && this.existingQuestions[eqIndex].singleCorrect >= this.existingQuestions[eqIndex].options.length) {
                    this.existingQuestions[eqIndex].singleCorrect = 0;
                }
            }
        },
        resetExistingCorrectAnswers(eqIndex) {
            this.existingQuestions[eqIndex].singleCorrect = 0;
            this.existingQuestions[eqIndex].options.forEach(opt => opt.is_correct = false);
        },

        async loadComplianceData() {
            if(!this.selectedExamId) return;
            this.isLoadingCompliance = true;
            try {
                const response = await fetch(`/admin/exams/${this.selectedExamId}/validate-compliance`);
                const data = await response.json();
                if(data.success) {
                    this.complianceData = data.compliance;
                    this.complianceHasErrors = !data.compliance.valid;

                    // Support legacy mapping and new categories mapping from Exam::validateStandardCompliance
                    if(data.compliance.categories && data.compliance.categories.length > 0) {
                        this.examStandardCategories = data.compliance.categories.map(cat => ({
                            id: String(cat.id),
                            name: cat.name,
                            content_areas: (cat.contentAreas || cat.content_areas || []).map(area => ({
                                id: String(area.id),
                                name: area.name,
                                max_points: area.max_points
                            }))
                        }));
                    }
                }
            } catch(e) {
                console.error("Compliance Load Error", e);
            } finally {
                this.isLoadingCompliance = false;
            }
        },

        // --- AJAX Loaders ---
        async loadCaseStudies(examId) {
            if(!examId) {
                this.caseStudies = []; this.subCaseStudies = []; this.isActiveExam = false; this.examStandardCategories = [];
                return;
            }
            this.selectedExamId = examId; // Sync
            try {
                const response = await fetch(`/admin/questions-ajax/case-studies/${examId}`);
                const data = await response.json();
                
                // New robust parsing
                const sections = (data && data.sections) ? data.sections : (Array.isArray(data) ? data : []);
                const examObj = (data && data.exam) ? data.exam : null;
                
                this.isActiveExam = (examObj && examObj.is_active == 1);
                this.caseStudies = sections;
                this.examQuestionLimit = examObj ? examObj.total_questions : 0;
                
                // Trigger Compliance Check - WAIT for it to populate categories
                await this.loadComplianceData();

                // Load Exam Standard Data for Tags (Fallback if compliance didn't have it)
                if (this.examStandardCategories.length === 0) {
                    const selectedExam = this.allExams.find(e => String(e.id) === String(examId));
                    const standard = selectedExam ? (selectedExam.exam_standard || selectedExam.examStandard) : null;
                    this.loadExamStandardData(standard);
                }

                if(this.selectedCaseStudyId) {
                    await this.loadSubCaseStudies(this.selectedCaseStudyId);
                }
            } catch(error) { 
                console.error("LoadCaseStudies Error", error);
                this.caseStudies = [];
            }
        },

        async loadSubCaseStudies(sectionId) {
             if(!sectionId) return;
             try {
                const response = await fetch(`/admin/questions-ajax/sub-case-studies/${sectionId}`);
                this.subCaseStudies = await response.json();
                if(this.selectedSubCaseId) {
                    this.$nextTick(() => {
                        if (!this.isEdit) this.loadExistingQuestions(this.selectedSubCaseId);
                    });
                }
             } catch(e) { console.error(e); }
        },

        async loadExistingQuestions(subCaseId) {
            if(!subCaseId) { this.existingQuestions = []; return; }
            try {
                const response = await fetch(`/admin/questions-ajax/questions/${subCaseId}`);
                let data = await response.json();
                if (this.isEdit && this.currentQuestionId) data = data.filter(q => q.id != this.currentQuestionId);

                this.existingQuestions = data.map(q => {
                    const opts = q.options.map(o => ({ text: o.option_text, is_correct: o.is_correct == 1 }));
                    let singleIdx = 0;
                    if(q.question_type === 'single') {
                         const idx = opts.findIndex(o => o.is_correct);
                         if(idx > -1) singleIdx = idx;
                    }
                    
                    // Map Tags - Ensuring IDs are strings for reliable dropdown matching
                    const tags = (q.tags || []).map(t => ({
                        score_category_id: String(t.score_category_id),
                        content_area_id: String(t.content_area_id) 
                    }));

                    return {
                        ...q,
                        question_text: q.question_text || '',
                        max_question_points: q.max_question_points || 1,
                        tags: tags,
                        options: opts,
                        singleCorrect: singleIdx,
                        isSaving: false
                    };
                });

                this.$nextTick(() => {
                    this.existingQuestions.forEach(q => this.initExistingEditor(q.id));
                });
            } catch(error) { console.error(error); }
        },

        // --- Editors ---
        initEditor(uniqueId) { this.$nextTick(() => { this.createEditor('question_text_' + uniqueId, uniqueId, false); }); },
        initExistingEditor(questionId) { this.$nextTick(() => { this.createEditor('existing_question_text_' + questionId, questionId, true); }); },
        createEditor(elementId, key, isExisting) {
            const el = document.getElementById(elementId);
            const editorMap = isExisting ? this.existingEditors : this.editors;
            if(editorMap[key] || !el) return;
            if(el.nextSibling && el.nextSibling.classList && el.nextSibling.classList.contains('ck-editor')) el.nextSibling.remove();
            
            ClassicEditor.create(el).then(editor => {
                editorMap[key] = editor;
                editor.model.document.on('change:data', () => {
                    const data = editor.getData();
                    const q = isExisting ? this.existingQuestions.find(i => i.id === key) : this.questions.find(i => i.id === key);
                    if(q) q.question_text = data;
                });
                // Set Data
                const q = isExisting ? this.existingQuestions.find(i => i.id === key) : this.questions.find(i => i.id === key);
                if(q && q.question_text) editor.setData(q.question_text);
            }).catch(e => console.error(e));
        },
        
        async removeExistingQuestion(index, id) {
             window.showAlert.confirm('Delete this question?', 'Delete', async () => {
                 // Fetch Delete logic... (same as before)
                  try {
                    const response = await fetch(`/admin/questions/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
                    });
                    if(response.ok) {
                        this.existingQuestions.splice(index, 1);
                        window.showAlert.toast('Deleted');
                    }
                  } catch(e) { window.showAlert.error('Error'); }
             });
        }
    }
}
</script>

<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

@endsection
