<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = $user->reservations()->with('laboratory');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->month) {
            $query->whereMonth('reservation_date', $request->month);
        }

        $reservations = $query->orderBy('reservation_date', 'desc')
                             ->orderBy('start_time', 'desc')
                             ->paginate(10);

        return view('user.reservations.index', compact('reservations'));
    }

    public function create()
    {
        $laboratories = Laboratory::where('status', 'active')->get();
        return view('user.reservations.create', compact('laboratories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'laboratory_id' => 'required|exists:laboratories,id',
            'reservation_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'purpose' => 'required|string|max:255',
            'description' => 'nullable|string',
            'participant_count' => 'required|integer|min:1',
        ]);

        $laboratory = Laboratory::findOrFail($validated['laboratory_id']);

        // Check capacity
        if ($validated['participant_count'] > $laboratory->capacity) {
            return back()->withErrors([
                'participant_count' => 'Jumlah peserta melebihi kapasitas laboratorium (' . $laboratory->capacity . ' orang).'
            ])->withInput();
        }

        // Check availability
        if (!$laboratory->isAvailable(
            $validated['reservation_date'],
            $validated['start_time'],
            $validated['end_time']
        )) {
            return back()->withErrors([
                'time' => 'Laboratorium tidak tersedia pada waktu yang dipilih.'
            ])->withInput();
        }

        // Check time constraints (8:00 - 17:00)
        $startTime = Carbon::createFromFormat('H:i', $validated['start_time']);
        $endTime = Carbon::createFromFormat('H:i', $validated['end_time']);
        $openTime = Carbon::createFromFormat('H:i', '08:00');
        $closeTime = Carbon::createFromFormat('H:i', '17:00');

        if ($startTime->lt($openTime) || $endTime->gt($closeTime)) {
            return back()->withErrors([
                'time' => 'Waktu reservasi harus antara pukul 08:00 - 17:00.'
            ])->withInput();
        }

        $validated['user_id'] = auth()->id();

        Reservation::create($validated);

        return redirect()->route('user.reservations.index')
            ->with('success', 'Reservasi berhasil dibuat dan menunggu persetujuan admin.');
    }

    public function show(Reservation $reservation)
    {
        // Ensure user can only see their own reservations
        if ($reservation->user_id !== auth()->id()) {
            abort(403);
        }

        $reservation->load(['laboratory', 'approvedBy']);
        return view('user.reservations.show', compact('reservation'));
    }

    public function edit(Reservation $reservation)
    {
        // Ensure user can only edit their own reservations
        if ($reservation->user_id !== auth()->id()) {
            abort(403);
        }

        // Only allow editing pending reservations
        if ($reservation->status !== 'pending') {
            return redirect()->route('user.reservations.index')
                ->with('error', 'Hanya reservasi yang masih pending yang dapat diedit.');
        }

        $laboratories = Laboratory::where('status', 'active')->get();
        return view('user.reservations.edit', compact('reservation', 'laboratories'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        // Ensure user can only update their own reservations
        if ($reservation->user_id !== auth()->id()) {
            abort(403);
        }

        // Only allow updating pending reservations
        if ($reservation->status !== 'pending') {
            return redirect()->route('user.reservations.index')
                ->with('error', 'Hanya reservasi yang masih pending yang dapat diedit.');
        }

        $validated = $request->validate([
            'laboratory_id' => 'required|exists:laboratories,id',
            'reservation_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'purpose' => 'required|string|max:255',
            'description' => 'nullable|string',
            'participant_count' => 'required|integer|min:1',
        ]);

        $laboratory = Laboratory::findOrFail($validated['laboratory_id']);

        // Check capacity
        if ($validated['participant_count'] > $laboratory->capacity) {
            return back()->withErrors([
                'participant_count' => 'Jumlah peserta melebihi kapasitas laboratorium (' . $laboratory->capacity . ' orang).'
            ])->withInput();
        }

        // Check availability (exclude current reservation)
        if (!$laboratory->isAvailable(
            $validated['reservation_date'],
            $validated['start_time'],
            $validated['end_time'],
            $reservation->id
        )) {
            return back()->withErrors([
                'time' => 'Laboratorium tidak tersedia pada waktu yang dipilih.'
            ])->withInput();
        }

        $reservation->update($validated);

        return redirect()->route('user.reservations.index')
            ->with('success', 'Reservasi berhasil diperbarui.');
    }

    public function destroy(Reservation $reservation)
    {
        // Ensure user can only delete their own reservations
        if ($reservation->user_id !== auth()->id()) {
            abort(403);
        }

        // Only allow deleting pending or future reservations
        if ($reservation->status === 'approved' && $reservation->reservation_date < today()) {
            return redirect()->route('user.reservations.index')
                ->with('error', 'Tidak dapat menghapus reservasi yang sudah berlalu.');
        }

        $reservation->delete();

        return redirect()->route('user.reservations.index')
            ->with('success', 'Reservasi berhasil dihapus.');
    }

    public function getCalendarEvents(Request $request)
    {
        $start = $request->start;
        $end = $request->end;

        $reservations = Reservation::with(['laboratory', 'user'])
            ->where('status', 'approved')
            ->whereBetween('reservation_date', [$start, $end])
            ->get();

        $events = $reservations->map(function($reservation) {
            return [
                'id' => $reservation->id,
                'title' => $reservation->laboratory->name . ' - ' . $reservation->purpose,
                'start' => $reservation->reservation_date . 'T' . $reservation->start_time,
                'end' => $reservation->reservation_date . 'T' . $reservation->end_time,
                'backgroundColor' => $this->getStatusColor($reservation->status),
                'borderColor' => $this->getStatusColor($reservation->status),
                'extendedProps' => [
                    'laboratory' => $reservation->laboratory->name,
                    'user' => $reservation->user->name,
                    'purpose' => $reservation->purpose,
                    'participant_count' => $reservation->participant_count,
                    'status' => $reservation->status,
                ]
            ];
        });

        return response()->json($events);
    }

    private function getStatusColor($status)
    {
        return match($status) {
            'pending' => '#f39c12',
            'approved' => '#27ae60',
            'rejected' => '#e74c3c',
            'completed' => '#95a5a6',
            default => '#3498db',
        };
    }
}