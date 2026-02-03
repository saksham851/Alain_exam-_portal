@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Data Management</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item" aria-current="page">Data Management</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Import/Export Data</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ti ti-check me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="row">
                    <!-- Import Questions Section -->
                    <div class="col-md-6 mb-4">
                        <div class="card border">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="ti ti-file-import me-2"></i>Import Questions</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">Import questions into unpublished exams with specific sections and case studies.</p>
                                
                                <form action="{{ route('admin.data.import-questions') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Select CSV File</label>
                                        <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                                        <small class="text-muted">Only CSV format supported</small>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ti ti-upload me-2"></i>Import Questions
                                        </button>
                                        <a href="{{ route('admin.data.download-question-sample') }}" class="btn btn-outline-secondary">
                                            <i class="ti ti-download me-2"></i>Download Sample CSV
                                        </a>
                                    </div>
                                </form>

                                <div class="mt-3">
                                    <small class="text-muted">
                                        <strong>CSV Format:</strong><br>
                                        • Exam must be unpublished<br>
                                        • Section must exist in exam<br>
                                        • Case study is optional<br>
                                        • Correct option: 1, 2, 3, or 4
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Import Case Studies Section -->
                    <div class="col-md-6 mb-4">
                        <div class="card border">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="ti ti-file-import me-2"></i>Import Case Studies</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">Import case studies into specific exam sections.</p>
                                
                                <form action="{{ route('admin.data.import-case-studies') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Select CSV File</label>
                                        <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                                        <small class="text-muted">Only CSV format supported</small>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ti ti-upload me-2"></i>Import Case Studies
                                        </button>
                                        <a href="{{ route('admin.data.download-case-study-sample') }}" class="btn btn-outline-secondary">
                                            <i class="ti ti-download me-2"></i>Download Sample CSV
                                        </a>
                                    </div>
                                </form>

                                <div class="mt-3">
                                    <small class="text-muted">
                                        <strong>CSV Format:</strong><br>
                                        • Exam must be unpublished<br>
                                        • Section must exist in exam<br>
                                        • Order number for sorting<br>
                                        • Content can be detailed text
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
