@extends('layouts.app')

@section('title', 'Detail Notifikasi')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Back Button -->
            <div class="mb-3">
                <a href="{{ route('user.notifications.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Kembali ke Daftar Notifikasi
                </a>
            </div>

            <!-- Notification Detail Card -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            @php
                                $iconClass = $notification->data['icon'] ?? 'fas fa-bell';
                                $colorClass = match($notification->data['type'] ?? 'general') {
                                    'reservation_approved' => 'text-success',
                                    'reservation_rejected' => 'text-danger',
                                    default => 'text-primary'
                                };
                            @endphp
                            <div class="notification-icon-wrapper me-3">
                                <i class="{{ $iconClass }} {{ $colorClass }} fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $notification->data['title'] ?? 'Notifikasi' }}</h5>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $notification->created_at->format('d F Y, H:i:s') }}
                                    ({{ $notification->created_at->diffForHumans() }})
                                </small>
                            </div>
                        </div>
                        
                        @if($notification->read_at)
                            <span class="badge bg-secondary">Sudah Dibaca</span>
                        @else
                            <span class="badge bg-primary">Baru</span>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <!-- Main Message -->
                    <div class="mb-4">
                        <h6>Pesan:</h6>
                        <p class="lead">{{ $notification->data['message'] ?? 'Tidak ada pesan.' }}</p>
                    </div>

                    <!-- Reservation Details -->
                    @if($notification->data['type'] === 'reservation_approved' || $notification->data['type'] === 'reservation_rejected')
                        <div class="mb-4">
                            <h6>Detail Reservasi:</h6>
                            <div class="row">
                                @if(isset($notification->data['laboratory_name']))
                                    <div class="col-md-6 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body py-2">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-flask text-primary me-2"></i>
                                                    <div>
                                                        <small class="text-muted">Laboratorium</small>
                                                        <div class="fw-semibold">{{ $notification->data['laboratory_name'] }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if(isset($notification->data['reservation_date']))
                                    <div class="col-md-6 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body py-2">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-calendar text-info me-2"></i>
                                                    <div>
                                                        <small class="text-muted">Tanggal</small>
                                                        <div class="fw-semibold">
                                                            {{ \Carbon\Carbon::parse($notification->data['reservation_date'])->format('d F Y') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if(isset($notification->data['start_time']) && isset($notification->data['end_time']))
                                    <div class="col-md-6 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body py-2">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-clock text-warning me-2"></i>
                                                    <div>
                                                        <small class="text-muted">Waktu</small>
                                                        <div class="fw-semibold">
                                                            {{ $notification->data['start_time'] }} - {{ $notification->data['end_time'] }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if(isset($notification->data['purpose']))
                                    <div class="col-md-6 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body py-2">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-info-circle text-secondary me-2"></i>
                                                    <div>
                                                        <small class="text-muted">Keperluan</small>
                                                        <div class="fw-semibold">{{ $notification->data['purpose'] }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Status Information -->
                        <div class="mb-4">
                            <h6>Status:</h6>
                            @if($notification->data['type'] === 'reservation_approved')
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Reservasi Disetujui</strong>
                                    <p class="mb-0 mt-2">
                                        Selamat! Reservasi laboratorium Anda telah disetujui. 
                                        Silakan datang sesuai jadwal yang telah ditentukan.
                                    </p>
                                </div>
                            @elseif($notification->data['type'] === 'reservation_rejected')
                                <div class="alert alert-danger">
                                    <i class="fas fa-times-circle me-2"></i>
                                    <strong>Reservasi Ditolak</strong>
                                    <p class="mb-0 mt-2">
                                        Mohon maaf, reservasi laboratorium Anda ditolak. 
                                        Anda dapat membuat reservasi baru dengan waktu atau laboratorium yang berbeda.
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Admin Notes -->
                        @if(isset($notification->data['admin_notes']) && $notification->data['admin_notes'])
                            <div class="mb-4">
                                <h6>Catatan Admin:</h6>
                                <div class="alert alert-info">
                                    <i class="fas fa-comment me-2"></i>
                                    {{ $notification->data['admin_notes'] }}
                                </div>
                            </div>
                        @elseif(isset($notification->data['reason']) && $notification->data['reason'])
                            <div class="mb-4">
                                <h6>Alasan Penolakan:</h6>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    {{ $notification->data['reason'] }}
                                </div>
                            </div>
                        @endif
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @if(isset($notification->data['reservation_id']))
                                <a href="{{ route('user.reservations.show', $notification->data['reservation_id']) }}" class="btn btn-primary">
                                    <i class="fas fa-eye me-1"></i>
                                    Lihat Detail Reservasi
                                </a>
                            @endif
                        </div>

                        <div>
                            <form action="{{ route('user.notifications.destroy', $notification->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Yakin ingin menghapus notifikasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="fas fa-trash me-1"></i>
                                    Hapus Notifikasi
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-muted">
                    <div class="row">
                        <div class="col-md-6">
                            <small>
                                <i class="fas fa-calendar-plus me-1"></i>
                                <strong>Dibuat:</strong> {{ $notification->created_at->format('d F Y, H:i:s') }}
                            </small>
                        </div>
                        <div class="col-md-6 text-md-end">
                            @if($notification->read_at)
                                <small>
                                    <i class="fas fa-eye me-1"></i>
                                    <strong>Dibaca:</strong> {{ $notification->read_at->format('d F Y, H:i:s') }}
                                </small>
                            @else
                                <small>
                                    <i class="fas fa-eye-slash me-1"></i>
                                    <strong>Belum dibaca</strong>
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Actions -->
            @if($notification->data['type'] === 'reservation_rejected')
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-lightbulb me-2 text-warning"></i>
                            Langkah Selanjutnya
                        </h6>
                        <p class="card-text">
                            Reservasi Anda ditolak, tetapi Anda masih dapat:
                        </p>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('user.reservations.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>
                                Buat Reservasi Baru
                            </a>
                            <a href="{{ route('user.laboratories.index') }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-flask me-1"></i>
                                Lihat Laboratorium Lain
                            </a>
                            <a href="{{ route('user.reservations.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-list me-1"></i>
                                Lihat Reservasi Saya
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.notification-icon-wrapper {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
    border-radius: 50%;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-radius: 0.75rem;
}

.alert {
    border-radius: 0.5rem;
}
</style>
@endpush