@extends('layouts.auth')

@section('content')
<div class="card my-5">
    <div class="card-body">
        <h3 class="mb-4"><b>Reset Password</b></h3>
            @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
            @endif
        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="form-group mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $request->email) }}" required autofocus>
                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="form-group mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" required autocomplete="new-password">
                @error('password') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </div>
        </form>
    </div>
</div>
@endsection