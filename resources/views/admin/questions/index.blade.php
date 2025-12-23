@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Question Bank</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item" aria-current="page">Questions</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Questions</h5>
                <a href="{{ route('admin.questions.create') }}" class="btn btn-primary btn-sm">
                    <i class="ti ti-plus me-1"></i> Add Question
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 35%;">Question</th>
                                <th>Case Study</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Options</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($questions as $question)
                            <tr>
                                <td>
                                    <div class="fw-bold mb-1">{{ Str::limit(strip_tags($question->question_text), 80) }}</div>
                                    <small class="text-muted">
                                        <i class="ti ti-file-text"></i> {{ $question->caseStudy->section->title ?? 'N/A' }}
                                    </small>
                                </td>
                                <td>
                                    <div class="small">
                                        <strong>{{ $question->caseStudy->title ?? 'N/A' }}</strong><br>
                                        <span class="text-muted">{{ $question->caseStudy->section->exam->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($question->question_type == 'single')
                                        <span class="badge bg-light-primary">Single Choice</span>
                                    @else
                                        <span class="badge bg-light-success">Multiple Choice</span>
                                    @endif
                                </td>
                                <td>
                                    @if($question->ig_weight > 0)
                                        <span class="badge bg-info">IG - Internal Governance</span>
                                    @elseif($question->dm_weight > 0)
                                        <span class="badge bg-warning">DM - Decision Making</span>
                                    @else
                                        <span class="text-muted">Not Set</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light-info">{{ $question->options->count() }} Options</span>
                                    <span class="badge bg-light-success">{{ $question->options->where('is_correct', 1)->count() }} Correct</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.questions.edit', $question->id) }}" class="btn btn-icon btn-link-primary btn-sm" title="Edit Question">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.questions.destroy', $question->id) }}" method="POST" class="d-inline-block" id="deleteForm{{ $question->id }}">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-icon btn-link-danger btn-sm" title="Delete Question" onclick="showDeleteModal(document.getElementById('deleteForm{{ $question->id }}'), 'Are you sure you want to delete this question?')">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No questions found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($questions->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $questions->firstItem() }} to {{ $questions->lastItem() }} of {{ $questions->total() }} entries
                        </div>
                        <div>
                            {{ $questions->links() }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.questions.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Questions from CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>CSV Format:</strong> Question Text, Type (single/multiple), IG Weight, DM Weight<br>
                        <small>Example: "What is Laravel?","single","1","0"</small>
                    </div>
                    <div class="mb-3">
                        <label for="sub_case_id" class="form-label">Select Case Study</label>
                        <select class="form-select" name="sub_case_id" id="sub_case_id" required>
                            <option value="">Choose Case Study...</option>
                            @foreach(\App\Models\CaseStudy::where('status', 1)->with('section.exam')->get() as $caseStudy)
                                <option value="{{ $caseStudy->id }}">
                                    {{ $caseStudy->title }} ({{ $caseStudy->section->exam->name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label">Select CSV File</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".csv" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-upload me-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
