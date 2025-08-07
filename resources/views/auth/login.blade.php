{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card shadow-lg border-0" style="border-radius: 20px; backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.95);">
                    <div class="card-body p-5">
                        <!-- Header Section -->
                        <div class="text-center mb-5">
                            <div class="position-relative d-inline-block mb-4">
                                <a href="{{ route('landing') }}" class="text-decoration-none logo-link" title="Kembali ke Beranda">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto clickable-logo" 
                                         style="width: 80px; height: 80px; box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3); background: linear-gradient(135deg, #2563eb, #1d4ed8); cursor: pointer; transition: all 0.3s ease;">
                                        <i class="fas fa-flask fa-2x text-white"></i>
                                    </div>
                                </a>
                            </div>
                            <a href="{{ route('landing') }}" class="text-decoration-none">
                                <h3 class="fw-bold text-dark mb-2 clickable-title" style="letter-spacing: 1px; cursor: pointer; transition: all 0.3s ease;">SISTEM RESERVASI LAB</h3>
                            </a>
                            <p class="text-muted small mb-0">STIKES BHAKTI HUSADA MULIA MADIUN</p>
                            <hr class="mx-auto mt-3" style="width: 60px; height: 3px; background: linear-gradient(90deg, #2563eb, #1d4ed8); border: none; border-radius: 2px;">
                        </div>

                        <!-- Login Form -->
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="nim_nip" class="form-label fw-semibold text-dark">
                                    <i class="fas fa-id-card me-2" style="color: #2563eb;"></i>NIM/NIP
                                </label>
                                <div class="position-relative">
                                    <input type="text" 
                                           class="form-control form-control-lg @error('nim_nip') is-invalid @enderror" 
                                           id="nim_nip" 
                                           name="nim_nip" 
                                           value="{{ old('nim_nip') }}" 
                                           required 
                                           autofocus
                                           placeholder="Masukkan NIM atau NIP"
                                           style="border-radius: 15px; border: 2px solid #e9ecef; padding-left: 20px; transition: all 0.3s ease;"
                                           onchange="this.style.borderColor='#2563eb'"
                                           onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 0.2rem rgba(37, 99, 235, 0.25)'"
                                           onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'">
                                </div>
                                @error('nim_nip')
                                    <div class="invalid-feedback d-block mt-2">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-5">
                                <label for="password" class="form-label fw-semibold text-dark">
                                    <i class="fas fa-lock me-2" style="color: #2563eb;"></i>Password
                                </label>
                                <div class="position-relative">
                                    <input type="password" 
                                           class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           required
                                           placeholder="Masukkan password"
                                           style="border-radius: 15px; border: 2px solid #e9ecef; padding-left: 20px; transition: all 0.3s ease;"
                                           onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 0.2rem rgba(37, 99, 235, 0.25)'"
                                           onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'">
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block mt-2">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <button type="submit" 
                                    class="btn btn-lg w-100 mb-4 fw-semibold text-white"
                                    style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); 
                                           border: none; 
                                           border-radius: 15px; 
                                           padding: 12px 0; 
                                           transition: all 0.3s ease;
                                           box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);"
                                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(37, 99, 235, 0.4)'"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(37, 99, 235, 0.3)'">
                                <i class="fas fa-sign-in-alt me-2"></i>Masuk ke Sistem
                            </button>
                        </form>

                        <!-- Register Link -->
                        <div class="text-center">
                            <div class="position-relative">
                                <hr class="text-muted">
                                <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">atau</span>
                            </div>
                            <p class="mb-0 mt-3">
                                <span class="text-muted">Belum punya akun?</span>
                                <a href="{{ route('register') }}" 
                                   class="text-decoration-none fw-semibold ms-1"
                                   style="color: #2563eb; transition: all 0.3s ease;"
                                   onmouseover="this.style.color='#1d4ed8'"
                                   onmouseout="this.style.color='#2563eb'">
                                    Daftar di sini
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center mt-4">
                    <p class="text-white small opacity-75 mb-0">
                        © 2024 STIKES Bhakti Husada Mulia Madiun
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

body {
    font-family: 'Inter', sans-serif;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
}

/* Custom focus effects */
.form-control:focus {
    outline: none;
}

/* Floating animation for the flask icon */
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}

.clickable-logo {
    animation: float 3s ease-in-out infinite;
}

/* Logo hover effects */
.logo-link:hover .clickable-logo {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 15px 40px rgba(59, 130, 246, 0.5) !important;
    animation-play-state: paused;
}

.clickable-title:hover {
    color: #2563eb !important;
    transform: translateY(-2px);
}

/* Active state untuk logo */
.logo-link:active .clickable-logo {
    transform: translateY(-3px) scale(1.02);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .card-body {
        padding: 2rem !important;
    }
    
    .clickable-logo {
        width: 60px !important;
        height: 60px !important;
    }
    
    .clickable-logo i {
        font-size: 1.5rem !important;
    }
}
</style>
@endsection