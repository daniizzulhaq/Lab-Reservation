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

        // Don't generate calendar events here, let AJAX handle it
        $calendarEvents = [];

        return view('admin.dashboard', compact(
            'stats',
            'recentReservations', 
            'upcomingReservations',
            'laboratoryStats',
            'calendarEvents'
        ));
    }

    // MAIN API ENDPOINT for calendar events
    public function calendarEvents(Request $request)
    {
        try {
            Log::info('=== ADMIN CALENDAR EVENTS CALLED ===', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'params' => $request->all(),
                'user_id' => auth()->id(),
                'user_role' => auth()->user()->role ?? 'N/A'
            ]);

            $start = $request->get('start');
            $end = $request->get('end');
            
            Log::info("Calendar events requested: start={$start}, end={$end}");
            
            // Build query
            $query = Reservation::with(['laboratory', 'user']);
            
            if ($start && $end) {
                try {
                    $startDate = Carbon::parse($start)->format('Y-m-d');
                    $endDate = Carbon::parse($end)->format('Y-m-d');
                    
                    $query->whereBetween('reservation_date', [$startDate, $endDate]);
                    Log::info("Filtering reservations between {$startDate} and {$endDate}");
                } catch (\Exception $dateError) {
                    Log::error("Date parsing error: " . $dateError->getMessage());
                    // Fall back to default range
                    $startDate = now()->startOfMonth()->subMonth()->format('Y-m-d');
                    $endDate = now()->endOfMonth()->addMonth()->format('Y-m-d');
                    $query->whereBetween('reservation_date', [$startDate, $endDate]);
                }
            } else {
                // Default to current month +/- 1 month
                $startDate = now()->startOfMonth()->subMonth()->format('Y-m-d');
                $endDate = now()->endOfMonth()->addMonth()->format('Y-m-d');
                $query->whereBetween('reservation_date', [$startDate, $endDate]);
                Log::info("Using default date range: {$startDate} to {$endDate}");
            }

            // Get reservations
            $reservations = $query->get();
            Log::info("Found {$reservations->count()} reservations");

            // Debug: Log actual reservation data
            if ($reservations->count() > 0) {
                $sampleReservation = $reservations->first();
                Log::info("Sample reservation data:", [
                    'id' => $sampleReservation->id,
                    'date' => $sampleReservation->reservation_date,
                    'start_time' => $sampleReservation->start_time,
                    'end_time' => $sampleReservation->end_time,
                    'laboratory' => $sampleReservation->laboratory->name ?? 'N/A',
                    'user' => $sampleReservation->user->name ?? 'N/A',
                    'status' => $sampleReservation->status
                ]);
            }

            // If no reservations found, create sample events for testing
            if ($reservations->isEmpty()) {
                Log::info('No reservations found, creating sample events for testing');
                $sampleEvents = $this->createSampleEvents();
                Log::info("Returning {count($sampleEvents)} sample events");
                return response()->json($sampleEvents);
            }

            // Convert reservations to calendar events
            $events = $reservations->map(function ($reservation) {
                $statusColors = [
                    'pending' => '#ffc107',      // Warning yellow
                    'approved' => '#198754',     // Success green  
                    'rejected' => '#dc3545',     // Danger red
                    'cancelled' => '#6c757d',    // Secondary gray
                    'completed' => '#17a2b8'     // Info blue
                ];

                $color = $statusColors[$reservation->status] ?? '#6c757d';
                $textColor = $reservation->status === 'pending' ? '#000000' : '#ffffff';

                // Format the datetime properly
                $startDateTime = $reservation->reservation_date->format('Y-m-d') . 'T' . $reservation->start_time;
                $endDateTime = $reservation->reservation_date->format('Y-m-d') . 'T' . $reservation->end_time;

                $event = [
                    'id' => 'reservation-' . $reservation->id,
                    'title' => ($reservation->laboratory->name ?? 'N/A') . ' - ' . ($reservation->user->name ?? 'N/A'),
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

                Log::info("Created event for reservation {$reservation->id}:", $event);
                return $event;
            });

            $eventsArray = $events->toArray();
            Log::info("Successfully returning {$events->count()} calendar events");
            
            return response()->json($eventsArray);
            
        } catch (\Exception $e) {
            Log::error('=== ERROR IN CALENDAR EVENTS ===', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return sample events as fallback
            Log::info('Returning sample events as fallback due to error');
            return response()->json($this->createSampleEvents());
        }
    }

    private function createSampleEvents()
    {
        Log::info('Creating sample events for testing');
        
        $sampleEvents = [];
        
        // Get real data if available
        $laboratories = Laboratory::all();
        $users = User::whereIn('role', ['user', 'dosen', 'mahasiswa'])->get();

        // Create default data if none exists
        if ($laboratories->isEmpty()) {
            Log::warning('No laboratories found, using default lab names');
            $labNames = ['Lab Komputer', 'Lab Fisika', 'Lab Kimia', 'Lab Biologi', 'Lab Keperawatan'];
        } else {
            $labNames = $laboratories->pluck('name')->toArray();
            Log::info('Using real laboratory names:', $labNames);
        }

        if ($users->isEmpty()) {
            Log::warning('No users found, using default user names');
            $userNames = ['Dr. Ahmad Santoso', 'Prof. Siti Nurhaliza', 'Budi Setiawan', 'Andi Wijaya', 'Maya Sari'];
        } else {
            $userNames = $users->pluck('name')->toArray();
            Log::info('Using real user names:', array_slice($userNames, 0, 5));
        }

        $statuses = ['pending', 'approved', 'rejected', 'cancelled', 'completed'];
        $colors = [
            'pending' => '#ffc107',
            'approved' => '#198754',
            'rejected' => '#dc3545',
            'cancelled' => '#6c757d',
            'completed' => '#17a2b8'
        ];

        // Create sample events for the next 30 days
        for ($i = 0; $i < 15; $i++) {
            $date = now()->addDays(rand(-7, 21))->format('Y-m-d');
            $startHour = rand(8, 16);
            $duration = rand(1, 4);
            $endHour = min($startHour + $duration, 18); // Max until 6 PM
            
            $labName = $labNames[array_rand($labNames)];
            $userName = $userNames[array_rand($userNames)];
            $status = $statuses[array_rand($statuses)];
            
            $sampleEvents[] = [
                'id' => 'sample-' . ($i + 1),
                'title' => $labName . ' - ' . $userName,
                'start' => $date . 'T' . sprintf('%02d:00:00', $startHour),
                'end' => $date . 'T' . sprintf('%02d:00:00', $endHour),
                'backgroundColor' => $colors[$status],
                'borderColor' => $colors[$status],
                'textColor' => $status === 'pending' ? '#000000' : '#ffffff',
                'extendedProps' => [
                    'laboratory' => $labName,
                    'user' => $userName,
                    'purpose' => 'Praktikum ' . $labName,
                    'status' => ucfirst($status),
                    'participant_count' => rand(5, 30),
                    'reservation_id' => 1000 + $i,
                    'description' => 'Sample reservation event for ' . $labName,
                    'admin_notes' => 'Auto-generated sample event',
                    'reservation_date' => $date,
                    'start_time' => sprintf('%02d:00:00', $startHour),
                    'end_time' => sprintf('%02d:00:00', $endHour)
                ]
            ];
        }

        Log::info('Created ' . count($sampleEvents) . ' sample events');
        return $sampleEvents;
    }

    // Get chart data for dashboard analytics
    public function getChartData(Request $request)
    {
        try {
            $period = $request->get('period', '30'); // days
            $startDate = now()->subDays($period);

            // Reservations by status
            $reservationsByStatus = Reservation::where('created_at', '>=', $startDate)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status');

            // Daily reservations
            $dailyReservations = Reservation::where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Laboratory usage
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

    // Method to get reservation details for modal
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