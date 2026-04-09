@extends('layouts.app')

@section('content')

@php
    $routePrefix = auth()->user()->role === 'manager' ? 'manager' : 'admin';
@endphp
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Add Case Studies</h5>
        </div>

      </div>
    </div>
  </div>
</div>

<div class="row" x-data="caseStudyForm()">
    <div class="col-md-12">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route($routePrefix . '.case-studies-bank.store') }}" method="POST" id="caseStudyForm">
            @csrf
            @if(request()->has('return_url'))
                <input type="hidden" name="return_url" value="{{ request('return_url') }}">
            @endif
            
            <!-- Hidden inputs for deleted case studies -->
            <template x-for="id in deletedCaseStudyIds" :key="id">
                <input type="hidden" name="deleted_case_studies[]" :value="id">
            </template>

            <div class="card">
                <div class="card-header">
                    <h5>Select Target Section</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">


                        <div class="col-md-6">
                            <label class="form-label fw-bold">Select Exam <span class="text-danger">*</span></label>
                            <select name="exam_id" id="examSelect" class="form-select" x-model="selectedExamId" @change="fetchSections()" required {{ request('exam_id') ? 'style=pointer-events:none;background-color:#e9ecef;' : '' }}>
                                <option value="">Choose Exam...</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Select Section <span class="text-danger">*</span></label>
                            <select name="section_id" id="sectionSelect" class="form-select" x-model="selectedSectionId" @change="fetchExistingCaseStudies()" required>
                                <option value="">Select Exam First...</option>
                                <template x-for="section in sections" :key="section.id">
                                    <option :value="section.id" x-text="section.title"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Container for Ajax Loaded Existing Case Studies (for manual selection flow) -->
                    <template x-if="existingCaseStudies.length > 0">
                        <div class="mb-4">
                            <h6 class="d-flex align-items-center mb-3 text-muted">
                                <i class="ti ti-edit me-2"></i> Existing Case Studies (Editable)
                            </h6>
                            <template x-for="(study, index) in existingCaseStudies" :key="study.id">
                                <div class="card border mb-3">
                                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3" 
                                         style="cursor: pointer;" 
                                         @click="study.isOpen = !study.isOpen">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ti fs-5 text-muted transition-transform" 
                                               :class="study.isOpen ? 'ti-chevron-down' : 'ti-chevron-right'"></i>
                                            <h6 class="mb-0 fw-bold text-dark" x-text="study.title || 'Case Study #' + (index + 1)"></h6>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-icon btn-light-danger rounded-circle" 
                                                @click.stop="removeExistingCaseStudy(index, study.id)"
                                                title="Delete this case study">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>

                                    <div x-show="study.isOpen" x-collapse>
                                        <div class="card-body pt-0">
                                            <hr class="mt-0 mb-4 border-light">
                                            
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label fw-bold small text-uppercase text-secondary">Title <span class="text-danger">*</span></label>
                                                    <input type="text" :name="'existing_case_studies['+study.id+'][title]'" class="form-control" 
                                                           x-model="study.title" required @click.stop>
                                                    <input type="hidden" :name="'existing_case_studies['+study.id+'][order_no]'" x-model="study.order_no">
                                                </div>

                                                <!-- Existing Visits for this Case Study -->
                                                <div class="col-md-12 mt-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                                        <h6 class="mb-0 fw-bold text-muted d-flex align-items-center">
                                                            <i class="ti ti-map-pin me-2 fs-5"></i> Visits
                                                            <span class="badge bg-primary-subtle text-primary ms-2 rounded-pill px-2" x-text="(study.visits ? study.visits.length : 0) + ' existing'"></span>
                                                        </h6>
                                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" @click.stop="addVisitToExisting(index)">
                                                            <i class="ti ti-plus me-1"></i> Add Visit
                                                        </button>
                                                    </div>

                                                    <!-- Existing visits (from DB) -->
                                                    <template x-if="study.visits && study.visits.length > 0">
                                                        <div class="vstack gap-3">
                                                            <template x-for="(visit, vIdx) in study.visits" :key="visit.id">
                                                                <div class="visit-block animate-in border rounded-3 overflow-hidden">
                                                                    <!-- Visit Header / Toggle -->
                                                                    <div class="d-flex justify-content-between align-items-center p-3 bg-light-subtle cursor-pointer"
                                                                         @click="visit.isOpen = !visit.isOpen">
                                                                        <div class="d-flex align-items-center gap-3">
                                                                            <i class="ti fs-6 text-muted transition-transform" :class="visit.isOpen ? 'ti-chevron-down' : 'ti-chevron-right'"></i>

                                                                            <div>
                                                                                <h6 class="fw-bold text-dark mb-0" x-text="visit.title || 'Visit #' + (vIdx + 1)"></h6>
                                                                            </div>
                                                                        </div>
                                                                        <button type="button" class="btn btn-sm btn-icon btn-light-danger rounded-circle"
                                                                                @click.stop="removeExistingVisit(index, vIdx, visit.id)"
                                                                                title="Remove visit">
                                                                            <i class="ti ti-trash"></i>
                                                                        </button>
                                                                    </div>

                                                                    <!-- Visit Body (Collapsible) -->
                                                                    <div x-show="visit.isOpen" x-collapse>
                                                                        <div class="p-3 bg-white border-top">
                                                                            <div class="row g-3">
                                                                                <div class="col-md-12">
                                                                                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Visit Name <span class="text-danger">*</span></label>
                                                                                    <input type="hidden" :name="'existing_case_studies['+study.id+'][visits]['+vIdx+'][id]'" :value="visit.id">
                                                                                    <input type="hidden" :name="'existing_case_studies['+study.id+'][visits]['+vIdx+'][order_no]'" x-model="visit.order_no">
                                                                                    <input type="text"
                                                                                           :name="'existing_case_studies['+study.id+'][visits]['+vIdx+'][title]'"
                                                                                           x-model="visit.title" class="form-control" required @click.stop>
                                                                                </div>
                                                                                <div class="col-md-12">
                                                      <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Visit Content / Description</label>
                                                      <textarea :name="'existing_case_studies['+study.id+'][visits]['+vIdx+'][description]'"
                                                                x-model="visit.description" class="form-control" rows="3" placeholder="Enter visit specific content here..." @click.stop></textarea>
                                                  </div>
                                                                            </div>

                                                                            <!-- Existing Questions Dropdown -->
                                                                            <div class="mt-4 pt-3 border-top">
                                                                                <div class="d-flex align-items-center justify-content-between cursor-pointer"
                                                                                     @click.stop="visit.isQuestionsOpen = !visit.isQuestionsOpen">
                                                                                    <h6 class="mb-0 text-muted small fw-bold text-uppercase d-flex align-items-center">
                                                                                        <i class="ti ti-help-circle me-1 fs-6"></i> 
                                                                                        Existing Questions 
                                                                                        <span class="badge bg-light ms-2 text-dark border" x-text="visit.questions ? visit.questions.length : 0"></span>
                                                                                    </h6>
                                                                                    <i class="ti fs-6 text-muted transition-transform" :class="visit.isQuestionsOpen ? 'ti-chevron-down' : 'ti-chevron-right'"></i>
                                                                                </div>

                                                                                <div x-show="visit.isQuestionsOpen" x-collapse class="mt-3">
                                                                                    <template x-if="visit.questions && visit.questions.length > 0">
                                                                                        <div class="list-group list-group-flush border rounded-2 overflow-hidden">
                                                                                            <template x-for="q in visit.questions" :key="q.id">
                                                                                                <div class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 px-3 bg-light-subtle">
                                                                                                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                                                                                                        <span class="badge bg-white border text-dark shadow-sm" x-text="q.question_type"></span>
                                                                                                        <div class="text-truncate small text-dark" style="max-width: 300px;" x-html="q.question_text"></div>
                                                                                                    </div>
                                                                                                    <div class="d-flex align-items-center gap-3">
                                                                                                        <span class="badge bg-light text-muted border" x-text="q.points + ' pts'"></span>
                                                                                                        <a :href="'/{{ $routePrefix }}/questions/' + q.id + '/edit'" class="btn btn-icon btn-sm btn-light-primary rounded-circle" title="Edit Question">
                                                                                                            <i class="ti ti-pencil"></i>
                                                                                                        </a>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </template>
                                                                                        </div>
                                                                                    </template>
                                                                                    <template x-if="!visit.questions || visit.questions.length === 0">
                                                                                        <div class="text-center text-muted small py-3 border border-dashed rounded bg-light-subtle">
                                                                                            No questions found for this visit.
                                                                                        </div>
                                                                                    </template>
                                                                                </div>
                                                                            </div>

                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>
                                                    <template x-if="!study.visits || study.visits.length === 0">
                                                        <div class="text-center text-muted small py-4 bg-light rounded border border-dashed">
                                                            <i class="ti ti-alert-circle me-1 fs-5 mb-2 d-block"></i>
                                                            No visits yet for this case study.
                                                        </div>
                                                    </template>

                                                    <!-- New visits to add to this existing case study -->
                                                    <template x-if="study.newVisits && study.newVisits.length > 0">
                                                        <div class="mt-3">
                                                            <div class="text-muted small fw-bold mb-2 ps-1"><i class="ti ti-plus me-1 text-success"></i>New Visits to Add:</div>
                                                            <div class="vstack gap-2">
                                                                <template x-for="(nv, nvIdx) in study.newVisits" :key="nvIdx">
                                                                    <div class="visit-block animate-in border border-success-subtle bg-success-subtle bg-opacity-10 rounded-3 p-3">
                                                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                                                            <div class="d-flex align-items-center gap-3">

                                                                                <div>
                                                                                    <h6 class="fw-bold text-dark mb-0">New Visit #<span x-text="(study.visits ? study.visits.length : 0) + nvIdx + 1"></span></h6>
                                                                                    <small class="text-success d-block" style="font-size: 11px;">Adding to existing case study</small>
                                                                                </div>
                                                                            </div>
                                                                            <button type="button" class="btn btn-sm btn-icon btn-light-danger rounded-circle"
                                                                                    @click="removeNewVisitFromExisting(index, nvIdx)">
                                                                                <i class="ti ti-trash"></i>
                                                                            </button>
                                                                        </div>

                                                                        <div class="row g-3">
                                                                            <div class="col-md-12">
                                                                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Visit Name <span class="text-danger">*</span></label>
                                                                                <input type="hidden" :name="'existing_case_studies['+study.id+'][new_visits]['+nvIdx+'][order_no]'" x-model="nv.order_no">
                                                                                <input type="text"
                                                                                       :name="'existing_case_studies['+study.id+'][new_visits]['+nvIdx+'][title]'"
                                                                                       x-model="nv.title" class="form-control" required placeholder="e.g. Follow-up">
                                                                            </div>
                                                                            <div class="col-md-12">
                                                  <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Visit Content / Description</label>
                                                  <textarea :name="'existing_case_studies['+study.id+'][new_visits]['+nvIdx+'][description]'"
                                                            x-model="nv.description" class="form-control" rows="3" placeholder="Enter visit specific content here..."></textarea>
                                              </div>
                                                                        </div>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </template>

                                                <!-- Hidden inputs for deleted visits -->
                                                <template x-for="vid in (study.deletedVisitIds || [])" :key="vid">
                                                    <input type="hidden" :name="'existing_case_studies['+study.id+'][deleted_visits][]'" :value="vid">
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 text-muted">Case Studies</h6>
                        <button type="button" class="btn btn-sm btn-primary" @click="addCaseStudy()">
                            <i class="ti ti-plus me-1"></i> Add Another Case Study
                        </button>
                    </div>

                    <template x-for="(caseStudy, index) in caseStudies" :key="index">
                        <div class="card border mb-3">
                            <div class="card-body position-relative">
                                <button type="button" class="btn btn-sm btn-icon btn-light-danger rounded-circle position-absolute top-0 end-0 m-2" 
                                        @click="removeCaseStudy(index)">
                                    <i class="ti ti-x"></i>
                                </button>
                                
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                                        <input type="text" :name="'case_studies['+index+'][title]'" class="form-control" 
                                               x-model="caseStudy.title" placeholder="Enter case study title" required>
                                        <input type="hidden" :name="'case_studies['+index+'][order_no]'" x-model="caseStudy.order_no">
                                    </div>

                                    <!-- Visits Section -->
                                    <div class="col-md-12 mt-4">
                                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                            <h6 class="mb-0 fw-bold text-muted"><i class="ti ti-map-pin me-1"></i> Visits (Required)</h6>
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" @click="addVisit(index)">
                                                <i class="ti ti-plus me-1"></i> Add Visit
                                            </button>
                                        </div>
                                        
                                        <div class="bg-light rounded p-3 mb-2" x-show="!caseStudy.visits || caseStudy.visits.length === 0">
                                            <div class="text-center text-muted small">
                                                <i class="ti ti-alert-circle mb-1 fs-5 d-block"></i>
                                                Please add at least one visit for this case study.
                                            </div>
                                        </div>

                                        <template x-for="(visit, vIndex) in caseStudy.visits" :key="vIndex">
                                            <div class="visit-block animate-in mb-3">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="icon-square bg-primary-subtle text-primary">
                                                            <i class="ti ti-map-pin fs-5"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="fw-bold text-dark mb-0">Visit #<span x-text="vIndex + 1"></span></h6>
                                                            <small class="text-muted d-block" style="font-size: 11px;">Step <span x-text="vIndex + 1"></span></small>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-icon btn-light-danger rounded-circle" 
                                                            @click="removeVisit(index, vIndex)" title="Remove Visit">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
                                                
                                                <div class="row g-3">
                                                    <div class="col-md-12">
                                                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Visit Name <span class="text-danger">*</span></label>
                                                        <input type="hidden" :name="'case_studies['+index+'][visits]['+vIndex+'][order_no]'" x-model="visit.order_no">
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-white text-muted border-end-0"><i class="ti ti-h-1"></i></span>
                                                            <input type="text" :name="'case_studies['+index+'][visits]['+vIndex+'][title]'" 
                                                                   x-model="visit.title" class="form-control border-start-0 ps-0" 
                                                                   placeholder="e.g. Initial Consultation" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                          <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Visit Content / Description</label>
                                                          <textarea :name="'case_studies['+index+'][visits]['+vIndex+'][description]'"
                                                                    x-model="visit.description" class="form-control" 
                                                                    rows="3" placeholder="Enter visit specific content here..."></textarea>
                                                      </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div></div>
                        <div>
                            <a href="{{ route($routePrefix . '.case-studies-bank.index') }}" class="btn btn-secondary me-2">
                                <i class="ti ti-x me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Save All Case Studies
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>

