@extends('layouts.app')

@section('title', 'Detail Reservasi')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-calendar-check me-2"></i>Detail Reservasi
        <span class="badge bg-{{ 
            $reservation->status === 'approved' ? 'success' : 
            ($reservation->status === 'pending' ? 'warning' : 
            ($reservation->status === 'rejected' ? 'danger' : 
            ($reservation->status === 'completed' ? 'info' : 'secondary'))) 
        }} ms-2">
            {{ ucfirst($reservation->status) }}
        </span>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('user.reservations.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
            @if($reservation->status === 'pending')
            <a href="{{ route('user.reservations.edit', $reservation) }}" class="btn btn-outline-warning">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <!-- Main Information Card -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Informasi Reservasi
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong><i class="fas fa-hashtag me-1"></i>Kode Reservasi:</strong>
                    </div>
                    <div class="col-sm-8">
                        <span class="font-monospace">{{ $reservation->reservation_code }}</span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong><i class="fas fa-flask me-1"></i>Laboratorium:</strong>
                    </div>
                    <div class="col-sm-8">
                        {{ $reservation->laboratory->name }}
                        <br>
                        <small class="text-muted">{{ $reservation->laboratory->code }}</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong><i class="fas fa-calendar me-1"></i>Tanggal:</strong>
                    </div>
                    <div class="col-sm-8">
                        {{ \Carbon\Carbon::parse($reservation->reservation_date)->format('l, d F Y') }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong><i class="fas fa-clock me-1"></i>Waktu:</strong>
                    </div>
                    <div class="col-sm-8">
                        {{ \Carbon\Carbon::parse($reservation->start_time)->format('H:i') }} - 
                        {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}
                        <small class="text-muted">
                            ({{ \Carbon\Carbon::parse($reservation->start_time)->diffInMinutes(\Carbon\Carbon::parse($reservation->end_time)) }} menit)
                        </small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong><i class="fas fa-users me-1"></i>Jumlah Peserta:</strong>
                    </div>
                    <div class="col-sm-8">
                        {{ $reservation->participant_count }} orang
                    </div>
                </div>

                @if($reservation->laboratory->location)
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong><i class="fas fa-map-marker-alt me-1"></i>Lokasi:</strong>
                    </div>
                    <div class="col-sm-8">
                        {{ $reservation->laboratory->location }}
                    </div>
                </div>
                @endif

                @if($reservation->purpose)
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong><i class="fas fa-bullseye me-1"></i>Tujuan:</strong>
                    </div>
                    <div class="col-sm-8">
                        <p class="mb-0">{{ $reservation->purpose }}</p>
                    </div>
                </div>
                @endif

                @if($reservation->description)
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong><i class="fas fa-align-left me-1"></i>Deskripsi:</strong>
                    </div>
                    <div class="col-sm-8">
                        <p class="mb-0">{{ $reservation->description }}</p>
                    </div>
                </div>
                @endif

                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong><i class="fas fa-calendar-plus me-1"></i>Dibuat:</strong>
                    </div>
                    <div class="col-sm-8">
                        {{ $reservation->created_at->format('d F Y, H:i') }}
                        <small class="text-muted">({{ $reservation->created_at->diffForHumans() }})</small>
                    </div>
                </div>

                @if($reservation->updated_at != $reservation->created_at)
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong><i class="fas fa-edit me-1"></i>Diperbarui:</strong>
                    </div>
                    <div class="col-sm-8">
                        {{ $reservation->updated_at->format('d F Y, H:i') }}
                        <small class="text-muted">({{ $reservation->updated_at->diffForHumans() }})</small>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Admin Notes -->
        @if($reservation->admin_notes && in_array($reservation->status, ['approved', 'rejected']))
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-sticky-note me-2"></i>Catatan Admin
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-{{ $reservation->status === 'approved' ? 'success' : 'danger' }} mb-0">
                    <p class="mb-0">{{ $reservation->admin_notes }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Status Timeline -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>Status Timeline
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Reservasi Dibuat</h6>
                            <small class="text-muted">{{ $reservation->created_at->format('d M Y, H:i') }}</small>
                        </div>
                    </div>

                    @if($reservation->status === 'approved')
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Reservasi Disetujui</h6>
                            <small class="text-muted">
                                @if($reservation->approved_at)
                                    {{ \Carbon\Carbon::parse($reservation->approved_at)->format('d M Y, H:i') }}
                                @else
                                    {{ $reservation->updated_at->format('d M Y, H:i') }}
                                @endif
                            </small>
                            @if($reservation->approved_by)
                            <div>
                                <small class="text-muted">Oleh: {{ $reservation->approvedBy->name ?? 'Admin' }}</small>
                            </div>
                            @endif
                        </div>
                    </div>
                    @elseif($reservation->status === 'rejected')
                    <div class="timeline-item">
                        <div class="timeline-marker bg-danger"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Reservasi Ditolak</h6>
                            <small class="text-muted">{{ $reservation->updated_at->format('d M Y, H:i') }}</small>
                        </div>
                    </div>
                    @elseif($reservation->status === 'cancelled')
                    <div class="timeline-item">
                        <div class="timeline-marker bg-secondary"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Reservasi Dibatalkan</h6>
                            <small class="text-muted">{{ $reservation->updated_at->format('d M Y, H:i') }}</small>
                        </div>
                    </div>
                    @elseif($reservation->status === 'completed')
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Reservasi Selesai</h6>
                            <small class="text-muted">{{ $reservation->updated_at->format('d M Y, H:i') }}</small>
                        </div>
                    </div>
                    @else
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Menunggu Persetujuan</h6>
                            <small class="text-muted">Status saat ini</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Laboratory Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-flask me-2"></i>Informasi Laboratorium
                </h5>
            </div>
            <div class="card-body">
                <h6>{{ $reservation->laboratory->name }}</h6>
                <p class="text-muted small">{{ $reservation->laboratory->code }}</p>
                
                @if($reservation->laboratory->description)
                <p class="small">{{ $reservation->laboratory->description }}</p>
                @endif

                @if($reservation->laboratory->capacity)
                <div class="mb-2">
                    <small class="text-muted">Kapasitas:</small>
                    <span class="small">{{ $reservation->laboratory->capacity }} orang</span>
                </div>
                @endif

                @if($reservation->laboratory->facilities)
                <div class="mb-2">
                    <small class="text-muted">Fasilitas:</small>
                    <div class="small">{{ $reservation->laboratory->facilities }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        @if($reservation->status === 'pending')
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-cogs me-2"></i>Aksi
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('user.reservations.edit', $reservation) }}" 
                       class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Edit Reservasi
                    </a>
                    
                    <form action="{{ route('user.reservations.destroy', $reservation) }}" 
                          method="POST" class="d-inline"
                          onsubmit="return confirm('Yakin ingin membatalkan reservasi ini? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-times me-2"></i>Batalkan Reservasi
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.25rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content h6 {
    font-size: 0.9rem;
    font-weight: 600;
}

.badge {
    font-size: 0.8rem;
}

.font-monospace {
    font-family: 'Courier New', Courier, monospace;
    font-weight: 600;
    color: #495057;
}

@media (max-width: 768px) {
    .timeline {
        padding-left: 1.5rem;
    }
    
    .timeline-marker {
        left: -1.5rem;
    }
}
</style>
@endpush