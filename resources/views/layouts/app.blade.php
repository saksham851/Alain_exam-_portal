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
        
        
    </head>
    <body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr" data-pc-theme_contrast="" data-pc-theme="light">
        <!-- [ Pre-loader ] start -->
        <div class="loader-bg">
            <div class="loader-track">
                <div class="loader-fill"></div>
            </div>
        </div>
        <!-- [ Pre-loader ] End -->

        <!-- [ Sidebar Menu ] start -->
        @include('partials.sidebar')
        <!-- [ Sidebar Menu ] end -->
        
        <!-- [ Header Topbar ] start -->
        @include('partials.header')
        <!-- [ Header ] end -->

        <!-- [ Main Content ] start -->
        <div class="pc-container">
            <div class="pc-content">
                @yield('content')
            </div>
        </div>
        <!-- [ Main Content ] end -->

        <!-- Required Js -->
        <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
        <script src="{{ asset('assets/js/fonts/custom-font.js') }}"></script>
        <script src="{{ asset('assets/js/pcoded.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>

        <script>
            // Delete confirmation modal
            function showDeleteModal(form, message) {
                if (confirm(message || 'Are you sure you want to delete this item?')) {
                    form.submit();
                }
            }

            // Layout-specific initialization
            if(typeof layout_change === 'function') layout_change('light');
            if(typeof layout_rtl_change === 'function') layout_rtl_change('false');
            if(typeof preset_change === 'function') preset_change('preset-1');

            // Feather icons
            feather.replace();
        </script>

        @stack('scripts')
    </body>
</html>