<style>
    /* Premium Visit Block Styling */
    .visit-block {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        position: relative;
        overflow: hidden;
    }
    .visit-block:hover {
        border-color: #cbd5e1;
        box-shadow: 0 8px 20px rgba(0,0,0,0.04);
        transform: translateY(-1px);
    }
    .visit-block input:focus, .visit-block textarea:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .icon-square {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    .form-control, .input-group-text {
        font-size: 0.9rem;
    }
    
    .animate-in {
        animation: slideUpFade 0.3s ease-out forwards;
    }
    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    function caseStudyForm() {
        // Store editors in non-reactive local variables
        let editors = {};
        let existingEditors = {};

        return {
            selectedExamId: '{{ request("exam_id") ?? "" }}',
            selectedSectionId: '{{ request("section_id") ?? "" }}',
            sections: [],
            compliance: null,
            existingCaseStudies: @json($existingCaseStudies ?? []),
            deletedCaseStudyIds: [],
            caseStudies: [],
            
            init() {
                if(this.selectedExamId) {
                    this.fetchSections();
                }
            },

            addCaseStudy() {
                // Validation: Previous case study must have at least one visit
                if (this.caseStudies.length > 0) {
                    const lastCS = this.caseStudies[this.caseStudies.length - 1];
                    // Skip validation if title is empty (meaning user hasn't really started filling it)
                    // But user specifically asked for "must add visit"
                    if (lastCS.title && (!lastCS.visits || lastCS.visits.length === 0)) {
                        window.showAlert.error('Please add at least one visit to the current case study before adding another.');
                        return;
                    }
                     // Strict check: even if title is empty, if it's in the list, it counts.
                     if (!lastCS.visits || lastCS.visits.length === 0) {
                        window.showAlert.error('Please add at least one visit to the current case study before adding another.');
                        return;
                    }
                }

                this.caseStudies.push({ 
                    title: '', 
                    order_no: 1, // Will be updated by recalculateOrders
                    content: '',
                    visits: []
                });
                
                this.recalculateOrders();

                this.$nextTick(() => {
                    // Item added, recalculate orders
                });
            },

            addVisit(index) {
                if (!this.caseStudies[index].visits) {
                    this.caseStudies[index].visits = [];
                }
                this.caseStudies[index].visits.push({
                    title: '',
                    order_no: this.caseStudies[index].visits.length + 1,
                    description: ''
                });
            },

            removeVisit(csIndex, vIndex) {
                // Since these are new, unsaved visits, we don't need to soft-delete from DB.
                // But we can still ask for confirmation to prevent accidental loss of data.
                if (this.caseStudies[csIndex].visits[vIndex].title || this.caseStudies[csIndex].visits[vIndex].description) {
                     window.showAlert.confirm('Are you sure you want to remove this visit?', 'Remove Visit?', () => {
                        this.caseStudies[csIndex].visits.splice(vIndex, 1);
                     });
                } else {
                    this.caseStudies[csIndex].visits.splice(vIndex, 1);
                }
            },

            // Add a new (unsaved) visit to an existing case study
            addVisitToExisting(csIndex) {
                const study = this.existingCaseStudies[csIndex];
                if (!study.newVisits) study.newVisits = [];
                const nextOrder = (study.visits ? study.visits.length : 0) + study.newVisits.length + 1;
                study.newVisits.push({ title: '', order_no: nextOrder, description: '' });
            },

            // Remove an existing (DB) visit from an existing case study
            removeExistingVisit(csIndex, vIdx, visitId) {
                window.showAlert.confirm('Are you sure you want to remove this visit?', 'Remove Visit?', () => {
                    const study = this.existingCaseStudies[csIndex];
                    if (!study.deletedVisitIds) study.deletedVisitIds = [];
                    study.deletedVisitIds.push(visitId);
                    study.visits.splice(vIdx, 1);
                });
            },

            // Remove a new (unsaved) visit from an existing case study
            removeNewVisitFromExisting(csIndex, nvIdx) {
                const study = this.existingCaseStudies[csIndex];
                if (study.newVisits[nvIdx].title || study.newVisits[nvIdx].description) {
                    window.showAlert.confirm('Are you sure you want to remove this unsaved visit?', 'Remove Visit?', () => {
                        study.newVisits.splice(nvIdx, 1);
                    });
                } else {
                    study.newVisits.splice(nvIdx, 1);
                }
            },

            removeCaseStudy(index) {
                this.caseStudies.splice(index, 1);
                this.recalculateOrders();
            },

            recalculateOrders() {
                let maxOrder = 0;
                
                // Find max order from existing
                this.existingCaseStudies.forEach(cs => {
                    let order = parseInt(cs.order_no) || 0;
                    if(order > maxOrder) maxOrder = order;
                });

                // Update new case studies
                this.caseStudies.forEach((cs, index) => {
                    cs.order_no = maxOrder + index + 1;
                });
            },

            async removeExistingCaseStudy(index, id) {
                console.log('Attempting to delete case study ID:', id);
                window.showAlert.confirm('Are you sure you want to delete this case study? This action cannot be undone.', 'Delete Case Study?', async () => {
                    try {
                        const response = await fetch(`/{{ $routePrefix }}/case-studies-bank/${id}`, {
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
                            throw new Error('Server returned invalid response (possibly HTML error). check console.');
                        }

                        if (result.success) {
                            // Remove from view
                            this.existingCaseStudies.splice(index, 1);
                            
                            // Make sure we recalculate orders for the new items since the "base" (existing) might have changed
                            this.recalculateOrders();

                            window.showAlert.toast('Case study deleted successfully');
                        } else {
                            window.showAlert.error(result.message || 'Error deleting case study');
                        }
                    } catch (error) {
                        console.error('Deletion Error:', error);
                        window.showAlert.error('Deletion failed: ' + error.message);
                    }
                });
            },


            async fetchSections() {
                if(!this.selectedExamId) {
                    this.sections = [];
                    return;
                }
                
                try {
                    const response = await fetch(`/{{ $routePrefix }}/questions-ajax/case-studies/${this.selectedExamId}`);
                    const data = await response.json();
                    
                    if(Array.isArray(data)) {
                        this.sections = data;
                        this.compliance = null;
                    } else {
                        this.sections = data.sections || [];
                        this.compliance = data.compliance;
                    }
                    
                    if(this.selectedSectionId) {
                         this.$nextTick(() => {
                             const el = document.getElementById('sectionSelect');
                             if(el) el.value = this.selectedSectionId;
                             this.fetchExistingCaseStudies(); 
                         });
                    }
                } catch(e) {
                    console.error('Error fetching sections', e);
                }
            },

            async fetchExistingCaseStudies() {
                if(!this.selectedSectionId) {
                    this.existingCaseStudies = [];
                    return;
                }

                try {
                    const response = await fetch(`/{{ $routePrefix }}/questions-ajax/sub-case-studies/${this.selectedSectionId}`);
                    const caseStudies = await response.json();
                    
                    this.existingCaseStudies = caseStudies.map(cs => ({
                         ...cs,
                         isOpen: false, // Default closed
                         visits: cs.visits.map(v => ({...v, isOpen: false, isQuestionsOpen: false})) // Visits also default closed
                    }));
                    
                    // Recalculate orders for new items
                    this.recalculateOrders();

                } catch(e) {
                    console.error('Error fetching existing case studies', e);
                }
            }
        }
    }
</script>
@endsection
