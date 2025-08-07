@extends('layouts.admin')

@section('title', 'Edit Pengguna')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Pengguna</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Terdapat kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Edit Pengguna</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <!-- NIM/NIP -->
                        <div class="form-group">
                            <label for="nim_nip" class="form-label">
                                NIM/NIP <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="nim_nip" 
                                   id="nim_nip" 
                                   class="form-control @error('nim_nip') is-invalid @enderror" 
                                   value="{{ old('nim_nip', $user->nim_nip) }}" 
                                   required>
                            @error('nim_nip')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nama -->
                        <div class="form-group">
                            <label for="name" class="form-label">
                                Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $user->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label for="email" class="form-label">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $user->email) }}" 
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="form-group">
                            <label for="phone" class="form-label">Nomor Telepon</label>
                            <input type="text" 
                                   name="phone" 
                                   id="phone" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone', $user->phone) }}" 
                                   placeholder="08xxxxxxxxxx">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <!-- Role -->
                        <div class="form-group">
                            <label for="role" class="form-label">
                                Role <span class="text-danger">*</span>
                            </label>
                            <select name="role" 
                                    id="role" 
                                    class="form-control @error('role') is-invalid @enderror" 
                                    required>
                                <option value="">Pilih Role</option>
                                <option value="dosen" {{ old('role', $user->role) == 'dosen' ? 'selected' : '' }}>
                                    Dosen
                                </option>
                                <option value="mahasiswa" {{ old('role', $user->role) == 'mahasiswa' ? 'selected' : '' }}>
                                    Mahasiswa
                                </option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Program Studi -->
                        <div class="form-group">
                            <label for="program_studi" class="form-label">Program Studi</label>
                            <input type="text" 
                                   name="program_studi" 
                                   id="program_studi" 
                                   class="form-control @error('program_studi') is-invalid @enderror" 
                                   value="{{ old('program_studi', $user->program_studi) }}" 
                                   placeholder="Contoh: Teknik Informatika">
                            @error('program_studi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label for="password" class="form-label">Password Baru</label>
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="Kosongkan jika tidak ingin mengubah password">
                            <small class="form-text text-muted">
                                Minimal 8 karakter. Kosongkan jika tidak ingin mengubah password.
                            </small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   id="password_confirmation" 
                                   class="form-control" 
                                   placeholder="Ulangi password baru">
                        </div>
                    </div>
                </div>

                <!-- User Info Card -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-left-info">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="font-weight-bold text-info">Informasi Akun</h6>
                                        <p class="mb-1">
                                            <strong>Bergabung:</strong> {{ $user->created_at->format('d F Y, H:i') }}
                                        </p>
                                        <p class="mb-1">
                                            <strong>Terakhir Update:</strong> {{ $user->updated_at->format('d F Y, H:i') }}
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="font-weight-bold text-info">Statistik</h6>
                                        <p class="mb-1">
                                            <strong>Total Reservasi:</strong> 
                                            <span class="badge badge-info">{{ $user->reservations_count ?? $user->reservations->count() ?? 0 }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>
@endsection