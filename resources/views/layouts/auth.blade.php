<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <title>Auth | {{ config('app.name', 'Exam Portal') }}</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  
  <!-- [Favicon] icon -->
  <!-- [Favicon] icon -->
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/logo_image.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/logo_image.png') }}">
  <link rel="shortcut icon" href="{{ asset('assets/images/logo_image.png') }}">
  
  <!-- [Google Font] Family -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" id="main-font-link">
  
  <!-- [Tabler Icons] -->
  <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}" >
  <!-- [Feather Icons] -->
  <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}" >
  <!-- [Font Awesome Icons] -->
  <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}" >
  <!-- [Material Icons] -->
  <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}" >
  
  <!-- [Template CSS Files] -->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link" >
  <link rel="stylesheet" href="{{ asset('assets/css/style-preset.css') }}" >
  <style>
    .auth-main .auth-wrapper.v3 .auth-form {
      background: none !important;
    }
    .auth-main .auth-wrapper.v3 .auth-form:after {
      display: none !important;
    }
  </style>
</head>

<body>
  <!-- [ Pre-loader ] start -->
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>
  <!-- [ Pre-loader ] End -->

  <div class="auth-main">
    <div class="auth-wrapper v3">
      <div class="auth-form">
        <div class="auth-header">
          <a href="#" class="d-flex align-items-center gap-2">
              <img src="{{ asset('assets/images/logo-new.png') }}" alt="img" class="img-fluid logo-lg" style="max-height: 50px;">
          </a>
        </div>
        
        @yield('content')
        
        <div class="auth-footer row">
            <div class="col my-1">
              <p class="m-0">Copyright © {{ date('Y') }} {{ config('app.name') }}</p>
            </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Required Js -->
  <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
  <script src="{{ asset('assets/js/fonts/custom-font.js') }}"></script>
  <script src="{{ asset('assets/js/pcoded.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>

  <script>layout_change('light');</script>
  <script>change_box_container('false');</script>
  <script>layout_rtl_change('false');</script>
  <script>preset_change("preset-1");</script>
  <script>font_change("Public-Sans");</script>
</body>
</html>
