<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ar', 'ku']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        const savedTheme = localStorage.getItem('theme') || localStorage.getItem('quran-theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark');
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            document.documentElement.setAttribute('data-theme', 'light');
        }
    </script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', __('common.app_description'))">
    <meta name="keywords" content="@yield('meta_keywords', 'Quran, Koran, Islam, Surah, Ayah, Tafsir, Recitation')">

    <title>@yield('title', __('common.app_name')) - {{ __('common.quran_application') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Arabic Font -->
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Kurdish Font -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Naskh+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- English Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Bootstrap CSS with RTL support -->
    @if(in_array(app()->getLocale(), ['ar', 'ku']))
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    @else
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    @endif

    <!-- Main Application CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- Component CSS -->
    <link rel="stylesheet" href="{{ asset('css/components/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/footer.css') }}">

    <!-- Custom RTL/LTR overrides based on current locale and configured settings -->
    @php
        $settings = \Illuminate\Support\Facades\Cache::remember('app_settings', 3600, function () {
            return \App\Models\Setting::firstOrCreate([]);
        });
        
        $fontAr = $settings->font_ar ?? 'Wafeq-Regular.otf';
        $fontKu = $settings->font_ku ?? '3_NRT-Bd.ttf';
        $fontEn = $settings->font_en ?? 'PatuaOne-Regular.ttf';

        $fontArExt = pathinfo($fontAr, PATHINFO_EXTENSION);
        $fontKuExt = pathinfo($fontKu, PATHINFO_EXTENSION);
        $fontEnExt = pathinfo($fontEn, PATHINFO_EXTENSION);
        
        $formatAr = strtolower($fontArExt) === 'otf' ? 'opentype' : 'truetype';
        $formatKu = strtolower($fontKuExt) === 'otf' ? 'opentype' : 'truetype';
        $formatEn = strtolower($fontEnExt) === 'otf' ? 'opentype' : 'truetype';
    @endphp

    <style>
        /* Define Custom Webfonts */
        @font-face {
            font-family: 'CustomArFont';
            src: url('{{ asset("fonts/ar/" . $fontAr) }}') format('{{ $formatAr }}');
            font-display: swap;
        }
        @font-face {
            font-family: 'CustomKuFont';
            src: url('{{ asset("fonts/ku/" . $fontKu) }}') format('{{ $formatKu }}');
            font-display: swap;
        }
        @font-face {
            font-family: 'CustomEnFont';
            src: url('{{ asset("fonts/en/" . $fontEn) }}') format('{{ $formatEn }}');
            font-display: swap;
        }

        /* Dynamic Font & Alignment Adjustments */
        {!! app()->getLocale() == 'ar' ? '
            body {
                font-family: "CustomArFont", "Amiri", serif;
            }
        ' : (app()->getLocale() == 'ku' ? '
            body {
                font-family: "CustomKuFont", "Noto Naskh Arabic", serif;
            }
        ' : '
            body {
                font-family: "CustomEnFont", "Inter", sans-serif;
            }
        ') !!}

        /* Enforce custom Arabic font for Quranic texts across all locales */
        .arabic-text, .quran-surah-arabic, .quran-surah-number, .surah-name-arabic {
            font-family: "CustomArFont", "Amiri", serif !important;
        }

        /* Sidebar RTL Adjustments */
        {!! in_array(app()->getLocale(), ['ar', 'ku']) ? '
            .quran-sidebar {
                left: auto;
                right: 0;
            }
            .quran-main-content {
                margin-left: 0;
                margin-right: var(--sidebar-width);
            }
            .sidebar-collapsed .quran-main-content {
                margin-right: var(--sidebar-collapsed-width);
            }
        ' : '' !!}

        /* Page-specific styles */
        @yield('page-styles')
    </style>

    @stack('styles')
</head>
<body>
    <div class="quran-layout" id="appLayout">
        <!-- Sidebar Component -->
        @include('layouts.partials.sidebar')

        <!-- Main Content Area -->
        <div class="quran-main-content" id="mainContent">
            <!-- Header Component -->
            @include('layouts.partials.header')

            <!-- Page Content Container -->
            <div class="quran-content-container">
                <!-- Breadcrumb -->
                @if(!isset($hideBreadcrumb) || !$hideBreadcrumb)
                    <nav aria-label="breadcrumb" class="quran-breadcrumb mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}">
                                    <i class="bi bi-house-door"></i> {{ __('common.home') }}
                                </a>
                            </li>
                            @yield('breadcrumb') 
                        </ol>
                    </nav>
                @endif

                <!-- Alert Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Validation Errors -->
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>{{ __('common.validation_errors') }}:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Main Page Content -->
                <main class="quran-page-content">
                    @yield('content')
                </main>
            </div>

            <!-- Footer Component -->
            @include('layouts.partials.footer')
        </div>
    </div>

    <!-- Modals Container -->
    <div id="modalContainer"></div>

    <!-- Loading Overlay -->
    <div class="quran-loading-overlay" id="loadingOverlay" style="display: none;">
        <div class="quran-loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ __('common.loading') }}</span>
            </div>
            <p class="mt-3">{{ __('common.please_wait') }}</p>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Axios for API calls -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- Application JavaScript -->
    <script>
        // Global Configuration
        window.quranConfig = {
            locale: '{{ app()->getLocale() }}',
            direction: '{{ \App\Helpers\LanguageHelper::getDirection() }}',
            baseUrl: '{{ url("/") }}',
            apiUrl: '{{ url("/api") }}',
            csrfToken: '{{ csrf_token() }}',
            userId: {{ auth()->id() ?? 'null' }},
            translations: {
                confirm: '{{ __("common.are_you_sure") }}',
                yes: '{{ __("common.yes") }}',
                no: '{{ __("common.no") }}',
                cancel: '{{ __("common.cancel") }}',
                save: '{{ __("common.save") }}',
                delete: '{{ __("common.delete") }}',
                loading: '{{ __("common.loading") }}',
                error: '{{ __("common.error_occurred") }}',
                success: '{{ __("common.success") }}'
            }
        };

        // JavaScript i18n Bridge — window.__('group.key', {placeholder: value})
        @php
            $jsGroups = ['common', 'validation', 'api', 'notifications'];
            $jsLocale = app()->getLocale();
            $jsFallback = config('app.fallback_locale', 'en');
            $jsTranslations = [];
            foreach ($jsGroups as $jsGroup) {
                $jsPath = lang_path($jsLocale . '/' . $jsGroup . '.php');
                $jsFallbackPath = lang_path($jsFallback . '/' . $jsGroup . '.php');
                if (file_exists($jsPath)) {
                    $r = include $jsPath;
                    if (is_array($r)) { $jsTranslations[$jsGroup] = $r; }
                } elseif (file_exists($jsFallbackPath)) {
                    $r = include $jsFallbackPath;
                    if (is_array($r)) { $jsTranslations[$jsGroup] = $r; }
                }
            }
        @endphp
        window.__jsTranslations = @json($jsTranslations);

        window.__ = function(key, replace) {
            const parts = key.split('.');
            let val = window.__jsTranslations;
            for (const p of parts) {
                if (val === undefined || val === null) return key;
                val = val[p];
            }
            if (val === undefined || val === null || typeof val !== 'string') return key;
            if (replace) {
                Object.keys(replace).forEach(k => {
                    val = val.replace(new RegExp(':' + k, 'g'), replace[k]);
                });
            }
            return val;
        };

        // Axios Configuration
        axios.defaults.headers.common['X-CSRF-TOKEN'] = window.quranConfig.csrfToken;
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['Accept-Language'] = window.quranConfig.locale;

        // Loading Overlay Functions
        window.showLoading = function() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        };

        window.hideLoading = function() {
            document.getElementById('loadingOverlay').style.display = 'none';
        };

        // Confirm Dialog
        window.confirmAction = function(message, callback) {
            if (confirm(message || window.quranConfig.translations.confirm)) {
                callback();
            }
        };

        // Toast Notification
        window.showToast = function(message, type = 'info') {
            const toastContainer = document.createElement('div');
            toastContainer.className = 'quran-toast-container';
            toastContainer.innerHTML = `
                <div class="quran-toast toast-${type}">
                    <div class="toast-body">
                        <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                        ${message}
                    </div>
                </div>
            `;
            document.body.appendChild(toastContainer);

            setTimeout(() => {
                toastContainer.remove();
            }, 3000);
        };
    </script>

    <!-- Main Application JS -->
    <script src="{{ asset('js/quran-app.js') }}"></script>

    <!-- Page-specific JavaScript -->
    @stack('scripts')

    <!-- Additional footer scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Initialize popovers
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                document.querySelectorAll('.alert').forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);

            // Handle RTL-specific adjustments
            if (window.quranConfig.direction === 'rtl') {
                document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
                    if (!menu.classList.contains('dropdown-menu-end')) {
                        menu.classList.add('dropdown-menu-end');
                    }
                });
            }

            // Add active class to current nav item
            const currentPath = window.location.pathname;
            document.querySelectorAll('.quran-nav-item').forEach(function(item) {
                const href = item.getAttribute('href');
                if (href && currentPath.startsWith(href) && href !== '#') {
                    item.classList.add('active');
                }
            });
        });

        // Handle window resize for responsive behavior
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                const width = window.innerWidth;
                const sidebar = document.getElementById('mainSidebar');
                const overlay = document.getElementById('sidebarOverlay');

                if (width >= 992) {
                    // Desktop: ensure sidebar is visible and overlay hidden
                    if (sidebar) {
                        sidebar.classList.remove('mobile-open');
                    }
                    if (overlay) {
                        overlay.classList.remove('active');
                    }
                }
            }, 250);
        });
    </script>

    <!-- Custom Styles for RTL/LTR -->
    <style>
        /* Toast Notifications */
        .quran-toast-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            animation: slideInRight 0.3s ease;
        }

        [dir="rtl"] .quran-toast-container {
            right: auto;
            left: 20px;
            animation: slideInLeft 0.3s ease;
        }

        .quran-toast {
            background: white;
            border-radius: 12px;
            padding: 12px 20px;
            margin-bottom: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border-left: 4px solid;
            animation: fadeOut 0.3s ease 2.7s forwards;
        }

        [dir="rtl"] .quran-toast {
            border-left: none;
            border-right: 4px solid;
        }

        .quran-toast.toast-success {
            border-color: var(--quran-success);
        }

        .quran-toast.toast-error {
            border-color: var(--quran-danger);
        }

        .quran-toast.toast-info {
            border-color: var(--quran-info);
        }

        .quran-toast.toast-warning {
            border-color: var(--quran-warning);
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideInLeft {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: translateY(-10px);
            }
        }

        /* Loading Overlay */
        .quran-loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .quran-loading-spinner {
            background: white;
            padding: 30px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        [data-theme="dark"] .quran-loading-spinner {
            background: var(--quran-bg-card);
            color: var(--quran-text-primary);
        }

        /* Breadcrumb Styling */
        .quran-breadcrumb {
            padding: 12px 0;
        }

        .breadcrumb {
            margin: 0;
            padding: 0;
            background: transparent;
        }

        .breadcrumb-item a {
            color: var(--quran-text-secondary);
            text-decoration: none;
            transition: color var(--transition-fast);
        }

        .breadcrumb-item a:hover {
            color: var(--quran-primary);
        }

        .breadcrumb-item.active {
            color: var(--quran-text-primary);
            font-weight: 500;
        }

        [dir="rtl"] .breadcrumb-item + .breadcrumb-item::before {
            float: right;
            padding-left: 0.5rem;
            padding-right: 0;
        }

        /* Alert Customization */
        .alert {
            border: none;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
        }

        .alert-success {
            background: color-mix(in srgb, var(--quran-success) 10%, white);
            color: var(--quran-success);
        }

        .alert-danger {
            background: color-mix(in srgb, var(--quran-danger) 10%, white);
            color: var(--quran-danger);
        }

        .alert-warning {
            background: color-mix(in srgb, var(--quran-warning) 10%, white);
            color: var(--quran-warning);
        }

        .alert-info {
            background: color-mix(in srgb, var(--quran-info) 10%, white);
            color: var(--quran-info);
        }

        [data-theme="dark"] .alert-success {
            background: color-mix(in srgb, var(--quran-success) 20%, var(--quran-bg-card));
        }

        [data-theme="dark"] .alert-danger {
            background: color-mix(in srgb, var(--quran-danger) 20%, var(--quran-bg-card));
        }

        [data-theme="dark"] .alert-warning {
            background: color-mix(in srgb, var(--quran-warning) 20%, var(--quran-bg-card));
        }

        [data-theme="dark"] .alert-info {
            background: color-mix(in srgb, var(--quran-info) 20%, var(--quran-bg-card));
        }

        /* Print Styles */
        @media print {
            .quran-sidebar,
            .quran-header,
            .quran-footer,
            .quran-fab,
            .quran-breadcrumb,
            .alert {
                display: none !important;
            }

            .quran-main-content {
                margin: 0 !important;
                padding: 0 !important;
            }

            .quran-content-container {
                padding: 0 !important;
            }
        }

        /* Accessibility Improvements */
        .btn:focus,
        .form-control:focus,
        .quran-nav-item:focus {
            outline: 2px solid var(--quran-primary);
            outline-offset: 2px;
        }

        /* High Contrast Mode Support */
        @media (prefers-contrast: high) {
            .quran-nav-item.active {
                border: 2px solid currentColor;
            }

            .quran-header-icon-btn {
                border: 1px solid currentColor;
            }
        }

        /* Reduced Motion Support */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</body>
</html>
