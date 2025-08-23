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
    /**
     * Display a listing of the user's reservations.
     */
    public function index()
    {
        try {
            $reservations = Reservation::with(['laboratory'])
                ->where('user_id', auth()->id())
                ->orderBy('reservation_date', 'desc')
                ->orderBy('start_time', 'desc')
                ->paginate(10);

            return view('user.reservations.index', compact('reservations'));
            
        } catch (\Exception $e) {
            \Log::error('Reservation index error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data reservasi.');
        }
    }

    /**
     * Show the form for creating a new reservation.
     */
    public function create()
    {
        try {
            // Get available laboratories
            $laboratories = Laboratory::where('status', 'active')
                ->orderBy('name')
                ->get();

            // Get current date for minimum date restriction
            $minDate = now()->format('Y-m-d');
            
            // Get operating hours (you can adjust these as needed)
            $operatingHours = [
                'start' => '08:00',
                'end' => '17:00'
            ];

            return view('user.reservations.create', compact('laboratories', 'minDate', 'operatingHours'));
            
        } catch (\Exception $e) {
            \Log::error('Reservation create form error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat form reservasi.');
        }
    }

    /**
     * Store a newly created reservation in storage.
     */
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

        // Check if requested date is a weekend
        $dayOfWeek = Carbon::parse($validated['reservation_date'])->dayOfWeek;
        if ($dayOfWeek == Carbon::SATURDAY || $dayOfWeek == Carbon::SUNDAY) {
            return back()->withErrors([
                'reservation_date' => 'Laboratorium tidak beroperasi pada akhir pekan'
            ])->withInput();
        }

        // ENHANCED: More comprehensive availability check with detailed error messages
        DB::beginTransaction();
        try {
            $availabilityResult = $this->checkAvailabilityInternal(
                $validated['laboratory_id'],
                $validated['reservation_date'],
                $validated['start_time'],
                $validated['end_time']
            );

            if (!$availabilityResult['available']) {
                DB::rollBack();
                
                // Get detailed conflict information
                $conflicts = $availabilityResult['conflicts'];
                $conflictMessages = [];
                
                foreach ($conflicts as $conflict) {
                    $conflictMessages[] = "Bentrok dengan reservasi {$conflict['reservation_code']} ({$conflict['start_time']}-{$conflict['end_time']}) untuk {$conflict['purpose']} - Status: {$conflict['status']}";
                }
                
                $detailedMessage = "Waktu yang dipilih tidak tersedia karena bertabrakan dengan reservasi berikut:\n\n" . implode("\n", $conflictMessages);
                
                return back()->withErrors([
                    'time' => $detailedMessage
                ])->withInput()->with('conflict_details', $conflicts);
            }

            // Generate unique reservation code
            $reservationCode = $this->generateReservationCode();

            // Create reservation
            $validated['user_id'] = auth()->id();
            $validated['reservation_code'] = $reservationCode;
            $validated['status'] = 'pending';

            $reservation = Reservation::create($validated);
            
            DB::commit();

            return redirect()->route('user.reservations.index')
                ->with('success', 'Reservasi berhasil dibuat dengan kode: ' . $reservationCode . '. Menunggu persetujuan admin.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Reservation creation error: ' . $e->getMessage());
            return back()->withErrors([
                'time' => 'Terjadi kesalahan saat menyimpan reservasi. Silakan coba lagi. Error: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Display the specified reservation.
     */
    public function show($id)
    {
        try {
            $reservation = Reservation::with(['laboratory', 'user'])
                ->where('user_id', auth()->id()) // Only show user's own reservations
                ->findOrFail($id);

            return view('user.reservations.show', compact('reservation'));
            
        } catch (\Exception $e) {
            \Log::error('Reservation show error: ' . $e->getMessage());
            return back()->with('error', 'Reservasi tidak ditemukan.');
        }
    }

    /**
     * Show the form for editing the specified reservation.
     */
    public function edit($id)
    {
        try {
            $reservation = Reservation::where('user_id', auth()->id())
                ->where('status', 'pending') // Only allow editing pending reservations
                ->findOrFail($id);

            // Get available laboratories
            $laboratories = Laboratory::where('status', 'active')
                ->orderBy('name')
                ->get();

            // Get current date for minimum date restriction
            $minDate = now()->format('Y-m-d');
            
            // Get operating hours
            $operatingHours = [
                'start' => '08:00',
                'end' => '17:00'
            ];

            return view('user.reservations.edit', compact('reservation', 'laboratories', 'minDate', 'operatingHours'));
            
        } catch (\Exception $e) {
            \Log::error('Reservation edit form error: ' . $e->getMessage());
            return back()->with('error', 'Reservasi tidak dapat diedit atau tidak ditemukan.');
        }
    }

    /**
     * Update the specified reservation in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $reservation = Reservation::where('user_id', auth()->id())
                ->where('status', 'pending') // Only allow updating pending reservations
                ->findOrFail($id);

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

            // Check if requested date is a weekend
            $dayOfWeek = Carbon::parse($validated['reservation_date'])->dayOfWeek;
            if ($dayOfWeek == Carbon::SATURDAY || $dayOfWeek == Carbon::SUNDAY) {
                return back()->withErrors([
                    'reservation_date' => 'Laboratorium tidak beroperasi pada akhir pekan'
                ])->withInput();
            }

            // Check availability (excluding current reservation)
            DB::beginTransaction();
            try {
                $availabilityResult = $this->checkAvailabilityInternal(
                    $validated['laboratory_id'],
                    $validated['reservation_date'],
                    $validated['start_time'],
                    $validated['end_time'],
                    $reservation->id // Exclude current reservation
                );

                if (!$availabilityResult['available']) {
                    DB::rollBack();
                    
                    $conflicts = $availabilityResult['conflicts'];
                    $conflictMessages = [];
                    
                    foreach ($conflicts as $conflict) {
                        $conflictMessages[] = "Bentrok dengan reservasi {$conflict['reservation_code']} ({$conflict['start_time']}-{$conflict['end_time']}) untuk {$conflict['purpose']} - Status: {$conflict['status']}";
                    }
                    
                    $detailedMessage = "Waktu yang dipilih tidak tersedia karena bertabrakan dengan reservasi berikut:\n\n" . implode("\n", $conflictMessages);
                    
                    return back()->withErrors([
                        'time' => $detailedMessage
                    ])->withInput()->with('conflict_details', $conflicts);
                }

                // Update reservation
                $reservation->update($validated);
                
                DB::commit();

                return redirect()->route('user.reservations.index')
                    ->with('success', 'Reservasi berhasil diupdate.');
                    
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Reservation update error: ' . $e->getMessage());
                return back()->withErrors([
                    'time' => 'Terjadi kesalahan saat mengupdate reservasi. Silakan coba lagi.'
                ])->withInput();
            }
            
        } catch (\Exception $e) {
            \Log::error('Reservation update error: ' . $e->getMessage());
            return back()->with('error', 'Reservasi tidak dapat diupdate atau tidak ditemukan.');
        }
    }

    /**
     * Remove the specified reservation from storage.
     */
    public function destroy($id)
    {
        try {
            $reservation = Reservation::where('user_id', auth()->id())
                ->where('status', 'pending') // Only allow deleting pending reservations
                ->findOrFail($id);

            $reservationCode = $reservation->reservation_code;
            $reservation->delete();

            return redirect()->route('user.reservations.index')
                ->with('success', "Reservasi {$reservationCode} berhasil dibatalkan.");
                
        } catch (\Exception $e) {
            \Log::error('Reservation delete error: ' . $e->getMessage());
            return back()->with('error', 'Reservasi tidak dapat dibatalkan atau tidak ditemukan.');
        }
    }

    /**
     * Check laboratory availability for AJAX requests with enhanced response
     */
    public function checkAvailability(Request $request)
    {
        try {
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
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'available' => false,
                'message' => 'Data tidak valid: ' . implode(', ', $e->validator->errors()->all()),
                'conflicts' => [],
                'available_slots' => []
            ], 400);
            
        } catch (\Exception $e) {
            \Log::error('Availability check error: ' . $e->getMessage());
            return response()->json([
                'available' => false,
                'message' => 'Terjadi kesalahan saat mengecek ketersediaan: ' . $e->getMessage(),
                'conflicts' => [],
                'available_slots' => []
            ], 500);
        }
    }

    /**
     * ENHANCED: Internal method for availability checking with better conflict detection and error handling
     */
    private function checkAvailabilityInternal($laboratoryId, $date, $startTime, $endTime, $excludeReservationId = null)
    {
        try {
            // Validate input times
            if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $startTime) || 
                !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $endTime)) {
                throw new \InvalidArgumentException('Format waktu tidak valid');
            }

            // Convert time strings to Carbon objects for better comparison
            $requestStart = Carbon::createFromFormat('H:i', $startTime);
            $requestEnd = Carbon::createFromFormat('H:i', $endTime);
            
            if ($requestEnd->lte($requestStart)) {
                throw new \InvalidArgumentException('Waktu selesai harus lebih besar dari waktu mulai');
            }

            // ENHANCED: More precise conflict detection query
            $conflictingReservations = Reservation::where('laboratory_id', $laboratoryId)
                ->where('reservation_date', $date)
                ->whereIn('status', ['pending', 'approved'])
                ->where(function ($query) use ($startTime, $endTime) {
                    // Convert to time format for database comparison
                    $startTimeForDB = $startTime . ':00';
                    $endTimeForDB = $endTime . ':00';
                    
                    $query->where(function ($q) use ($startTimeForDB, $endTimeForDB) {
                        // Case 1: New reservation starts during existing reservation
                        $q->where('start_time', '<=', $startTimeForDB)
                          ->where('end_time', '>', $startTimeForDB);
                    })
                    ->orWhere(function ($q) use ($startTimeForDB, $endTimeForDB) {
                        // Case 2: New reservation ends during existing reservation  
                        $q->where('start_time', '<', $endTimeForDB)
                          ->where('end_time', '>=', $endTimeForDB);
                    })
                    ->orWhere(function ($q) use ($startTimeForDB, $endTimeForDB) {
                        // Case 3: New reservation completely contains existing reservation
                        $q->where('start_time', '>=', $startTimeForDB)
                          ->where('end_time', '<=', $endTimeForDB);
                    })
                    ->orWhere(function ($q) use ($startTimeForDB, $endTimeForDB) {
                        // Case 4: Existing reservation completely contains new reservation
                        $q->where('start_time', '<=', $startTimeForDB)
                          ->where('end_time', '>=', $endTimeForDB);
                    })
                    ->orWhere(function ($q) use ($startTimeForDB, $endTimeForDB) {
                        // Case 5: Exact time match
                        $q->where('start_time', '=', $startTimeForDB)
                          ->orWhere('end_time', '=', $endTimeForDB);
                    });
                })
                ->when($excludeReservationId, function ($query, $excludeReservationId) {
                    return $query->where('id', '!=', $excludeReservationId);
                })
                ->with(['user:id,name', 'laboratory:id,name'])
                ->select('id', 'start_time', 'end_time', 'purpose', 'reservation_code', 'status', 'user_id', 'laboratory_id', 'participant_count', 'description')
                ->get();

            $isAvailable = $conflictingReservations->isEmpty();
            
            $response = [
                'available' => $isAvailable,
                'conflicts' => $conflictingReservations->map(function($reservation) {
                    return [
                        'id' => $reservation->id,
                        'reservation_code' => $reservation->reservation_code,
                        'start_time' => Carbon::createFromFormat('H:i:s', $reservation->start_time)->format('H:i'),
                        'end_time' => Carbon::createFromFormat('H:i:s', $reservation->end_time)->format('H:i'),
                        'purpose' => $reservation->purpose,
                        'status' => $reservation->status,
                        'user_name' => $reservation->user ? $reservation->user->name : 'Unknown',
                        'participant_count' => $reservation->participant_count,
                        'description' => $reservation->description,
                    ];
                })->toArray()
            ];

            if ($isAvailable) {
                $response['message'] = 'Laboratorium tersedia pada waktu yang dipilih';
                $response['available_slots'] = [];
            } else {
                $conflictCount = $conflictingReservations->count();
                $conflictDetails = $conflictingReservations->map(function($reservation) {
                    $startTime = Carbon::createFromFormat('H:i:s', $reservation->start_time)->format('H:i');
                    $endTime = Carbon::createFromFormat('H:i:s', $reservation->end_time)->format('H:i');
                    $status = ucfirst($reservation->status);
                    return "{$reservation->reservation_code} ({$startTime}-{$endTime}) - {$reservation->purpose} [{$status}]";
                })->join('; ');
                
                $response['message'] = "KONFLIK WAKTU TERDETEKSI! Terdapat {$conflictCount} reservasi yang bentrok: {$conflictDetails}";
                
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
            
        } catch (\Exception $e) {
            \Log::error('Availability check internal error: ' . $e->getMessage());
            return [
                'available' => false,
                'message' => 'Terjadi kesalahan saat mengecek ketersediaan: ' . $e->getMessage(),
                'conflicts' => [],
                'available_slots' => []
            ];
        }
    }

    /**
     * ENHANCED: Find available time slots with better error handling
     */
    private function findAvailableSlots($laboratoryId, $date, $preferredStart, $preferredEnd, $excludeReservationId = null)
    {
        try {
            $operatingStart = '08:00';
            $operatingEnd = '17:00';
            $preferredDuration = $this->calculateMinutes($preferredStart, $preferredEnd);
            
            // Get all existing reservations for the day, ordered by start time
            $existingReservations = Reservation::where('laboratory_id', $laboratoryId)
                ->where('reservation_date', $date)
                ->whereIn('status', ['pending', 'approved'])
                ->when($excludeReservationId, function ($query, $excludeReservationId) {
                    return $query->where('id', '!=', $excludeReservationId);
                })
                ->orderBy('start_time')
                ->select('start_time', 'end_time', 'reservation_code', 'purpose')
                ->get();

            $availableSlots = [];
            $currentTime = $operatingStart;

            foreach ($existingReservations as $reservation) {
                $reservationStart = Carbon::createFromFormat('H:i:s', $reservation->start_time)->format('H:i');
                $reservationEnd = Carbon::createFromFormat('H:i:s', $reservation->end_time)->format('H:i');
                
                // Check if there's a gap before this reservation
                if ($currentTime < $reservationStart) {
                    $gapDuration = $this->calculateMinutes($currentTime, $reservationStart);
                    
                    // Show available gaps that can accommodate the preferred duration
                    if ($gapDuration >= $preferredDuration) {
                        $availableSlots[] = [
                            'start' => $currentTime,
                            'end' => $reservationStart,
                            'duration' => $gapDuration,
                            'type' => 'before_reservation',
                            'note' => "Tersedia sebelum reservasi {$reservation->reservation_code}"
                        ];
                    }
                }
                
                // Move current time to after this reservation
                $currentTime = max($currentTime, $reservationEnd);
            }

            // Check for availability after the last reservation
            if ($currentTime < $operatingEnd) {
                $remainingDuration = $this->calculateMinutes($currentTime, $operatingEnd);
                
                if ($remainingDuration >= $preferredDuration) {
                    $availableSlots[] = [
                        'start' => $currentTime,
                        'end' => $operatingEnd,
                        'duration' => $remainingDuration,
                        'type' => 'end_of_day',
                        'note' => 'Tersedia hingga akhir hari'
                    ];
                }
            }

            // If no slots found with preferred duration, suggest shorter slots
            if (empty($availableSlots) && $preferredDuration > 60) {
                return $this->findAvailableSlots($laboratoryId, $date, $preferredStart, $preferredEnd, $excludeReservationId, 60);
            }

            return $availableSlots;
            
        } catch (\Exception $e) {
            \Log::error('Find available slots error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate minutes between two times with error handling
     */
    private function calculateMinutes($startTime, $endTime)
    {
        try {
            $start = Carbon::createFromFormat('H:i', $startTime);
            $end = Carbon::createFromFormat('H:i', $endTime);
            
            // Handle cases where end time is next day (crosses midnight)
            if ($end->lt($start)) {
                $end->addDay();
            }
            
            return $end->diffInMinutes($start);
            
        } catch (\Exception $e) {
            \Log::error('Calculate minutes error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Generate unique reservation code with better uniqueness check
     */
    private function generateReservationCode()
    {
        $attempts = 0;
        $maxAttempts = 10;
        
        do {
            $code = 'RSV-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            $attempts++;
            
            if ($attempts >= $maxAttempts) {
                // Use timestamp as fallback to ensure uniqueness
                $code = 'RSV-' . date('Ymd') . '-' . time() . '-' . strtoupper(Str::random(2));
                break;
            }
        } while (Reservation::where('reservation_code', $code)->exists());

        return $code;
    }

    /**
     * Get laboratory capacity for AJAX requests
     */
    public function getLaboratoryCapacity($id)
    {
        try {
            $laboratory = Laboratory::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'capacity' => $laboratory->capacity,
                'name' => $laboratory->name,
                'code' => $laboratory->code ?? null,
                'status' => $laboratory->status
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Laboratorium tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Enhanced method to get calendar events with conflict indicators
     */
    public function getCalendarEvents(Request $request)
    {
        $start = $request->start;
        $end = $request->end;

        $reservations = Reservation::with(['laboratory', 'user'])
            ->whereIn('status', ['pending', 'approved']) // Include both pending and approved
            ->whereBetween('reservation_date', [$start, $end])
            ->get();

        $events = $reservations->map(function($reservation) {
            $backgroundColor = $this->getStatusColor($reservation->status);
            
            // Add conflict indicator for overlapping reservations
            $hasConflict = $this->checkForOverlappingReservations($reservation);
            if ($hasConflict) {
                $backgroundColor = '#e74c3c'; // Red for conflicts
            }
            
            return [
                'id' => $reservation->id,
                'title' => $reservation->laboratory->name . ' - ' . $reservation->purpose,
                'start' => $reservation->reservation_date . 'T' . $reservation->start_time,
                'end' => $reservation->reservation_date . 'T' . $reservation->end_time,
                'backgroundColor' => $backgroundColor,
                'borderColor' => $backgroundColor,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'laboratory' => $reservation->laboratory->name,
                    'user' => $reservation->user->name,
                    'purpose' => $reservation->purpose,
                    'participant_count' => $reservation->participant_count,
                    'status' => $reservation->status,
                    'reservation_code' => $reservation->reservation_code,
                    'description' => $reservation->description,
                    'hasConflict' => $hasConflict
                ]
            ];
        });

        return response()->json($events);
    }

    /**
     * Check for overlapping reservations (for calendar display)
     */
    private function checkForOverlappingReservations($reservation)
    {
        $overlapping = Reservation::where('laboratory_id', $reservation->laboratory_id)
            ->where('reservation_date', $reservation->reservation_date)
            ->where('id', '!=', $reservation->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($reservation) {
                $query->where(function ($q) use ($reservation) {
                    $q->where('start_time', '<=', $reservation->start_time)
                      ->where('end_time', '>', $reservation->start_time);
                })
                ->orWhere(function ($q) use ($reservation) {
                    $q->where('start_time', '<', $reservation->end_time)
                      ->where('end_time', '>=', $reservation->end_time);
                })
                ->orWhere(function ($q) use ($reservation) {
                    $q->where('start_time', '>=', $reservation->start_time)
                      ->where('end_time', '<=', $reservation->end_time);
                })
                ->orWhere(function ($q) use ($reservation) {
                    $q->where('start_time', '<=', $reservation->start_time)
                      ->where('end_time', '>=', $reservation->end_time);
                });
            })
            ->exists();
            
        return $overlapping;
    }

    private function getStatusColor($status)
    {
        return match($status) {
            'pending' => '#f39c12',     // Orange for pending
            'approved' => '#27ae60',    // Green for approved
            'rejected' => '#e74c3c',    // Red for rejected
            'completed' => '#95a5a6',   // Gray for completed
            'cancelled' => '#6c757d',   // Dark gray for cancelled
            default => '#3498db',       // Blue as default
        };
    }
}