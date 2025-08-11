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

<<<<<<< HEAD
=======
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css' rel='stylesheet' />

>>>>>>> 92b809e (notifikasi)
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

<<<<<<< HEAD
=======
        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            font-size: 0.75rem;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            border: 2px solid white;
        }

        .notification-dropdown {
            min-width: 350px;
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 12px 16px;
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .notification-item:hover {
            background-color: #f8f9fa;
        }

        .notification-item.unread {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
        }

        .notification-icon {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 12px;
        }

        .notification-icon.success {
            background-color: #d4edda;
            color: #155724;
        }

        .notification-icon.danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .notification-text {
            flex: 1;
            font-size: 0.9rem;
        }

        .notification-time {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 4px;
        }

>>>>>>> 92b809e (notifikasi)
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
<<<<<<< HEAD
    <div class="container">
        <a class="navbar-brand">
            <i class="fas fa-flask me-2"></i>
            Sistem Reservasi Lab
        </a>
=======
            <div class="container">
                <a class="navbar-brand">
                    <i class="fas fa-flask me-2"></i>
                    Sistem Reservasi Lab
                </a>
>>>>>>> 92b809e (notifikasi)

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
<<<<<<< HEAD
                                    </ul>
                                </li>
                            @endif
                            
                           
=======
                                        <li><a class="dropdown-item" href="{{ route('admin.reports.index') }}">
                                            <i class="fas fa-chart-bar me-2"></i>Laporan
                                        </a></li>
                                    </ul>
                                </li>
                            @elseif(in_array(auth()->user()->role, ['dosen', 'mahasiswa']))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('user.dashboard') }}">
                                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('user.laboratories.index') }}">
                                        <i class="fas fa-flask me-1"></i> Laboratorium
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('user.reservations.index') }}">
                                        <i class="fas fa-calendar-check me-1"></i> Reservasi Saya
                                    </a>
                                </li>
                            @endif
>>>>>>> 92b809e (notifikasi)
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
<<<<<<< HEAD
=======
                            <!-- Notifications Dropdown (Only for dosen/mahasiswa) -->
                            @if(in_array(auth()->user()->role, ['dosen', 'mahasiswa']))
                                <li class="nav-item dropdown">
                                    <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" id="notificationDropdown">
                                        <i class="fas fa-bell"></i>
                                        <span class="notification-badge d-none" id="notificationBadge">0</span>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end notification-dropdown" id="notificationDropdownMenu">
                                        <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-light">
                                            <h6 class="mb-0">Notifikasi</h6>
                                            <button class="btn btn-sm btn-outline-primary" onclick="markAllAsRead()" id="markAllBtn" style="display: none;">
                                                Tandai Semua Dibaca
                                            </button>
                                        </div>
                                        <div id="notificationList">
                                            <div class="text-center py-3">
                                                <i class="fas fa-spinner fa-spin"></i> Memuat...
                                            </div>
                                        </div>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item text-center" href="{{ route('user.notifications.index') }}">
                                            <i class="fas fa-list me-1"></i> Lihat Semua Notifikasi
                                        </a>
                                    </div>
                                </li>
                            @endif

>>>>>>> 92b809e (notifikasi)
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
    
<<<<<<< HEAD
=======
    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales/id.min.js'></script>
    
>>>>>>> 92b809e (notifikasi)
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
<<<<<<< HEAD
        });

=======

            // Load notifications on page load for logged in users
            @auth
                @if(in_array(auth()->user()->role, ['dosen', 'mahasiswa']))
                    loadNotifications();
                    // Refresh notifications every 30 seconds
                    setInterval(loadNotifications, 30000);
                @endif
            @endauth
        });

        // Notification functions
        @auth
        @if(in_array(auth()->user()->role, ['dosen', 'mahasiswa']))
        function loadNotifications() {
            fetch('{{ route("user.api.notifications.latest") }}?limit=5')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateNotificationBadge(data.unread_count);
                        updateNotificationDropdown(data.notifications);
                    }
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                });
        }

        function updateNotificationBadge(count) {
            const badge = document.getElementById('notificationBadge');
            const markAllBtn = document.getElementById('markAllBtn');
            
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('d-none');
                markAllBtn.style.display = 'inline-block';
            } else {
                badge.classList.add('d-none');
                markAllBtn.style.display = 'none';
            }
        }

        function updateNotificationDropdown(notifications) {
            const list = document.getElementById('notificationList');
            
            if (notifications.length === 0) {
                list.innerHTML = `
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-inbox me-2"></i>
                        Tidak ada notifikasi
                    </div>
                `;
                return;
            }

            list.innerHTML = notifications.map(notification => `
                <div class="notification-item ${!notification.is_read ? 'unread' : ''}" onclick="markAsRead('${notification.id}')">
                    <div class="d-flex">
                        <div class="notification-icon ${notification.color}">
                            <i class="${notification.icon}"></i>
                        </div>
                        <div class="notification-text">
                            <div class="fw-semibold">${notification.title}</div>
                            <div class="text-muted small">${notification.message}</div>
                            <div class="notification-time">${notification.created_at}</div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function markAsRead(notificationId) {
            fetch(`{{ route('user.notifications.read', '') }}/${notificationId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (response.ok) {
                    loadNotifications();
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
        }

        function markAllAsRead() {
            fetch('{{ route("user.notifications.readAll") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (response.ok) {
                    loadNotifications();
                }
            })
            .catch(error => {
                console.error('Error marking all notifications as read:', error);
            });
        }
        @endif
        @endauth

>>>>>>> 92b809e (notifikasi)
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