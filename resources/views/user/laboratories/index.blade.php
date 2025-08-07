@extends('layouts.app')

@section('title', 'Daftar Laboratorium')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-flask me-2 text-primary"></i>Daftar Laboratorium
        </h1>
    </div>

    <!-- Search and Filter Form -->
    <div class="card shadow-sm mb-4">
        <div class="card-body bg-light">
            <form method="GET" action="{{ route('user.laboratories.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label fw-semibold">
                            <i class="fas fa-search text-primary me-1"></i>Cari Laboratorium
                        </label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Nama, kode, atau lokasi...">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="capacity" class="form-label fw-semibold">
                            <i class="fas fa-users text-primary me-1"></i>Kapasitas Minimal
                        </label>
                        <input type="number" class="form-control" id="capacity" name="capacity" 
                               value="{{ request('capacity') }}" min="1" placeholder="Jumlah orang">
                    </div>
                    
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="btn-group w-100" role="group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>Cari
                            </button>
                            <a href="{{ route('user.laboratories.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('user.reservations.create') }}" class="btn btn-success w-100">
                            <i class="fas fa-plus me-1"></i>Reservasi
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Laboratory Cards -->
    <div class="row">
        @forelse($laboratories as $lab)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0 lab-card">
                <!-- Card Header -->
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1 fw-bold">
                                <i class="fas fa-flask me-2"></i>{{ $lab->name }}
                            </h5>
                            <small class="opacity-75">{{ $lab->code }}</small>
                        </div>
                        <span class="badge bg-light text-primary">
                            <i class="fas fa-check-circle me-1"></i>{{ ucfirst($lab->status) }}
                        </span>
                    </div>
                </div>
                
                <!-- Card Body -->
                <div class="card-body">
                    <!-- Lab Details -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-users text-primary me-2"></i>
                                <small class="text-muted">Kapasitas:</small>
                            </div>
                            <div class="fw-bold">{{ $lab->capacity }} orang</div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                <small class="text-muted">Lokasi:</small>
                            </div>
                            <div class="fw-semibold">{{ $lab->location ?: 'Tidak disebutkan' }}</div>
                        </div>
                    </div>
                    
                    <!-- Facilities -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-tools text-success me-2"></i>
                            <small class="text-muted fw-semibold">Fasilitas:</small>
                        </div>
                        <p class="small text-secondary mb-0">
                            {{ $lab->facilities ? Str::limit($lab->facilities, 80) : 'Tidak ada informasi fasilitas' }}
                        </p>
                    </div>
                    
                    <!-- Description -->
                    @if($lab->description)
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            <small class="text-muted fw-semibold">Deskripsi:</small>
                        </div>
                        <p class="text-secondary small">{{ Str::limit($lab->description, 100) }}</p>
                    </div>
                    @endif
                    
                    <!-- Statistics -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-calendar-check text-success me-2"></i>
                            <small class="text-muted">
                                {{ $lab->reservations_count ?? 0 }} reservasi
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Card Footer -->
                <div class="card-footer bg-white border-top-0">
                    <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                        <a href="{{ route('user.laboratories.show', $lab) }}" 
                           class="btn btn-outline-primary flex-fill">
                            <i class="fas fa-eye me-1"></i>Detail
                        </a>
                        <a href="{{ route('user.reservations.create', ['laboratory_id' => $lab->id]) }}" 
                           class="btn btn-primary flex-fill">
                            <i class="fas fa-calendar-plus me-1"></i>Reservasi
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <!-- Empty State -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-search fa-4x text-muted opacity-50"></i>
                    </div>
                    <h5 class="text-muted mb-3">Tidak ada laboratorium ditemukan</h5>
                    <p class="text-muted mb-4">Coba ubah kriteria pencarian Anda atau lihat semua laboratorium yang tersedia</p>
                    <a href="{{ route('user.laboratories.index') }}" class="btn btn-primary">
                        <i class="fas fa-list me-2"></i>Lihat Semua Laboratorium
                    </a>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($laboratories->hasPages())
    <div class="d-flex justify-content-center mt-4">
        <nav aria-label="Laboratory pagination">
            {{ $laboratories->appends(request()->query())->links('pagination::bootstrap-4') }}
        </nav>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.lab-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.lab-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

.card-header.bg-primary {
    background: linear-gradient(135deg, #007bff, #0056b3) !important;
}

.badge.bg-light {
    border: 1px solid rgba(255,255,255,0.3);
}

.btn-group .btn {
    flex: 1;
}

.form-label.fw-semibold {
    font-weight: 600 !important;
}

/* Icon colors */
.text-primary { color: #007bff !important; }
.text-danger { color: #dc3545 !important; }
.text-success { color: #28a745 !important; }
.text-info { color: #17a2b8 !important; }

/* Custom spacing */
.g-3 > * {
    padding-right: calc(var(--bs-gutter-x) * .5);
    padding-left: calc(var(--bs-gutter-x) * .5);
    margin-top: var(--bs-gutter-y);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .lab-card:hover {
        transform: none;
    }
    
    .col-6 {
        margin-bottom: 1rem;
    }
    
    .d-md-flex {
        flex-direction: column;
    }
    
    .d-md-flex .btn {
        margin-bottom: 0.5rem;
    }
    
    .d-md-flex .btn:last-child {
        margin-bottom: 0;
    }
}

/* Loading animation for cards */
.lab-card.loading {
    opacity: 0.7;
    pointer-events: none;
}

/* Pagination styling */
.pagination {
    --bs-pagination-padding-x: 0.75rem;
    --bs-pagination-padding-y: 0.375rem;
    --bs-pagination-color: #007bff;
    --bs-pagination-border-color: #dee2e6;
}

.page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add click handler for lab cards
    const labCards = document.querySelectorAll('.lab-card');
    
    labCards.forEach(card => {
        // Skip cards that are empty state
        if (!card.querySelector('.card-footer')) return;
        
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking on buttons
            if (e.target.closest('.btn') || e.target.closest('a')) return;
            
            const detailLink = this.querySelector('a[href*="laboratories"][href*="show"]');
            if (detailLink) {
                window.location.href = detailLink.href;
            }
        });
    });
    
    // Add loading state to form submission
    const searchForm = document.querySelector('form[method="GET"]');
    if (searchForm) {
        searchForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Mencari...';
                submitBtn.disabled = true;
            }
        });
    }
});
</script>
@endpush