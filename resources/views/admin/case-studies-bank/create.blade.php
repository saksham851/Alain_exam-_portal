@extends('layouts.app')

@section('content')
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

        <form action="{{ route('admin.case-studies-bank.store') }}" method="POST" id="caseStudyForm">
            @csrf
            
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
                                <div class="card border mb-3" style="background-color: #fcfcfc;">
                                    <div class="card-body position-relative">
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2" 
                                                @click="removeExistingCaseStudy(index, study.id)"
                                                title="Delete this case study">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                        <div class="row">
                                            <div class="col-md-8 mb-3">
                                                <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                                                <input type="text" :name="'existing_case_studies['+study.id+'][title]'" class="form-control" 
                                                       x-model="study.title" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-bold">Order No <span class="text-danger">*</span></label>
                                                <input type="number" :name="'existing_case_studies['+study.id+'][order_no]'" class="form-control" 
                                                       x-model="study.order_no" min="1" required>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label fw-bold">Content / Scenario</label>
                                                <textarea :id="'existing_editor_'+study.id" :name="'existing_case_studies['+study.id+'][content]'" 
                                                          class="form-control" rows="4" x-model="study.content"></textarea>
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
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2" 
                                        @click="removeCaseStudy(index)">
                                    <i class="ti ti-x"></i>
                                </button>
                                
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                                        <input type="text" :name="'case_studies['+index+'][title]'" class="form-control" 
                                               x-model="caseStudy.title" placeholder="Enter case study title" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Order No <span class="text-danger">*</span></label>
                                        <input type="number" :name="'case_studies['+index+'][order_no]'" class="form-control" 
                                               x-model="caseStudy.order_no" min="1" required>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Content / Scenario</label>
                                        <textarea :id="'editor_'+index" :name="'case_studies['+index+'][content]'" 
                                                  class="form-control" rows="4" x-model="caseStudy.content"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="card-footer text-end bg-light">
                    <a href="{{ route('admin.case-studies-bank.index') }}" class="btn btn-secondary me-2">
                        <i class="ti ti-x me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> Save All Case Studies
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>

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
            existingCaseStudies: @json($existingCaseStudies ?? []),
            deletedCaseStudyIds: [],
            caseStudies: [
                { title: '', order_no: 1, content: '' }
            ],
            
            init() {
                if(this.selectedExamId) {
                    this.fetchSections();
                }

                // Init existing editors if data is present on load
                if (this.existingCaseStudies.length > 0) {
                    this.$nextTick(() => {
                        this.existingCaseStudies.forEach((study, index) => {
                            this.initExistingEditor(study.id);
                        });
                    });
                }
                
                // Init new case study editor
                this.$nextTick(() => { this.initEditor(0); });
            },

            addCaseStudy() {
                this.caseStudies.push({ 
                    title: '', 
                    order_no: this.caseStudies.length + 1, 
                    content: '' 
                });
                this.$nextTick(() => {
                    this.initEditor(this.caseStudies.length - 1);
                });
            },

            removeCaseStudy(index) {
                const editorKey = index; 

                if(editors[editorKey]) {
                    editors[editorKey].destroy()
                        .then(() => { delete editors[editorKey]; })
                        .catch(e => console.error(e));
                }

                this.caseStudies.splice(index, 1);
                
                this.$nextTick(() => {
                     this.caseStudies.forEach((_, i) => {
                         this.initEditor(i);
                     });
                });
            },

            async removeExistingCaseStudy(index, id) {
                console.log('Attempting to delete case study ID:', id);
                window.showAlert.confirm('Are you sure you want to delete this case study? This action cannot be undone.', 'Delete Case Study?', async () => {
                    try {
                        const response = await fetch(`/admin/case-studies-bank/${id}`, {
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
                            // Destroy editor
                            if(existingEditors[id]) {
                                existingEditors[id].destroy()
                                    .then(() => { delete existingEditors[id]; })
                                    .catch(e => {
                                        console.error(e);
                                        delete existingEditors[id];
                                    });
                            }
                            
                            // Remove from view
                            this.existingCaseStudies.splice(index, 1);
                            
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

            initEditor(index) {
                const elId = 'editor_' + index;
                this.$nextTick(() => {
                    const el = document.getElementById(elId);
                    if(el) {
                        if(editors[index]) return;

                        if(el.nextSibling && el.nextSibling.classList && el.nextSibling.classList.contains('ck-editor')) {
                             el.nextSibling.remove();
                             el.style.display = 'block';
                        }

                        ClassicEditor.create(el)
                            .then(editor => {
                                editors[index] = editor;
                                editor.model.document.on('change:data', () => {
                                    this.caseStudies[index].content = editor.getData();
                                });
                                editor.setData(this.caseStudies[index].content);
                            })
                            .catch(error => { console.error(error); });
                    }
                });
            },

            initExistingEditor(id) {
                const elId = 'existing_editor_' + id;
                this.$nextTick(() => {
                    const el = document.getElementById(elId);
                    if(el) {
                         if(existingEditors[id]) return;

                         if(el.nextSibling && el.nextSibling.classList && el.nextSibling.classList.contains('ck-editor')) {
                             el.nextSibling.remove();
                             el.style.display = 'block';
                        }

                        ClassicEditor.create(el)
                            .then(editor => {
                                existingEditors[id] = editor;
                                editor.model.document.on('change:data', () => {
                                    const study = this.existingCaseStudies.find(s => s.id === id);
                                    if(study) study.content = editor.getData();
                                });
                                const study = this.existingCaseStudies.find(s => s.id === id);
                                if(study) editor.setData(study.content);
                            })
                            .catch(error => { console.error(error); });
                    }
                });
            },

            async fetchSections() {
                if(!this.selectedExamId) {
                    this.sections = [];
                    return;
                }
                
                try {
                    const response = await fetch(`/admin/questions-ajax/case-studies/${this.selectedExamId}`);
                    this.sections = await response.json();
                    
                    if(this.selectedSectionId) {
                         this.$nextTick(() => {
                             document.getElementById('sectionSelect').value = this.selectedSectionId;
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
                    const response = await fetch(`/admin/questions-ajax/sub-case-studies/${this.selectedSectionId}`);
                    const caseStudies = await response.json();
                    
                    this.existingCaseStudies = caseStudies;
                    
                    // Initialize editors for updated list
                    this.$nextTick(() => {
                        this.existingCaseStudies.forEach(study => {
                            this.initExistingEditor(study.id);
                        });
                    });

                    // Calculate max order to auto-set the new form's order
                    let maxOrder = 0;
                    this.existingCaseStudies.forEach(cs => {
                        if(cs.order_no && cs.order_no > maxOrder) maxOrder = cs.order_no;
                    });
                    
                    if(this.caseStudies.length === 1 && this.caseStudies[0].order_no === 1 && maxOrder > 0) {
                        this.caseStudies[0].order_no = maxOrder + 1;
                    }

                } catch(e) {
                    console.error('Error fetching existing case studies', e);
                }
            }
        }
    }
</script>
@endsection
