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

        // Calendar events - Generate events for the current user
        try {
            $calendarEvents = $this->generateUserCalendarEvents($user->id);
            Log::info('User calendar events generated: ' . count($calendarEvents));
        } catch (\Exception $e) {
            Log::error('Error generating user calendar events: ' . $e->getMessage());
            $calendarEvents = [];
        }

        return view('user.dashboard', compact(
            'stats',
            'recentReservations',
            'upcomingReservations',
            'calendarEvents'
        ));
    }

    private function generateUserCalendarEvents($userId)
    {
        $startDate = now()->startOfMonth()->subMonth();
        $endDate = now()->endOfMonth()->addMonths(2);

        $reservations = Reservation::with(['laboratory'])
            ->where('user_id', $userId)
            ->whereBetween('reservation_date', [$startDate, $endDate])
            ->get();

        Log::info("Found {$reservations->count()} user reservations between {$startDate} and {$endDate}");

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
                'id' => 'user-reservation-' . $reservation->id,
                'title' => $reservation->laboratory->name,
                'start' => $reservation->reservation_date->format('Y-m-d') . 'T' . $reservation->start_time,
                'end' => $reservation->reservation_date->format('Y-m-d') . 'T' . $reservation->end_time,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => $textColor,
                'extendedProps' => [
                    'laboratory' => $reservation->laboratory->name,
                    'user' => $reservation->user->name ?? Auth::user()->name,
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

    // API endpoint for user calendar events - Enhanced with debugging
    public function calendarEvents(Request $request)
    {
        try {
            Log::info('=== USER CALENDAR API CALLED ===');
            Log::info('Request URL: ' . $request->fullUrl());
            Log::info('Request Method: ' . $request->method());
            Log::info('User Agent: ' . $request->userAgent());
            
            $user = Auth::user();
            Log::info('Authenticated User: ' . ($user ? $user->id . ' - ' . $user->name : 'NOT AUTHENTICATED'));
            
            if (!$user) {
                Log::error('User not authenticated for calendar API');
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $startDate = now()->startOfMonth()->subMonth();
            $endDate = now()->endOfMonth()->addMonths(2);
            
            Log::info("Searching reservations between {$startDate} and {$endDate}");

            $reservations = Reservation::with(['laboratory', 'user'])
                ->where('user_id', $user->id)
                ->whereBetween('reservation_date', [$startDate, $endDate])
                ->get();

            Log::info("API: Found {$reservations->count()} user reservations");
            
            // Log each reservation for debugging
            foreach ($reservations as $reservation) {
                Log::info("Reservation {$reservation->id}: {$reservation->laboratory->name} on {$reservation->reservation_date} - Status: {$reservation->status}");
            }

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

                $event = [
                    'id' => 'user-reservation-' . $reservation->id,
                    'title' => $reservation->laboratory->name,
                    'start' => $reservation->reservation_date->format('Y-m-d') . 'T' . $reservation->start_time,
                    'end' => $reservation->reservation_date->format('Y-m-d') . 'T' . $reservation->end_time,
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'textColor' => $textColor,
                    'extendedProps' => [
                        'laboratory' => $reservation->laboratory->name,
                        'user' => $reservation->user->name ?? $user->name,
                        'purpose' => $reservation->purpose,
                        'status' => ucfirst($reservation->status),
                        'participant_count' => $reservation->participant_count,
                        'reservation_id' => $reservation->id,
                        'description' => $reservation->description ?? '',
                        'admin_notes' => $reservation->admin_notes ?? ''
                    ]
                ];
                
                Log::info("Generated event: " . json_encode($event));
                return $event;
            });

            Log::info("API: Returning {$events->count()} calendar events");
            Log::info('=== END USER CALENDAR API ===');
            
            // Add headers for debugging
            return response()->json($events->values()->toArray())
                ->header('X-Debug-Events-Count', $events->count())
                ->header('X-Debug-User-ID', $user->id);
            
        } catch (\Exception $e) {
            Log::error('=== ERROR in user calendarEvents ===');
            Log::error('Error Message: ' . $e->getMessage());
            Log::error('Error File: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Stack Trace: ' . $e->getTraceAsString());
            Log::error('=== END ERROR ===');
            
            return response()->json([
                'error' => 'Failed to load calendar events',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'debug' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ] : null
            ], 500);
        }
    }
}