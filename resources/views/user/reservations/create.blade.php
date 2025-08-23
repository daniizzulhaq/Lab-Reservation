@extends('layouts.app')

@section('title', 'Buat Reservasi Baru')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-calendar-plus me-2"></i>Buat Reservasi Baru</h1>
    <a href="{{ route('user.reservations.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-form me-2"></i>Form Reservasi</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('user.reservations.store') }}" id="reservationForm">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="laboratory_id" class="form-label">Laboratorium <span class="text-danger">*</span></label>
                            <select class="form-select @error('laboratory_id') is-invalid @enderror" 
                                    id="laboratory_id" name="laboratory_id" required>
                                <option value="">Pilih Laboratorium</option>
                                @foreach($laboratories as $lab)
                                    <option value="{{ $lab->id }}" 
                                            data-capacity="{{ $lab->capacity }}"
                                            {{ old('laboratory_id', request('laboratory_id')) == $lab->id ? 'selected' : '' }}>
                                        {{ $lab->name }} ({{ $lab->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('laboratory_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text" id="labCapacity" style="display: none;">
                                Kapasitas: <span class="fw-bold"></span> orang
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="reservation_date" class="form-label">Tanggal Reservasi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('reservation_date') is-invalid @enderror" 
                                   id="reservation_date" name="reservation_date" 
                                   value="{{ old('reservation_date', request('date')) }}" 
                                   min="{{ today()->format('Y-m-d') }}" required>
                            @error('reservation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_time" class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                   id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-clock me-1"></i>Tersedia 24 jam
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="end_time" class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                   id="end_time" name="end_time" value="{{ old('end_time') }}" required>
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('time')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <!-- REMOVED: Minimum duration text -->
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="participant_count" class="form-label">Jumlah Peserta <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('participant_count') is-invalid @enderror" 
                                   id="participant_count" name="participant_count" value="{{ old('participant_count') }}" 
                                   min="1" required>
                            @error('participant_count')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="purpose" class="form-label">Tujuan Penggunaan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('purpose') is-invalid @enderror" 
                                   id="purpose" name="purpose" value="{{ old('purpose') }}" 
                                   placeholder="Contoh: Praktikum Keperawatan Dasar" required>
                            @error('purpose')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="description" class="form-label">Deskripsi Tambahan</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="Informasi tambahan tentang kegiatan (opsional)">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Availability Check Result -->
                    <div id="availabilityAlert" style="display: none;"></div>
                    
                    <!-- Conflicting Reservations Display -->
                    <div id="conflictDetails" style="display: none;" class="mb-3">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Konflik Waktu Terdeteksi</h6>
                            </div>
                            <div class="card-body" id="conflictList">
                                <!-- Conflict details will be populated here -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Available Slots Suggestions -->
                    <div id="suggestedSlots" style="display: none;" class="mb-3">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Saran Waktu Tersedia</h6>
                            </div>
                            <div class="card-body" id="slotsList">
                                <!-- Available slots will be populated here -->
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-primary" id="checkAvailability">
                            <i class="fas fa-search me-1"></i>Cek Ketersediaan
                        </button>
                        <div>
                            <button type="button" class="btn btn-secondary me-2" onclick="history.back()">
                                <i class="fas fa-times me-1"></i>Batal
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-paper-plane me-1"></i>Kirim Reservasi
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Sidebar Information -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Penting</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-clock text-primary me-2"></i>
                        <strong>Ketersediaan:</strong> 24/7 (Sepanjang hari)
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-calendar-alt text-success me-2"></i>
                        <strong>Hari Kerja:</strong> Senin - Jumat (Weekend terbatas)
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-user-check text-warning me-2"></i>
                        <strong>Persetujuan:</strong> Dibutuhkan persetujuan admin
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-edit text-info me-2"></i>
                        <strong>Edit:</strong> Hanya reservasi pending yang dapat diedit
                    </li>
                    <li class="mb-0">
                        <i class="fas fa-shield-alt text-danger me-2"></i>
                        <strong>Anti Double Booking:</strong> Sistem akan mencegah konflik waktu
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>Bantuan</h5>
            </div>
            <div class="card-body">
                <p class="small mb-2">Mengalami kesulitan dalam membuat reservasi?</p>
                <button class="btn btn-outline-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#helpModal">
                    <i class="fas fa-life-ring me-1"></i>Panduan Reservasi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-question-circle me-2"></i>Panduan Membuat Reservasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-step-forward me-2"></i>Langkah-langkah:</h6>
                        <ol class="small">
                            <li>Pilih laboratorium yang ingin direservasi</li>
                            <li>Tentukan tanggal dan waktu yang diinginkan</li>
                            <li>Isi jumlah peserta dan tujuan penggunaan</li>
                            <li>Cek ketersediaan sebelum mengirim</li>
                            <li>Kirim reservasi dan tunggu persetujuan admin</li>
                        </ol>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Hal yang Perlu Diperhatikan:</h6>
                        <ul class="small">
                            <li>Reservasi hanya bisa dibuat untuk hari ini dan masa depan</li>
                            <li>Laboratorium tersedia 24/7 (tanpa batasan jam)</li>
                            <li>Jumlah peserta tidak boleh melebihi kapasitas lab</li>
                            <li>Sistem akan mencegah double booking otomatis</li>
                            <li>Reservasi memerlukan persetujuan admin</li>
                            <li>Anda akan mendapat notifikasi status reservasi</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Enhanced conflict detection with better notifications
// Enhanced conflict detection with better notifications and error handling
document.addEventListener('DOMContentLoaded', function() {
    const labSelect = document.getElementById('laboratory_id');
    const participantInput = document.getElementById('participant_count');
    const checkBtn = document.getElementById('checkAvailability');
    
    // Show lab capacity when lab is selected
    labSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const capacityDiv = document.getElementById('labCapacity');
        
        if (selectedOption.value) {
            const capacity = selectedOption.dataset.capacity;
            capacityDiv.style.display = 'block';
            capacityDiv.querySelector('span').textContent = capacity;
            
            // Update participant input max value
            participantInput.setAttribute('max', capacity);
        } else {
            capacityDiv.style.display = 'none';
        }
        
        // Auto-check availability when lab changes
        autoCheckAvailability();
    });
    
    // Auto-check availability when inputs change with debounce
    let timeoutId;
    ['laboratory_id', 'reservation_date', 'start_time', 'end_time'].forEach(id => {
        document.getElementById(id).addEventListener('change', function() {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(autoCheckAvailability, 500); // Debounce 500ms
        });
    });
    
    // Manual check availability
    checkBtn.addEventListener('click', checkAvailability);
    
    // Trigger initial capacity display
    labSelect.dispatchEvent(new Event('change'));
    
    // Add real-time conflict checking while typing time
    ['start_time', 'end_time'].forEach(id => {
        document.getElementById(id).addEventListener('input', function() {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                showInstantTimeValidation();
                autoCheckAvailability();
            }, 300);
        });
    });
});

// Instant time validation feedback
function showInstantTimeValidation() {
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;
    const alertDiv = document.getElementById('availabilityAlert');
    
    if (startTime && endTime) {
        if (startTime >= endTime) {
            alertDiv.style.display = 'block';
            alertDiv.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Peringatan:</strong> Waktu selesai harus lebih besar dari waktu mulai!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            document.getElementById('submitBtn').disabled = true;
        }
    }
}

function autoCheckAvailability() {
    const lab = document.getElementById('laboratory_id').value;
    const date = document.getElementById('reservation_date').value;
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;
    
    if (lab && date && startTime && endTime) {
        checkAvailability();
    } else {
        // Clear notifications if incomplete data
        clearAllNotifications();
        document.getElementById('submitBtn').disabled = false;
    }
}

function checkAvailability() {
    const lab = document.getElementById('laboratory_id').value;
    const date = document.getElementById('reservation_date').value;
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;
    const alertDiv = document.getElementById('availabilityAlert');
    const conflictDiv = document.getElementById('conflictDetails');
    const suggestedDiv = document.getElementById('suggestedSlots');
    
    if (!lab || !date || !startTime || !endTime) {
        clearAllNotifications();
        return;
    }
    
    // Validate time duration
    if (startTime >= endTime) {
        showTimeError('Waktu selesai harus lebih besar dari waktu mulai!');
        document.getElementById('submitBtn').disabled = true;
        return;
    }
    
    // Calculate duration
    const start = new Date(`2000-01-01T${startTime}:00`);
    const end = new Date(`2000-01-01T${endTime}:00`);
    if (end < start) {
        end.setDate(end.getDate() + 1); // Handle overnight booking
    }
    const durationMinutes = (end - start) / 1000 / 60;
    
    // Show loading notification
    showLoadingNotification();
    
    // Use the availability check endpoint
    const checkUrl = `{{ route('user.api.availability-check') }}?laboratory_id=${lab}&date=${date}&start_time=${startTime}&end_time=${endTime}`;
    
    fetch(checkUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Availability check response:', data); // Debug log
            
            if (data.available) {
                showSuccessNotification(durationMinutes);
                document.getElementById('submitBtn').disabled = false;
                conflictDiv.style.display = 'none';
                suggestedDiv.style.display = 'none';
            } else {
                showConflictNotification();
                document.getElementById('submitBtn').disabled = true;
                
                // Show conflict details with enhanced information
                if (data.conflicts && data.conflicts.length > 0) {
                    displayConflicts(data.conflicts);
                    
                    // Show toast notification for immediate attention
                    showConflictToast(data.conflicts.length, data.conflicts);
                    
                    // Show browser notification if supported
                    showBrowserNotification('Konflik Reservasi Terdeteksi', 
                        `${data.conflicts.length} reservasi bertabrakan dengan waktu yang dipilih`);
                }
                
                // Show suggested available slots
                if (data.available_slots && data.available_slots.length > 0) {
                    displaySuggestedSlots(data.available_slots);
                } else {
                    // If no suggestions, show message
                    showNoSlotsMessage();
                }
            }
        })
        .catch(error => {
            console.error('Availability check error:', error);
            showErrorNotification('Gagal memeriksa ketersediaan: ' + error.message);
            document.getElementById('submitBtn').disabled = true;
        });
}

