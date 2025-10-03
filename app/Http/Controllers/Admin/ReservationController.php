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
        \DB::beginTransaction();
        
        // Validate request
        $request->validate([
            'admin_notes' => 'nullable|string|max:500'
        ]);

        // Update reservation status
        $reservation->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'admin_notes' => $request->input('admin_notes', ''),
            'approved_at' => now()
        ]);

        // Log the approval
        \Log::info('Reservation approved', [
            'reservation_id' => $reservation->id,
            'admin_id' => auth()->id(),
            'user_id' => $reservation->user_id,
            'admin_notes' => $request->input('admin_notes', '')
        ]);

        // Send notification to user
        try {
            // Make sure reservation has laboratory loaded
            $reservation->load(['laboratory', 'user']);
            
            if ($reservation->user) {
                $reservation->user->notify(new \App\Notifications\ReservationApproved($reservation));
                \Log::info('Approval notification sent', [
                    'reservation_id' => $reservation->id,
                    'user_id' => $reservation->user_id
                ]);
            } else {
                \Log::error('Cannot send notification - user not found', [
                    'reservation_id' => $reservation->id,
                    'user_id' => $reservation->user_id
                ]);
            }
        } catch (\Exception $notifyError) {
            \Log::error('Failed to send approval notification', [
                'reservation_id' => $reservation->id,
                'error' => $notifyError->getMessage()
            ]);
            // Don't fail the approval if notification fails
        }

        \DB::commit();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Reservasi berhasil disetujui dan notifikasi telah dikirim.'
            ]);
        }

        return redirect()->back()->with('success', 'Reservasi berhasil disetujui dan notifikasi telah dikirim.');

    } catch (\Exception $e) {
        \DB::rollback();
        
        \Log::error('Failed to approve reservation', [
            'reservation_id' => $reservation->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui reservasi: ' . $e->getMessage()
            ], 500);
        }

        return redirect()->back()->with('error', 'Gagal menyetujui reservasi.');
    }
}

    public function reject(Request $request, Reservation $reservation)
{
    try {
        \DB::beginTransaction();
        
        // Validate request
        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
            'reason' => 'nullable|string|max:500'
        ]);

        $reason = $request->input('reason') ?: $request->input('admin_notes', 'Tidak ada alasan yang diberikan');

        // Update reservation status
        $reservation->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'admin_notes' => $reason,
            'rejected_at' => now()
        ]);

        // Log the rejection
        \Log::info('Reservation rejected', [
            'reservation_id' => $reservation->id,
            'admin_id' => auth()->id(),
            'user_id' => $reservation->user_id,
            'reason' => $reason
        ]);

        // Send notification to user
        try {
            // Make sure reservation has laboratory loaded
            $reservation->load(['laboratory', 'user']);
            
            if ($reservation->user) {
                $reservation->user->notify(new \App\Notifications\ReservationRejected($reservation, $reason));
                \Log::info('Rejection notification sent', [
                    'reservation_id' => $reservation->id,
                    'user_id' => $reservation->user_id,
                    'reason' => $reason
                ]);
            } else {
                \Log::error('Cannot send notification - user not found', [
                    'reservation_id' => $reservation->id,
                    'user_id' => $reservation->user_id
                ]);
            }
        } catch (\Exception $notifyError) {
            \Log::error('Failed to send rejection notification', [
                'reservation_id' => $reservation->id,
                'error' => $notifyError->getMessage()
            ]);
            // Don't fail the rejection if notification fails
        }

        \DB::commit();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Reservasi berhasil ditolak dan notifikasi telah dikirim.'
            ]);
        }

        return redirect()->back()->with('success', 'Reservasi berhasil ditolak dan notifikasi telah dikirim.');

    } catch (\Exception $e) {
        \DB::rollback();
        
        \Log::error('Failed to reject reservation', [
            'reservation_id' => $reservation->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak reservasi: ' . $e->getMessage()
            ], 500);
        }

        return redirect()->back()->with('error', 'Gagal menolak reservasi.');
    }
}

    /**
     * Cancel an approved reservation
     * This method allows admin to cancel reservations that have already been approved
     */
    public function cancel(Request $request, Reservation $reservation)
    {
        try {
            \DB::beginTransaction();
            
            // Validate request
            $request->validate([
                'admin_notes' => 'required|string|max:1000'
            ], [
                'admin_notes.required' => 'Alasan pembatalan wajib diisi',
                'admin_notes.max' => 'Alasan pembatalan tidak boleh lebih dari 1000 karakter'
            ]);

            // Check if reservation can be cancelled
            if (!in_array($reservation->status, ['approved', 'pending'])) {
                throw new \Exception('Hanya reservasi dengan status "Disetujui" atau "Menunggu" yang dapat dibatalkan');
            }

            $cancelReason = $request->input('admin_notes');
            $oldStatus = $reservation->status;

            // Update reservation status to cancelled
            $reservation->update([
                'status' => 'cancelled',
                'admin_notes' => $cancelReason,
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id()
            ]);

            // Log the cancellation
            \Log::info('Reservation cancelled by admin', [
                'reservation_id' => $reservation->id,
                'admin_id' => auth()->id(),
                'user_id' => $reservation->user_id,
                'old_status' => $oldStatus,
                'cancel_reason' => $cancelReason
            ]);

            // Send notification to user
            try {
                // Make sure reservation has all required relationships loaded
                $reservation->load(['laboratory', 'user']);
                
                if ($reservation->user) {
                    // Check if ReservationCancelled notification exists
                    if (class_exists('\App\Notifications\ReservationCancelled')) {
                        $reservation->user->notify(new \App\Notifications\ReservationCancelled($reservation, $cancelReason));
                        \Log::info('Cancellation notification sent using ReservationCancelled', [
                            'reservation_id' => $reservation->id,
                            'user_id' => $reservation->user_id,
                            'reason' => $cancelReason
                        ]);
                    } else {
                        // Use existing ReservationRejected notification as fallback with modified message
                        $modifiedReason = "PEMBATALAN RESERVASI: " . $cancelReason . " (Reservasi yang telah disetujui ini dibatalkan oleh admin)";
                        $reservation->user->notify(new \App\Notifications\ReservationRejected($reservation, $modifiedReason));
                        \Log::info('Cancellation notification sent using ReservationRejected fallback', [
                            'reservation_id' => $reservation->id,
                            'user_id' => $reservation->user_id,
                            'reason' => $cancelReason
                        ]);
                    }
                } else {
                    \Log::error('Cannot send cancellation notification - user not found', [
                        'reservation_id' => $reservation->id,
                        'user_id' => $reservation->user_id
                    ]);
                }
            } catch (\Exception $notifyError) {
                \Log::error('Failed to send cancellation notification', [
                    'reservation_id' => $reservation->id,
                    'error' => $notifyError->getMessage()
                ]);
                // Don't fail the cancellation if notification fails
            }

            \DB::commit();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Reservasi berhasil dibatalkan dan notifikasi telah dikirim ke pengguna.',
                    'new_status' => 'cancelled'
                ]);
            }

            return redirect()->back()->with('success', 'Reservasi berhasil dibatalkan dan notifikasi telah dikirim ke pengguna.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \DB::rollback();
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            \DB::rollback();
            
            \Log::error('Failed to cancel reservation', [
                'reservation_id' => $reservation->id,
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membatalkan reservasi: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal membatalkan reservasi: ' . $e->getMessage());
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