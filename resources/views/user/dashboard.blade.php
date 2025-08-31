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
    transition: all 0.2s ease;
    border-radius: 4px;
}

.fc-event:hover {
    opacity: 0.8 !important;
    transform: scale(1.02) !important;
}

/* Status-based event styling */
.reservation-status-pending {
    background-color: #ffc107 !important;
    border-color: #ffc107 !important;
    color: #000 !important;
}

.reservation-status-approved {
    background-color: #28a745 !important;
    border-color: #28a745 !important;
    color: #fff !important;
}

.reservation-status-rejected {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
    color: #fff !important;
}

.reservation-status-cancelled {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
    color: #fff !important;
}

.reservation-status-completed {
    background-color: #17a2b8 !important;
    border-color: #17a2b8 !important;
    color: #fff !important;
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

/* No events message styling */
.no-events-message {
    border: 2px dashed #dee2e6 !important;
    background: rgba(248, 249, 250, 0.95) !important;
    border-radius: 8px;
    padding: 2rem;
}

/* Loading and error states */
.alert {
    border-radius: 0.375rem;
}

/* Calendar loading overlay */
.calendar-loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    border-radius: 8px;
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
    
    .fc-toolbar {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .fc-toolbar-chunk {
        justify-content: center;
    }
    
    .no-events-message {
        padding: 1rem;
        margin: 0.5rem;
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
                <div class="card-body p-2" style="position: relative;">
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
    console.log('=== STARTING USER CALENDAR INITIALIZATION ===');
    
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) {
        console.error('Calendar element not found!');
        showCalendarError('Element kalender tidak ditemukan');
        return;
    }
    
    if (typeof FullCalendar === 'undefined') {
        console.error('FullCalendar library not loaded!');
        showCalendarError('Library FullCalendar tidak dimuat');
        return;
    }
    
    // Show initial loading
    showCalendarLoading();
    
    try {
        console.log('Initializing FullCalendar...');
        
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            height: 'auto',
            
            // PERBAIKAN UTAMA: Event source configuration yang lebih robust
            eventSources: [{
                url: '/user/api/calendar-events',
                method: 'GET',
                extraParams: function() {
                    return {
                        '_token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    };
                },
                success: function(data) {
                    console.log('=== CALENDAR EVENTS LOADED ===');
                    console.log('Events received:', data);
                    console.log('Number of events:', data.length);
                    
                    hideCalendarLoading();
                    
                    if (data.length === 0) {
                        console.log('No events found, showing empty message');
                        setTimeout(() => showNoEventsMessage(), 100);
                    } else {
                        console.log('Events found, hiding empty message');
                        hideNoEventsMessage();
                        
                        // Log individual events for debugging
                        data.forEach((event, index) => {
                            console.log(`Event ${index + 1}:`, {
                                id: event.id,
                                title: event.title,
                                start: event.start,
                                end: event.end,
                                status: event.extendedProps?.status
                            });
                        });
                    }
                },
                failure: function(error) {
                    console.error('=== CALENDAR EVENTS FAILED ===');
                    console.error('Error details:', error);
                    hideCalendarLoading();
                    showCalendarError('Gagal memuat data kalender. Silakan refresh halaman.');
                }
            }],
            
            eventDisplay: 'block',
            dayMaxEvents: 3,
            moreLinkText: 'lainnya',
            
            // Loading indicator
            loading: function(isLoading) {
                console.log('Calendar loading state:', isLoading);
                if (isLoading) {
                    showCalendarLoading();
                } else {
                    hideCalendarLoading();
                }
            },
            
            // Event rendering dengan debugging
            eventDidMount: function(info) {
                console.log('=== EVENT MOUNTED ===');
                console.log('Event title:', info.event.title);
                console.log('Event start:', info.event.start);
                console.log('Event end:', info.event.end);
                console.log('Event props:', info.event.extendedProps);
                
                const props = info.event.extendedProps || {};
                const startTime = info.event.start ? info.event.start.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'}) : '';
                const endTime = info.event.end ? info.event.end.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'}) : '';
                
                // Tooltip
                info.el.title = `${info.event.title}\nWaktu: ${startTime} - ${endTime}\nStatus: ${props.status || 'Unknown'}`;
                info.el.style.cursor = 'pointer';
                
                // Add status-based styling
                const status = props.status ? props.status.toLowerCase() : 'unknown';
                info.el.classList.add('reservation-status-' + status);
                
                // Hover effects
                info.el.addEventListener('mouseenter', function() {
                    this.style.opacity = '0.8';
                    this.style.transform = 'scale(1.02)';
                    this.style.transition = 'all 0.2s ease';
                });
                
                info.el.addEventListener('mouseleave', function() {
                    this.style.opacity = '1';
                    this.style.transform = 'scale(1)';
                });
            },
            
            eventClick: function(info) {
                console.log('Event clicked:', info.event);
                showEventModal(info.event);
            },
            
            // Handle events set dengan debugging
            eventsSet: function(events) {
                console.log('=== EVENTS SET CALLBACK ===');
                console.log('Total events set:', events.length);
                
                events.forEach((event, index) => {
                    console.log(`Event ${index + 1} in calendar:`, {
                        id: event.id,
                        title: event.title,
                        start: event.start,
                        end: event.end
                    });
                });
                
                if (events.length === 0) {
                    console.log('No events in calendar, showing empty message');
                    setTimeout(() => showNoEventsMessage(), 100);
                } else {
                    console.log('Events exist in calendar, hiding empty message');
                    hideNoEventsMessage();
                }
            },
            
            // Enhanced error handling
            eventSourceFailure: function(error) {
                console.error('=== EVENT SOURCE FAILURE ===');
                console.error('Error:', error);
                hideCalendarLoading();
                showCalendarError('Gagal memuat reservasi. Periksa koneksi internet atau refresh halaman.');
            },
            
            // Additional debugging callbacks
            datesSet: function(info) {
                console.log('=== CALENDAR DATES SET ===');
                console.log('Date range:', info.start, 'to', info.end);
            }
        });
        
        // Render calendar
        console.log('Rendering calendar...');
        calendar.render();
        
        // Make globally accessible
        window.calendar = calendar;
        
        console.log('=== USER CALENDAR INITIALIZED SUCCESSFULLY ===');
        
        // Test API endpoint setelah calendar initialized
        setTimeout(() => {
            testApiEndpoint();
        }, 1000);
        
    } catch (error) {
        console.error('=== ERROR INITIALIZING CALENDAR ===');
        console.error('Error details:', error);
        hideCalendarLoading();
        showCalendarError('Error menginisialisasi kalender: ' + error.message);
    }
});

