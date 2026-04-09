@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('superadmin.admins.index') }}">Dashboard</a></li>
          <li class="breadcrumb-item" aria-current="page">Manage Staff</li>
        </ul>
      </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row mt-2">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white py-3 px-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0 fw-bold">Admin & Manager Management</h5>
                        <span class="text-muted small fw-normal">{{ $admins->total() }} total staff accounts</span>
                    </div>
                    <button class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#inviteAdminModal">
                        <i class="ti ti-user-plus"></i>
                        <span>Invite Staff</span>
                    </button>
                </div>
            </div>

            <!-- Compact Filters Section (Matches Student Management) -->
            <div class="card-body bg-light-subtle py-3 px-4 border-bottom">
                <form method="GET" action="{{ auth()->user()->role === 'admin' ? route('admin.admins.index') : route('superadmin.admins.index') }}" id="staffFilterForm">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small mb-1 text-uppercase" style="letter-spacing: 0.5px;">Search Staff</label>
                            <div class="input-group input-group-sm mb-0">
                                <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                       placeholder="Name or email..." value="{{ request('search') }}" id="staffSearchInput">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="card-body p-0">
                @if($admins->isEmpty())
                    <div class="text-center py-5">
                        <div style="font-size: 64px; margin-bottom: 16px;">👤</div>
                        <h5 class="text-muted">No staff added yet</h5>
                        <p class="text-muted small">Click "Invite Staff" to add your first administrator or manager.</p>
                        <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#inviteAdminModal">
                            <i class="ti ti-user-plus me-1"></i> Invite First Staff
                        </button>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: #f8fafc;">
                                <tr>
                                    <th class="border-0 text-muted small fw-bold px-4">STAFF</th>
                                    <th class="border-0 text-muted small fw-bold">EMAIL ADDRESS</th>
                                    <th class="border-0 text-muted small fw-bold">ROLE</th>
                                    <th class="border-0 text-muted small fw-bold">STATUS</th>
                                    <th class="border-0 text-muted small fw-bold">JOINED</th>
                                    <th class="border-0 text-end text-muted small fw-bold px-4">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($admins as $admin)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avtar avtar-s bg-light-primary text-primary rounded-circle me-2">
                                                <i class="ti ti-user fs-5"></i>
                                            </div>
                                            <h6 class="mb-0 fw-bold">{{ $admin->first_name }} {{ $admin->last_name }}</h6>
                                        </div>
                                    </td>
                                    <td class="py-3"><span class="text-muted small">{{ $admin->email }}</span></td>
                                    <td class="py-3">
                                        @if($admin->role === 'admin')
                                            <span class="badge bg-light-primary text-primary border-0">Admin</span>
                                        @else
                                            <span class="badge bg-light-info text-info border-0">Manager</span>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        @if($admin->status)
                                            <span class="badge bg-light-success text-success border-0"><i class="ti ti-check me-1"></i>Active</span>
                                        @else
                                            <span class="badge bg-light-danger text-danger border-0"><i class="ti ti-x me-1"></i>Inactive</span>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        <span class="text-muted small">{{ $admin->created_at ? $admin->created_at->format('M d, Y') : 'N/A' }}</span>
                                    </td>
                                    <td class="text-end px-4 py-3">
                                        <div class="dropdown">
                                            <button class="btn p-0 text-secondary bg-transparent border-0 shadow-none" type="button" 
                                                    data-bs-toggle="dropdown" 
                                                    data-bs-boundary="viewport" 
                                                    data-bs-popper-config='{"strategy":"fixed"}'
                                                    aria-expanded="false">
                                                <i class="ti ti-dots-vertical f-18"></i>
                                            </button>
                                       <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <form action="{{ auth()->user()->role === 'admin' ? route('admin.admins.resend', $admin->id) : route('superadmin.admins.resend', $admin->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item no-debounce text-primary">
                                                            <i class="ti ti-mail-forward me-2 text-primary"></i>Resend Invite
                                                        </button>
                                                    </form>
                                                </li>
                                                @if($admin->status)
                                                <li>
                                                    <form action="{{ auth()->user()->role === 'admin' ? route('admin.admins.deactivate', $admin->id) : route('superadmin.admins.deactivate', $admin->id) }}" method="POST" id="deactivate-form-{{ $admin->id }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="button" class="dropdown-item no-debounce text-warning"
                                                            onclick="showAlert.confirm('Are you sure you want to deactivate this staff account?', 'Deactivate Account', function() { document.getElementById('deactivate-form-{{ $admin->id }}').submit(); })">
                                                            <i class="ti ti-user-off me-2 text-warning"></i>Deactivate
                                                        </button>
                                                    </form>
                                                </li>
                                                @else
                                                <li>
                                                    <form action="{{ auth()->user()->role === 'admin' ? route('admin.admins.activate', $admin->id) : route('superadmin.admins.activate', $admin->id) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="dropdown-item no-debounce text-success">
                                                            <i class="ti ti-user-check me-2 text-success"></i>Activate
                                                        </button>
                                                    </form>
                                                </li>
                                                @endif
                                                <li>
                                                    <form action="{{ auth()->user()->role === 'admin' ? route('admin.admins.destroy', $admin->id) : route('superadmin.admins.destroy', $admin->id) }}" method="POST"
                                                        id="delete-form-{{ $admin->id }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="dropdown-item no-debounce text-danger" 
                                                            onclick="showDeleteModal(document.getElementById('delete-form-{{ $admin->id }}'), 'Are you sure you want to permanently delete this staff member?')">
                                                            <i class="ti ti-trash me-2 text-danger"></i>Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            @if(method_exists($admins, 'links'))
                <x-custom-pagination :paginator="$admins" />
            @endif
        </div>
    </div>
