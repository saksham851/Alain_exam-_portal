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
    
    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf
        <div class="form-group mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" id="email" placeholder="Email Address" value="{{ old('email') }}" required autofocus>
          @error('email') <small class="text-danger">{{ $message }}</small> @enderror
          <small class="text-danger" id="emailError" style="display:none;"></small>
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
          <small class="text-danger" id="passwordError" style="display:none;"></small>
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
        // Password Toggle Logic
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

        // Form Validation Logic
        const loginForm = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const emailError = document.getElementById('emailError');
        const passwordError = document.getElementById('passwordError');

        if(loginForm) {
            loginForm.addEventListener('submit', function(e) {
                let isValid = true;

                // Reset errors
                emailError.style.display = 'none';
                emailError.innerText = '';
                passwordError.style.display = 'none';
                passwordError.innerText = '';

                // Email Validation
                const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                if (!emailInput.value || !emailRegex.test(emailInput.value)) {
                    emailError.innerText = 'Please enter a valid email address.';
                    emailError.style.display = 'block';
                    isValid = false;
                }

                // Password Validation (1 Uppercase, 1 Special/Unique Char, Min 8 chars)
                // (?=.*[A-Z]) -> At least one Uppercase
                // (?=.*[!@#$&*]) -> At least one special character (adjust list as needed)
                // or just (?=.*[\W_]) for any non-word char
                const passwordRegex = /^(?=.*[A-Z])(?=.*[\W_]).{6,}$/; 
                // Note: Min length 6 is standard, user didn't specify length but usually 6 or 8. Using 6 to be safe but strict on chars.
                
                if (!passwordInput.value) {
                     passwordError.innerText = 'Password is required.';
                     passwordError.style.display = 'block';
                     isValid = false;
                } else if (!passwordRegex.test(passwordInput.value)) {
                    passwordError.innerText = 'Password must contain at least 1 uppercase letter and 1 special character.';
                    passwordError.style.display = 'block';
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });

            // Clear errors on input
            emailInput.addEventListener('input', () => {
                emailError.style.display = 'none';
            });
            passwordInput.addEventListener('input', () => {
                passwordError.style.display = 'none';
            });
        }
    });
</script>
@endsection