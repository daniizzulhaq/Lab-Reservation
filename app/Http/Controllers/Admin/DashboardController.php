<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laboratory;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

    // FIXED: Calendar Events API - mengikuti flow dari user dashboard
    public function calendarEvents(Request $request)
    {
        try {
            Log::info('=== ADMIN CALENDAR EVENTS CALLED ===', [
                'user_id' => auth()->id(),
                'params' => $request->all()
            ]);

            // Check authentication dan authorization
            if (!auth()->check() || !auth()->user()->isAdmin()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $start = $request->get('start');
            $end = $request->get('end');
            
            // Build query untuk semua reservasi (admin melihat semua)
            $query = Reservation::with(['laboratory', 'user']);
            
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
                // Default to wider range
                $startDate = now()->startOfYear()->format('Y-m-d');
                $endDate = now()->endOfYear()->format('Y-m-d');
                $query->whereBetween('reservation_date', [$startDate, $endDate]);
            }

            // Get all reservations for admin
            $reservations = $query->get();
            Log::info("Found {$reservations->count()} total reservations for admin");

            // Convert reservations to calendar events - MENGIKUTI FORMAT USER
            $events = $reservations->map(function ($reservation) {
                // Skip jika tidak ada laboratory atau user
                if (!$reservation->laboratory || !$reservation->user) {
                    return null;
                }

                $statusColors = [
                    'pending' => '#ffc107',
                    'approved' => '#28a745',
                    'rejected' => '#dc3545',
                    'cancelled' => '#6c757d',
                    'completed' => '#17a2b8'
                ];

                $color = $statusColors[$reservation->status] ?? '#6c757d';
                $textColor = $reservation->status === 'pending' ? '#000000' : '#ffffff';

                // MENGGUNAKAN FORMAT DATETIME YANG SAMA DENGAN USER
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
                    'id' => 'admin-reservation-' . $reservation->id,
                    'title' => $reservation->laboratory->name . ' - ' . $reservation->user->name,
                    'start' => $startDateTime,
                    'end' => $endDateTime,
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'textColor' => $textColor,
                    'extendedProps' => [
                        'laboratory' => $reservation->laboratory->name ?? 'N/A',
                        'user' => $reservation->user->name ?? 'N/A',
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
            })->filter(); // Filter out null values

            $eventsArray = $events->values()->toArray(); // Reset array keys after filter
            
            // Log final events untuk debugging
            Log::info("Final admin events array:", [
                'count' => count($eventsArray),
                'sample_event' => count($eventsArray) > 0 ? $eventsArray[0] : null
            ]);
            
            return response()->json($eventsArray);
            
        } catch (\Exception $e) {
            Log::error('=== ERROR IN ADMIN CALENDAR EVENTS ===', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([]);
        }
    }

    // NEW: Debug endpoint for troubleshooting
    public function debugCalendar(Request $request)
    {
        try {
            Log::info('Debug calendar endpoint called');

            // Check database connectivity
            DB::connection()->getPdo();
            
            // Get basic counts
            $totalReservations = Reservation::count();
            $reservationsWithLab = Reservation::whereHas('laboratory')->count();
            $reservationsWithUser = Reservation::whereHas('user')->count();
            
            // Get sample data
            $sampleReservations = Reservation::with(['laboratory', 'user'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function($r) {
                    return [
                        'id' => $r->id,
                        'reservation_date' => $r->reservation_date,
                        'start_time' => $r->start_time,
                        'end_time' => $r->end_time,
                        'laboratory' => $r->laboratory ? $r->laboratory->name : 'NULL',
                        'user' => $r->user ? $r->user->name : 'NULL',
                        'status' => $r->status,
                        'created_at' => $r->created_at
                    ];
                });

            // Test current month data
            $currentMonthStart = now()->startOfMonth()->format('Y-m-d');
            $currentMonthEnd = now()->endOfMonth()->format('Y-m-d');
            $currentMonthCount = Reservation::whereBetween('reservation_date', [$currentMonthStart, $currentMonthEnd])->count();

            $debug = [
                'timestamp' => now()->toISOString(),
                'database_connection' => 'OK',
                'auth_user' => auth()->user() ? [
                    'id' => auth()->user()->id,
                    'name' => auth()->user()->name,
                    'role' => auth()->user()->role
                ] : null,
                'reservation_counts' => [
                    'total' => $totalReservations,
                    'with_laboratory' => $reservationsWithLab,
                    'with_user' => $reservationsWithUser,
                    'current_month' => $currentMonthCount
                ],
                'date_ranges' => [
                    'current_month_start' => $currentMonthStart,
                    'current_month_end' => $currentMonthEnd,
                ],
                'sample_reservations' => $sampleReservations,
                'route_info' => [
                    'calendar_events_route' => route('admin.api.calendar.events'),
                    'debug_route' => route('admin.api.calendar.debug'),
                ],
                'environment' => [
                    'app_env' => config('app.env'),
                    'app_debug' => config('app.debug'),
                    'app_url' => config('app.url')
                ]
            ];

            Log::info('Debug data prepared', ['total_items' => count($debug)]);

            return response()->json($debug, 200, [
                'Content-Type' => 'application/json; charset=utf-8'
            ]);

        } catch (\Exception $e) {
            Log::error('Debug calendar failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    // NEW: Test simple events endpoint
    public function testEvents()
    {
        try {
            Log::info('Test events endpoint called');

            // Return simple test data
            $testEvents = [
                [
                    'id' => 'test-1',
                    'title' => 'Test Event 1',
                    'start' => now()->format('Y-m-d') . 'T09:00:00',
                    'end' => now()->format('Y-m-d') . 'T10:00:00',
                    'backgroundColor' => '#198754',
                    'borderColor' => '#198754',
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'laboratory' => 'Test Lab',
                        'user' => 'Test User',
                        'status' => 'Test'
                    ]
                ],
                [
                    'id' => 'test-2', 
                    'title' => 'Test Event 2',
                    'start' => now()->addDay()->format('Y-m-d') . 'T14:00:00',
                    'end' => now()->addDay()->format('Y-m-d') . 'T15:00:00',
                    'backgroundColor' => '#ffc107',
                    'borderColor' => '#ffc107',
                    'textColor' => '#000000',
                    'extendedProps' => [
                        'laboratory' => 'Test Lab 2',
                        'user' => 'Test User 2',
                        'status' => 'Pending'
                    ]
                ]
            ];

            Log::info('Returning test events', ['count' => count($testEvents)]);

            return response()->json($testEvents, 200, [
                'Content-Type' => 'application/json; charset=utf-8',
                'X-Test-Response' => 'true'
            ]);

        } catch (\Exception $e) {
            Log::error('Test events failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Enhanced chart data method
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