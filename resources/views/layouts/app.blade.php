<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/logo_image.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/logo_image.png') }}">
        <link rel="shortcut icon" href="{{ asset('assets/images/logo_image.png') }}">

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
        
        <!-- Custom Styles for SweetAlert Z-Index Fix -->
        <style>
            /* Ensure SweetAlert appears above Bootstrap modals */
            .swal2-container {
                z-index: 9999 !important;
            }
            
            /* Ensure SweetAlert backdrop appears above modals */
            .swal2-container.swal2-backdrop-show {
                z-index: 9998 !important;
            }
        </style>
        
        <!-- Custom Styles to Prevent Toast Shaking -->
        <style>
            /* Disable icon animations */
            .swal2-icon.swal2-success.swal2-icon-show .swal2-success-line-tip { animation: none !important; display: block !important; }
            .swal2-icon.swal2-success.swal2-icon-show .swal2-success-line-long { animation: none !important; display: block !important; }
            .swal2-icon.swal2-success .swal2-success-ring { animation: none !important; }
            .swal2-icon.swal2-error.swal2-icon-show .swal2-x-mark { animation: none !important; }
            .swal2-icon { animation: none !important; transform: scale(1) !important; }
        </style>
        
        <!-- Custom Styles to Hide Page Headers -->
        <style>
            /* Hide page headers globally to save space */
            .page-header {
                display: none !important;
            }
            
            /* Remove top padding/margin from main content area */
            .pc-container {
                padding-top: 0 !important;
            }
            
            section.pc-container {
                margin-top: 0 !important;
                padding-top: 20px !important;
            }
        </style>
        
        <!-- Hide Default Template Loader -->
        <style>
            .loader-bg {
                display: none !important;
            }
            
            /* Alpine.js x-cloak */
            [x-cloak] {
                display: none !important;
            }
            
            /* Prevent logo spinning/animation */
            .logo-lg, .b-brand img, .m-header img {
                animation: none !important;
                transform: none !important;
            }
        </style>
        
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

        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            // Global SweetAlert2 Helper Functions
            // Global SweetAlert2 Helper Functions - Toast Configuration
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            window.showAlert = {
                success: function(message, title = 'Success!') {
                    Toast.fire({
                        icon: 'success',
                        title: title,
                        text: message
                    });
                },
                error: function(message, title = 'Error!') {
                    Toast.fire({
                        icon: 'error',
                        title: title,
                        text: message
                    });
                },
                warning: function(message, title = 'Warning!') {
                    Toast.fire({
                        icon: 'warning',
                        title: title,
                        text: message
                    });
                },
                info: function(message, title = 'Info') {
                    Toast.fire({
                        icon: 'info',
                        title: title,
                        text: message
                    });
                },
                confirm: function(message, title = 'Are you sure?', callback) {
                    Swal.fire({
                        title: title,
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#4680ff',
                        cancelButtonColor: '#dc2626',
                        confirmButtonText: 'Yes, proceed!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed && callback) {
                            callback();
                        }
                    });
                },
                toast: function(message, type = 'success') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });
                    Toast.fire({
                        icon: type,
                        title: message
                    });
                }
            };

            // Check for Laravel session messages and show alerts
            @if(session('success'))
                showAlert.success(@json(session('success')));
            @endif

            @if(session('error'))
                showAlert.error(@json(session('error')));
            @endif

            @if(session('warning'))
                showAlert.warning(@json(session('warning')));
            @endif

            @if(session('info'))
                showAlert.info(@json(session('info')));
            @endif

            // Delete confirmation modal
            function showDeleteModal(form, message) {
                showAlert.confirm(
                    message || 'This action cannot be undone!',
                    'Are you sure?',
                    function() {
                        form.submit();
                    }
                );
            }

            // Layout-specific initialization
            if(typeof layout_change === 'function') layout_change('light');
            if(typeof layout_rtl_change === 'function') layout_rtl_change('false');
            if(typeof preset_change === 'function') preset_change('preset-1');

            // Feather icons
            feather.replace();

            // Custom handler for all sidebar submenu toggles
            document.addEventListener('DOMContentLoaded', function() {
                // Wait a bit for feather icons to render
                setTimeout(function() {
                    const menuItems = document.querySelectorAll('.pc-navbar > li.pc-hasmenu');
                    
                    menuItems.forEach(function(menuItem) {
                        const menuLink = menuItem.querySelector('a.pc-link');
                        const submenu = menuItem.querySelector('.pc-submenu');
                        
                        if (menuLink && submenu) {
                            menuLink.addEventListener('click', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                
                                // Toggle the menu
                                if (menuItem.classList.contains('pc-trigger')) {
                                    menuItem.classList.remove('pc-trigger');
                                    submenu.style.display = 'none';
                                } else {
                                    menuItem.classList.add('pc-trigger');
                                    submenu.style.display = 'block';
                                }
                            });
                        }
                    });
                }, 100);

                // Global Multi-Click Prevention for Forms
                document.addEventListener('submit', function(e) {
                    const form = e.target;
                    // Find all submit buttons in this form
                    const submitBtns = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                    
                    submitBtns.forEach(btn => {
                        if (!btn.hasAttribute('data-no-disable')) {
                            // Delay slightly to allow the click event to finish if it's a manual click
                            setTimeout(() => {
                                btn.disabled = true;
                                if (!btn.querySelector('.spinner-border')) {
                                    const text = btn.innerText.trim() || 'Processing';
                                    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${text}...`;
                                }
                            }, 50);
                        }
                    });
                });

                // Global Multi-Click Prevention for Generic Buttons (Debounce)
                document.addEventListener('click', function(e) {
                    const btn = e.target.closest('button, a.btn');
                    if (!btn || btn.type === 'submit' || btn.classList.contains('no-debounce')) return;

                    // Skip Alpine.js or HTMX managed buttons as they handle their own state
                    if (btn.hasAttribute('x-on:click') || btn.hasAttribute('@click') || btn.hasAttribute('hx-post')) return;

                    if (btn.getAttribute('data-is-clicked') === 'true') {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }

                    // Mark as clicked
                    btn.setAttribute('data-is-clicked', 'true');
                    
                    // Re-enable after a timeout (2 seconds)
                    setTimeout(() => {
                        btn.removeAttribute('data-is-clicked');
                    }, 2000);
                }, true); // Use capture phase
            });
        </script>

        @stack('scripts')
    </body>
</html>
