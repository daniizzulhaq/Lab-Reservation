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

        // Calendar events - Generate events for the next 3 months
        try {
            $calendarEvents = $this->generateCalendarEvents();
            Log::info('Calendar events generated: ' . count($calendarEvents));
        } catch (\Exception $e) {
            Log::error('Error generating calendar events: ' . $e->getMessage());
            $calendarEvents = $this->createSampleEvents();
        }

        return view('admin.dashboard', compact(
            'stats',
            'recentReservations', 
            'upcomingReservations',
            'laboratoryStats',
            'calendarEvents'
        ));
    }

    private function generateCalendarEvents()
    {
        $startDate = now()->startOfMonth()->subMonth();
        $endDate = now()->endOfMonth()->addMonths(2);

        $reservations = Reservation::with(['laboratory', 'user'])
            ->whereBetween('reservation_date', [$startDate, $endDate])
            ->get();

        Log::info("Found {$reservations->count()} reservations between {$startDate} and {$endDate}");

        $events = $reservations->map(function ($reservation) {
            $statusColors = [
                'pending' => '#ffc107',      // Warning yellow
                'approved' => '#28a745',     // Success green  
                'rejected' => '#dc3545',     // Danger red
                'cancelled' => '#6c757d',    // Secondary gray
                'completed' => '#17a2b8'     // Info blue
            ];

            $color = $statusColors[$reservation->status] ?? '#6c757d';
            $textColor = $reservation->status === 'pending' ? '#000000' : '#ffffff';

            return [
                'id' => 'reservation-' . $reservation->id,
                'title' => $reservation->laboratory->name . ' - ' . $reservation->user->name,
                'start' => $reservation->reservation_date->format('Y-m-d') . 'T' . $reservation->start_time,
                'end' => $reservation->reservation_date->format('Y-m-d') . 'T' . $reservation->end_time,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => $textColor,
                'extendedProps' => [
                    'laboratory' => $reservation->laboratory->name,
                    'user' => $reservation->user->name,
                    'purpose' => $reservation->purpose,
                    'status' => $reservation->status,
                    'participant_count' => $reservation->participant_count,
                    'reservation_id' => $reservation->id,
                    'description' => $reservation->description ?? '',
                    'admin_notes' => $reservation->admin_notes ?? ''
                ]
            ];
        });

        return $events->toArray();
    }

    private function createSampleEvents()
    {
        Log::info('Creating sample events for testing');
        
        $sampleEvents = [];
        $laboratories = Laboratory::take(3)->get();
        $users = User::whereIn('role', ['user', 'dosen', 'mahasiswa'])->take(5)->get();

        if ($laboratories->isEmpty() || $users->isEmpty()) {
            Log::warning('No laboratories or users found for sample events');
            return [];
        }

        $statuses = ['pending', 'approved', 'rejected', 'cancelled', 'completed'];
        $colors = [
            'pending' => '#ffc107',
            'approved' => '#28a745',
            'rejected' => '#dc3545',
            'cancelled' => '#6c757d',
            'completed' => '#17a2b8'
        ];

        // Create sample events for the next 14 days
        for ($i = 0; $i < 10; $i++) {
            $date = now()->addDays(rand(1, 14))->format('Y-m-d');
            $startHour = rand(8, 16);
            $duration = rand(1, 4);
            $endHour = $startHour + $duration;
            
            $laboratory = $laboratories->random();
            $user = $users->random();
            $status = $statuses[array_rand($statuses)];
            
            $sampleEvents[] = [
                'id' => 'sample-' . ($i + 1),
                'title' => $laboratory->name . ' - ' . $user->name,
                'start' => $date . 'T' . sprintf('%02d:00:00', $startHour),
                'end' => $date . 'T' . sprintf('%02d:00:00', $endHour),
                'backgroundColor' => $colors[$status],
                'borderColor' => $colors[$status],
                'textColor' => $status === 'pending' ? '#000000' : '#ffffff',
                'extendedProps' => [
                    'laboratory' => $laboratory->name,
                    'user' => $user->name,
                    'purpose' => 'Sample Purpose ' . ($i + 1),
                    'status' => $status,
                    'participant_count' => rand(5, 30),
                    'reservation_id' => $i + 1000,
                    'description' => 'This is a sample event for testing purposes',
                    'admin_notes' => 'Sample admin notes'
                ]
            ];
        }

        return $sampleEvents;
    }

    // API endpoint for calendar events
    public function calendarEvents(Request $request)
    {
        try {
            $start = $request->get('start');
            $end = $request->get('end');
            
            Log::info("Calendar events requested: start={$start}, end={$end}");
            
            $query = Reservation::with(['laboratory', 'user']);
            
            if ($start && $end) {
                $startDate = Carbon::parse($start)->format('Y-m-d');
                $endDate = Carbon::parse($end)->format('Y-m-d');
                
                $query->whereBetween('reservation_date', [$startDate, $endDate]);
                Log::info("Filtering reservations between {$startDate} and {$endDate}");
            } else {
                // Default to current month +/- 1 month
                $startDate = now()->startOfMonth()->subMonth();
                $endDate = now()->endOfMonth()->addMonth();
                
                $query->where('reservation_date', '>=', $startDate)
                      ->where('reservation_date', '<=', $endDate);
                      
                Log::info("Using default date range: {$startDate} to {$endDate}");
            }

            $reservations = $query->get();
            Log::info("Found {$reservations->count()} reservations");

            $events = $reservations->map(function ($reservation) {
                $statusColors = [
                    'pending' => '#ffc107',
                    'approved' => '#28a745',
                    'rejected' => '#dc3545',
                    'cancelled' => '#6c757d',
                    'completed' => '#17a2b8'
                ];

                $color = $statusColors[$reservation->status] ?? '#6c757d';
                $textColor = $reservation->status === 'pending' ? '#000000' : '#ffffff';

                return [
                    'id' => 'reservation-' . $reservation->id,
                    'title' => $reservation->laboratory->name . ' - ' . $reservation->user->name,
                    'start' => $reservation->reservation_date->format('Y-m-d') . 'T' . $reservation->start_time,
                    'end' => $reservation->reservation_date->format('Y-m-d') . 'T' . $reservation->end_time,
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'textColor' => $textColor,
                    'extendedProps' => [
                        'laboratory' => $reservation->laboratory->name,
                        'user' => $reservation->user->name,
                        'purpose' => $reservation->purpose,
                        'status' => ucfirst($reservation->status),
                        'participant_count' => $reservation->participant_count,
                        'reservation_id' => $reservation->id,
                        'description' => $reservation->description ?? '',
                        'admin_notes' => $reservation->admin_notes ?? ''
                    ]
                ];
            });

            Log::info("Returning {$events->count()} calendar events");
            
            return response()->json($events);
            
        } catch (\Exception $e) {
            Log::error('Error in calendarEvents: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json(['error' => 'Failed to load calendar events'], 500);
        }
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