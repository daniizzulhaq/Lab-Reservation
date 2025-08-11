@extends('layouts.app')

@section('title', 'Dashboard User')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-tachometer-alt me-2 text-primary"></i>Dashboard
            <small class="text-muted">- {{ ucfirst(auth()->user()->role) }}</small>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('user.reservations.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Buat Reservasi Baru
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Reservasi
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['my_reservations'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="bg-primary text-white rounded-circle p-3">
                                <i class="fas fa-calendar fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Menunggu Persetujuan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['pending_reservations'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="bg-warning text-white rounded-circle p-3">
                                <i class="fas fa-clock fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Disetujui
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['approved_reservations'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="bg-success text-white rounded-circle p-3">
                                <i class="fas fa-check-circle fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Mendatang
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['upcoming_reservations'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="bg-info text-white rounded-circle p-3">
                                <i class="fas fa-calendar-plus fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar and Upcoming Reservations -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Kalender Reservasi
                    </h5>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>Reservasi Mendatang
                    </h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if($upcomingReservations->count() > 0)
                        @foreach($upcomingReservations as $reservation)
                        <div class="d-flex align-items-start mb-3 p-3 bg-light rounded">
                            <div class="flex-shrink-0 me-3">
                                <div class="bg-primary text-white rounded text-center p-2" style="min-width: 50px;">
                                    <div class="fw-bold">{{ $reservation->reservation_date->format('d') }}</div>
                                    <small>{{ $reservation->reservation_date->format('M') }}</small>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $reservation->laboratory->name }}</h6>
                                <p class="mb-1 text-muted small">{{ Str::limit($reservation->purpose, 50) }}</p>
                                <small class="text-primary">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ substr($reservation->start_time, 0, 5) }} - {{ substr($reservation->end_time, 0, 5) }}
                                </small>
                            </div>
                        </div>
                        @endforeach
                        <div class="text-center mt-3">
                            <a href="{{ route('user.reservations.index') }}" class="btn btn-outline-primary btn-sm">
                                Lihat Semua Reservasi
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada reservasi mendatang</p>
                            <a href="{{ route('user.reservations.create') }}" class="btn btn-primary btn-sm">
                                Buat Reservasi
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reservations -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="fas fa-history me-2"></i>Riwayat Reservasi Terbaru
            </h5>
        </div>
        <div class="card-body">
            @if($recentReservations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Laboratorium</th>
                                <th>Waktu</th>
                                <th>Tujuan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentReservations as $reservation)
                            <tr>
                                <td>{{ $reservation->reservation_date->format('d/m/Y') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-flask text-primary me-2"></i>
                                        {{ $reservation->laboratory->name }}
                                    </div>
                                </td>
                                <td>{{ substr($reservation->start_time, 0, 5) }} - {{ substr($reservation->end_time, 0, 5) }}</td>
                                <td>{{ Str::limit($reservation->purpose, 30) }}</td>
                                <td>
                                    <span class="badge 
                                        @if($reservation->status === 'approved') bg-success
                                        @elseif($reservation->status === 'pending') bg-warning text-dark
                                        @elseif($reservation->status === 'rejected') bg-danger
                                        @elseif($reservation->status === 'completed') bg-info
                                        @else bg-secondary
                                        @endif">
                                        {{ ucfirst($reservation->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('user.reservations.show', $reservation) }}" 
                                           class="btn btn-outline-primary" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($reservation->status === 'pending')
                                            <a href="{{ route('user.reservations.edit', $reservation) }}" 
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                    </div>
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
                    <a href="{{ route('user.reservations.create') }}" class="btn btn-primary">
                        Buat Reservasi Pertama
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
<<<<<<< HEAD
=======
    
    // Check if FullCalendar is loaded
    if (typeof FullCalendar === 'undefined') {
        console.error('FullCalendar library is not loaded!');
        calendarEl.innerHTML = '<div class="alert alert-danger">Error: FullCalendar library tidak dapat dimuat!</div>';
        return;
    }
    
>>>>>>> 92b809e (notifikasi)
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        height: 400,
        events: {
<<<<<<< HEAD
            url: '{{ route("user.api.calendar-events") }}',
            failure: function() {
                alert('Gagal memuat data kalender!');
=======
            url: '{{ route("user.api.dashboard-calendar-events") }}',
            failure: function(error) {
                console.error('Calendar error:', error);
                alert('Gagal memuat data kalender! Silakan refresh halaman.');
>>>>>>> 92b809e (notifikasi)
            }
        },
        eventClick: function(info) {
            const event = info.event;
            const props = event.extendedProps;
            
            let content = `Laboratorium: ${props.laboratory}\n`;
<<<<<<< HEAD
            content += `Waktu: ${event.start.toLocaleTimeString()} - ${event.end.toLocaleTimeString()}\n`;
=======
            content += `Waktu: ${event.start.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'})} - ${event.end.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'})}\n`;
>>>>>>> 92b809e (notifikasi)
            content += `Tujuan: ${props.purpose}\n`;
            content += `Peserta: ${props.participant_count} orang\n`;
            content += `Status: ${props.status}`;
            
<<<<<<< HEAD
            alert(content);
=======
            if (confirm(content + '\n\nApakah Anda ingin melihat detail reservasi ini?')) {
                window.location.href = `{{ route('user.reservations.show', '') }}/${props.reservation_id}`;
            }
        },
        eventDidMount: function(info) {
            // Add tooltip
            info.el.setAttribute('title', `${info.event.extendedProps.laboratory} - ${info.event.extendedProps.status}`);
>>>>>>> 92b809e (notifikasi)
        }
    });
    
    calendar.render();
});
</script>
@endsection

@push('styles')
<style>
.text-xs {
    font-size: 0.75rem;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.card {
    transition: transform 0.15s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.bg-light {
    background-color: #f8f9fa !important;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.075);
}

#calendar {
    max-width: 100%;
}

/* Custom badge colors */
.badge.bg-warning {
    color: #000 !important;
}

<<<<<<< HEAD
=======
/* FullCalendar custom styles */
.fc-event {
    cursor: pointer;
    transition: all 0.3s ease;
}

.fc-event:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.fc-toolbar-title {
    font-size: 1.25rem !important;
    font-weight: 600;
}

.fc-button {
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
}

.fc-button:hover {
    background-color: #0b5ed7 !important;
    border-color: #0a58ca !important;
}

>>>>>>> 92b809e (notifikasi)
/* Responsive adjustments */
@media (max-width: 768px) {
    .btn-toolbar {
        flex-wrap: wrap;
    }
    
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .table-responsive {
        font-size: 0.9rem;
    }
<<<<<<< HEAD
=======
    
    .fc-toolbar {
        flex-direction: column;
        gap: 10px;
    }
    
    .fc-toolbar-chunk {
        display: flex;
        justify-content: center;
    }
>>>>>>> 92b809e (notifikasi)
}
</style>
@endpush