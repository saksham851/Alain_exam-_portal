@extends('layouts.auth')

@section('content')
<div class="card my-5">
  <div class="card-body">
    <div class="mb-4">
      <h3 class="mb-0"><b>Login</b></h3>
    </div>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif
    
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="form-group mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="Email Address" value="{{ old('email') }}" required autofocus>
          @error('email') <small class="text-danger">{{ $message }}</small> @enderror
        </div>
        <div class="form-group mb-3">
          <label class="form-label">Password</label>
          <div class="input-group">
            <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
            <span class="input-group-text bg-white" id="togglePassword" style="cursor: pointer;">
                <i class="ti ti-eye" id="eyeIcon"></i>
            </span>
          </div>
          @error('password') <small class="text-danger">{{ $message }}</small> @enderror
        </div>
        
        <div class="d-flex mt-1 justify-content-between">
          <div class="form-check">
            <input class="form-check-input input-primary" type="checkbox" id="customCheckc1" name="remember">
            <label class="form-check-label text-muted" for="customCheckc1">Keep me sign in</label>
          </div>
          <a href="{{ route('password.request') }}" class="text-secondary f-w-400">Forgot Password?</a>
        </div>
        
        <div class="d-grid mt-4">
          <button type="submit" class="btn btn-primary">Login</button>
        </div>
    </form>
  </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        const icon = document.getElementById('eyeIcon');
        
        if(togglePassword && password && icon) {
            togglePassword.addEventListener('click', function (e) {
                // toggle the type attribute
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                // toggle the eye icon
                if(type === 'password') {
                    icon.classList.remove('ti-eye-off');
                    icon.classList.add('ti-eye');
                } else {
                    icon.classList.remove('ti-eye');
                    icon.classList.add('ti-eye-off');
                }
            });
        }
    });
</script>
@endsection