@extends('layouts.app')

@section('title', 'Daftar Reservasi')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-calendar-check me-2"></i>Daftar Reservasi</h1>
</div>

<!-- Search and Filter Form -->
<div class="search-form">
    <form method="GET" action="{{ route('user.reservations.index') }}">
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="search" class="form-label">Cari Reservasi</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Kode reservasi atau laboratorium...">
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <label for="date_from" class="form-label">Dari Tanggal</label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2 mb-3">
                <label for="date_to" class="form-label">Sampai Tanggal</label>
                <input type="date" class="form-control" id="date_to" name="date_to" 
                       value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3 mb-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i>Cari
                </button>
                <a href="{{ route('user.reservations.index') }}" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-times me-1"></i>Reset
                </a>
                <a href="{{ route('user.reservations.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>Buat Reservasi
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Reservations Cards -->
<div class="row">
    @forelse($reservations as $reservation)
    <div class="col-lg-6 col-md-12 mb-4">
        <div class="card reservation-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">
                        <i class="fas fa-hashtag me-1"></i>{{ $reservation->reservation_code }}
                    </h6>
                    <small class="text-muted">{{ $reservation->created_at->format('d M Y, H:i') }}</small>
                </div>
                <span class="badge bg-{{ 
                    $reservation->status === 'approved' ? 'success' : 
                    ($reservation->status === 'pending' ? 'warning' : 
                    ($reservation->status === 'rejected' ? 'danger' : 
                    ($reservation->status === 'completed' ? 'info' : 'secondary'))) 
                }}">
                    {{ ucfirst($reservation->status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="card-title">
                        <i class="fas fa-flask me-2"></i>{{ $reservation->laboratory->name }}
                    </h6>
                    <small class="text-muted">{{ $reservation->laboratory->code }}</small>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small"><i class="fas fa-calendar me-1"></i>Tanggal:</span>
                            <span class="small fw-bold">{{ \Carbon\Carbon::parse($reservation->reservation_date)->format('d M Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small"><i class="fas fa-clock me-1"></i>Waktu:</span>
                            <span class="small">{{ substr($reservation->start_time, 0, 5) }} - {{ substr($reservation->end_time, 0, 5) }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small"><i class="fas fa-users me-1"></i>Peserta:</span>
                            <span class="small">{{ $reservation->participant_count }} orang</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small"><i class="fas fa-map-marker-alt me-1"></i>Lokasi:</span>
                            <span class="small">{{ $reservation->laboratory->location ?: '-' }}</span>
                        </div>
                    </div>
                </div>

                @if($reservation->purpose)
                <div class="mb-3">
                    <span class="text-muted small"><i class="fas fa-bullseye me-1"></i>Tujuan:</span>
                    <p class="small mb-0 mt-1">{{ Str::limit($reservation->purpose, 100) }}</p>
                </div>
                @endif

                @if($reservation->notes && $reservation->status !== 'pending')
                <div class="mb-3">
                    <span class="text-muted small"><i class="fas fa-sticky-note me-1"></i>Catatan Admin:</span>
                    <p class="small mb-0 mt-1 text-{{ $reservation->status === 'approved' ? 'success' : 'danger' }}">
                        {{ $reservation->notes }}
                    </p>
                </div>
                @endif
            </div>
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="btn-group" role="group">
                        <a href="{{ route('user.reservations.show', $reservation) }}" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>Detail
                        </a>
                        @if($reservation->status === 'pending')
                        <a href="{{ route('user.reservations.edit', $reservation) }}" 
                           class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        @endif
                    </div>
                    
                    @if($reservation->status === 'pending')
                    <form action="{{ route('user.reservations.destroy', $reservation) }}" 
                          method="POST" class="d-inline"
                          onsubmit="return confirm('Yakin ingin membatalkan reservasi ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-times me-1"></i>Batalkan
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Belum ada reservasi</h5>
                <p class="text-muted">Anda belum membuat reservasi laboratorium</p>
                <a href="{{ route('user.reservations.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Buat Reservasi Pertama
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($reservations->hasPages())
<div class="d-flex justify-content-center">
    {{ $reservations->appends(request()->query())->links() }}
</div>
@endif

@endsection

@push('styles')
<style>
.reservation-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.reservation-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.search-form {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 0.375rem;
    margin-bottom: 2rem;
}

.badge {
    font-size: 0.75rem;
}
</style>
@endpush