@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-bell me-2 text-primary"></i>
                        Notifikasi
                    </h2>
                    <p class="text-muted mb-0">Kelola semua notifikasi Anda</p>
                </div>
                
                @if($unreadCount > 0)
                    <div class="d-flex gap-2">
                        <form action="{{ route('user.notifications.readAll') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check-double me-1"></i>
                                Tandai Semua Dibaca ({{ $unreadCount }})
                            </button>
                        </form>
                        
                        <form action="{{ route('user.notifications.deleteAllRead') }}" method="POST" class="d-inline" 
                              onsubmit="return confirm('Yakin ingin menghapus semua notifikasi yang sudah dibaca?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-trash me-1"></i>
                                Hapus yang Dibaca
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Filter Controls -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('user.notifications.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Filter Tipe</label>
                            <select name="type" class="form-select">
                                <option value="">Semua Tipe</option>
                                <option value="reservation_approved" {{ request('type') == 'reservation_approved' ? 'selected' : '' }}>
                                    Reservasi Disetujui
                                </option>
                                <option value="reservation_rejected" {{ request('type') == 'reservation_rejected' ? 'selected' : '' }}>
                                    Reservasi Ditolak
                                </option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Status Baca</label>
                            <select name="read_status" class="form-select">
                                <option value="">Semua</option>
                                <option value="unread" {{ request('read_status') == 'unread' ? 'selected' : '' }}>
                                    Belum Dibaca
                                </option>
                                <option value="read" {{ request('read_status') == 'read' ? 'selected' : '' }}>
                                    Sudah Dibaca
                                </option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                                <a href="{{ route('user.notifications.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="card">
                <div class="card-body p-0">
                    @if($notifications->count() > 0)
                        @foreach($notifications as $notification)
                            <div class="notification-item border-bottom {{ !$notification->read_at ? 'bg-light border-start border-primary border-4' : '' }} p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex">
                                        <!-- Notification Icon -->
                                        <div class="me-3">
                                            @php
                                                $iconClass = $notification->data['icon'] ?? 'fas fa-bell';
                                                $colorClass = match($notification->data['type'] ?? 'general') {
                                                    'reservation_approved' => 'text-success',
                                                    'reservation_rejected' => 'text-danger',
                                                    default => 'text-primary'
                                                };
                                            @endphp
                                            <div class="notification-icon-wrapper p-2 rounded-circle bg-light">
                                                <i class="{{ $iconClass }} {{ $colorClass }}"></i>
                                            </div>
                                        </div>

                                        <!-- Notification Content -->
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-1">
                                                <h6 class="mb-0 me-2">{{ $notification->data['title'] ?? 'Notifikasi' }}</h6>
                                                @if(!$notification->read_at)
                                                    <span class="badge bg-primary">Baru</span>
                                                @endif
                                            </div>
                                            
                                            <p class="text-muted mb-2">{{ $notification->data['message'] ?? '' }}</p>
                                            
                                            <!-- Additional Details -->
                                            @if(isset($notification->data['laboratory_name']))
                                                <div class="small text-muted">
                                                    <i class="fas fa-flask me-1"></i>
                                                    <strong>Laboratorium:</strong> {{ $notification->data['laboratory_name'] }}
                                                    
                                                    @if(isset($notification->data['reservation_date']))
                                                        <span class="ms-3">
                                                            <i class="fas fa-calendar me-1"></i>
                                                            <strong>Tanggal:</strong> 
                                                            {{ \Carbon\Carbon::parse($notification->data['reservation_date'])->format('d F Y') }}
                                                        </span>
                                                    @endif
                                                    
                                                    @if(isset($notification->data['start_time']) && isset($notification->data['end_time']))
                                                        <span class="ms-3">
                                                            <i class="fas fa-clock me-1"></i>
                                                            <strong>Waktu:</strong> 
                                                            {{ $notification->data['start_time'] }} - {{ $notification->data['end_time'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif

                                            <!-- Admin Notes for Rejected Reservations -->
                                            @if(isset($notification->data['reason']) && $notification->data['reason'])
                                                <div class="mt-2 p-2 bg-light rounded">
                                                    <small class="text-muted">
                                                        <i class="fas fa-comment me-1"></i>
                                                        <strong>Catatan Admin:</strong> {{ $notification->data['reason'] }}
                                                    </small>
                                                </div>
                                            @endif
                                            
                                            <div class="d-flex align-items-center justify-content-between mt-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ $notification->created_at->diffForHumans() }}
                                                    <span class="text-muted ms-2">
                                                        ({{ $notification->created_at->format('d F Y, H:i') }})
                                                    </span>
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @if(!$notification->read_at)
                                                <li>
                                                    <form action="{{ route('user.notifications.read', $notification->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fas fa-check me-2"></i>
                                                            Tandai Dibaca
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                            
                                            @if(isset($notification->data['reservation_id']))
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('user.reservations.show', $notification->data['reservation_id']) }}">
                                                        <i class="fas fa-eye me-2"></i>
                                                        Lihat Reservasi
                                                    </a>
                                                </li>
                                            @endif
                                            
                                            <li><hr class="dropdown-divider"></li>
                                            
                                            <li>
                                                <form action="{{ route('user.notifications.destroy', $notification->id) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Yakin ingin menghapus notifikasi ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash me-2"></i>
                                                        Hapus
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-inbox fa-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted">Tidak ada notifikasi</h5>
                            <p class="text-muted">
                                @if(request()->has(['type', 'read_status']))
                                    Tidak ada notifikasi yang sesuai dengan filter yang dipilih.
                                @else
                                    Anda belum memiliki notifikasi apapun.
                                @endif
                            </p>
                            
                            @if(request()->has(['type', 'read_status']))
                                <a href="{{ route('user.notifications.index') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-times me-1"></i>
                                    Reset Filter
                                </a>
                            @else
                                <a href="{{ route('user.reservations.index') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-calendar-plus me-1"></i>
                                    Buat Reservasi
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
                
                @if($notifications->hasPages())
                    <div class="card-footer">
                        {{ $notifications->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>

            <!-- Statistics Card -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Belum Dibaca</h5>
                                    <h2 class="mb-0">{{ $unreadCount }}</h2>
                                </div>
                                <i class="fas fa-bell fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Total Notifikasi</h5>
                                    <h2 class="mb-0">{{ $totalCount }}</h2>
                                </div>
                                <i class="fas fa-list fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.notification-item {
    transition: all 0.3s ease;
}

.notification-item:hover {
    background-color: #f8f9fa !important;
}

.notification-icon-wrapper {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.badge {
    font-size: 0.75em;
}
</style>
@endpush