@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-tachometer-alt me-2 text-primary"></i>Dashboard Admin
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="{{ route('admin.reservations.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Reservasi
                </a>
            </div>
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
                                Total Laboratorium
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_laboratories'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="bg-primary text-white rounded-circle p-3">
                                <i class="fas fa-flask fa-lg"></i>
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
                                Total Pengguna
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_users'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="bg-info text-white rounded-circle p-3">
                                <i class="fas fa-users fa-lg"></i>
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
                                Total Reservasi
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_reservations'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="bg-success text-white rounded-circle p-3">
                                <i class="fas fa-calendar-check fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar and Quick Actions -->
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
                        <button type="button" class="btn btn-light btn-sm" onclick="changeCalendarView('timeGridDay')">Hari</button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Loading State -->
                    <div id="calendar-loading" class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2">Memuat kalender...</p>
                    </div>
                    
                    <!-- Calendar Container -->
                    <div id="calendar" style="display: none;"></div>
                    
                    <!-- Error State -->
                    <div id="calendar-error" class="alert alert-danger" style="display: none;">
                        <h6>Error Loading Calendar:</h6>
                        <p id="error-message"></p>
                        <button class="btn btn-primary btn-sm" onclick="location.reload()">Reload Page</button>
                    </div>
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
            <!-- Quick Actions -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Aksi Cepat
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.reservations.index', ['status' => 'pending']) }}" class="btn btn-warning">
                            <i class="fas fa-clock me-2"></i>Review Reservasi Pending ({{ $stats['pending_reservations'] }})
                        </a>
                        <a href="{{ route('admin.laboratories.index') }}" class="btn btn-primary">
                            <i class="fas fa-flask me-2"></i>Kelola Laboratorium
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-success">
                            <i class="fas fa-users me-2"></i>Kelola Pengguna
                        </a>
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                            <i class="fas fa-chart-bar me-2"></i>Lihat Laporan
                        </a>
                    </div>
                </div>
            </div>

            <!-- Reservasi Mendatang -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-plus me-2"></i>Reservasi Mendatang
                    </h5>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    @if($upcomingReservations->count() > 0)
                        @foreach($upcomingReservations->take(5) as $reservation)
                        <div class="d-flex align-items-start mb-3 p-3 bg-light rounded">
                            <div class="flex-shrink-0 me-3">
                                <div class="bg-success text-white rounded text-center p-2" style="min-width: 50px;">
                                    <div class="fw-bold">{{ $reservation->reservation_date->format('d') }}</div>
                                    <small>{{ $reservation->reservation_date->format('M') }}</small>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $reservation->laboratory->name }}</h6>
                                <p class="mb-1 text-muted small">{{ $reservation->user->name }}</p>
                                <small class="text-success">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ substr($reservation->start_time, 0, 5) }} - {{ substr($reservation->end_time, 0, 5) }}
                                </small>
                            </div>
                        </div>
                        @endforeach
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.reservations.index') }}" class="btn btn-outline-success btn-sm">
                                Lihat Semua Reservasi
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada reservasi mendatang</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reservations & Laboratory Stats -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Reservasi Terbaru
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentReservations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Pengguna</th>
                                        <th>Laboratorium</th>
                                        <th>Tanggal</th>
                                        <th>Waktu</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentReservations as $reservation)
                                    <tr>
                                        <td>{{ $reservation->user->name }}</td>
                                        <td>{{ $reservation->laboratory->name }}</td>
                                        <td>{{ $reservation->reservation_date->format('d/m/Y') }}</td>
                                        <td>{{ substr($reservation->start_time, 0, 5) }} - {{ substr($reservation->end_time, 0, 5) }}</td>
                                        <td>
                                            <span class="badge 
                                                @if($reservation->status === 'approved') bg-success
                                                @elseif($reservation->status === 'pending') bg-warning text-dark
                                                @elseif($reservation->status === 'rejected') bg-danger
                                                @else bg-secondary
                                                @endif">
                                                {{ ucfirst($reservation->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.reservations.show', $reservation) }}" 
                                               class="btn btn-outline-primary btn-sm">
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
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Statistik Lab
                    </h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if(isset($laboratoryStats) && $laboratoryStats->count() > 0)
                        @foreach($laboratoryStats as $lab)
                            <div class="mb-3 p-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-1">{{ $lab->name }}</h6>
                                    <span class="badge bg-primary">{{ $lab->reservations_count }}</span>
                                </div>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar bg-success" 
                                         style="width: {{ $lab->reservations_count > 0 ? ($lab->approved_count / $lab->reservations_count * 100) : 0 }}%"></div>
                                </div>
                                <small class="text-muted">
                                    {{ $lab->approved_count }} disetujui dari {{ $lab->reservations_count }} total
                                </small>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-flask fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Data statistik lab tidak tersedia</p>
                        </div>
                    @endif
                </div>
            </div>
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

@section('scripts')
<!-- FullCalendar CSS dan JS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initializing admin calendar...');
    
    const calendarEl = document.getElementById('calendar');
    const loadingEl = document.getElementById('calendar-loading');
    const errorEl = document.getElementById('calendar-error');
    const errorMessage = document.getElementById('error-message');
    
    if (!calendarEl) {
        console.error('❌ Calendar element not found!');
        return;
    }
    
    // Check if FullCalendar is loaded
    if (typeof FullCalendar === 'undefined') {
        console.error('❌ FullCalendar library not loaded!');
        showError('FullCalendar library tidak dapat dimuat. Silakan refresh halaman.');
        return;
    }
    
    console.log('✅ FullCalendar library loaded successfully');
    
    try {
        // Initialize FullCalendar with AJAX events loading
        console.log('🎯 Initializing FullCalendar...');
        
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            height: 'auto',
            
            // FIXED: Use correct route name for AJAX
            events: {
                url: '{{ route("admin.api.calendar.events") }}',
                method: 'GET',
                extraParams: function() {
                    return {
                        '_token': '{{ csrf_token() }}'
                    };
                },
                failure: function(error) {
                    console.error('❌ Failed to load events via AJAX:', error);
                    showError('Gagal memuat data kalender dari server. Error: ' + (error.message || 'Network error'));
                },
                success: function(data) {
                    console.log('✅ Events loaded successfully:', data.length, 'events');
                    if (data.length === 0) {
                        console.warn('⚠️ No events received from server');
                    } else {
                        console.log('📋 Sample event:', data[0]);
                    }
                }
            },
            
            eventDisplay: 'block',
            dayMaxEvents: 3,
            moreLinkText: 'lainnya',
            
            loading: function(isLoading) {
                console.log('🔄 Calendar loading:', isLoading);
                if (isLoading) {
                    showLoading();
                } else {
                    hideLoading();
                }
            },
            
            eventsSet: function(events) {
                console.log('📅 Events set in calendar:', events.length);
                if (events.length === 0) {
                    console.warn('⚠️ No events displayed in calendar');
                    showNoEventsMessage();
                } else {
                    hideNoEventsMessage();
                    console.log('✅ Calendar events displayed successfully');
                }
            },
            
            eventClick: function(info) {
                console.log('👆 Event clicked:', info.event);
                showEventModal(info.event);
            },
            
            eventDidMount: function(info) {
                const props = info.event.extendedProps || {};
                let startTime = '';
                let endTime = '';
                
                try {
                    startTime = info.event.start ? info.event.start.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'}) : '';
                    endTime = info.event.end ? info.event.end.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'}) : '';
                } catch (e) {
                    console.warn('⚠️ Error formatting time:', e);
                }
                
                // Add tooltip
                info.el.title = `${info.event.title}\nWaktu: ${startTime} - ${endTime}\nStatus: ${props.status || 'Unknown'}\nPengguna: ${props.user || 'N/A'}`;
                info.el.style.cursor = 'pointer';
                
                // Add hover effects
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
            
            // Add error handling for failed event loading
            eventSourceFailure: function(error) {
                console.error('❌ Event source failure:', error);
                showError('Tidak dapat memuat events dari server');
            }
        });
        
        // Make calendar globally accessible
        window.calendar = calendar;
        
        console.log('🚀 Rendering calendar...');
        calendar.render();
        console.log('✅ Calendar rendered successfully');
        
    } catch (error) {
        console.error('❌ Error initializing calendar:', error);
        showError('Error initializing calendar: ' + error.message);
    }
    
    // Helper functions
    function showLoading() {
        if (loadingEl) loadingEl.style.display = 'block';
        if (calendarEl) calendarEl.style.display = 'none';
        if (errorEl) errorEl.style.display = 'none';
    }
    
    function hideLoading() {
        if (loadingEl) loadingEl.style.display = 'none';
        if (calendarEl) calendarEl.style.display = 'block';
    }
    
    function showError(message) {
        console.error('❌ Showing error:', message);
        if (loadingEl) loadingEl.style.display = 'none';
        if (calendarEl) calendarEl.style.display = 'none';
        if (errorMessage) errorMessage.textContent = message;
        if (errorEl) errorEl.style.display = 'block';
    }
    
    function showNoEventsMessage() {
        // Add a message when no events are found
        const calendarBody = calendarEl.querySelector('.fc-view-harness');
        if (calendarBody && !calendarBody.querySelector('.no-events-message')) {
            const noEventsDiv = document.createElement('div');
            noEventsDiv.className = 'no-events-message alert alert-info mt-3';
            noEventsDiv.innerHTML = '<i class="fas fa-info-circle"></i> Tidak ada reservasi untuk periode ini';
            calendarBody.appendChild(noEventsDiv);
        }
    }
    
    function hideNoEventsMessage() {
        const noEventsMsg = calendarEl.querySelector('.no-events-message');
        if (noEventsMsg) {
            noEventsMsg.remove();
        }
    }
    
    function showEventModal(event) {
        const props = event.extendedProps || {};
        
        let startDate = 'N/A';
        let startTime = 'N/A';
        let endTime = 'N/A';
        
        try {
            if (event.start) {
                startDate = event.start.toLocaleDateString('id-ID');
                startTime = event.start.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
            }
            if (event.end) {
                endTime = event.end.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
            }
        } catch (e) {
            console.warn('⚠️ Error formatting date/time:', e);
        }
        
        const modalBody = document.getElementById('eventModalBody');
        if (modalBody) {
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <strong>Laboratorium:</strong><br>
                        <p class="mb-3">${props.laboratory || 'N/A'}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Pengguna:</strong><br>
                        <p class="mb-3">${props.user || 'N/A'}</p>
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
                        <span class="badge bg-${getStatusBadgeColor((props.status || 'unknown').toLowerCase())}">${props.status || 'Unknown'}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <strong>Tujuan:</strong><br>
                        <p class="mb-0">${props.purpose || 'No purpose specified'}</p>
                    </div>
                </div>
            `;
        }
        
        // Update view button
        const viewBtn = document.getElementById('viewReservationBtn');
        if (viewBtn && props.reservation_id) {
            viewBtn.href = `{{ url('admin/reservations') }}/${props.reservation_id}`;
        }
        
        // Show modal
        try {
            const modalElement = document.getElementById('eventModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        } catch (e) {
            console.error('❌ Error showing modal:', e);
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
        return colors[status] || 'secondary';
    }
});

// Global function to change calendar view
function changeCalendarView(viewName) {
    if (window.calendar) {
        window.calendar.changeView(viewName);
        console.log('📅 Calendar view changed to:', viewName);
    } else {
        console.error('❌ Calendar not available');
    }
}

// Function to refresh calendar events
function refreshCalendarEvents() {
    if (window.calendar) {
        console.log('🔄 Refreshing calendar events...');
        window.calendar.refetchEvents();
    }
}

// Manual test function to check if events endpoint is working
function testEventsEndpoint() {
    console.log('🧪 Testing events endpoint...');
    
    fetch('{{ route("admin.api.calendar.events") }}', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        console.log('📡 Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('📊 Events data received:', data);
        if (Array.isArray(data) && data.length > 0) {
            console.log('✅ Events endpoint is working, found', data.length, 'events');
            console.log('📋 Sample event:', data[0]);
        } else {
            console.warn('⚠️ No events returned from endpoint');
        }
    })
    .catch(error => {
        console.error('❌ Error testing endpoint:', error);
    });
}

// Call test function on page load for debugging
setTimeout(testEventsEndpoint, 2000);
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
    min-height: 500px;
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

/* Custom badge colors */
.badge.bg-warning {
    color: #000 !important;
}

.progress {
    border-radius: 3px;
}

.progress-bar {
    border-radius: 3px;
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

/* Modal improvements */
.modal-lg .modal-body {
    padding: 1.5rem;
}

/* FullCalendar customizations */
.fc-theme-standard .fc-scrollgrid {
    border: 1px solid #dee2e6;
}

.fc-day-today {
    background-color: rgba(13, 110, 253, 0.1) !important;
}

.fc-event-title {
    font-weight: 500;
}

.fc-toolbar {
    margin-bottom: 1rem;
}

.fc-toolbar-chunk {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    #calendar {
        font-size: 0.8rem;
        min-height: 400px;
    }
    
    .fc-toolbar {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .fc-toolbar-chunk {
        justify-content: center;
    }
    
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
}
</style>
@endpush