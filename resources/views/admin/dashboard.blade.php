@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-tachometer-alt me-2"></i>Dashboard Admin</h1>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total Laboratorium</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['total_labs'] }}</h3>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-flask fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total Pengguna</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['total_users'] }}</h3>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-users fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Reservasi Pending</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['pending_reservations'] }}</h3>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-clock fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Reservasi Hari Ini</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['today_reservations'] }}</h3>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-calendar-day fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Penggunaan Laboratorium</h5>
            </div>
            <div class="card-body">
                <canvas id="labUsageChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Status Reservasi</h5>
            </div>
            <div class="card-body">
                <canvas id="reservationStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Reservations -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Reservasi Terbaru</h5>
    </div>
    <div class="card-body">
        @if($recentReservations->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Laboratorium</th>
                            <th>Pemohon</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentReservations as $reservation)
                        <tr>
                            <td>{{ $reservation->reservation_date->format('d/m/Y') }}</td>
                            <td>{{ $reservation->laboratory->name }}</td>
                            <td>
                                {{ $reservation->user->name }}
                                <small class="text-muted d-block">{{ ucfirst($reservation->user->role) }}</small>
                            </td>
                            <td>{{ $reservation->start_time }} - {{ $reservation->end_time }}</td>
                            <td>
                                <span class="badge status-{{ $reservation->status }}">
                                    {{ ucfirst($reservation->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.reservations.show', $reservation) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">Belum ada reservasi</p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fetch chart data
    fetch('{{ route("admin.api.chart-data") }}')
        .then(response => response.json())
        .then(data => {
            // Lab Usage Chart
            const labUsageCtx = document.getElementById('labUsageChart').getContext('2d');
            new Chart(labUsageCtx, {
                type: 'bar',
                data: {
                    labels: data.lab_usage.map(lab => lab.name),
                    datasets: [{
                        label: 'Jumlah Reservasi',
                        data: data.lab_usage.map(lab => lab.reservations_count),
                        backgroundColor: 'rgba(52, 152, 219, 0.8)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Reservation Status Chart
            const statusCtx = document.getElementById('reservationStatusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'Approved', 'Rejected'],
                    datasets: [{
                        data: [
                            {{ $stats['pending_reservations'] }},
                            {{ $stats['today_reservations'] }},
                            5 // placeholder for rejected
                        ],
                        backgroundColor: [
                            '#f39c12',
                            '#27ae60',
                            '#e74c3c'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true
                }
            });
        });
});
</script>
@endsection