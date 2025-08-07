<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('', 'Laravel'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f8f9fa;
        }
        
        /* Auth pages styling */
        body.auth-page {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%) !important;
            margin: 0;
            padding: 0;
        }
        
        .navbar-brand {
            font-weight: 600;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .btn {
            border-radius: 0.5rem;
        }
        
        .table {
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .alert {
            border-radius: 0.5rem;
        }
        
        .form-control, .form-select {
            border-radius: 0.5rem;
        }

        /* Hide navbar and footer for auth pages */
        body.auth-page .navbar,
        body.auth-page footer {
            display: none !important;
        }

        body.auth-page main {
            padding: 0 !important;
        }

        /* Navbar styling for logged in users */
        .navbar {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
            box-shadow: 0 2px 15px rgba(37, 99, 235, 0.2);
        }

        .navbar-brand {
            color: white !important;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 0 2px;
            padding: 8px 12px !important;
        }

        .navbar-nav .nav-link:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            transform: translateY(-1px);
        }

        .dropdown-menu {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.98);
        }

        .dropdown-item {
            border-radius: 8px;
            margin: 2px 4px;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            transform: translateX(4px);
        }

        /* Footer styling */
        footer {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
            border-top: 1px solid #e2e8f0;
        }
    </style>

    @stack('styles')
</head>
<body class="@if(request()->routeIs('login') || request()->routeIs('register')) auth-page @endif">
    @if(!request()->routeIs('login') && !request()->routeIs('register'))
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand">
            <i class="fas fa-flask me-2"></i>
            Sistem Reservasi Lab
        </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            @if(auth()->user()->role === 'admin')
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-cogs me-1"></i> Admin
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.laboratories.index') }}">
                                            <i class="fas fa-flask me-2"></i>Laboratorium
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.reservations.index') }}">
                                            <i class="fas fa-calendar-check me-2"></i>Reservasi
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">
                                            <i class="fas fa-users me-2"></i>Pengguna
                                        </a></li>
                                    </ul>
                                </li>
                            @endif
                            
                           
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">
                                        <i class="fas fa-sign-in-alt me-1"></i> {{ __('Login') }}
                                    </a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">
                                        <i class="fas fa-user-plus me-1"></i> {{ __('Register') }}
                                    </a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user me-1"></i> {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="fas fa-user-cog me-2"></i> Profile
                                    </a>
                                    
                                    <div class="dropdown-divider"></div>
                                    
                                    <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt me-2"></i> {{ __('Logout') }}
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
        </nav>
    @endif

    <!-- Main Content -->
    <main class="@if(!request()->routeIs('login') && !request()->routeIs('register')) py-4 @endif">
        <!-- Flash Messages -->
        @if(!request()->routeIs('login') && !request()->routeIs('register'))
            <div class="container">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>
        @endif

        @yield('content')
    </main>

    @if(!request()->routeIs('login') && !request()->routeIs('register'))
        <!-- Footer -->
        <footer class="text-center text-muted py-4 mt-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 text-md-start">
                        <p class="mb-0">
                            <i class="fas fa-flask me-2 text-primary"></i>
                            <strong>STIKES Bhakti Husada Mulia Madiun</strong>
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="mb-0">
                            &copy; {{ date('Y') }} Sistem Reservasi Lab. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    @endif

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Auto dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    if (alert.classList.contains('show')) {
                        bootstrap.Alert.getOrCreateInstance(alert).close();
                    }
                });
            }, 5000);
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>