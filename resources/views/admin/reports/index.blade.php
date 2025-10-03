@extends('layouts.admin')

@section('title', 'Laporan')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Laporan</h1>
        
        <!-- Export Buttons - Tanpa Filter -->
        <div class="btn-group">
            <a href="{{ route('admin.reports.excel') }}" class="btn btn-success" target="_blank">
                <i class="fas fa-file-excel"></i> Export Excel (Semua)
            </a>
            <a href="{{ route('admin.reports.pdf') }}" class="btn btn-danger" target="_blank">
                <i class="fas fa-file-pdf"></i> Export PDF (Semua)
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="start_date">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="{{ request('start_date') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="end_date">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="laboratory_id">Laboratorium</label>
                            <select class="form-control" id="laboratory_id" name="laboratory_id">
                                <option value="">Semua Laboratorium</option>
                                @if(isset($laboratories))
                                    @foreach($laboratories as $lab)
                                        <option value="{{ $lab->id }}" 
                                                {{ request('laboratory_id') == $lab->id ? 'selected' : '' }}>
                                            {{ $lab->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-refresh"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Export with Current Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-success">Export dengan Filter Saat Ini</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-3">Export laporan dengan filter yang sedang diterapkan:</p>
                    <div class="btn-group">
                        <a href="{{ route('admin.reports.excel', request()->all()) }}" class="btn btn-success" target="_blank">
                            <i class="fas fa-file-excel"></i> Export Excel dengan Filter
                        </a>
                        <a href="{{ route('admin.reports.pdf', request()->all()) }}" class="btn btn-danger" target="_blank">
                            <i class="fas fa-file-pdf"></i> Export PDF dengan Filter
                        </a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg-light p-3 rounded">
                        <small class="text-muted">
                            <strong>Filter yang aktif:</strong><br>
                            @if(request()->anyFilled(['start_date', 'end_date', 'laboratory_id', 'status']))
                                @if(request('start_date'))
                                    <span class="badge badge-primary">Dari: {{ date('d/m/Y', strtotime(request('start_date'))) }}</span>
                                @endif
                                @if(request('end_date'))
                                    <span class="badge badge-primary">Sampai: {{ date('d/m/Y', strtotime(request('end_date'))) }}</span>
                                @endif
                                @if(request('laboratory_id'))
                                    <span class="badge badge-info">Lab: {{ $laboratories->find(request('laboratory_id'))->name ?? 'Unknown' }}</span>
                                @endif
                                @if(request('status'))
                                    <span class="badge badge-warning">Status: {{ ucfirst(request('status')) }}</span>
                                @endif
                            @else
                                <em>Tidak ada filter aktif - Export semua data</em>
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Reservasi
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $totalReservations ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Reservasi Selesai
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $completedReservations ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Reservasi Pending
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $pendingReservations ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Laboratorium
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $totalLaboratories ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-flask fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reservations Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Data Reservasi</h6>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('admin.reports.excel', request()->all()) }}" class="btn btn-success" target="_blank">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a href="{{ route('admin.reports.pdf', request()->all()) }}" class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Tanggal</th>
                            <th width="12%">Waktu</th>
                            <th width="15%">Laboratorium</th>
                            <th width="15%">Nama Peminjam</th>
                            <th width="20%">Keperluan</th>
                            <th width="10%">Status</th>
                            <th width="13%">Dibuat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservations ?? [] as $index => $reservation)
                            <tr>
                                <td>{{ ($reservations->currentPage() - 1) * $reservations->perPage() + $index + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($reservation->reservation_date)->format('d/m/Y') }}</td>
                                <td>
                                    <small>
                                        {{ $reservation->start_time }} - {{ $reservation->end_time }}
                                    </small>
                                </td>
                                <td>
                                    <span class="font-weight-bold">{{ $reservation->laboratory->name ?? '-' }}</span>
                                </td>
                                <td>{{ $reservation->user->name ?? $reservation->name ?? '-' }}</td>
                                <td>
                                    <span data-toggle="tooltip" title="{{ $reservation->purpose }}">
                                        {{ Str::limit($reservation->purpose, 40) }}
                                    </span>
                                </td>
                                <td>
                                    @switch($reservation->status)
                                        @case('pending')
                                            <span class="badge badge-warning">Pending</span>
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
                                        @case('cancelled')
                                            <span class="badge badge-secondary">Dibatalkan</span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">{{ ucfirst($reservation->status) }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <small>{{ $reservation->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>Tidak ada data reservasi yang ditemukan</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($reservations) && $reservations->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Menampilkan {{ $reservations->firstItem() ?? 0 }} sampai {{ $reservations->lastItem() ?? 0 }} 
                        dari {{ $reservations->total() ?? 0 }} hasil
                    </div>
                    <div>
                        {{ $reservations->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Laboratory Usage Chart -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Grafik Penggunaan Laboratorium</h6>
        </div>
        <div class="card-body">
            <div class="chart-area">
                <canvas id="laboratoryChart"></canvas>
            </div>
            @if(empty($chartData))
                <div class="text-center text-muted py-4">
                    <i class="fas fa-chart-bar fa-3x mb-3"></i>
                    <p>Tidak ada data untuk ditampilkan dalam grafik</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    if (typeof $ !== 'undefined') {
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    // Chart initialization
    const chartData = @json($chartData ?? []);
    
    if (chartData && Object.keys(chartData).length > 0) {
        const ctx = document.getElementById('laboratoryChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(chartData),
                datasets: [{
                    label: 'Jumlah Reservasi',
                    data: Object.values(chartData),
                    backgroundColor: 'rgba(78, 115, 223, 0.8)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection