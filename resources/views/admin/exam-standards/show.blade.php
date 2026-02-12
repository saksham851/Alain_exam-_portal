@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">{{ $standard->name }}</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('admin.exam-standards.index') }}">Exam Standards</a></li>
          <li class="breadcrumb-item active">{{ $standard->name }}</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row">
    <div class="col-md-12">
        <!-- Basic Info -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Standard Information</h5>
                <div>
                    <a href="{{ route('admin.exam-standards.edit', $standard->id) }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-edit me-1"></i> Edit Standard
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> {{ $standard->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Exams Using This Standard:</strong> 
                            <span class="badge bg-info">{{ $standard->exams->count() }}</span>
                        </p>
                    </div>
                    @if($standard->description)
                    <div class="col-md-12">
                        <p><strong>Description:</strong> {{ $standard->description }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Categories -->
        @foreach($standard->categories as $category)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ $category->name }}</h5>
                <small class="text-muted">Score Category {{ $category->category_number }}</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Content Area</th>
                                <th class="text-center">Required Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($category->contentAreas as $area)
                            <tr>
                                <td>{{ $area->name }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light-primary text-primary">{{ $area->max_points }} pts</span>
                                </td>
                            </tr>
                            @endforeach
                            <tr class="table-active">
                                <td><strong>Total Points</strong></td>
                                <td class="text-center">
                                    <strong>{{ $category->contentAreas->sum('max_points') }} pts</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endforeach

        <!-- Exams Using This Standard -->
        @if($standard->exams->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Exams Using This Standard</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Exam Name</th>
                                <th>Category</th>
                                <th>Passing Scores</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($standard->exams as $exam)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.exams.show', $exam->id) }}">{{ $exam->name }}</a>
                                </td>
                                <td>{{ $exam->category->name ?? 'N/A' }}</td>
                                <td>
                                    <small>
                                        Overall: {{ $exam->passing_score_overall }} pts
                                    </small>
                                </td>
                                <td>
                                    @if($exam->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Back Button -->
        <div class="card">
            <div class="card-body">
                <a href="{{ route('admin.exam-standards.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Back to Standards
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
