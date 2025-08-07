<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laboratory;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $laboratories = Laboratory::withCount('reservations')->paginate(10);
        return view('admin.laboratories.index', compact('laboratories'));
    }

    public function create()
    {
        return view('admin.laboratories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:laboratories',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'facilities' => 'nullable|string',
            'status' => 'required|in:active,maintenance,inactive',
            'location' => 'nullable|string',
        ]);

        Laboratory::create($validated);

        return redirect()->route('admin.laboratories.index')
            ->with('success', 'Laboratorium berhasil ditambahkan.');
    }

    public function show(Laboratory $laboratory)
    {
        $laboratory->load(['reservations' => function($query) {
            $query->with('user')->orderBy('reservation_date', 'desc');
        }]);

        return view('admin.laboratories.show', compact('laboratory'));
    }

    public function edit(Laboratory $laboratory)
    {
        return view('admin.laboratories.edit', compact('laboratory'));
    }

    public function update(Request $request, Laboratory $laboratory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:laboratories,code,' . $laboratory->id,
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'facilities' => 'nullable|string',
            'status' => 'required|in:active,maintenance,inactive',
            'location' => 'nullable|string',
        ]);

        $laboratory->update($validated);

        return redirect()->route('admin.laboratories.index')
            ->with('success', 'Laboratorium berhasil diperbarui.');
    }

    public function destroy(Laboratory $laboratory)
    {
        if ($laboratory->reservations()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus laboratorium yang memiliki reservasi.');
        }

        $laboratory->delete();

        return redirect()->route('admin.laboratories.index')
            ->with('success', 'Laboratorium berhasil dihapus.');
    }
}
