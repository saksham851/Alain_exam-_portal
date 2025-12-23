<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

        <!-- [Template CSS Files] -->
        <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}" >
        <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}" >
        <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}" >
        <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}" >
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link" >
        <link rel="stylesheet" href="{{ asset('assets/css/style-preset.css') }}" >
        
        <style>
            body {
                background-color: #f8f9fa;
            }
            .exam-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }
            .exam-header {
                background: white;
                padding: 15px 30px;
                border-bottom: 1px solid #e0e0e0;
                display: flex;
                justify-content: space-between;
                align-items: center;
                position: fixed;
                top: 0;
                width: 100%;
                z-index: 1050;
                height: 70px;
            }
            .exam-content {
                margin-top: 80px; /* Space for fixed header */
            }
        </style>
    </head>
    <body data-pc-preset="preset-1" data-pc-theme="light">
        
        <!-- [ Minimal Header ] -->
        <div class="exam-header shadow-sm">
            <div class="d-flex align-items-center">
                
                <h4 class="mb-0 text-primary ms-2"><i class="ti ti-activity-heartbeat me-2"></i>Exam Portal</h4>
            </div>
            <div class="d-flex align-items-center">
                <div class="d-flex align-items-center ms-3">
                    <div class="avtar avtar-s bg-light-primary text-primary rounded-circle me-2">
                        <i class="ti ti-user"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</h6>
                        <small class="text-muted">Candidate</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- [ Main Content ] -->
        <div class="exam-container exam-content">
            @yield('content')
        </div>

        <!-- Required Js -->
        <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
        <script src="{{ asset('assets/js/fonts/custom-font.js') }}"></script>
        <script src="{{ asset('assets/js/pcoded.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>

        <script>
            layout_change('light');
            layout_caption_change('true');
            layout_rtl_change('false');
            preset_change('preset-1');
            feather.replace();
        </script>

        @stack('scripts')
    </body>
</html>
