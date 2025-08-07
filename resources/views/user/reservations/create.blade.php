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
                                   id="start_time" name="start_time" value="{{ old('start_time') }}" 
                                   min="08:00" max="17:00" required>
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="end_time" class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                   id="end_time" name="end_time" value="{{ old('end_time') }}" 
                                   min="08:00" max="17:00" required>
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('time')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
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
                        <strong>Jam Operasional:</strong> 08:00 - 17:00 WIB
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-calendar-alt text-success me-2"></i>
                        <strong>Hari Kerja:</strong> Senin - Jumat
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-user-check text-warning me-2"></i>
                        <strong>Persetujuan:</strong> Dibutuhkan persetujuan admin
                    </li>
                    <li class="mb-0">
                        <i class="fas fa-edit text-info me-2"></i>
                        <strong>Edit:</strong> Hanya reservasi pending yang dapat diedit
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
                            <li>Jam operasional laboratorium: 08:00 - 17:00</li>
                            <li>Jumlah peserta tidak boleh melebihi kapasitas lab</li>
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
    });
    
    // Auto-check availability when inputs change
    ['laboratory_id', 'reservation_date', 'start_time', 'end_time'].forEach(id => {
        document.getElementById(id).addEventListener('change', autoCheckAvailability);
    });
    
    // Manual check availability
    checkBtn.addEventListener('click', checkAvailability);
    
    // Trigger initial capacity display
    labSelect.dispatchEvent(new Event('change'));
});

function autoCheckAvailability() {
    const lab = document.getElementById('laboratory_id').value;
    const date = document.getElementById('reservation_date').value;
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;
    
    if (lab && date && startTime && endTime) {
        checkAvailability();
    }
}

function checkAvailability() {
    const lab = document.getElementById('laboratory_id').value;
    const date = document.getElementById('reservation_date').value;
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;
    const alertDiv = document.getElementById('availabilityAlert');
    
    if (!lab || !date || !startTime || !endTime) {
        alertDiv.style.display = 'none';
        return;
    }
    
    alertDiv.style.display = 'block';
    alertDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin me-2"></i>Memeriksa ketersediaan...</div>';
    
    fetch(`{{ route('admin.api.availability-check') }}?laboratory_id=${lab}&date=${date}&start_time=${startTime}&end_time=${endTime}`)
        .then(response => response.json())
        .then(data => {
            if (data.available) {
                alertDiv.innerHTML = `
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        Laboratorium tersedia pada waktu yang Anda pilih!
                    </div>
                `;
                document.getElementById('submitBtn').disabled = false;
            } else {
                alertDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle me-2"></i>
                        Laboratorium tidak tersedia pada waktu tersebut. Silakan pilih waktu lain.
                    </div>
                `;
                document.getElementById('submitBtn').disabled = true;
            }
        })
        .catch(error => {
            alertDiv.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Gagal memeriksa ketersediaan. Silakan coba lagi.
                </div>
            `;
        });
}

// Form validation
document.getElementById('reservationForm').addEventListener('submit', function(e) {
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;
    
    if (startTime >= endTime) {
        e.preventDefault();
        alert('Waktu selesai harus lebih besar dari waktu mulai!');
        return false;
    }
    
    // Additional validations can be added here
});
</script>
@endsection