// Enhanced Notification Functions
function showLoadingNotification() {
    const alertDiv = document.getElementById('availabilityAlert');
    alertDiv.style.display = 'block';
    alertDiv.innerHTML = `
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div>
                    <strong>🔍 Memeriksa ketersediaan...</strong>
                    <br><small>Sedang mengecek konflik dengan reservasi lain</small>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}

function showSuccessNotification(durationMinutes) {
    const alertDiv = document.getElementById('availabilityAlert');
    const hours = Math.floor(durationMinutes / 60);
    const minutes = durationMinutes % 60;
    
    alertDiv.innerHTML = `
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>✅ Laboratorium Tersedia!</strong>
            <br><small><i class="fas fa-clock me-1"></i>Durasi: ${hours} jam ${minutes} menit</small>
            <br><small class="text-success"><i class="fas fa-shield-alt me-1"></i>Tidak ada konflik waktu dengan reservasi lain</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Show success toast
    showToast('success', '✅ Waktu Tersedia!', 'Laboratorium dapat direservasi pada waktu yang dipilih');
    
    // Show browser notification
    showBrowserNotification('Laboratorium Tersedia', 
        `Waktu ${document.getElementById('start_time').value} - ${document.getElementById('end_time').value} tersedia`);
}

function showConflictNotification() {
    const alertDiv = document.getElementById('availabilityAlert');
    alertDiv.innerHTML = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle me-2"></i>
            <strong>❌ KONFLIK WAKTU TERDETEKSI!</strong>
            <br><small><i class="fas fa-exclamation-triangle me-1"></i>Waktu yang dipilih bentrok dengan reservasi yang sudah ada</small>
            <br><small class="text-muted"><i class="fas fa-lightbulb me-1"></i>Silakan pilih waktu lain atau lihat saran di bawah</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}