// Test API endpoint function
function testApiEndpoint() {
    console.log('=== TESTING API ENDPOINT ===');
    
    fetch('/user/api/calendar-events', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
    })
    .then(response => {
        console.log('API Response status:', response.status);
        console.log('API Response headers:', response.headers);
        return response.json();
    })
    .then(data => {
        console.log('=== API TEST SUCCESSFUL ===');
        console.log('API Response data:', data);
        console.log('Number of events from API:', data.length);
        
        if (data.length > 0) {
            console.log('Sample event from API:', data[0]);
        }
    })
    .catch(error => {
        console.error('=== API TEST FAILED ===');
        console.error('API Error:', error);
    });
}

// Enhanced loading functions
function showCalendarLoading() {
    console.log('Showing calendar loading...');
    const calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        // Remove existing loading if any
        hideCalendarLoading();
        
        const loadingDiv = document.createElement('div');
        loadingDiv.id = 'calendar-loading';
        loadingDiv.className = 'calendar-loading-overlay';
        loadingDiv.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mb-0">Memuat data kalender...</p>
            </div>
        `;
        
        calendarEl.style.position = 'relative';
        calendarEl.appendChild(loadingDiv);
    }
}

function hideCalendarLoading() {
    const loadingDiv = document.getElementById('calendar-loading');
    if (loadingDiv) {
        console.log('Hiding calendar loading...');
        loadingDiv.remove();
    }
}

// Enhanced no events message
function showNoEventsMessage() {
    console.log('Showing no events message...');
    const calendarEl = document.getElementById('calendar');
    if (calendarEl && !calendarEl.querySelector('.no-events-message')) {
        const noEventsDiv = document.createElement('div');
        noEventsDiv.className = 'no-events-message';
        noEventsDiv.innerHTML = `
            <div class="text-center text-muted">
                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                <h5>Belum ada reservasi</h5>
                <p class="mb-3">Kalender akan menampilkan reservasi Anda setelah dibuat</p>
                <a href="{{ route('user.reservations.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Buat Reservasi
                </a>
            </div>
        `;
        
        noEventsDiv.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
            background: rgba(248, 249, 250, 0.95);
            padding: 2rem;
            border-radius: 8px;
            border: 2px dashed #dee2e6;
            min-width: 300px;
        `;
        
        calendarEl.style.position = 'relative';
        calendarEl.appendChild(noEventsDiv);
    }
}

