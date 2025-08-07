@extends('layouts.app')

@section('title', 'Edit Laboratorium - ' . $laboratory->name)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-edit me-2"></i>Edit Laboratorium</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.laboratories.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-flask me-2"></i>Form Edit Laboratorium
                </h5>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.laboratories.update', $laboratory) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nama Laboratorium <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $laboratory->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="code" class="form-label">Kode Laboratorium <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                   id="code" name="code" value="{{ old('code', $laboratory->code) }}" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="capacity" class="form-label">Kapasitas <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('capacity') is-invalid @enderror" 
                                       id="capacity" name="capacity" value="{{ old('capacity', $laboratory->capacity) }}" min="1" required>
                                <span class="input-group-text">orang</span>
                            </div>
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Lokasi</label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                   id="location" name="location" value="{{ old('location', $laboratory->location) }}" 
                                   placeholder="Contoh: Gedung A Lantai 2">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="facilities" class="form-label">Fasilitas</label>
                        <textarea class="form-control @error('facilities') is-invalid @enderror" 
                                  id="facilities" name="facilities" rows="3" 
                                  placeholder="Contoh: Mikroskop, Komputer, AC, Proyektor, dll">{{ old('facilities', $laboratory->facilities) }}</textarea>
                        @error('facilities')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4" 
                                  placeholder="Deskripsi singkat tentang laboratorium">{{ old('description', $laboratory->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="">Pilih Status</option>
                            <option value="aktif" {{ old('status', $laboratory->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="tidak_aktif" {{ old('status', $laboratory->status) == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                            <option value="maintenance" {{ old('status', $laboratory->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Foto Laboratorium</label>
                        <input type="file" class="form-control @error('image') is-invalid @enderror" 
                               id="image" name="image" accept="image/*">
                        <div class="form-text">Format: JPG, PNG, GIF. Maksimal 2MB. Biarkan kosong jika tidak ingin mengubah foto.</div>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        
                        @if($laboratory->image)
                        <div class="mt-2">
                            <p class="mb-1">Foto saat ini:</p>
                            <img src="{{ asset('storage/' . $laboratory->image) }}" alt="{{ $laboratory->name }}" 
                                 class="img-thumbnail" style="max-height: 150px;">
                        </div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.laboratories.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Informasi Laboratorium
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Dibuat pada:</small>
                    <div>{{ $laboratory->created_at->format('d M Y, H:i') }}</div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Terakhir diupdate:</small>
                    <div>{{ $laboratory->updated_at->format('d M Y, H:i') }}</div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Total Reservasi:</small>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-check text-primary me-1"></i>
                        <span class="fw-bold">{{ $laboratory->reservations_count ?? 0 }}</span>
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Status Saat Ini:</small>
                    <div>
                        @if($laboratory->status == 'aktif')
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle me-1"></i>Aktif
                            </span>
                        @elseif($laboratory->status == 'maintenance')
                            <span class="badge bg-warning">
                                <i class="fas fa-wrench me-1"></i>Maintenance
                            </span>
                        @else
                            <span class="badge bg-danger">
                                <i class="fas fa-times-circle me-1"></i>Tidak Aktif
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.laboratories.show', $laboratory) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye me-1"></i>Lihat Detail
                    </a>
                    <form action="{{ route('admin.laboratories.destroy', $laboratory) }}" method="POST" 
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus laboratorium ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                            <i class="fas fa-trash me-1"></i>Hapus Laboratorium
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Preview image before upload
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Remove existing preview if any
                const existingPreview = document.getElementById('image-preview');
                if (existingPreview) {
                    existingPreview.remove();
                }
                
                // Create new preview
                const preview = document.createElement('div');
                preview.id = 'image-preview';
                preview.className = 'mt-2';
                preview.innerHTML = `
                    <p class="mb-1">Preview foto baru:</p>
                    <img src="${e.target.result}" alt="Preview" class="img-thumbnail" style="max-height: 150px;">
                `;
                
                // Insert after file input
                e.target.parentNode.insertBefore(preview, e.target.nextSibling);
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Auto-generate code from name
    document.getElementById('name').addEventListener('input', function(e) {
        const name = e.target.value;
        const codeInput = document.getElementById('code');
        
        // Only auto-generate if code field is empty
        if (!codeInput.value || codeInput.dataset.autoGenerated === 'true') {
            const code = name.toLowerCase()
                           .replace(/\s+/g, '_')
                           .replace(/[^a-z0-9_]/g, '')
                           .substring(0, 20);
            
            if (code) {
                codeInput.value = code;
                codeInput.dataset.autoGenerated = 'true';
            }
        }
    });
    
    // Remove auto-generated flag when user manually edits code
    document.getElementById('code').addEventListener('input', function(e) {
        e.target.dataset.autoGenerated = 'false';
    });
</script>
@endpush