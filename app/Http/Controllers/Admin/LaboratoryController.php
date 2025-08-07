<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laboratory;
use Illuminate\Http\Request;

class LaboratoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $laboratories = Laboratory::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.laboratories.index', compact('laboratories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.laboratories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Laboratory::create([
            'name' => $request->name,
            'capacity' => $request->capacity,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.laboratories.index')
            ->with('success', 'Laboratorium berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Laboratory $laboratory)
    {
        return view('admin.laboratories.show', compact('laboratory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Laboratory $laboratory)
    {
        return view('admin.laboratories.edit', compact('laboratory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Laboratory $laboratory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $laboratory->update([
            'name' => $request->name,
            'capacity' => $request->capacity,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.laboratories.index')
            ->with('success', 'Laboratorium berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Laboratory $laboratory)
    {
        // Check if laboratory has any reservations
        if ($laboratory->reservations()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus laboratorium yang memiliki reservasi.');
        }

        $laboratory->delete();

        return redirect()->route('admin.laboratories.index')
            ->with('success', 'Laboratorium berhasil dihapus.');
    }
}