function hideNoEventsMessage() {
    const noEventsMsg = document.querySelector('.no-events-message');
    if (noEventsMsg) {
        console.log('Hiding no events message...');
        noEventsMsg.remove();
    }
}

function showCalendarError(message) {
    console.log('Showing calendar error:', message);
    const calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        calendarEl.innerHTML = `
            <div class="alert alert-danger text-center m-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Error:</strong> ${message}
                <br><br>
                <button class="btn btn-primary btn-sm me-2" onclick="location.reload()">
                    <i class="fas fa-refresh me-1"></i>Refresh Halaman
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="debugUserCalendarState()">
                    <i class="fas fa-bug me-1"></i>Debug Info
                </button>
            </div>
        `;
    }
}

// Helper functions
function changeCalendarView(view) {
    console.log('Changing calendar view to:', view);
    if (calendar) {
        calendar.changeView(view);
    } else {
        console.error('Calendar not initialized');
    }
}

function refreshCalendar() {
    console.log('=== REFRESHING USER CALENDAR ===');
    if (calendar) {
        showCalendarLoading();
        calendar.refetchEvents();
        console.log('Events refetched');
    } else {
        console.log('Calendar not initialized, reloading page');
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

// Enhanced debug function
function debugUserCalendarState() {
    console.log('=== USER CALENDAR DEBUG INFO ===');
    console.log('Calendar object:', calendar);
    console.log('Calendar initialized:', calendar !== null);
    console.log('Calendar events:', calendar ? calendar.getEvents() : 'Calendar not initialized');
    console.log('Calendar element:', document.getElementById('calendar'));
    console.log('FullCalendar loaded:', typeof FullCalendar !== 'undefined');
    console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));
    
    // Test API endpoint
    console.log('Testing API endpoint...');
    fetch('/user/api/calendar-events', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
    })
    .then(response => {
        console.log('API Response status:', response.status);
        console.log('API Response OK:', response.ok);
        return response.json();
    })
    .then(data => {
        console.log('API response data:', data);
        console.log('Events from API:', data.length);
        
        if (data.length > 0) {
            console.log('Sample event structure:', data[0]);
            
            // Validate event format
            data.forEach((event, index) => {
                const isValidStart = event.start && !isNaN(Date.parse(event.start));
                const isValidEnd = event.end && !isNaN(Date.parse(event.end));
                
                console.log(`Event ${index + 1} validation:`, {
                    id: event.id,
                    title: event.title,
                    start: event.start,
                    end: event.end,
                    validStart: isValidStart,
                    validEnd: isValidEnd,
                    startParsed: isValidStart ? new Date(event.start) : 'Invalid',
                    endParsed: isValidEnd ? new Date(event.end) : 'Invalid'
                });
            });
        }
    })
    .catch(error => {
        console.error('API error:', error);
    });
}

// Make functions globally accessible
window.debugUserCalendarState = debugUserCalendarState;
window.refreshCalendar = refreshCalendar;
window.changeCalendarView = changeCalendarView;
window.testApiEndpoint = testApiEndpoint;
</script>
@endpush