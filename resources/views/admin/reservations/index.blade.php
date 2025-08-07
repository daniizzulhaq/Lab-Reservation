@extends('layouts.admin')

@section('title', 'Kelola Reservasi')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Kelola Reservasi</h1>
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

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Reservasi</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reservations.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="laboratory_id" class="form-label">Laboratorium</label>
                        <select name="laboratory_id" id="laboratory_id" class="form-control">
                            <option value="">Semua Laboratorium</option>
                            @foreach($laboratories as $lab)
                                <option value="{{ $lab->id }}" {{ request('laboratory_id') == $lab->id ? 'selected' : '' }}>
                                    {{ $lab->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date" class="form-label">Tanggal</label>
                        <input type="date" name="date" id="date" class="form-control" value="{{ request('date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.reservations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Reservations Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Reservasi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Pengguna</th>
                            <th>Laboratorium</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservations as $index => $reservation)
                            <tr>
                                <td>{{ $reservations->firstItem() + $index }}</td>
                                <td>
                                    <div>
                                        <strong>{{ $reservation->user->name ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $reservation->user->email ?? 'N/A' }}</small>
                                    </div>
                                </td>
                                <td>{{ $reservation->laboratory->name ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($reservation->reservation_date)->format('d/m/Y') }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($reservation->start_time)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}
                                </td>
                                <td>
                                    <!-- Debug: hapus setelah berhasil -->
                                   
                                    
                                    @if($reservation->status == 'pending')
                                        <span class="badge badge-warning" style="background-color: #ffc107 !important; color: #212529 !important; padding: 0.5em 0.75em; font-size: 0.8em; border-radius: 0.25rem; display: inline-block;">Menunggu</span>
                                    @elseif($reservation->status == 'approved')
                                        <span class="badge badge-success" style="background-color: #28a745 !important; color: white !important; padding: 0.5em 0.75em; font-size: 0.8em; border-radius: 0.25rem; display: inline-block;">Disetujui</span>
                                    @elseif($reservation->status == 'rejected')
                                        <span class="badge badge-danger" style="background-color: #dc3545 !important; color: white !important; padding: 0.5em 0.75em; font-size: 0.8em; border-radius: 0.25rem; display: inline-block;">Ditolak</span>
                                    @elseif($reservation->status == 'cancelled')
                                        <span class="badge badge-secondary" style="background-color: #6c757d !important; color: white !important; padding: 0.5em 0.75em; font-size: 0.8em; border-radius: 0.25rem; display: inline-block;">Dibatalkan</span>
                                    @else
                                        <span class="badge badge-light" style="background-color: #f8f9fa !important; color: #495057 !important; border: 1px solid #dee2e6; padding: 0.5em 0.75em; font-size: 0.8em; border-radius: 0.25rem; display: inline-block;">{{ ucfirst($reservation->status ?? 'Tidak Diketahui') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.reservations.show', $reservation) }}" 
                                           class="btn btn-info btn-sm" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if(strtolower(trim($reservation->status ?? '')) == 'pending')
                                            <button type="button" class="btn btn-success btn-sm approve-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#approveModal{{ $reservation->id }}" 
                                                    data-reservation-id="{{ $reservation->id }}"
                                                    title="Setujui">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm reject-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#rejectModal{{ $reservation->id }}" 
                                                    data-reservation-id="{{ $reservation->id }}"
                                                    title="Tolak">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data reservasi</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($reservations->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $reservations->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modals -->
@foreach($reservations as $reservation)
    @if(strtolower(trim($reservation->status ?? '')) == 'pending')
        <!-- Approve Modal -->
        <div class="modal fade" id="approveModal{{ $reservation->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('admin.reservations.approve', $reservation) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Setujui Reservasi</h5>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <strong>Detail Reservasi:</strong>
                                <ul>
                                    <li>Pengguna: {{ $reservation->user->name ?? 'N/A' }}</li>
                                    <li>Laboratorium: {{ $reservation->laboratory->name ?? 'N/A' }}</li>
                                    <li>Tanggal: {{ \Carbon\Carbon::parse($reservation->reservation_date)->format('d/m/Y') }}</li>
                                    <li>Waktu: {{ \Carbon\Carbon::parse($reservation->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}</li>
                                </ul>
                            </div>
                            <p>Apakah Anda yakin ingin menyetujui reservasi ini?</p>
                            <div class="mb-3">
                                <label for="admin_notes_approve{{ $reservation->id }}" class="form-label">Catatan Admin (Opsional)</label>
                                <textarea name="admin_notes" id="admin_notes_approve{{ $reservation->id }}" 
                                          class="form-control" rows="3" 
                                          placeholder="Catatan tambahan..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Setujui
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal{{ $reservation->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('admin.reservations.reject', $reservation) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Tolak Reservasi</h5>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <strong>Detail Reservasi:</strong>
                                <ul>
                                    <li>Pengguna: {{ $reservation->user->name ?? 'N/A' }}</li>
                                    <li>Laboratorium: {{ $reservation->laboratory->name ?? 'N/A' }}</li>
                                    <li>Tanggal: {{ \Carbon\Carbon::parse($reservation->reservation_date)->format('d/m/Y') }}</li>
                                    <li>Waktu: {{ \Carbon\Carbon::parse($reservation->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}</li>
                                </ul>
                            </div>
                            <p>Apakah Anda yakin ingin menolak reservasi ini?</p>
                            <div class="mb-3">
                                <label for="admin_notes_reject{{ $reservation->id }}" class="form-label">
                                    Alasan Penolakan <span class="text-danger">*</span>
                                </label>
                                <textarea name="admin_notes" id="admin_notes_reject{{ $reservation->id }}" 
                                          class="form-control" rows="3" required
                                          placeholder="Berikan alasan penolakan..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times"></i> Tolak
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endsection

@push('scripts')
<script>
// Fungsi untuk menampilkan modal
function showApproveModal(reservationId) {
    $('#approveModal' + reservationId).modal('show');
}

function showRejectModal(reservationId) {
    $('#rejectModal' + reservationId).modal('show');
}

$(document).ready(function() {
    // Handle approve button click
    $('.approve-btn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var reservationId = $(this).data('reservation-id');
        var modalId = '#approveModal' + reservationId;
        
        // Show modal using jQuery
        $(modalId).modal('show');
    });

    // Handle reject button click
    $('.reject-btn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var reservationId = $(this).data('reservation-id');
        var modalId = '#rejectModal' + reservationId;
        
        // Show modal using jQuery
        $(modalId).modal('show');
    });

    // Handle form submission dengan loading state
    $('form[action*="approve"], form[action*="reject"]').on('submit', function() {
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
        
        // Fallback untuk mengaktifkan kembali tombol
        setTimeout(function() {
            submitBtn.prop('disabled', false);
            submitBtn.html(originalText);
        }, 5000);
    });

    // Auto-dismiss alerts
    $('.alert').delay(5000).fadeOut();
});
</script>
@endpush

@push('styles')
<style>
/* Badge Styling */
.badge {
    font-size: 0.75em !important;
    padding: 0.4em 0.8em !important;
    border-radius: 0.25rem !important;
    font-weight: 600 !important;
    display: inline-block !important;
    text-align: center !important;
}

.badge-warning {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

.badge-success {
    background-color: #28a745 !important;
    color: #ffffff !important;
}

.badge-danger {
    background-color: #dc3545 !important;
    color: #ffffff !important;
}

.badge-secondary {
    background-color: #6c757d !important;
    color: #ffffff !important;
}

.badge-light {
    background-color: #f8f9fa !important;
    color: #495057 !important;
    border: 1px solid #dee2e6 !important;
}

/* Button Styling */
.btn-group .btn {
    margin-right: 2px;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: 0.2rem;
}

.btn-group > .btn {
    position: relative;
    z-index: 1;
}

/* Table Styling */
.table td {
    vertical-align: middle !important;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

/* Modal Styling */
.modal-dialog {
    max-width: 600px;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

/* Alert Styling */
.alert {
    border-radius: 0.375rem;
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Form Styling */
.form-control {
    border-radius: 0.375rem;
    border: 1px solid #d1d3e2;
}

.form-control:focus {
    border-color: #5a5c69;
    box-shadow: 0 0 0 0.2rem rgba(90, 92, 105, 0.25);
}

/* Card Styling */
.card {
    border-radius: 0.35rem;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e3e6f0;
}

/* Responsive */
@media (max-width: 768px) {
    .btn-group {
        display: flex;
        flex-direction: column;
        width: 100%;
    }
    
    .btn-group .btn {
        margin-right: 0;
        margin-bottom: 2px;
    }
}
</style>
@endpush