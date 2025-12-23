<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body>
    <div class="auth-wrapper" style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
        <div class="card" style="max-width: 400px; width: 100%;">
            <div class="card-body text-center">
                <h4 class="mb-3">Logout</h4>
                <p class="mb-4">Are you sure you want to logout?</p>
                
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-power me-1"></i> Yes, Logout
                    </button>
                </form>
                
                <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('student.dashboard') }}" class="btn btn-secondary ms-2">
                    Cancel
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-submit after 2 seconds
        setTimeout(function() {
            document.querySelector('form').submit();
        }, 2000);
    </script>
</body>
</html>
