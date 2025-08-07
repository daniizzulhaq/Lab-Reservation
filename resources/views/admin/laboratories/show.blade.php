@extends('layouts.admin')

@section('title', 'Detail Laboratorium - ' . $laboratory->name)

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-flask me-2"></i>Detail Laboratorium
        </h1>
        <div class="btn-group" role="group">
            <a href="{{ route('admin.laboratories.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
            <a href="{{ route('admin.laboratories.edit', $laboratory) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
            <button type="button" class="btn btn-danger btn-sm" 
                    data-toggle="modal" 
                    data-target="#deleteModal">
                <i class="fas fa-trash me-1"></i>Hapus
            </button>
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- Main Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>Informasi Utama
                    </h6>
                </div>
                <div class="card-body">
                    @if($laboratory->image)
                    <div class="mb-4 text-center">
                        <img src="{{ asset('storage/' . $laboratory->image) }}" 
                             alt="{{ $laboratory->name }}" 
                             class="img-fluid rounded shadow"
                             style="max-height: 300px; object-fit: cover;">
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Nama Laboratorium:</strong>
                            <div class="mt-1 h5 text-dark">{{ $laboratory->name }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Kode Laboratorium:</strong>
                            <div class="mt-1">
                                <code class="bg-light px-2 py-1 rounded">{{ $laboratory->code ?? '-' }}</code>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Kapasitas:</strong>
                            <div class="mt-1">
                                <span class="badge badge-info badge-lg">
                                    <i class="fas fa-users me-1"></i>{{ $laboratory->capacity }} orang
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Status:</strong>
                            <div class="mt-1">
                                @if($laboratory->is_active ?? ($laboratory->status == 'aktif'))
                                    <span class="badge badge-success badge-lg">
                                        <i class="fas fa-check-circle me-1"></i>Aktif
                                    </span>
                                @elseif(isset($laboratory->status) && $laboratory->status == 'maintenance')
                                    <span class="badge badge-warning badge-lg">
                                        <i class="fas fa-wrench me-1"></i>Maintenance
                                    </span>
                                @else
                                    <span class="badge badge-danger badge-lg">
                                        <i class="fas fa-times-circle me-1"></i>Tidak Aktif
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($laboratory->location)
                    <div class="mb-3">
                        <strong class="text-muted">Lokasi:</strong>
                        <div class="mt-1">
                            <i class="fas fa-map-marker-alt text-muted me-1"></i>{{ $laboratory->location }}
                        </div>
                    </div>
                    @endif

                    @if($laboratory->facilities)
                    <div class="mb-3">
                        <strong class="text-muted">Fasilitas:</strong>
                        <div class="mt-2">
                            <div class="bg-light p-3 rounded">
                                {!! nl2br(e($laboratory->facilities)) !!}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($laboratory->description)
                    <div class="mb-3">
                        <strong class="text-muted">Deskripsi:</strong>
                        <div class="mt-2">
                            <div class="bg-light p-3 rounded">
                                {!! nl2br(e($laboratory->description)) !!}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Information -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cogs me-2"></i>Aksi Cepat
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.laboratories.edit', $laboratory) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit me-1"></i>Edit Laboratorium
                        </a>
                        @if(Route::has('admin.reservations.index'))
                        <a href="{{ route('admin.reservations.index') }}?laboratory={{ $laboratory->id }}" class="btn btn-info btn-sm">
                            <i class="fas fa-calendar-alt me-1"></i>Lihat Reservasi
                        </a>
                        @endif
                        @if(Route::has('admin.schedules.index'))
                        <a href="{{ route('admin.schedules.index') }}?laboratory={{ $laboratory->id }}" class="btn btn-success btn-sm">
                            <i class="fas fa-clock me-1"></i>Kelola Jadwal
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-2"></i>Statistik
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Total Reservasi:</span>
                            <span class="badge badge-primary">
                                {{ $laboratory->reservations_count ?? 0 }}
                            </span>
                        </div>
                    </div>
                    @if(isset($laboratory->active_reservations_count))
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Reservasi Aktif:</span>
                            <span class="badge badge-success">
                                {{ $laboratory->active_reservations_count }}
                            </span>
                        </div>
                    </div>
                    @endif
                    @if(isset($laboratory->utilization_rate))
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Tingkat Utilitas:</span>
                            <span class="badge badge-info">
                                {{ number_format($laboratory->utilization_rate, 1) }}%
                            </span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- System Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-database me-2"></i>Informasi Sistem
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">ID Laboratorium:</small>
                        <div><code>#{{ $laboratory->id }}</code></div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Dibuat pada:</small>
                        <div>{{ $laboratory->created_at->format('d M Y, H:i') }}</div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Terakhir diupdate:</small>
                        <div>{{ $laboratory->updated_at->format('d M Y, H:i') }}</div>
                    </div>
                    @if($laboratory->updated_at != $laboratory->created_at)
                    <div class="mb-3">
                        <small class="text-muted">Diupdate:</small>
                        <div class="text-info">{{ $laboratory->updated_at->diffForHumans() }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reservations (if applicable) -->
    @if(isset($recentReservations) && $recentReservations->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-history me-2"></i>Reservasi Terbaru
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Pemesan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentReservations as $reservation)
                        <tr>
                            <td>{{ $reservation->date ? \Carbon\Carbon::parse($reservation->date)->format('d M Y') : '-' }}</td>
                            <td>{{ $reservation->start_time ?? '-' }} - {{ $reservation->end_time ?? '-' }}</td>
                            <td>{{ $reservation->user->name ?? $reservation->reserver_name ?? '-' }}</td>
                            <td>
                                @if($reservation->status == 'approved')
                                    <span class="badge badge-success">Disetujui</span>
                                @elseif($reservation->status == 'pending')
                                    <span class="badge badge-warning">Menunggu</span>
                                @elseif($reservation->status == 'rejected')
                                    <span class="badge badge-danger">Ditolak</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($reservation->status ?? 'Unknown') }}</span>
                                @endif
                            </td>
                            <td>
                                @if(Route::has('admin.reservations.show'))
                                <a href="{{ route('admin.reservations.show', $reservation) }}" 
                                   class="btn btn-info btn-xs" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(Route::has('admin.reservations.index'))
            <div class="text-center">
                <a href="{{ route('admin.reservations.index') }}?laboratory={{ $laboratory->id }}" 
                   class="btn btn-outline-primary btn-sm">
                    Lihat Semua Reservasi
                </a>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus Laboratorium</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center">
                    Apakah Anda yakin ingin menghapus laboratorium <strong>"{{ $laboratory->name }}"</strong>?
                </p>
                <div class="alert alert-warning">
                    <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data yang terkait dengan laboratorium ini.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Batal
                </button>
                <form action="{{ route('admin.laboratories.destroy', $laboratory) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.badge-lg {
    font-size: 0.9em;
    padding: 0.5rem 0.75rem;
}

.btn-xs {
    padding: 0.25rem 0.4rem;
    font-size: 0.75rem;
    line-height: 1.2;
    border-radius: 0.2rem;
}

.img-fluid {
    transition: transform 0.2s;
}

.img-fluid:hover {
    transform: scale(1.05);
    cursor: pointer;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image modal functionality
    const labImage = document.querySelector('.img-fluid');
    if (labImage) {
        labImage.addEventListener('click', function() {
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $laboratory->name }}</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="${this.src}" alt="${this.alt}" class="img-fluid">
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            $(modal).modal('show');
            $(modal).on('hidden.bs.modal', function() {
                document.body.removeChild(modal);
            });
        });
    }
    
    // Auto-refresh statistics (optional)
    if (typeof refreshStats === 'function') {
        setInterval(refreshStats, 30000); // Refresh every 30 seconds
    }
});
</script>
@endpush