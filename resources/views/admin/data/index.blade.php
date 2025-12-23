@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Comprehensive Data Management</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item" aria-current="page">Data Management</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row">
    <!-- Export Section -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="ti ti-download me-2"></i>Export Complete Data</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Download all your exam data in a single CSV file with complete hierarchy:
                </p>
                <ul class="mb-3">
                    <li>Exam → Case Study → Sub Case Study → Question</li>
                    <li>All question options and correct answers</li>
                    <li>Easy to understand format</li>
                </ul>
                <a href="{{ route('admin.data.export-complete') }}" class="btn btn-success">
                    <i class="ti ti-download me-1"></i> Export Complete Data
                </a>
            </div>
        </div>
    </div>

    <!-- Import Section -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="ti ti-upload me-2"></i>Import Complete Data</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Upload a CSV file to create exams, case studies, and questions in bulk:
                </p>
                
                <div class="alert alert-info">
                    <strong><i class="ti ti-info-circle me-1"></i> Not sure about format?</strong><br>
                    <a href="{{ route('admin.data.download-sample') }}" class="text-primary">
                        <i class="ti ti-file-download"></i> Download Sample File
                    </a> to see the correct format with examples.
                </div>

                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="ti ti-upload me-1"></i> Import Data
                </button>
            </div>
        </div>
    </div>
</div>

<!-- CSV Format Documentation -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="ti ti-file-text me-2"></i>CSV Format Guide</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Column</th>
                                <th>Description</th>
                                <th>Example</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Exam Name</strong></td>
                                <td>Name of the exam</td>
                                <td>Laravel Fundamentals</td>
                            </tr>
                            <tr>
                                <td><strong>Exam Duration (mins)</strong></td>
                                <td>Duration in minutes</td>
                                <td>60</td>
                            </tr>
                            <tr>
                                <td><strong>Case Study Title</strong></td>
                                <td>Title of the case study</td>
                                <td>Introduction to Laravel</td>
                            </tr>
                            <tr>
                                <td><strong>Case Study Order</strong></td>
                                <td>Display order number</td>
                                <td>1</td>
                            </tr>
                            <tr>
                                <td><strong>Sub Case Study Title</strong></td>
                                <td>Title of the sub case study</td>
                                <td>Basic Concepts</td>
                            </tr>
                            <tr>
                                <td><strong>Sub Case Study Order</strong></td>
                                <td>Display order number</td>
                                <td>1</td>
                            </tr>
                            <tr>
                                <td><strong>Question Text</strong></td>
                                <td>The question</td>
                                <td>What is Laravel?</td>
                            </tr>
                            <tr>
                                <td><strong>Question Type</strong></td>
                                <td>"Single Choice" or "Multiple Choice"</td>
                                <td>Single Choice</td>
                            </tr>
                            <tr>
                                <td><strong>Internal Governance (IG)?</strong></td>
                                <td>Is this an IG question? (Yes/No)</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td><strong>Decision Making (DM)?</strong></td>
                                <td>Is this a DM question? (Yes/No)</td>
                                <td>No</td>
                            </tr>
                            <tr>
                                <td><strong>Option A, B, C, D</strong></td>
                                <td>Answer options (4 options required)</td>
                                <td>A PHP framework</td>
                            </tr>
                            <tr>
                                <td><strong>Correct Answer(s)</strong></td>
                                <td>Correct option keys. For multiple correct: A,B,C</td>
                                <td>A or A,B,C</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.data.import-complete') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Complete Exam Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong><i class="ti ti-alert-triangle me-1"></i> Important:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Make sure your CSV follows the exact format</li>
                            <li>Download the sample file if you're unsure</li>
                            <li>This will create new exams, case studies, and questions</li>
                            <li>Duplicate exams will be reused (not created again)</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <label for="file" class="form-label">Select CSV File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="file" name="file" accept=".csv" required>
                        <small class="text-muted">
                            Need help? <a href="{{ route('admin.data.download-sample') }}">Download sample template</a>
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-upload me-1"></i> Start Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
