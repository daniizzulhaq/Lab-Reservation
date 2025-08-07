@extends('layouts.admin')

@section('title', 'Detail Reservasi')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Reservasi</h1>
        <a href="{{ route('admin.reservations.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
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

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Reservasi</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID Reservasi:</strong></td>
                                    <td>#{{ $reservation->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Pengguna:</strong></td>
                                    <td>{{ $reservation->user->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $reservation->user->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Laboratorium:</strong></td>
                                    <td>{{ $reservation->laboratory->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($reservation->reservation_date)->format('d/m/Y') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Waktu:</strong></td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($reservation->start_time)->format('H:i') }} - 
                                        {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @switch($reservation->status)
                                            @case('pending')
                                                <span class="badge badge-warning fs-6">Menunggu</span>
                                                @break
                                            @case('approved')
                                                <span class="badge badge-success fs-6">Disetujui</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge badge-danger fs-6">Ditolak</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge badge-secondary fs-6">Dibatalkan</span>
                                                @break
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Dibuat:</strong></td>
                                    <td>{{ $reservation->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @if($reservation->approved_by)
                                    <tr>
                                        <td><strong>Diproses oleh:</strong></td>
                                        <td>{{ $reservation->approvedBy->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal Diproses:</strong></td>
                                        <td>{{ $reservation->approved_at ? $reservation->approved_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($reservation->purpose)
                        <div class="mt-3">
                            <strong>Tujuan Penggunaan:</strong>
                            <p class="mt-2">{{ $reservation->purpose }}</p>
                        </div>
                    @endif

                    @if($reservation->admin_notes)
                        <div class="mt-3">
                            <strong>Catatan Admin:</strong>
                            <div class="alert alert-info mt-2">
                                {{ $reservation->admin_notes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            @if($reservation->status === 'pending')
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Tindakan</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success" 
                                    data-toggle="modal" data-target="#approveModal">
                                <i class="fas fa-check"></i> Setujui Reservasi
                            </button>
                            <button type="button" class="btn btn-danger" 
                                    data-toggle="modal" data-target="#rejectModal">
                                <i class="fas fa-times"></i> Tolak Reservasi
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Approve Modal -->
                <div class="modal fade" id="approveModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('admin.reservations.approve', $reservation) }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Setujui Reservasi</h5>
                                    <button type="button" class="close" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>Apakah Anda yakin ingin menyetujui reservasi ini?</p>
                                    <div class="mb-3">
                                        <label for="admin_notes" class="form-label">Catatan Admin (Opsional)</label>
                                        <textarea name="admin_notes" id="admin_notes" 
                                                  class="form-control" rows="3" 
                                                  placeholder="Catatan tambahan..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-success">Setujui</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Reject Modal -->
                <div class="modal fade" id="rejectModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('admin.reservations.reject', $reservation) }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Tolak Reservasi</h5>
                                    <button type="button" class="close" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>Apakah Anda yakin ingin menolak reservasi ini?</p>
                                    <div class="mb-3">
                                        <label for="admin_notes_reject" class="form-label">Alasan Penolakan *</label>
                                        <textarea name="admin_notes" id="admin_notes_reject" 
                                                  class="form-control" rows="3" required
                                                  placeholder="Berikan alasan penolakan..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-danger">Tolak</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Laboratorium</h6>
                </div>
                <div class="card-body">
                    <h5>{{ $reservation->laboratory->name ?? 'N/A' }}</h5>
                    <p><strong>Kapasitas:</strong> {{ $reservation->laboratory->capacity ?? 'N/A' }} orang</p>
                    @if($reservation->laboratory->description)
                        <p><strong>Deskripsi:</strong></p>
                        <p class="text-muted">{{ $reservation->laboratory->description }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection