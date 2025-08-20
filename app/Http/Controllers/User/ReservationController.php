<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = $user->reservations()->with('laboratory');

        // Search functionality
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('reservation_code', 'like', '%' . $request->search . '%')
                  ->orWhereHas('laboratory', function($labQuery) use ($request) {
                      $labQuery->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Status filter
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->date_from) {
            $query->where('reservation_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->where('reservation_date', '<=', $request->date_to);
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
            'description' => 'nullable|string|max:1000',
            'participant_count' => 'required|integer|min:1',
        ]);

        $laboratory = Laboratory::findOrFail($validated['laboratory_id']);

        // Check capacity
        if ($validated['participant_count'] > $laboratory->capacity) {
            return back()->withErrors([
                'participant_count' => "Jumlah peserta tidak boleh melebihi kapasitas laboratorium ({$laboratory->capacity} orang)"
            ])->withInput();
        }

        // Check minimum duration (30 minutes)
        $duration = $this->calculateMinutes($validated['start_time'], $validated['end_time']);
        if ($duration < 30) {
            return back()->withErrors([
                'time' => 'Durasi reservasi minimal 30 menit'
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

        // Check if requested date is a weekend
        $dayOfWeek = Carbon::parse($validated['reservation_date'])->dayOfWeek;
        if ($dayOfWeek == Carbon::SATURDAY || $dayOfWeek == Carbon::SUNDAY) {
            return back()->withErrors([
                'reservation_date' => 'Laboratorium tidak beroperasi pada akhir pekan'
            ])->withInput();
        }

        // Final availability check
        $availabilityResult = $this->checkAvailabilityInternal(
            $validated['laboratory_id'],
            $validated['reservation_date'],
            $validated['start_time'],
            $validated['end_time']
        );

        if (!$availabilityResult['available']) {
            return back()->withErrors([
                'time' => 'Waktu yang dipilih tidak tersedia. ' . $availabilityResult['message']
            ])->withInput();
        }

        // Generate unique reservation code
        $reservationCode = $this->generateReservationCode();

        // Create reservation
        $validated['user_id'] = auth()->id();
        $validated['reservation_code'] = $reservationCode;
        $validated['status'] = 'pending';

        Reservation::create($validated);

        return redirect()->route('user.reservations.index')
            ->with('success', 'Reservasi berhasil dibuat dengan kode: ' . $reservationCode . '. Menunggu persetujuan admin.');
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
            'description' => 'nullable|string|max:1000',
            'participant_count' => 'required|integer|min:1',
        ]);

        $laboratory = Laboratory::findOrFail($validated['laboratory_id']);

        // Check capacity
        if ($validated['participant_count'] > $laboratory->capacity) {
            return back()->withErrors([
                'participant_count' => "Jumlah peserta tidak boleh melebihi kapasitas laboratorium ({$laboratory->capacity} orang)"
            ])->withInput();
        }

        // Check minimum duration (30 minutes)
        $duration = $this->calculateMinutes($validated['start_time'], $validated['end_time']);
        if ($duration < 30) {
            return back()->withErrors([
                'time' => 'Durasi reservasi minimal 30 menit'
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

        // Check availability (exclude current reservation)
        $availabilityResult = $this->checkAvailabilityInternal(
            $validated['laboratory_id'],
            $validated['reservation_date'],
            $validated['start_time'],
            $validated['end_time'],
            $reservation->id
        );

        if (!$availabilityResult['available']) {
            return back()->withErrors([
                'time' => 'Waktu yang dipilih tidak tersedia. ' . $availabilityResult['message']
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

        // Only allow deleting pending reservations
        if ($reservation->status !== 'pending') {
            return redirect()->route('user.reservations.index')
                ->with('error', 'Hanya reservasi pending yang dapat dibatalkan.');
        }

        $reservation->update(['status' => 'cancelled']);

        return redirect()->route('user.reservations.index')
            ->with('success', 'Reservasi berhasil dibatalkan.');
    }

    /**
     * Check laboratory availability for AJAX requests
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'laboratory_id' => 'required|exists:laboratories,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $result = $this->checkAvailabilityInternal(
            $request->laboratory_id,
            $request->date,
            $request->start_time,
            $request->end_time,
            $request->get('current_reservation_id')
        );

        return response()->json($result);
    }

    /**
     * Internal method for availability checking
     */
    private function checkAvailabilityInternal($laboratoryId, $date, $startTime, $endTime, $excludeReservationId = null)
    {
        // Check for time conflicts with existing reservations
        $conflictingReservations = Reservation::where('laboratory_id', $laboratoryId)
            ->where('reservation_date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($startTime, $endTime) {
                // Check for any time overlap
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            })
            ->when($excludeReservationId, function ($query, $excludeReservationId) {
                return $query->where('id', '!=', $excludeReservationId);
            })
            ->select('start_time', 'end_time', 'purpose', 'reservation_code')
            ->get();

        $isAvailable = $conflictingReservations->isEmpty();
        
        $response = [
            'available' => $isAvailable,
            'conflicts' => $conflictingReservations->toArray()
        ];

        if ($isAvailable) {
            $response['message'] = 'Laboratorium tersedia pada waktu yang dipilih';
        } else {
            $response['message'] = 'Terdapat ' . $conflictingReservations->count() . ' konflik waktu';
            
            // Suggest available time slots
            $response['available_slots'] = $this->findAvailableSlots(
                $laboratoryId, 
                $date, 
                $startTime, 
                $endTime,
                $excludeReservationId
            );
        }

        return $response;
    }

    /**
     * Find available time slots for the given date and laboratory
     */
    private function findAvailableSlots($laboratoryId, $date, $preferredStart, $preferredEnd, $excludeReservationId = null)
    {
        $operatingStart = '08:00';
        $operatingEnd = '17:00';
        $preferredDuration = $this->calculateMinutes($preferredStart, $preferredEnd);
        
        // Get all existing reservations for the day
        $existingReservations = Reservation::where('laboratory_id', $laboratoryId)
            ->where('reservation_date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->when($excludeReservationId, function ($query, $excludeReservationId) {
                return $query->where('id', '!=', $excludeReservationId);
            })
            ->orderBy('start_time')
            ->select('start_time', 'end_time')
            ->get();

        $availableSlots = [];
        $currentTime = $operatingStart;

        foreach ($existingReservations as $reservation) {
            // Check if there's a gap before this reservation
            if ($currentTime < $reservation->start_time) {
                $gapDuration = $this->calculateMinutes($currentTime, $reservation->start_time);
                
                if ($gapDuration >= $preferredDuration) {
                    $availableSlots[] = [
                        'start' => $currentTime,
                        'end' => $reservation->start_time,
                        'duration' => $gapDuration
                    ];
                }
            }
            
            // Move current time to after this reservation
            $currentTime = max($currentTime, $reservation->end_time);
        }

        // Check for availability after the last reservation
        if ($currentTime < $operatingEnd) {
            $remainingDuration = $this->calculateMinutes($currentTime, $operatingEnd);
            
            if ($remainingDuration >= $preferredDuration) {
                $availableSlots[] = [
                    'start' => $currentTime,
                    'end' => $operatingEnd,
                    'duration' => $remainingDuration
                ];
            }
        }

        return $availableSlots;
    }

    /**
     * Calculate minutes between two times
     */
    private function calculateMinutes($startTime, $endTime)
    {
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);
        
        return $end->diffInMinutes($start);
    }

    /**
     * Generate unique reservation code
     */
    private function generateReservationCode()
    {
        do {
            $code = 'RSV-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        } while (Reservation::where('reservation_code', $code)->exists());

        return $code;
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
            'cancelled' => '#6c757d',
            default => '#3498db',
        };
    }
}