@extends('layouts.auth')

@section('content')
<div class="card my-5">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h3 class="mb-0"><b>Register</b></h3>
            <a href="{{ route('login') }}" class="link-primary">Already have an account?</a>
        </div>
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group mb-3">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control" placeholder="Enter First Name"
                    value="{{ old('first_name') }}" required autofocus>
                @error('first_name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control" placeholder="Enter Last Name"
                    value="{{ old('last_name') }}" required>
                @error('last_name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" placeholder="Enter Phone Number"
                    value="{{ old('phone') }}">
                @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-control" required>
                    <option value="student" selected>Student</option>
                    <option value="admin">Admin</option>
                </select>
                @error('role') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="Email Address"
                    value="{{ old('email') }}" required>
                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                @error('password') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control"
                    placeholder="Confirm Password" required>
                @error('password_confirmation') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary">Sign Up</button>
            </div>
            
            <div class="saprator mt-3">
                <span>Or</span>
            </div>

            <div class="text-center mt-3">
                <p class="mb-0">By signing up, you agree to our <a href="#" class="link-primary">Terms of Service</a>.</p>
            </div>

        </form>
    </div>
</div>
@endsection