</div>

{{-- Invite Admin Modal --}}
<div class="modal fade" id="inviteAdminModal" tabindex="-1" aria-labelledby="inviteAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 rounded">
            <div class="modal-header border-0" style="background-color: #01284E;">
                <h6 class="modal-title mb-0 text-white" id="inviteAdminModalLabel">
                    <i class="ti ti-user-plus me-1 text-white"></i> Invite Staff
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ auth()->user()->role === 'admin' ? route('admin.admins.invite') : route('superadmin.admins.invite') }}" method="POST" id="inviteAdminForm">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="first_name" class="form-label small fw-semibold mb-1">First Name <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control form-control-sm @error('first_name') is-invalid @enderror"
                            id="first_name"
                            name="first_name"
                            placeholder="First Name"
                            value="{{ old('first_name') }}"
                            required
                        >
                        @error('first_name')
                            <div class="invalid-feedback"><i class="ti ti-alert-circle me-1"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="admin_email" class="form-label small fw-semibold mb-1">Email Address <span class="text-danger">*</span></label>
                        <input
                            type="email"
                            class="form-control form-control-sm @error('email') is-invalid @enderror"
                            id="admin_email"
                            name="email"
                            placeholder="staff@example.com"
                            value="{{ old('email') }}"
                            required
                        >
                        @error('email')
                            <div class="invalid-feedback"><i class="ti ti-alert-circle me-1"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    @if(auth()->user()->role === 'admin')
                        <input type="hidden" name="role" value="manager">
                    @else
                    <div class="mb-3">
                        <label for="admin_role" class="form-label small fw-semibold mb-1">Role <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" id="admin_role" name="role" required>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin (Full Access)</option>
                            <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager (Limited Access)</option>
                        </select>
                        <small class="text-muted mt-1 d-block">Select the level of access for this staff member.</small>
                    </div>
                    @endif

                    <div class="alert alert-primary py-2 mb-0 d-flex align-items-center">
                        <i class="ti ti-info-circle me-2 fs-4"></i>
                        <small>An invitation email will be sent with a link for the user to securely set their password.</small>
                    </div>

                </form>
            </div>
            <div class="modal-footer p-2">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="inviteAdminForm" class="btn btn-sm btn-primary">
                    <i class="ti ti-send me-1"></i> Send Invitation
                </button>
            </div>
        </div>
    </div>
</div>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('inviteAdminModal'));
        modal.show();
    });
</script>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('staffFilterForm');
    const searchInput = document.getElementById('staffSearchInput');
    
    let searchTimeout;
    
    // Auto-submit on search input (debounced - 1000ms delay)
    if (searchInput) {
        // Keep focus and cursor at end when typing
        if (searchInput.value) {
            searchInput.focus();
            const val = searchInput.value;
            searchInput.value = '';
            searchInput.value = val;
        }

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                filterForm.submit();
            }, 1000);
        });
    }
});
</script>
@endpush

@endsection
