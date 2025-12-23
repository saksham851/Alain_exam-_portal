@extends('layouts.auth')

@section('content')
<div class="card my-5">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0"><b>Reset Password</b></h3>
        </div>
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif
        <p class="text-muted mb-4">Enter your email address and we'll send you a link to reset your password.</p>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="form-group mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="Email Address" value="{{ old('email') }}" required autofocus>
                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary">Send Reset Link</button>
            </div>

            <div class="d-flex justify-content-center mt-4">
                <a href="{{ route('login') }}" class="link-primary">Back to Login</a>
            </div>
        </form>
    </div>
</div>
@endsection