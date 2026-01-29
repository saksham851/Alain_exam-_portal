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
    
    <form method="POST" action="{{ route('login.post') }}" id="loginForm">
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
        
        <div class="d-flex mt-1 justify-content-end">
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

        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        const passwordRegex = /^(?=.*[A-Z])(?=.*[\W_]).{6,}$/;

        const validateEmail = () => {
            if (emailInput.value && !emailRegex.test(emailInput.value)) {
                emailError.innerText = 'Please enter a valid email address.';
                emailError.style.display = 'block';
                return false;
            }
            return true;
        };

        if(loginForm) {
            // Real-time validation on blur
            emailInput.addEventListener('blur', validateEmail);

            // Clear errors on input
            emailInput.addEventListener('input', () => {
                emailError.style.display = 'none';
            });
            passwordInput.addEventListener('input', () => {
                passwordError.style.display = 'none';
            });

            loginForm.addEventListener('submit', function(e) {
                let isValid = true;

                // Reset errors
                emailError.style.display = 'none';
                passwordError.style.display = 'none';

                // Email Validation
                if (!emailInput.value) {
                     // Browser handles required, but safety check
                } else if (!validateEmail()) {
                    isValid = false;
                }

                // Password Validation
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
        }
    });
</script>
@endsection