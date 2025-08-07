@extends('layouts.admin')

@section('title', 'Kelola Laboratorium')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Kelola Laboratorium</h1>
        <a href="{{ route('admin.laboratories.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Laboratorium
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

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Laboratorium</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Laboratorium</th>
                            <th>Kapasitas</th>
                            <th>Status</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laboratories as $index => $laboratory)
                            <tr>
                                <td>{{ $laboratories->firstItem() + $index }}</td>
                                <td>{{ $laboratory->name }}</td>
                                <td>{{ $laboratory->capacity }} orang</td>
                                <td>
                                    @if($laboratory->status == 'active')
                                        <span class="badge badge-success text-dark">Aktif</span>
                                    @else
                                        <span class="badge badge-danger text-dark">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>{{ Str::limit($laboratory->description, 50) }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.laboratories.show', $laboratory) }}" 
                                           class="btn btn-info btn-sm" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.laboratories.edit', $laboratory) }}" 
                                           class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.laboratories.destroy', $laboratory) }}" 
                                              method="POST" class="d-inline" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus laboratorium {{ $laboratory->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data laboratorium</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($laboratories->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $laboratories->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection