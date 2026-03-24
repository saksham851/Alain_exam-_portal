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

                <div class="row justify-content-center">
                    <!-- Import Questions Section -->
                    <div class="col-md-8 mb-4">
                        <div class="card border mb-0">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="ti ti-file-import me-2"></i>Import Questions</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small text-center mb-4">Import questions into unpublished exams with specific sections and case studies.</p>
                                
                                <form action="{{ route('admin.data.import-questions') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Select CSV File</label>
                                        <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                                        <small class="text-muted">Only CSV (.csv or .txt) format supported</small>
                                    </div>
                                    
                                    <div class="d-grid gap-2 mb-4">
                                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                            <i class="ti ti-upload me-2"></i>Import Questions Now
                                        </button>
                                        <a href="{{ route('admin.data.download-question-sample') }}" class="btn btn-outline-info">
                                            <i class="ti ti-download me-2"></i>Download Master CSV Template
                                        </a>
                                    </div>
                                </form>

                                <div class="bg-light p-3 rounded">
                                    <h6 class="mb-2"><i class="ti ti-info-circle me-1"></i>Import Requirements:</h6>
                                    <small class="text-muted d-block mb-1">• Exam must be in <strong>Unpublished</strong> status</small>
                                    <small class="text-muted d-block mb-1">• Section Name must exactly match the exam structure</small>
                                    <small class="text-muted d-block mb-1">• CSV must include <strong>Visit Content</strong> and <strong>Max Point</strong> columns (total 16 columns)</small>
                                    <small class="text-muted d-block mb-0">• Correct Option should be letters (A-D) or comma-separated (e.g., A,C)</small>
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
