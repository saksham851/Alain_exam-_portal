@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Student Management</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item" aria-current="page">Users</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>All Students</h5>
            </div>
            
            <!-- Advanced Filters -->
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('admin.users.index') }}" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Exam Name</label>
                            <select name="exam_id" class="form-select">
                                <option value="">All Exams</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                                        {{ $exam->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Exam Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">No. of Attempts</label>
                            <input type="number" name="attempts" class="form-control" 
                                   placeholder="Attempts" min="0" value="{{ request('attempts') }}">
                        </div>
                        
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-25 me-2">
                                <i class="ti ti-filter me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary" title="Clear Filters">
                                <i class="ti ti-x"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Active Filters Indicator -->
            @if(request()->hasAny(['exam_id', 'category_id', 'attempts']))
            <div class="card-body border-bottom bg-light py-2">
                <div class="d-flex align-items-center">
                    <small class="text-muted me-2"><i class="ti ti-filter-check"></i> Active Filters:</small>
                    @if(request('exam_id'))
                        <span class="badge bg-primary me-1">
                            Exam: {{ $exams->firstWhere('id', request('exam_id'))->name ?? 'Unknown' }}
                        </span>
                    @endif
                    @if(request('category_id'))
                        <span class="badge bg-info me-1">
                            Category: {{ $categories->firstWhere('id', request('category_id'))->name ?? 'Unknown' }}
                        </span>
                    @endif
                    @if(request('attempts'))
                        <span class="badge bg-success me-1">Attempts: {{ request('attempts') }}</span>
                    @endif
                </div>
            </div>
            @endif
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Attempts</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avtar avtar-s bg-light-primary text-primary">
                                            {{ strtoupper(substr($user->first_name, 0, 1)) }}
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-0">{{ $user->first_name }} {{ $user->last_name }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('admin.attempts.by-user', $user->id) }}" class="badge bg-light-info text-info">
                                        {{ $user->studentExams->sum(fn($se) => $se->attempts->count()) }} attempts
                                    </a>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.attempts.by-user', $user->id) }}" class="btn btn-icon btn-link-info btn-sm" title="View Attempts">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this student?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-link-danger btn-sm" title="Delete Student">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                     {{-- Mock Links for now since it is array pagination in mock --}}
                     @if(method_exists($users, 'links'))
                        {{ $users->links() }}
                     @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
