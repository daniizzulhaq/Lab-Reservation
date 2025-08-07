@extends('layouts.app')

@section('title', $laboratory->name)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-flask me-2"></i>{{ $laboratory->name }}
        <small class="text-muted">{{ $laboratory->code }}</small>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('user.laboratories.index') }}" class="btn btn-outline-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
        <a href="{{ route('user.reservations.create', ['laboratory_id' => $laboratory->id]) }}" 
           class="btn btn-primary">
            <i class="fas fa-calendar-plus me-1"></i>Buat Reservasi
        </a>
    </div>
</div>

<div class="row">
    <!-- Laboratory Information -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Laboratorium</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Nama:</td>
                                <td>{{ $laboratory->name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Kode:</td>
                                <td>{{ $laboratory->code }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Kapasitas:</td>
                                <td>{{ $laboratory->capacity }} orang</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Lokasi:</td>
                                <td>{{ $laboratory->location ?: 'Tidak disebutkan' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Status:</td>
                                <td>
                                    <span class="badge bg-success">
                                        {{ ucfirst($laboratory->status) }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        @if($laboratory->description)
                        <h6 class="fw-bold">Deskripsi:</h6>
                        <p>{{ $laboratory->description }}</p>
                        @endif
                        
                        @if($laboratory->facilities)
                        <h6 class="fw-bold">Fasilitas:</h6>
                        <p>{{ $laboratory->facilities }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Schedule -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-day me-2"></i>Jadwal Hari Ini
                    <small class="text-muted">{{ now()->format('d F Y') }}</small>
                </h5>
            </div>
            <div class="card-body">
                <div id="todaySchedule">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat jadwal...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions and Availability Check -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Cek Ketersediaan</h5>
            </div>
            <div class="card-body">
                <form id="availabilityForm">
                    <div class="mb-3">
                        <label for="check_date" class="form-label">Tanggal</label>
                        <input type="date" class="form-control" id="check_date" 
                               value="{{ today()->format('Y-m-d') }}" min="{{ today()->format('Y-m-d') }}">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Cek Ketersediaan
                    </button>
                </form>
                
                <div id="availabilityResult" class="mt-3" style="display: none;">
                    <!-- Results will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistik</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border-end">
                            <h4 class="text-primary mb-1">{{ $laboratory->reservations->count() }}</h4>
                            <small class="text-muted">Total Reservasi</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-success mb-1">{{ $laboratory->reservations->where('status', 'approved')->count() }}</h4>
                        <small class="text-muted">Disetujui</small>
                    </div>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">Jam Operasional:</small>
                    <div class="fw-bold">08:00 - 17:00 WIB</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadTodaySchedule();
    
    document.getElementById('availabilityForm').addEventListener('submit', function(e) {
        e.preventDefault();
        checkAvailability();
    });
    
    // Auto-check availability when date changes
    document.getElementById('check_date').addEventListener('change', checkAvailability);
});

function loadTodaySchedule() {
    const today = new Date().toISOString().split('T')[0];
    
    fetch(`{{ route('user.laboratories.availability', $laboratory) }}?date=${today}`)
        .then(response => response.json())
        .then(data => {
            displaySchedule(data.reservations);
        })
        .catch(error => {
            document.getElementById('todaySchedule').innerHTML = 
                '<div class="alert alert-danger">Gagal memuat jadwal</div>';
        });
}

function checkAvailability() {
    const date = document.getElementById('check_date').value;
    const resultDiv = document.getElementById('availabilityResult');
    
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm"></div></div>';
    
    fetch(`{{ route('user.laboratories.availability', $laboratory) }}?date=${date}`)
        .then(response => response.json())
        .then(data => {
            displayAvailability(data.reservations, date);
        })
        .catch(error => {
            resultDiv.innerHTML = '<div class="alert alert-danger">Gagal memeriksa ketersediaan</div>';
        });
}

function displaySchedule(reservations) {
    const scheduleDiv = document.getElementById('todaySchedule');
    
    if (reservations.length === 0) {
        scheduleDiv.innerHTML = `
            <div class="text-center py-3">
                <i class="fas fa-calendar-check fa-2x text-success mb-2"></i>
                <p class="mb-0 text-success">Laboratorium tersedia sepanjang hari</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="timeline">';
    reservations.forEach(reservation => {
        html += `
            <div class="d-flex align-items-center mb-2 p-2 bg-light rounded">
                <div class="flex-shrink-0 me-3">
                    <div class="badge bg-primary">${reservation.start_time} - ${reservation.end_time}</div>
                </div>
                <div class="flex-grow-1">
                    <small class="text-muted">${reservation.purpose}</small>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    scheduleDiv.innerHTML = html;
}

function displayAvailability(reservations, date) {
    const resultDiv = document.getElementById('availabilityResult');
    const selectedDate = new Date(date);
    const today = new Date();
    
    let html = `<h6 class="mb-3">Ketersediaan ${selectedDate.toLocaleDateString('id-ID')}</h6>`;
    
    if (reservations.length === 0) {
        html += `
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                Laboratorium tersedia sepanjang hari (08:00 - 17:00)
            </div>
            <a href="{{ route('user.reservations.create', ['laboratory_id' => $laboratory->id]) }}?date=${date}" 
               class="btn btn-success btn-sm w-100">
                <i class="fas fa-plus me-1"></i>Buat Reservasi
            </a>
        `;
    } else {
        html += '<div class="mb-3">';
        html += '<small class="text-muted">Waktu yang sudah dibooking:</small>';
        reservations.forEach(reservation => {
            html += `
                <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                    <span class="small">${reservation.start_time} - ${reservation.end_time}</span>
                    <span class="badge bg-danger">Terpakai</span>
                </div>
            `;
        });
        html += '</div>';
        
        html += `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Beberapa waktu sudah dibooking. Pilih waktu yang tersedia.
            </div>
            <a href="{{ route('user.reservations.create', ['laboratory_id' => $laboratory->id]) }}?date=${date}" 
               class="btn btn-primary btn-sm w-100">
                <i class="fas fa-plus me-1"></i>Buat Reservasi
            </a>
        `;
    }
    
    resultDiv.innerHTML = html;
}
</script>
@endsection