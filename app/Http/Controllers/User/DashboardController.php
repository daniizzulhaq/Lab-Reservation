<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Statistics for current user
        $stats = [
            'my_reservations' => Reservation::where('user_id', $user->id)->count(),
            'pending_reservations' => Reservation::where('user_id', $user->id)->where('status', 'pending')->count(),
            'approved_reservations' => Reservation::where('user_id', $user->id)->where('status', 'approved')->count(),
            'upcoming_reservations' => Reservation::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where('reservation_date', '>=', today())
                ->count(),
        ];

        // Recent reservations
        $recentReservations = Reservation::with(['laboratory'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Upcoming reservations
        $upcomingReservations = Reservation::with(['laboratory'])
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('reservation_date', '>=', today())
            ->orderBy('reservation_date')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        // Empty array untuk initial load
        $calendarEvents = [];

        return view('user.dashboard', compact(
            'stats',
            'recentReservations',
            'upcomingReservations',
            'calendarEvents'
        ));
    }

    // PERBAIKAN: API ENDPOINT untuk calendar events dengan format datetime yang benar
   // Replace calendarEvents method in DashboardController
public function calendarEvents(Request $request)
{
    try {
        $user = Auth::user();
        
        Log::info('=== USER CALENDAR EVENTS CALLED ===', [
            'user_id' => $user->id,
            'params' => $request->all()
        ]);

        $start = $request->get('start');
        $end = $request->get('end');
        
        // Build query untuk user ini saja
        $query = Reservation::with(['laboratory'])
            ->where('user_id', $user->id);
        
        if ($start && $end) {
            try {
                $startDate = Carbon::parse($start)->format('Y-m-d');
                $endDate = Carbon::parse($end)->format('Y-m-d');
                $query->whereBetween('reservation_date', [$startDate, $endDate]);
            } catch (\Exception $dateError) {
                // Fall back to default range
                $startDate = now()->startOfMonth()->subMonth()->format('Y-m-d');
                $endDate = now()->endOfMonth()->addMonth()->format('Y-m-d');
                $query->whereBetween('reservation_date', [$startDate, $endDate]);
            }
        } else {
            // Default to wider range untuk debugging
            $startDate = now()->startOfYear()->format('Y-m-d');
            $endDate = now()->endOfYear()->format('Y-m-d');
            $query->whereBetween('reservation_date', [$startDate, $endDate]);
        }

        // Get user's reservations
        $reservations = $query->get();
        Log::info("Found {$reservations->count()} user reservations");

        // Convert reservations to calendar events
        $events = $reservations->map(function ($reservation) use ($user) {
            $statusColors = [
                'pending' => '#ffc107',
                'approved' => '#28a745',
                'rejected' => '#dc3545',
                'cancelled' => '#6c757d',
                'completed' => '#17a2b8'
            ];

            $color = $statusColors[$reservation->status] ?? '#6c757d';
            $textColor = $reservation->status === 'pending' ? '#000000' : '#ffffff';

            // PERBAIKAN UTAMA: Format datetime yang benar
            try {
                // Parse tanggal reservasi
                $reservationDate = Carbon::parse($reservation->reservation_date);
                
                // Parse waktu start dan end (hapus microseconds jika ada)
                $startTimeStr = explode('.', $reservation->start_time)[0]; // Remove microseconds
                $endTimeStr = explode('.', $reservation->end_time)[0];     // Remove microseconds
                
                // Parse waktu
                $startTime = Carbon::createFromFormat('H:i:s', $startTimeStr);
                $endTime = Carbon::createFromFormat('H:i:s', $endTimeStr);
                
                // Kombinasikan tanggal dan waktu dengan benar
                $startDateTime = $reservationDate->copy()
                    ->setHour($startTime->hour)
                    ->setMinute($startTime->minute)
                    ->setSecond(0)
                    ->format('Y-m-d\TH:i:s'); // Format ISO tanpa timezone
                    
                $endDateTime = $reservationDate->copy()
                    ->setHour($endTime->hour)
                    ->setMinute($endTime->minute)
                    ->setSecond(0)
                    ->format('Y-m-d\TH:i:s'); // Format ISO tanpa timezone
                    
                // VALIDASI: Pastikan end time tidak lebih kecil dari start time
                if (Carbon::parse($endDateTime)->lt(Carbon::parse($startDateTime))) {
                    Log::warning("Invalid time range for reservation {$reservation->id}: end < start");
                    // Jika end time lebih kecil, tambahkan 1 jam ke start time sebagai fallback
                    $endDateTime = Carbon::parse($startDateTime)->addHour()->format('Y-m-d\TH:i:s');
                }
                    
                Log::info("DateTime formatting for reservation {$reservation->id}:", [
                    'original_date' => $reservation->reservation_date->format('Y-m-d'),
                    'original_start' => $reservation->start_time,
                    'original_end' => $reservation->end_time,
                    'formatted_start' => $startDateTime,
                    'formatted_end' => $endDateTime,
                    'is_valid_range' => Carbon::parse($endDateTime)->gt(Carbon::parse($startDateTime))
                ]);
                
            } catch (\Exception $timeError) {
                Log::error("Time parsing error for reservation {$reservation->id}: " . $timeError->getMessage());
                
                // Fallback sederhana
                $dateStr = $reservation->reservation_date->format('Y-m-d');
                $startDateTime = $dateStr . 'T08:00:00';
                $endDateTime = $dateStr . 'T09:00:00';
            }

            $event = [
                'id' => 'user-reservation-' . $reservation->id,
                'title' => $reservation->laboratory->name,
                'start' => $startDateTime,
                'end' => $endDateTime,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => $textColor,
                'extendedProps' => [
                    'laboratory' => $reservation->laboratory->name ?? 'N/A',
                    'user' => $user->name,
                    'purpose' => $reservation->purpose ?? 'No purpose specified',
                    'status' => ucfirst($reservation->status),
                    'participant_count' => $reservation->participant_count ?? 0,
                    'reservation_id' => $reservation->id,
                    'description' => $reservation->description ?? '',
                    'admin_notes' => $reservation->admin_notes ?? '',
                    'reservation_date' => $reservation->reservation_date->format('Y-m-d'),
                    'start_time' => $reservation->start_time,
                    'end_time' => $reservation->end_time
                ]
            ];

            return $event;
        });

        $eventsArray = $events->toArray();
        
        // Log final events untuk debugging
        Log::info("Final events array:", [
            'count' => count($eventsArray),
            'sample_event' => count($eventsArray) > 0 ? $eventsArray[0] : null
        ]);
        
        return response()->json($eventsArray);
        
    } catch (\Exception $e) {
        Log::error('=== ERROR IN USER CALENDAR EVENTS ===', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        return response()->json([]);
    }
}

    // Method to get user's reservation details for modal
    public function getReservationDetails($id)
    {
        try {
            $user = Auth::user();
            
            // Pastikan reservasi milik user ini
            $reservation = Reservation::with(['laboratory', 'approvedBy'])
                ->where('user_id', $user->id)
                ->findOrFail($id);

            return response()->json([
                'id' => $reservation->id,
                'laboratory' => $reservation->laboratory->name,
                'user' => $user->name,
                'purpose' => $reservation->purpose,
                'description' => $reservation->description,
                'reservation_date' => $reservation->reservation_date->format('d/m/Y'),
                'start_time' => substr($reservation->start_time, 0, 5),
                'end_time' => substr($reservation->end_time, 0, 5),
                'participant_count' => $reservation->participant_count,
                'status' => $reservation->status,
                'admin_notes' => $reservation->admin_notes,
                'approved_by' => $reservation->approvedBy ? $reservation->approvedBy->name : null,
                'created_at' => $reservation->created_at->format('d/m/Y H:i'),
                'updated_at' => $reservation->updated_at->format('d/m/Y H:i')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in user getReservationDetails: ' . $e->getMessage());
            return response()->json(['error' => 'Reservation not found'], 404);
        }
    }
}