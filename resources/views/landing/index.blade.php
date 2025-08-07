<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Reservasi Laboratorium</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .btn-primary-custom {
            background: linear-gradient(45deg, #3b82f6, #1d4ed8);
            border: none;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(59, 130, 246, 0.4);
            background: linear-gradient(45deg, #1d4ed8, #1e3a8a);
        }

        .features-section {
            padding: 100px 0;
            background: #f8fafc;
        }

        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: none;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 2rem;
            color: white;
        }

        .feature-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #1f2937;
        }

        .feature-text {
            color: #6b7280;
            line-height: 1.6;
        }

        .stats-section {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            padding: 80px 0;
            color: white;
        }

        .stat-item {
            text-align: center;
            padding: 20px;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            display: block;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .cta-section {
            padding: 100px 0;
            background: white;
            text-align: center;
        }

        .cta-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #1f2937;
        }

        .cta-text {
            font-size: 1.2rem;
            color: #6b7280;
            margin-bottom: 40px;
        }

        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color) !important;
        }

        .hero-image {
            position: relative;
            z-index: 2;
        }

        .hero-image img {
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .btn-primary-custom {
                padding: 12px 25px;
                font-size: 1rem;
            }
        }

        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .floating-elements::before,
        .floating-elements::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .floating-elements::before {
            top: 20%;
            right: 10%;
            animation-delay: -2s;
        }

        .floating-elements::after {
            bottom: 20%;
            left: 10%;
            animation-delay: -4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-flask me-2"></i>
                Lab Reservation
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <div class="navbar-nav">
                    <a class="nav-link" href="#features">Fitur</a>
                    <a class="nav-link" href="#stats">Statistik</a>
                    <a class="nav-link" href="#cta">Mulai</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="floating-elements"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">Sistem Reservasi Laboratorium</h1>
                        <p class="hero-subtitle">
                            Kelola dan reservasi laboratorium dengan mudah dan efisien. 
                            Platform terintegrasi untuk admin, dosen, dan mahasiswa.
                        </p>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="{{ route('login') }}" class="btn btn-primary-custom">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Masuk ke Sistem
                            </a>
                            <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg px-4">
                                <i class="fas fa-user-plus me-2"></i>
                                Daftar Akun
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image text-center mt-5 mt-lg-0">
                        <div class="position-relative">
                            <div style="background: rgba(255,255,255,0.1); border-radius: 20px; padding: 40px; backdrop-filter: blur(10px);">
                                <i class="fas fa-microscope" style="font-size: 8rem; color: rgba(255,255,255,0.8);"></i>
                                <div class="mt-3">
                                    <h4 style="color: rgba(255,255,255,0.9);">Laboratorium Modern</h4>
                                    <p style="color: rgba(255,255,255,0.7);">Fasilitas lengkap untuk kebutuhan akademik</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-5 fw-bold mb-3">Fitur Unggulan</h2>
                    <p class="lead text-muted">Sistem reservasi laboratorium yang dirancang untuk memudahkan semua pengguna</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h4 class="feature-title">Reservasi Online</h4>
                        <p class="feature-text">
                            Pesan laboratorium secara online kapan saja dengan sistem booking yang mudah dan intuitif.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <h4 class="feature-title">Manajemen User</h4>
                        <p class="feature-text">
                            Kelola pengguna dengan role berbeda: Admin, Dosen, dan Mahasiswa dengan hak akses sesuai kebutuhan.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h4 class="feature-title">Laporan & Statistik</h4>
                        <p class="feature-text">
                            Dapatkan laporan penggunaan laboratorium dan statistik reservasi dalam format Excel dan PDF.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4 class="feature-title">Real-time Availability</h4>
                        <p class="feature-text">
                            Cek ketersediaan laboratorium secara real-time untuk menghindari konflik jadwal.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h4 class="feature-title">Notifikasi</h4>
                        <p class="feature-text">
                            Terima notifikasi untuk persetujuan, penolakan, dan reminder reservasi laboratorium.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4 class="feature-title">Mobile Friendly</h4>
                        <p class="feature-text">
                            Akses sistem dari perangkat apapun dengan desain responsif yang optimal.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section" id="stats">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">15+</span>
                        <span class="stat-label">Laboratorium</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Pengguna Aktif</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">1000+</span>
                        <span class="stat-label">Reservasi Berhasil</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">99%</span>
                        <span class="stat-label">Uptime System</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section" id="cta">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h2 class="cta-title">Mulai Gunakan Sistem Sekarang</h2>
                    <p class="cta-text">
                        Bergabunglah dengan ratusan pengguna yang sudah merasakan kemudahan reservasi laboratorium digital.
                    </p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="{{ route('login') }}" class="btn btn-primary-custom">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Login Sekarang
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg px-4">
                            <i class="fas fa-user-plus me-2"></i>
                            Buat Akun Baru
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-flask me-2"></i>Lab Reservation System</h5>
                    <p class="mb-0">Sistem reservasi laboratorium yang mudah dan efisien.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">&copy; 2024 Lab Reservation System. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-custom');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                navbar.style.boxShadow = '0 2px 20px rgba(0,0,0,0.1)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.boxShadow = 'none';
            }
        });
    </script>
</body>
</html>