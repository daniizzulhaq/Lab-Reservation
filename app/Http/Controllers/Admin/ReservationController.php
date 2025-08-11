<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::with(['user', 'laboratory']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by laboratory
        if ($request->filled('laboratory_id')) {
            $query->where('laboratory_id', $request->laboratory_id);
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('reservation_date', $request->date);
        }

        $reservations = $query->orderBy('created_at', 'desc')->paginate(15);
        $laboratories = Laboratory::orderBy('name')->get();

        return view('admin.reservations.index', compact('reservations', 'laboratories'));
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['user', 'laboratory']);
        return view('admin.reservations.show', compact('reservation'));
    }

    public function approve(Request $request, Reservation $reservation)
    {
        try {
            // Validate input
            $request->validate([
                'admin_notes' => 'nullable|string|max:1000',
            ]);

            // Check if reservation is still pending
            if ($reservation->status !== 'pending') {
                return redirect()->back()->with('error', 'Reservasi ini sudah tidak dalam status menunggu.');
            }

            // Check for time conflicts with other approved reservations
            $conflictExists = Reservation::where('laboratory_id', $reservation->laboratory_id)
                ->where('reservation_date', $reservation->reservation_date)
                ->where('status', 'approved')
                ->where('id', '!=', $reservation->id)
                ->where(function ($query) use ($reservation) {
                    // Check if times overlap
                    $query->where(function ($q) use ($reservation) {
                        // New reservation starts during existing reservation
                        $q->where('start_time', '<=', $reservation->start_time)
                          ->where('end_time', '>', $reservation->start_time);
                    })->orWhere(function ($q) use ($reservation) {
                        // New reservation ends during existing reservation
                        $q->where('start_time', '<', $reservation->end_time)
                          ->where('end_time', '>=', $reservation->end_time);
                    })->orWhere(function ($q) use ($reservation) {
                        // New reservation completely contains existing reservation
                        $q->where('start_time', '>=', $reservation->start_time)
                          ->where('end_time', '<=', $reservation->end_time);
                    });
                })
                ->exists();

            if ($conflictExists) {
                return redirect()->back()->with('error', 'Terdapat konflik waktu dengan reservasi lain yang sudah disetujui pada tanggal dan jam yang sama.');
            }

            // Update reservation status
            DB::beginTransaction();
            
            $reservation->update([
                'status' => 'approved',
                'admin_notes' => $request->admin_notes,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Reservasi berhasil disetujui.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error approving reservation: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyetujui reservasi.');
        }
    }

    public function reject(Request $request, Reservation $reservation)
    {
        try {
            // Validate input - admin notes required for rejection
            $request->validate([
                'admin_notes' => 'required|string|max:1000',
            ], [
                'admin_notes.required' => 'Alasan penolakan harus diisi.',
                'admin_notes.max' => 'Alasan penolakan maksimal 1000 karakter.'
            ]);

            // Check if reservation is still pending
            if ($reservation->status !== 'pending') {
                return redirect()->back()->with('error', 'Reservasi ini sudah tidak dalam status menunggu.');
            }

            // Update reservation status
            DB::beginTransaction();
            
            $reservation->update([
                'status' => 'rejected',
                'admin_notes' => $request->admin_notes,
                'approved_by' => auth()->id(), // We use same field for who processed it
                'approved_at' => now(), // We use same field for when it was processed
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Reservasi berhasil ditolak.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error rejecting reservation: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menolak reservasi.');
        }
    }

    public function checkAvailability(Request $request)
    {
        try {
            $request->validate([
                'laboratory_id' => 'required|exists:laboratories,id',
                'date' => 'required|date',
                'start_time' => 'required',
                'end_time' => 'required',
                'exclude_reservation_id' => 'nullable|exists:reservations,id'
            ]);

            $conflictExists = Reservation::where('laboratory_id', $request->laboratory_id)
                ->where('reservation_date', $request->date)
                ->where('status', 'approved')
                ->when($request->exclude_reservation_id, function ($query) use ($request) {
                    return $query->where('id', '!=', $request->exclude_reservation_id);
                })
                ->where(function ($query) use ($request) {
                    $query->where(function ($q) use ($request) {
                        $q->where('start_time', '<=', $request->start_time)
                          ->where('end_time', '>', $request->start_time);
                    })->orWhere(function ($q) use ($request) {
                        $q->where('start_time', '<', $request->end_time)
                          ->where('end_time', '>=', $request->end_time);
                    })->orWhere(function ($q) use ($request) {
                        $q->where('start_time', '>=', $request->start_time)
                          ->where('end_time', '<=', $request->end_time);
                    });
                })
                ->exists();

            return response()->json([
                'available' => !$conflictExists,
                'message' => $conflictExists ? 'Waktu tidak tersedia' : 'Waktu tersedia'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'available' => false,
                'message' => 'Terjadi kesalahan saat mengecek ketersediaan'
            ], 500);
        }
    }
}