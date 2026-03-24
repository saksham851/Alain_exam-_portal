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
          <h5 class="m-b-10">Edit Case Study</h5>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row" x-data="editCaseStudyForm()">
    <div class="col-md-12">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route($routePrefix . '.case-studies-bank.update', $caseStudy->id) }}" method="POST" id="caseStudyForm">
            @csrf
            @method('PUT')
            @if(request()->has('return_url'))
                <input type="hidden" name="return_url" value="{{ request('return_url') }}">
            @endif
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Case Study Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Select Exam <span class="text-danger">*</span></label>
                            <select name="exam_id" id="examSelect" class="form-select" x-model="selectedExamId" @change="fetchSections()" required>
                                <option value="">Choose Exam...</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Select Section <span class="text-danger">*</span></label>
                            <select name="section_id" id="sectionSelect" class="form-select" x-model="selectedSectionId" required>
                                <option value="">Select Exam First...</option>
                                <template x-for="section in sections" :key="section.id">
                                    <option :value="section.id" x-text="section.title" :selected="section.id == selectedSectionId"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" 
                                   value="{{ old('title', $caseStudy->title) }}" placeholder="Enter case study title" required>
                            <input type="hidden" name="order_no" value="{{ $caseStudy->order_no }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visits Management -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Visits (Case Study Flow)</h5>
                    <button type="button" class="btn btn-sm btn-primary" @click="addVisit()">
                        <i class="ti ti-plus me-1"></i> Add Visit
                    </button>
                </div>
                <div class="card-body bg-light-subtle">
                    <div class="vstack gap-3">
                        <template x-for="(visit, index) in visits" :key="index">
                            <div class="visit-block animate-in" :id="visit.id ? 'visit-' + visit.id : ''">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="icon-square bg-primary-subtle text-primary">
                                            <i class="ti ti-map-pin fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">Visit #<span x-text="index + 1"></span></h6>
                                            <small class="text-muted d-block" style="font-size: 11px;">
                                                <span x-text="visit.id ? 'ID: ' + visit.id : 'New Visit'"></span>
                                            </small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-icon btn-light-danger rounded-circle" @click="removeVisit(index)" title="Remove Visit">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                                
                                <!-- Hidden Inputs -->
                                <input type="hidden" :name="'visits['+index+'][id]'" :value="visit.id">
                                
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Visit Title <span class="text-danger">*</span></label>
                                        <input type="hidden" :name="'visits['+index+'][order_no]'" x-model="visit.order_no">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted border-end-0"><i class="ti ti-h-1"></i></span>
                                            <input type="text" :name="'visits['+index+'][title]'" class="form-control border-start-0 ps-0" x-model="visit.title" placeholder="e.g. Initial Consultation" required>
                                        </div>
                                    </div>
                                     <div class="col-md-12">
                                         <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Visit Content / Description</label>
                                         <textarea :name="'visits['+index+'][description]'" class="form-control" rows="3" x-model="visit.description" placeholder="Brief description of this visit scenario..."></textarea>
                                     </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    
                    <template x-if="visits.length === 0">
                        <div class="text-center py-5 border border-dashed rounded-3 bg-white">
                            <div class="bg-light-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                                <i class="ti ti-map-pin fs-3 text-primary"></i>
                            </div>
                            <h6 class="fw-bold text-dark">No Visits Added</h6>
                            <p class="text-muted small mb-3">Define the flow of this case study by adding visits.</p>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-4" @click="addVisit()">
                                <i class="ti ti-plus me-1"></i> Add First Visit
                            </button>
                        </div>
                    </template>

                    <!-- Deleted Visits Tracking -->
                    <template x-for="id in deletedVisits" :key="id">
                        <input type="hidden" name="deleted_visits[]" :value="id">
                    </template>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center bg-white border-top py-3">
                    <button type="button" class="btn btn-outline-primary rounded-pill px-3" @click="addVisit()">
                        <i class="ti ti-plus me-1"></i> Add Another Visit
                    </button>
                    <div>
                        <a href="{{ route($routePrefix . '.case-studies-bank.index') }}" class="btn btn-light-secondary rounded-pill px-4 me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="ti ti-device-floppy me-1"></i> Update Case Study
                        </button>
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

    .highlight-visit {
        animation: highlightPulse 2s ease-in-out;
        border-color: #4680ff !important;
        box-shadow: 0 0 0 4px rgba(70, 128, 255, 0.1) !important;
    }
    
    @keyframes highlightPulse {
        0%, 100% { background-color: #fff; }
        50% { background-color: #f0f7ff; }
    }
</style>

<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    function editCaseStudyForm() {
        return {
            selectedExamId: '{{ $caseStudy->section->exam_id }}',
            selectedSectionId: '{{ $caseStudy->section_id }}',
            sections: [],
            visits: @json($caseStudy->visits),
            deletedVisits: [],
            
            init() {
                if(this.selectedExamId) {
                    this.fetchSections();
                }
                
                // Initialize visits if null
                if (!this.visits) this.visits = [];

                
                // Scroll to visit if hash is present
                this.$nextTick(() => {
                    if(window.location.hash) {
                        const element = document.querySelector(window.location.hash);
                        if(element) {
                            setTimeout(() => {
                                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                element.classList.add('highlight-visit');
                                setTimeout(() => element.classList.remove('highlight-visit'), 2000);
                            }, 500);
                        }
                    }
                });
            },

            addVisit() {
                this.visits.push({
                    id: null,
                    title: '',
                    description: '',
                    order_no: this.visits.length + 1,
                });
            },

            removeVisit(index) {
                const visit = this.visits[index];
                
                if (visit.id) {
                    window.showAlert.confirm(
                        'Deleting this visit will also soft-delete all questions associated with it. You can restore them later if needed.', 
                        'Are you sure you want to delete this visit?', 
                        () => {
                            this.deletedVisits.push(visit.id);
                            this.visits.splice(index, 1);
                        }
                    );
                } else {
                    this.visits.splice(index, 1);
                }
            },

            async fetchSections() {
                if(!this.selectedExamId) {
                    this.sections = [];
                    return;
                }
                
                try {
                    const response = await fetch(`/{{ $routePrefix }}/questions-ajax/case-studies/${this.selectedExamId}`);
                    const data = await response.json();
                    
                    if (data.sections) {
                        this.sections = data.sections;
                    } else if (Array.isArray(data)) {
                        this.sections = data;
                    } else {
                        this.sections = [];
                    }

                    // Force UI update for selected value if needs be, though x-model should handle it
                    if (this.selectedSectionId) {
                        this.$nextTick(() => {
                             // This helps in case options were rebuilt and value was lost visually
                             document.getElementById('sectionSelect').value = this.selectedSectionId;
                        });
                    }

                } catch(e) {
                    console.error('Error fetching sections', e);
                }
            }
        }
    }
</script>
@endsection