function showTimeError(message) {
    const alertDiv = document.getElementById('availabilityAlert');
    alertDiv.style.display = 'block';
    alertDiv.innerHTML = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle me-2"></i>
            <strong>⚠️ Error Waktu:</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}

function showErrorNotification(customMessage = null) {
    const alertDiv = document.getElementById('availabilityAlert');
    const message = customMessage || 'Terjadi kesalahan saat mengecek konflik. Silakan coba lagi.';
    
    alertDiv.innerHTML = `
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>⚠️ Gagal Memeriksa Ketersediaan</strong>
            <br><small>${message}</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}

function clearAllNotifications() {
    document.getElementById('availabilityAlert').style.display = 'none';
    document.getElementById('conflictDetails').style.display = 'none';
    document.getElementById('suggestedSlots').style.display = 'none';
}

// Enhanced Toast Notification Function
function showConflictToast(conflictCount, conflicts) {
    // Create toast if doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    const toastId = 'conflictToast-' + Date.now();
    
    // Create detailed conflict summary
    const conflictSummary = conflicts.slice(0, 2).map(conflict => 
        `${conflict.reservation_code} (${conflict.start_time}-${conflict.end_time})`
    ).join(', ');
    const additionalCount = conflicts.length > 2 ? ` dan ${conflicts.length - 2} lainnya` : '';
    
    const toastHtml = `
        <div id="${toastId}" class="toast show" role="alert">
            <div class="toast-header bg-danger text-white">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong class="me-auto">🚫 Konflik Reservasi</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                <strong>${conflictCount} reservasi</strong> bertabrakan dengan waktu yang Anda pilih.
                <br><small class="text-muted">${conflictSummary}${additionalCount}</small>
                <br><small><i class="fas fa-search me-1"></i>Periksa detail konflik di bawah form.</small>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Auto dismiss after 10 seconds
    setTimeout(() => {
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }
    }, 10000);
}

