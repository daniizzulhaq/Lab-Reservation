{{-- resources/views/auth/register.blade.php --}}
@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-lg border-0" style="border-radius: 20px; backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.95);">
                    <div class="card-body p-5">
                        <!-- Header Section -->
                        <div class="text-center mb-5">
                            <div class="position-relative d-inline-block mb-4">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                     style="width: 80px; height: 80px; box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3); background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                                    <i class="fas fa-user-plus fa-2x text-white"></i>
                                </div>
                            </div>
                            <h3 class="fw-bold text-dark mb-2" style="letter-spacing: 1px;">DAFTAR AKUN BARU</h3>
                            <p class="text-muted small mb-0">STIKES BHAKTI HUSADA MULIA MADIUN</p>
                            <hr class="mx-auto mt-3" style="width: 60px; height: 3px; background: linear-gradient(90deg, #2563eb, #1d4ed8); border: none; border-radius: 2px;">
                        </div>

                        <!-- Register Form -->
                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="nim_nip" class="form-label fw-semibold text-dark">
                                        <i class="fas fa-id-card me-2" style="color: #2563eb;"></i>NIM/NIP
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('nim_nip') is-invalid @enderror" 
                                           id="nim_nip" 
                                           name="nim_nip" 
                                           value="{{ old('nim_nip') }}" 
                                           required
                                           placeholder="NIM/NIP"
                                           style="border-radius: 15px; border: 2px solid #e9ecef; padding-left: 20px; transition: all 0.3s ease;"
                                           onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 0.2rem rgba(37, 99, 235, 0.25)'"
                                           onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'">
                                    @error('nim_nip')
                                        <div class="invalid-feedback d-block mt-2">
                                            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="role" class="form-label fw-semibold text-dark">
                                        <i class="fas fa-users me-2" style="color: #2563eb;"></i>Status
                                    </label>
                                    <select class="form-select form-control-lg @error('role') is-invalid @enderror" 
                                            id="role" 
                                            name="role" 
                                            required
                                            style="border-radius: 15px; border: 2px solid #e9ecef; padding-left: 20px; transition: all 0.3s ease;"
                                            onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 0.2rem rgba(37, 99, 235, 0.25)'"
                                            onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'">
                                        <option value="">Pilih Status</option>
                                        <option value="mahasiswa" {{ old('role') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                                        <option value="dosen" {{ old('role') == 'dosen' ? 'selected' : '' }}>Dosen</option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback d-block mt-2">
                                            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="name" class="form-label fw-semibold text-dark">
                                    <i class="fas fa-user me-2" style="color: #2563eb;"></i>Nama Lengkap
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}" 
                                       required
                                       placeholder="Nama lengkap"
                                       style="border-radius: 15px; border: 2px solid #e9ecef; padding-left: 20px; transition: all 0.3s ease;"
                                       onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 0.2rem rgba(37, 99, 235, 0.25)'"
                                       onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'">
                                @error('name')
                                    <div class="invalid-feedback d-block mt-2">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold text-dark">
                                    <i class="fas fa-envelope me-2" style="color: #2563eb;"></i>Email
                                </label>
                                <input type="email" 
                                       class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       required
                                       placeholder="email@example.com"
                                       style="border-radius: 15px; border: 2px solid #e9ecef; padding-left: 20px; transition: all 0.3s ease;"
                                       onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 0.2rem rgba(37, 99, 235, 0.25)'"
                                       onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'">
                                @error('email')
                                    <div class="invalid-feedback d-block mt-2">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="phone" class="form-label fw-semibold text-dark">
                                        <i class="fas fa-phone me-2" style="color: #2563eb;"></i>No. HP
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone') }}"
                                           placeholder="08xxxxxxxxxx"
                                           style="border-radius: 15px; border: 2px solid #e9ecef; padding-left: 20px; transition: all 0.3s ease;"
                                           onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 0.2rem rgba(37, 99, 235, 0.25)'"
                                           onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'">
                                    @error('phone')
                                        <div class="invalid-feedback d-block mt-2">
                                            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="program_studi" class="form-label fw-semibold text-dark">
                                        <i class="fas fa-graduation-cap me-2" style="color: #2563eb;"></i>Program Studi
                                    </label>
                                    <select class="form-select form-control-lg @error('program_studi') is-invalid @enderror" 
                                            id="program_studi" 
                                            name="program_studi" 
                                            required
                                            style="border-radius: 15px; border: 2px solid #e9ecef; padding-left: 20px; transition: all 0.3s ease;"
                                            onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 0.2rem rgba(37, 99, 235, 0.25)'"
                                            onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'">
                                        <option value="">Pilih Program Studi</option>
                                        <option value="Keperawatan" {{ old('program_studi') == 'Keperawatan' ? 'selected' : '' }}>Keperawatan</option>
                                        <option value="Kebidanan" {{ old('program_studi') == 'Kebidanan' ? 'selected' : '' }}>Kebidanan</option>
                                        <option value="Farmasi" {{ old('program_studi') == 'Farmasi' ? 'selected' : '' }}>Farmasi</option>
                                        <option value="Kesehatan Masyarakat" {{ old('program_studi') == 'Kesehatan Masyarakat' ? 'selected' : '' }}>Kesehatan Masyarakat</option>
                                    </select>
                                    @error('program_studi')
                                        <div class="invalid-feedback d-block mt-2">
                                            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-semibold text-dark">
                                        <i class="fas fa-lock me-2" style="color: #2563eb;"></i>Password
                                    </label>
                                    <input type="password" 
                                           class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           required
                                           placeholder="Minimal 6 karakter"
                                           style="border-radius: 15px; border: 2px solid #e9ecef; padding-left: 20px; transition: all 0.3s ease;"
                                           onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 0.2rem rgba(37, 99, 235, 0.25)'"
                                           onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'">
                                    @error('password')
                                        <div class="invalid-feedback d-block mt-2">
                                            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label fw-semibold text-dark">
                                        <i class="fas fa-lock me-2" style="color: #2563eb;"></i>Konfirmasi Password
                                    </label>
                                    <input type="password" 
                                           class="form-control form-control-lg" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           required
                                           placeholder="Ulangi password"
                                           style="border-radius: 15px; border: 2px solid #e9ecef; padding-left: 20px; transition: all 0.3s ease;"
                                           onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 0.2rem rgba(37, 99, 235, 0.25)'"
                                           onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'">
                                </div>
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
                                <i class="fas fa-user-plus me-2"></i>Daftar Akun
                            </button>
                        </form>

                        <!-- Login Link -->
                        <div class="text-center">
                            <div class="position-relative">
                                <hr class="text-muted">
                                <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">atau</span>
                            </div>
                            <p class="mb-0 mt-3">
                                <span class="text-muted">Sudah punya akun?</span>
                                <a href="{{ route('login') }}" 
                                   class="text-decoration-none fw-semibold ms-1"
                                   style="color: #2563eb; transition: all 0.3s ease;"
                                   onmouseover="this.style.color='#1d4ed8'"
                                   onmouseout="this.style.color='#2563eb'">
                                    Login di sini
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
.form-control:focus,
.form-select:focus {
    outline: none;
}

/* Floating animation for the user-plus icon */
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}

.rounded-circle {
    animation: float 3s ease-in-out infinite;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .card-body {
        padding: 2rem !important;
    }
    
    .rounded-circle {
        width: 60px !important;
        height: 60px !important;
    }
    
    .rounded-circle i {
        font-size: 1.5rem !important;
    }
    
    .row .col-md-6 {
        margin-bottom: 1rem;
    }
    
    .row .col-md-6:last-child {
        margin-bottom: 0;
    }
}

/* Custom styling for select dropdown */
.form-select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
}
</style>
@endsection