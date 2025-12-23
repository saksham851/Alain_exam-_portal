@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Sections</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item" aria-current="page">Sections</li>
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
                <h5 class="mb-0">All Sections</h5>
                <a href="{{ route('admin.case-studies.create') }}" class="btn btn-primary btn-sm">
                    <i class="ti ti-plus me-1"></i> Add New Section
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Exam</th>
                                <th>Case Studies</th>
                                <th>Questions</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($caseStudies as $cs)
                            <tr>
                                <td>
                                    <h6 class="mb-0">{{ $cs->title }}</h6>
                                    <small class="text-muted">{{ Str::limit(strip_tags($cs->description), 50) }}</small>
                                </td>
                                <td>{{ $cs->exam->name ?? 'N/A' }}</td>
                                <td><span class="badge bg-light-primary text-primary">{{ $cs->caseStudies->count() }}</span></td>
                                <td><span class="badge bg-light-info text-info">{{ $cs->caseStudies->sum(function($sc) { return $sc->questions->count(); }) }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('admin.case-studies.edit', $cs->id) }}" class="btn btn-icon btn-link-primary btn-sm"><i class="ti ti-edit"></i></a>
                                    <form action="{{ route('admin.case-studies.destroy', $cs->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure?');">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-icon btn-link-danger btn-sm" onclick="this.closest('form').submit()"><i class="ti ti-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No sections found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($caseStudies->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $caseStudies->firstItem() }} to {{ $caseStudies->lastItem() }} of {{ $caseStudies->total() }} entries
                        </div>
                        <div>
                            {{ $caseStudies->links() }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
