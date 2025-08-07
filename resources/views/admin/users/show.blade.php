@extends('layouts.admin')

@section('title', 'Detail Pengguna')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Pengguna</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit fa-sm text-white-50"></i> Edit
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- User Profile Card -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Profil Pengguna</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="avatar-circle mx-auto mb-3" style="width: 80px; height: 80px; background-color: #5a5c69; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user fa-2x text-white"></i>
                        </div>
                        <h5 class="font-weight-bold">{{ $user->name }}</h5>
                        <p class="text-muted mb-2">{{ $user->nim_nip }}</p>
                        @if($user->role === 'dosen')
                            <span class="badge badge-primary badge-pill">Dosen</span>
                        @elseif($user->role === 'mahasiswa')
                            <span class="badge badge-info badge-pill">Mahasiswa</span>
                        @else
                            <span class="badge badge-secondary badge-pill">{{ ucfirst($user->role) }}</span>
                        @endif
                    </div>
                    
                    <hr>
                    
                    <div class="text-left">
                        <div class="mb-2">
                            <strong><i class="fas fa-envelope text-primary mr-2"></i>Email:</strong><br>
                            <small class="text-muted">{{ $user->email }}</small>
                        </div>
                        
                        @if($user->phone)
                        <div class="mb-2">
                            <strong><i class="fas fa-phone text-primary mr-2"></i>Telepon:</strong><br>
                            <small class="text-muted">{{ $user->phone }}</small>
                        </div>
                        @endif
                        
                        @if($user->program_studi)
                        <div class="mb-2">
                            <strong><i class="fas fa-graduation-cap text-primary mr-2"></i>Program Studi:</strong><br>
                            <small class="text-muted">{{ $user->program_studi }}</small>
                        </div>
                        @endif
                        
                        <div class="mb-2">
                            <strong><i class="fas fa-calendar text-primary mr-2"></i>Bergabung:</strong><br>
                            <small class="text-muted">{{ $user->created_at->format('d F Y, H:i') }}</small>
                        </div>
                        
                        <div class="mb-2">
                            <strong><i class="fas fa-clock text-primary mr-2"></i>Terakhir Update:</strong><br>
                            <small class="text-muted">{{ $user->updated_at->format('d F Y, H:i') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics and Details -->
        <div class="col-lg-8">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-6 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Total Reservasi
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $user->reservations->count() }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Reservasi Bulan Ini
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $user->reservations()->whereMonth('created_at', now()->month)->count() }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reservations History -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Riwayat Reservasi</h6>
                    @if($user->reservations->count() > 5)
                        <small class="text-muted">Menampilkan 5 reservasi terbaru</small>
                    @endif
                </div>
                <div class="card-body">
                    @if($user->reservations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Waktu</th>
                                        <th>Ruangan/Lab</th>
                                        <th>Keperluan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->reservations->take(5) as $reservation)
                                        <tr>
                                            <td>{{ $reservation->date ? $reservation->date->format('d/m/Y') : '-' }}</td>
                                            <td>
                                                @if($reservation->start_time && $reservation->end_time)
                                                    {{ $reservation->start_time->format('H:i') }} - {{ $reservation->end_time->format('H:i') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $reservation->room->name ?? $reservation->lab->name ?? '-' }}</td>
                                            <td>
                                                <span title="{{ $reservation->purpose }}">
                                                    {{ Str::limit($reservation->purpose, 30) }}
                                                </span>
                                            </td>
                                            <td>
                                                @switch($reservation->status)
                                                    @case('pending')
                                                        <span class="badge badge-warning">Menunggu</span>
                                                        @break
                                                    @case('approved')
                                                        <span class="badge badge-success">Disetujui</span>
                                                        @break
                                                    @case('rejected')
                                                        <span class="badge badge-danger">Ditolak</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge badge-info">Selesai</span>
                                                        @break
                                                    @default
                                                        <span class="badge badge-secondary">{{ ucfirst($reservation->status) }}</span>
                                                @endswitch
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($user->reservations->count() > 5)
                            <div class="text-center mt-3">
                                <a href="#" class="btn btn-sm btn-outline-primary">Lihat Semua Reservasi</a>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-calendar-times fa-3x mb-3"></i>
                            <p>Belum ada riwayat reservasi</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Account Status & Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Status Akun & Tindakan</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="font-weight-bold">Status Akun</h6>
                            <p class="mb-2">
                                <strong>Status:</strong> 
                                <span class="badge badge-success">Aktif</span>
                            </p>
                            <p class="mb-2">
                                <strong>Email Verified:</strong>
                                @if($user->email_verified_at)
                                    <span class="badge badge-success">Terverifikasi</span>
                                    <br><small class="text-muted">{{ $user->email_verified_at->format('d/m/Y H:i') }}</small>
                                @else
                                    <span class="badge badge-warning">Belum Terverifikasi</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="font-weight-bold">Tindakan</h6>
                            <div class="btn-group-vertical d-grid gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit Pengguna
                                </a>
                                @if(!$user->email_verified_at)
                                <button type="button" class="btn btn-info btn-sm">
                                    <i class="fas fa-envelope"></i> Kirim Verifikasi Email
                                </button>
                                @endif
                                <button type="button" class="btn btn-danger btn-sm" 
                                        data-toggle="modal" 
                                        data-target="#deleteModal">
                                    <i class="fas fa-trash"></i> Hapus Pengguna
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan.
                </div>
                <p>Apakah Anda yakin ingin menghapus pengguna <strong>{{ $user->name }}</strong>?</p>
                
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">Detail Pengguna:</h6>
                        <ul class="mb-0">
                            <li><strong>NIM/NIP:</strong> {{ $user->nim_nip }}</li>
                            <li><strong>Email:</strong> {{ $user->email }}</li>
                            <li><strong>Role:</strong> {{ ucfirst($user->role) }}</li>
                            <li><strong>Total Reservasi:</strong> {{ $user->reservations->count() }}</li>
                        </ul>
                    </div>
                </div>
                
                <p class="mt-3 mb-0">
                    <small class="text-danger">
                        <strong>Catatan:</strong> Menghapus pengguna ini akan menghapus semua data terkait termasuk riwayat reservasi.
                    </small>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Batal
                </button>
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Hapus Pengguna
                    </button>
                </form>
            </div>
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