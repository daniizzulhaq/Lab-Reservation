@extends('layouts.app')

@section('title', 'Dashboard User')

{{-- Pindahkan CSS ke head --}}
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
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
    min-height: 400px;
}

.fc-event {
    cursor: pointer !important;
    transition: opacity 0.2s ease;
}

.fc-event:hover {
    opacity: 0.8;
}

/* Custom badge colors */
.badge.bg-warning {
    color: #000 !important;
}

/* Calendar legend */
.card-footer .badge {
    font-size: 0.6rem;
    padding: 0.2rem 0.4rem;
}

/* Loading spinner */
#calendar-loading {
    background-color: #f8f9fa;
    border-radius: 0.375rem;
}

/* Error styling */
#calendar-error {
    margin: 0;
}

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
    
    #calendar {
        font-size: 0.8rem;
        min-height: 300px;
    }
}
</style>
@endpush

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
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Kalender Reservasi
                    </h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-light btn-sm" onclick="changeCalendarView('dayGridMonth')">Bulan</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="changeCalendarView('timeGridWeek')">Minggu</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="refreshCalendar()">Refresh</button>
                    </div>
                </div>
                <div class="card-body p-2">
                    <div id="calendar"></div>
                </div>
                <div class="card-footer bg-light">
                    <small class="text-muted">
                        <span class="badge bg-warning me-2">■</span>Pending
                        <span class="badge bg-success me-2">■</span>Approved  
                        <span class="badge bg-danger me-2">■</span>Rejected
                        <span class="badge bg-secondary me-2">■</span>Cancelled
                        <span class="badge bg-info me-2">■</span>Completed
                    </small>
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

<!-- Event Detail Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Reservasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventModalBody">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <a href="#" id="viewReservationBtn" class="btn btn-primary">Lihat Detail</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<script>
// Global variables
let calendar = null;

// Initialize calendar when page loads
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) {
        console.error('Calendar element not found!');
        return;
    }
    
    if (typeof FullCalendar === 'undefined') {
        console.error('FullCalendar library not loaded!');
        return;
    }
    
    // Get calendar events from PHP
    let calendarEvents = [];
    
    @if(isset($calendarEvents) && is_array($calendarEvents))
        calendarEvents = @json($calendarEvents);
    @endif
    
    // Initialize calendar
    try {
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            height: 'auto',
            events: calendarEvents,
            eventDisplay: 'block',
            dayMaxEvents: 3,
            moreLinkText: 'lainnya',
            
            eventClick: function(info) {
                showEventModal(info.event);
            },
            
            eventDidMount: function(info) {
                const props = info.event.extendedProps || {};
                const startTime = info.event.start ? info.event.start.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'}) : '';
                const endTime = info.event.end ? info.event.end.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'}) : '';
                
                info.el.title = `${info.event.title}\nWaktu: ${startTime} - ${endTime}\nStatus: ${props.status || 'Unknown'}`;
                info.el.style.cursor = 'pointer';
            }
        });
        
        // Render calendar
        calendar.render();
        
        // Make globally accessible
        window.calendar = calendar;
        
    } catch (error) {
        console.error('Error initializing calendar:', error);
    }
});

// Helper functions
function changeCalendarView(view) {
    if (calendar) {
        calendar.changeView(view);
    }
}

function refreshCalendar() {
    if (calendar) {
        calendar.refetchEvents();
    } else {
        location.reload();
    }
}

function showEventModal(event) {
    const props = event.extendedProps || {};
    
    // Format dates
    const startDate = event.start ? event.start.toLocaleDateString('id-ID') : 'N/A';
    const startTime = event.start ? event.start.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'}) : 'N/A';
    const endTime = event.end ? event.end.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'}) : 'N/A';
    
    // Update modal content
    const modalBody = document.getElementById('eventModalBody');
    if (modalBody) {
        modalBody.innerHTML = `
            <div class="row">
                <div class="col-md-12">
                    <strong>Laboratorium:</strong><br>
                    <p class="mb-3">${props.laboratory || event.title}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <strong>Tanggal:</strong><br>
                    <p class="mb-3">${startDate}</p>
                </div>
                <div class="col-md-6">
                    <strong>Waktu:</strong><br>
                    <p class="mb-3">${startTime} - ${endTime}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <strong>Jumlah Peserta:</strong><br>
                    <p class="mb-3">${props.participant_count || 0} orang</p>
                </div>
                <div class="col-md-6">
                    <strong>Status:</strong><br>
                    <span class="badge bg-${getStatusBadgeColor(props.status)}">${props.status || 'Unknown'}</span>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <strong>Tujuan:</strong><br>
                    <p class="mb-0">${props.purpose || 'Tidak ada keterangan'}</p>
                </div>
            </div>
        `;
    }
    
    // Update view button
    const viewBtn = document.getElementById('viewReservationBtn');
    if (viewBtn && props.reservation_id) {
        viewBtn.href = `/user/reservations/${props.reservation_id}`;
    }
    
    // Show modal
    const modalElement = document.getElementById('eventModal');
    if (modalElement && typeof bootstrap !== 'undefined') {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
}

function getStatusBadgeColor(status) {
    const colors = {
        'pending': 'warning',
        'approved': 'success',
        'rejected': 'danger',
        'cancelled': 'secondary',
        'completed': 'info'
    };
    return colors[status?.toLowerCase()] || 'secondary';
}
</script>
@endpush