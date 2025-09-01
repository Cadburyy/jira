<!doctype html>
@php
    use App\Models\Setting;

    $settings = cache()->remember('app_settings', 60, function () {
        return Setting::pluck('value', 'key')->toArray();
    });

    $brand = $settings['brand_name'] ?? 'Citra Nugerah Karya';
    $theme = $settings['theme'] ?? 'light';
    $font  = $settings['font'] ?? 'Nunito';
    $logoUrl = !empty($settings['logo_path'])
        ? asset('storage/'.$settings['logo_path'])
        : asset('images/cnk.png');

    $fontHrefName = str_replace(' ', '+', $font);
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $brand }}</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family={{ $fontHrefName }}:400,600,700" rel="stylesheet">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <style>
        /* Theme tokens */
        :root {
            --bg: #f8f9fa;
            --text: #111827;
            --surface: #ffffff;
            --muted: #6b7280;
            --border: #e5e7eb;
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: '{{ $font }}', system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif;
        }

        .navbar.navbar-light.bg-white,
        .dropdown-menu,
        .card,
        .navbar-collapse {
            background-color: var(--surface) !important;
            color: var(--text) !important;
        }

        .navbar .nav-link,
        .dropdown-item {
            color: var(--text) !important;
        }

        .card-header {
            background-color: var(--surface) !important;
            border-bottom: 1px solid var(--border) !important;
        }

        /* === your existing styles below === */
        .fixed-blur-navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
            backdrop-filter: blur(5px);
            background-color: rgba(255, 255, 255, 0.8);
        }
        #loading-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            width: 0;
            background-color: #3498db;
            transition: width 0.3s ease-in-out, opacity 0.5s ease-in-out;
            z-index: 1031;
        }
        .navbar-nav .nav-link {
            position: relative;
            padding-bottom: 12px;
        }
        .navbar-nav .nav-link::before,
        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 0;
            height: 2px;
            width: 0;
            transition: width 0.3s ease-in-out;
        }
        .navbar-nav .nav-link::before {
            background-color: #ef4444;
            bottom: 7px;
        }
        .navbar-nav .nav-link::after {
            background-color: #3b82f6;
        }
        .navbar-nav .nav-link:hover::before { width: 62.5%; }
        .navbar-nav .nav-link:hover::after { width: 37.5%; }

        @media (max-width: 767px) {
            .navbar-collapse {
                background-color: rgba(243, 244, 246, 0.95);
                padding: 1rem;
                border-radius: 0.75rem;
                margin-top: 0.5rem;
                animation: slideDown 0.3s ease-in-out;
            }
            .navbar-nav .nav-item { border-bottom: 1px solid #e5e7eb; }
            .navbar-nav .nav-item:last-child { border-bottom: none; }
            .navbar-nav .nav-link {
                padding: 0.75rem 1rem;
                font-weight: 500;
                transition: background-color 0.2s ease-in-out;
            }
            .navbar-nav .nav-link:hover {
                background-color: #d1d5db;
                border-radius: 0.5rem;
                transform: translateX(5px);
            }
            .dropdown-menu { background-color: transparent !important; border: none !important; }
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body data-theme="{{ $theme }}">
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm fixed-blur-navbar">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                    <img src="{{ $logoUrl }}" alt="Logo" style="height: 30px;">
                </a>
                <span class="ms-2 align-items-center">{{ $brand }}</span>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                    data-bs-target="#navbarSupportedContent" 
                    aria-controls="navbarSupportedContent" aria-expanded="false" 
                    aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto"></ul>
                    <ul class="navbar-nav ms-auto">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif
                        @else
                            @php
                                $user = Auth::user();
                                $isAdmin = $user->hasRole('Admin');
                                $isRequestor = $user->hasRole('Requestor');
                                $isTeknisi = $user->hasRole('Teknisi');
                                $isView = $user->hasRole('Views');
                                $isTeknisiAdmin = $user->hasRole('AdminTeknisi');
                            @endphp
                            @if($isView || $isAdmin || $isRequestor || $isTeknisi || $isTeknisiAdmin)
                                <li><a class="nav-link" href="{{ route('home') }}">Home</a></li>
                            @endif
                            @if($isAdmin || $isRequestor || $isTeknisi || $isTeknisiAdmin)
                                <li><a class="nav-link" href="{{ route('dandories.index') }}">Dandory Tickets</a></li>
                            @endif
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" 
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ $user->name }}
                                </a>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    @if($isAdmin)
                                        <a class="dropdown-item" href="{{ route('settings.index') }}">Settings</a>
                                    @endif
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
            <div id="loading-bar"></div>
        </nav>

        <main class="py-4 mt-5">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <div>
                            <div class="card-body">
                                @yield('content')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

   <script>
    const loadingBar = document.getElementById('loading-bar');
    document.addEventListener('DOMContentLoaded', function() {
        loadingBar.style.width = '90%';
    });
    window.addEventListener('load', function() {
        loadingBar.style.width = '100%';
        loadingBar.style.opacity = '0';
    });
   </script>
</body>
</html>
