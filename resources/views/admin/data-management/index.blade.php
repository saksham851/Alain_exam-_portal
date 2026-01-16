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
                <div class="row">
                    <!-- Import Section -->
                    <div class="col-md-6">
                        <h6 class="mb-3">Import Data</h6>
                        <form action="{{ route('admin.data.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Import Type</label>
                                <select name="import_type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="students">Students</option>
                                    <option value="exams">Exams</option>
                                    <option value="questions">Questions</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Select File</label>
                                <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.xls" required>
                                <small class="text-muted">Supported formats: CSV, Excel</small>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-upload me-2"></i>Import Data
                            </button>
                        </form>
                    </div>

                    <!-- Export Section -->
                    <div class="col-md-6">
                        <h6 class="mb-3">Export Data</h6>
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.sections.index') }}?export=csv" class="btn btn-outline-primary">
                                <i class="ti ti-download me-2"></i>Export Sections
                            </a>
                            <a href="{{ route('admin.case-studies-bank.index') }}?export=csv" class="btn btn-outline-primary">
                                <i class="ti ti-download me-2"></i>Export Case Studies
                            </a>
                            <a href="{{ route('admin.questions.index') }}?export=csv" class="btn btn-outline-primary">
                                <i class="ti ti-download me-2"></i>Export Questions
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
