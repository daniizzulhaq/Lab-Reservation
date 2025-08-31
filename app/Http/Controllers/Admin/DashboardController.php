<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laboratory;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistics
        $stats = [
            'total_laboratories' => Laboratory::count(),
            'total_users' => User::whereIn('role', ['user', 'dosen', 'mahasiswa'])->count(),
            'pending_reservations' => Reservation::where('status', 'pending')->count(),
            'total_reservations' => Reservation::count(),
        ];

        // Recent reservations
        $recentReservations = Reservation::with(['user', 'laboratory'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Upcoming reservations
        $upcomingReservations = Reservation::with(['user', 'laboratory'])
            ->where('status', 'approved')
            ->where('reservation_date', '>=', today())
            ->orderBy('reservation_date')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        // Laboratory statistics
        $laboratoryStats = Laboratory::withCount([
            'reservations', 
            'reservations as approved_count' => function($query) {
                $query->where('status', 'approved');
            }
        ])->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentReservations', 
            'upcomingReservations',
            'laboratoryStats'
        ));
    }

    // FIXED: Improved API endpoint for calendar events
    public function calendarEvents(Request $request)
    {
        try {
            Log::info('=== ADMIN CALENDAR EVENTS API CALLED ===', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'params' => $request->all(),
                'headers' => $request->headers->all(),
                'user_id' => auth()->id(),
                'user_role' => auth()->user()->role ?? 'N/A'
            ]);

            // Get parameters from FullCalendar
            $start = $request->get('start');
            $end = $request->get('end');
            
            Log::info("FullCalendar requesting events: start={$start}, end={$end}");

            // Build query for reservations
            $query = Reservation::with(['laboratory', 'user']);
            
            // FIXED: Better date handling for FullCalendar format
            if ($start && $end) {
                try {
                    // FullCalendar sends dates in ISO format (YYYY-MM-DD or YYYY-MM-DDTHH:mm:ss)
                    $startDate = Carbon::parse($start)->format('Y-m-d');
                    $endDate = Carbon::parse($end)->format('Y-m-d');
                    
                    $query->whereBetween('reservation_date', [$startDate, $endDate]);
                    Log::info("Filtering reservations between {$startDate} and {$endDate}");
                } catch (\Exception $dateError) {
                    Log::error("Date parsing error: " . $dateError->getMessage());
                    // Fallback: get current month data
                    $startDate = now()->startOfMonth()->format('Y-m-d');
                    $endDate = now()->endOfMonth()->format('Y-m-d');
                    $query->whereBetween('reservation_date', [$startDate, $endDate]);
                    Log::info("Using fallback date range: {$startDate} to {$endDate}");
                }
            } else {
                // Default: get current month +/- 1 month for better calendar coverage
                $startDate = now()->startOfMonth()->subMonth()->format('Y-m-d');
                $endDate = now()->endOfMonth()->addMonth()->format('Y-m-d');
                $query->whereBetween('reservation_date', [$startDate, $endDate]);
                Log::info("Using default date range: {$startDate} to {$endDate}");
            }

            // Get reservations
            $reservations = $query->orderBy('reservation_date')
                                ->orderBy('start_time')
                                ->get();
            
            Log::info("Found {$reservations->count()} reservations in database");

            // Debug: Log database query
            Log::info("SQL Query: " . $query->toSql());
            Log::info("SQL Bindings: ", $query->getBindings());

            // FIXED: Always return events array, even if empty
            if ($reservations->isEmpty()) {
                Log::warning('No reservations found in database for the requested date range');
                // Return empty array instead of sample events in production
                return response()->json([]);
            }

            // Convert reservations to FullCalendar format
            $events = $reservations->map(function ($reservation) {
                Log::info("Processing reservation ID: {$reservation->id}");
                
                $statusColors = [
                    'pending' => '#ffc107',      // Warning yellow
                    'approved' => '#198754',     // Success green  
                    'rejected' => '#dc3545',     // Danger red
                    'cancelled' => '#6c757d',    // Secondary gray
                    'completed' => '#17a2b8'     // Info blue
                ];

                $color = $statusColors[$reservation->status] ?? '#6c757d';
                $textColor = $reservation->status === 'pending' ? '#000000' : '#ffffff';

                // FIXED: Proper datetime formatting for FullCalendar
                // FullCalendar expects ISO 8601 format
                $reservationDate = $reservation->reservation_date instanceof Carbon 
                    ? $reservation->reservation_date->format('Y-m-d')
                    : Carbon::parse($reservation->reservation_date)->format('Y-m-d');
                
                // FIXED: Handle time format properly (remove microseconds if present)
                $startTime = substr($reservation->start_time, 0, 8); // HH:MM:SS
                $endTime = substr($reservation->end_time, 0, 8);     // HH:MM:SS
                
                $startDateTime = $reservationDate . 'T' . $startTime;
                $endDateTime = $reservationDate . 'T' . $endTime;

                Log::info("Event datetime: {$startDateTime} to {$endDateTime}");

                $event = [
                    'id' => 'reservation-' . $reservation->id,
                    'title' => ($reservation->laboratory->name ?? 'Lab') . ' - ' . ($reservation->user->name ?? 'User'),
                    'start' => $startDateTime,
                    'end' => $endDateTime,
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'textColor' => $textColor,
                    'allDay' => false, // FIXED: Set to false for timed events
                    'extendedProps' => [
                        'laboratory' => $reservation->laboratory->name ?? 'N/A',
                        'user' => $reservation->user->name ?? 'N/A',
                        'purpose' => $reservation->purpose ?? 'No purpose specified',
                        'status' => ucfirst($reservation->status),
                        'participant_count' => $reservation->participant_count ?? 0,
                        'reservation_id' => $reservation->id,
                        'description' => $reservation->description ?? '',
                        'admin_notes' => $reservation->admin_notes ?? '',
                        'reservation_date' => $reservationDate,
                        'start_time' => $startTime,
                        'end_time' => $endTime
                    ]
                ];

                Log::info("Created calendar event:", $event);
                return $event;
            });

            $eventsArray = $events->toArray();
            Log::info("Successfully returning {$events->count()} calendar events to FullCalendar");
            
            // FIXED: Set proper headers for AJAX response
            return response()->json($eventsArray, 200, [
                'Content-Type' => 'application/json'
            ]);
            
        } catch (\Exception $e) {
            Log::error('=== CRITICAL ERROR IN CALENDAR EVENTS ===', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty array with error status
            return response()->json([], 500);
        }
    }

    // ADDITIONAL: Method to debug calendar data
    public function debugCalendar(Request $request)
    {
        try {
            $debug = [
                'total_reservations' => Reservation::count(),
                'reservations_with_lab' => Reservation::whereHas('laboratory')->count(),
                'reservations_with_user' => Reservation::whereHas('user')->count(),
                'recent_reservations' => Reservation::with(['laboratory', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get()
                    ->map(function($r) {
                        return [
                            'id' => $r->id,
                            'date' => $r->reservation_date,
                            'start_time' => $r->start_time,
                            'end_time' => $r->end_time,
                            'laboratory' => $r->laboratory->name ?? 'NULL',
                            'user' => $r->user->name ?? 'NULL',
                            'status' => $r->status
                        ];
                    }),
                'date_range_check' => [
                    'current_month_start' => now()->startOfMonth()->format('Y-m-d'),
                    'current_month_end' => now()->endOfMonth()->format('Y-m-d'),
                    'reservations_this_month' => Reservation::whereBetween('reservation_date', [
                        now()->startOfMonth()->format('Y-m-d'),
                        now()->endOfMonth()->format('Y-m-d')
                    ])->count()
                ]
            ];

            return response()->json($debug);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Rest of the methods remain the same...
    public function getChartData(Request $request)
    {
        try {
            $period = $request->get('period', '30');
            $startDate = now()->subDays($period);

            $reservationsByStatus = Reservation::where('created_at', '>=', $startDate)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status');

            $dailyReservations = Reservation::where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $laboratoryUsage = Laboratory::withCount(['reservations' => function($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }])->get();

            return response()->json([
                'reservationsByStatus' => $reservationsByStatus,
                'dailyReservations' => $dailyReservations,
                'laboratoryUsage' => $laboratoryUsage
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getChartData: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load chart data'], 500);
        }
    }

    public function getReservationDetails($id)
    {
        try {
            $reservation = Reservation::with(['laboratory', 'user', 'approvedBy'])
                ->findOrFail($id);

            return response()->json([
                'id' => $reservation->id,
                'laboratory' => $reservation->laboratory->name,
                'user' => $reservation->user->name,
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
            Log::error('Error in getReservationDetails: ' . $e->getMessage());
            return response()->json(['error' => 'Reservation not found'], 404);
        }
    }
}