function showToast(type, title, message) {
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : type === 'danger' ? 'bg-danger' : 'bg-info';
    const icon = type === 'success' ? 'fa-check-circle' : type === 'danger' ? 'fa-times-circle' : 'fa-info-circle';
    
    const toastHtml = `
        <div id="${toastId}" class="toast show" role="alert">
            <div class="toast-header ${bgClass} text-white">
                <i class="fas ${icon} me-2"></i>
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }
    }, 5000);
}

// Enhanced browser notification
function showBrowserNotification(title, message) {
    if ("Notification" in window) {
        if (Notification.permission === "granted") {
            new Notification(title, {
                body: message,
                icon: '/favicon.ico',
                tag: 'reservation-conflict'
            });
        } else if (Notification.permission !== "denied") {
            Notification.requestPermission().then(function (permission) {
                if (permission === "granted") {
                    new Notification(title, {
                        body: message,
                        icon: '/favicon.ico',
                        tag: 'reservation-conflict'
                    });
                }
            });
        }
    }
}

function displayConflicts(conflicts) {
    const conflictDiv = document.getElementById('conflictDetails');
    const conflictList = document.getElementById('conflictList');
    
    let conflictHtml = `
        <div class="alert alert-danger mb-3">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>🚫 Ditemukan ${conflicts.length} konflik waktu:</strong>
            <br><small class="text-muted">Reservasi berikut bertabrakan dengan waktu yang Anda pilih</small>
        </div>
        <div class="row">
    `;
    
    conflicts.forEach((conflict, index) => {
        const statusBadge = getStatusBadgeClass(conflict.status);
        const statusIcon = getStatusIcon(conflict.status);
        
        conflictHtml += `
            <div class="col-md-6 mb-3">
                <div class="card border-danger shadow-sm h-100">
                    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-clock me-1"></i>${conflict.start_time} - ${conflict.end_time}
                        </h6>
                        <span class="badge ${statusBadge}">${statusIcon} ${conflict.status.toUpperCase()}</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-2">
                            <strong><i class="fas fa-code me-1"></i>Kode:</strong> 
                            <code class="bg-light p-1 rounded">${conflict.reservation_code}</code>
                        </div>
                        <div class="mb-2">
                            <strong><i class="fas fa-bullseye me-1"></i>Tujuan:</strong> 
                            ${conflict.purpose}
                        </div>
                        <div class="mb-2">
                            <strong><i class="fas fa-users me-1"></i>Peserta:</strong> 
                            ${conflict.participant_count || 'N/A'} orang
                        </div>
                        ${conflict.user_name ? `
                            <div class="mb-2">
                                <strong><i class="fas fa-user me-1"></i>Oleh:</strong> 
                                <span class="text-info">${conflict.user_name}</span>
                            </div>
                        ` : ''}
                        ${conflict.description ? `
                            <div class="mb-0">
                                <strong><i class="fas fa-sticky-note me-1"></i>Catatan:</strong> 
                                <small class="text-muted">${conflict.description}</small>
                            </div>
                        ` : ''}
                    </div>
                    <div class="card-footer bg-light">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Waktu ini tidak dapat digunakan karena sudah direservasi
                        </small>
                    </div>
                </div>
            </div>
        `;
    });
    
    conflictHtml += '</div>';
    
    // Add summary at the bottom
    conflictHtml += `
        <div class="alert alert-info mt-3">
            <i class="fas fa-lightbulb me-2"></i>
            <strong>💡 Saran:</strong> Silakan pilih waktu lain atau gunakan saran waktu tersedia di bawah ini.
        </div>
    `;
    
    conflictList.innerHTML = conflictHtml;
    conflictDiv.style.display = 'block';
    
    // Highlight conflicting time inputs with animation
    highlightConflictingInputs();
}

function getStatusBadgeClass(status) {
    switch(status) {
        case 'approved': return 'bg-success';
        case 'pending': return 'bg-warning text-dark';
        case 'rejected': return 'bg-secondary';
        case 'cancelled': return 'bg-dark';
        default: return 'bg-secondary';
    }
}

function getStatusIcon(status) {
    switch(status) {
        case 'approved': return '<i class="fas fa-check"></i>';
        case 'pending': return '<i class="fas fa-hourglass-half"></i>';
        case 'rejected': return '<i class="fas fa-times"></i>';
        case 'cancelled': return '<i class="fas fa-ban"></i>';
        default: return '<i class="fas fa-question"></i>';
    }
}

function highlightConflictingInputs() {
    const startInput = document.getElementById('start_time');
    const endInput = document.getElementById('end_time');
    
    [startInput, endInput].forEach(input => {
        input.classList.add('border-danger');
        input.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
        input.style.backgroundColor = '#ffeaa7';
        
        // Add shake animation
        input.style.animation = 'shake 0.5s ease-in-out';
    });
    
    // Remove highlight and animation after 3 seconds
    setTimeout(() => {
        [startInput, endInput].forEach(input => {
            input.classList.remove('border-danger');
            input.style.boxShadow = '';
            input.style.backgroundColor = '';
            input.style.animation = '';
        });
    }, 3000);
}

function displaySuggestedSlots(slots) {
    const suggestedDiv = document.getElementById('suggestedSlots');
    const slotsList = document.getElementById('slotsList');
    
    let slotsHtml = `
        <div class="alert alert-info mb-3">
            <i class="fas fa-lightbulb me-2"></i>
            <strong>💡 Kami menemukan ${slots.length} slot waktu yang tersedia:</strong>
            <br><small>Klik pada waktu yang diinginkan untuk menggunakan saran tersebut</small>
        </div>
        <div class="row">
    `;
    
    slots.forEach((slot, index) => {
        const hours = Math.floor(slot.duration / 60);
        const minutes = slot.duration % 60;
        const typeIcon = slot.type === 'before_reservation' ? 'fa-arrow-left' : 'fa-arrow-right';
        
        slotsHtml += `
            <div class="col-md-4 mb-2">
                <button type="button" class="btn btn-outline-success btn-sm w-100 suggested-slot shadow-sm position-relative" 
                        data-start="${slot.start}" data-end="${slot.end}"
                        title="Klik untuk menggunakan waktu ini">
                    <div class="d-flex flex-column align-items-center">
                        <div class="fw-bold mb-1">
                            <i class="fas fa-clock me-1"></i>${slot.start} - ${slot.end}
                        </div>
                        <small class="text-muted mb-1">
                            <i class="fas fa-hourglass me-1"></i>${hours}j ${minutes}m tersedia
                        </small>
                        ${slot.note ? `
                            <small class="text-info">
                                <i class="fas ${typeIcon} me-1"></i>${slot.note}
                            </small>
                        ` : ''}
                    </div>
                </button>
            </div>
        `;
    });
    
    slotsHtml += '</div>';
    
    // Add helpful tip
    slotsHtml += `
        <div class="alert alert-success mt-3">
            <i class="fas fa-hand-pointer me-2"></i>
            <strong>👆 Tip:</strong> Klik pada salah satu waktu di atas untuk langsung mengisi form dengan waktu tersebut.
        </div>
    `;
    
    slotsList.innerHTML = slotsHtml;
    suggestedDiv.style.display = 'block';
    
    // Add click handlers for suggested slots with enhanced feedback
    document.querySelectorAll('.suggested-slot').forEach(button => {
        button.addEventListener('click', function() {
            const startTime = this.dataset.start;
            const endTime = this.dataset.end;
            
            // Highlight the button as selected with animation
            document.querySelectorAll('.suggested-slot').forEach(btn => {
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-success');
            });
            this.classList.remove('btn-outline-success');
            this.classList.add('btn-success');
            this.innerHTML += ' <i class="fas fa-check ms-1"></i>';
            
            // Update time inputs with smooth transition
            const startInput = document.getElementById('start_time');
            const endInput = document.getElementById('end_time');
            
            // Add transition effect
            [startInput, endInput].forEach(input => {
                input.style.transition = 'all 0.3s ease';
                input.style.backgroundColor = '#d4edda';
                input.style.borderColor = '#28a745';
            });
            
            startInput.value = startTime;
            endInput.value = endTime;
            
            // Show success toast with details
            const hours = Math.floor((new Date(`2000-01-01T${endTime}:00`) - new Date(`2000-01-01T${startTime}:00`)) / 1000 / 60 / 60);
            const minutes = Math.floor((new Date(`2000-01-01T${endTime}:00`) - new Date(`2000-01-01T${startTime}:00`)) / 1000 / 60) % 60;
            
            showToast('success', '✅ Waktu Diperbarui!', 
                `Waktu reservasi diubah ke ${startTime} - ${endTime} (${hours}j ${minutes}m)`);
            
            // Reset input styles after animation
            setTimeout(() => {
                startInput.style.backgroundColor = '';
                endInput.style.backgroundColor = '';
                startInput.style.borderColor = '';
                endInput.style.borderColor = '';
            }, 2000);
            
            // Auto-check availability with new times
            setTimeout(() => {
                clearAllNotifications();
                autoCheckAvailability();
            }, 1000);
        });
    });
}

function showNoSlotsMessage() {
    const suggestedDiv = document.getElementById('suggestedSlots');
    const slotsList = document.getElementById('slotsList');
    
    slotsList.innerHTML = `
        <div class="alert alert-warning">
            <i class="fas fa-calendar-times me-2"></i>
            <strong>⚠️ Tidak ada slot waktu tersedia</strong>
            <br><small>Semua waktu pada hari ini sudah direservasi. Silakan pilih tanggal lain.</small>
        </div>
    `;
    
    suggestedDiv.style.display = 'block';
}

// Enhanced form validation with better error messages
document.getElementById('reservationForm').addEventListener('submit', function(e) {
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;
    const submitBtn = document.getElementById('submitBtn');
    const lab = document.getElementById('laboratory_id').value;
    const date = document.getElementById('reservation_date').value;
    
    // Comprehensive validation
    if (!lab) {
        e.preventDefault();
        showToast('danger', '❌ Validasi Error', 'Silakan pilih laboratorium terlebih dahulu!');
        document.getElementById('laboratory_id').focus();
        return false;
    }
    
    if (!date) {
        e.preventDefault();
        showToast('danger', '❌ Validasi Error', 'Silakan pilih tanggal reservasi!');
        document.getElementById('reservation_date').focus();
        return false;
    }
    
    if (!startTime || !endTime) {
        e.preventDefault();
        showToast('danger', '❌ Validasi Error', 'Silakan isi waktu mulai dan selesai!');
        return false;
    }
    
    if (startTime >= endTime) {
        e.preventDefault();
        showToast('danger', '❌ Error Waktu', 'Waktu selesai harus lebih besar dari waktu mulai!');
        document.getElementById('end_time').focus();
        return false;
    }
    
    if (submitBtn.disabled) {
        e.preventDefault();
        showToast('danger', '🚫 Tidak Dapat Menyimpan', 
            'Terdapat konflik waktu! Silakan periksa ketersediaan dan pilih waktu lain.');
        return false;
    }
    
    // Show loading state with progress indication
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan Reservasi...';
    submitBtn.disabled = true;
    
    // Show submission toast
    showToast('info', '📝 Menyimpan Reservasi...', 'Sedang memproses permintaan reservasi Anda');
    
    // Show browser notification
    showBrowserNotification('Menyimpan Reservasi', 
        'Sedang memproses reservasi untuk ' + document.querySelector('#laboratory_id option:checked').textContent);
});

// Add CSS for shake animation
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 20%, 40%, 60%, 80%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    }
    
    .suggested-slot:hover {
        transform: translateY(-2px);
        transition: transform 0.2s ease;
    }
    
    .conflict-highlight {
        animation: pulse 1s infinite;
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }
`;
document.head.appendChild(style);
</script>
@endsection