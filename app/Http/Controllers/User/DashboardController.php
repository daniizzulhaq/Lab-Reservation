<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
use App\Models\Laboratory;
use App\Models\Reservation;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $stats = [
            'my_reservations' => $user->reservations()->count(),
            'pending_reservations' => $user->reservations()->where('status', 'pending')->count(),
            'approved_reservations' => $user->reservations()->where('status', 'approved')->count(),
            'upcoming_reservations' => $user->reservations()
                ->where('status', 'approved')
                ->where('reservation_date', '>=', today())
                ->count(),
        ];

        $recentReservations = $user->reservations()
            ->with('laboratory')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $upcomingReservations = $user->reservations()
            ->with('laboratory')
            ->where('status', 'approved')
            ->where('reservation_date', '>=', today())
            ->orderBy('reservation_date')
            ->orderBy('start_time')
            ->take(3)
            ->get();

        return view('user.dashboard', compact('stats', 'recentReservations', 'upcomingReservations'));
    }
}
=======
use App\Models\Reservation;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display user dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // User Statistics
        $stats = [
            'my_reservations' => Reservation::where('user_id', $user->id)->count(),
            'pending_reservations' => Reservation::where('user_id', $user->id)
                ->where('status', 'pending')->count(),
            'approved_reservations' => Reservation::where('user_id', $user->id)
                ->where('status', 'approved')->count(),
            'upcoming_reservations' => Reservation::where('user_id', $user->id)
                ->where('reservation_date', '>=', Carbon::today())
                ->where('status', 'approved')
                ->count(),
        ];
        
        // Upcoming Reservations (next 7 days)
        $upcomingReservations = Reservation::with('laboratory')
            ->where('user_id', $user->id)
            ->where('reservation_date', '>=', Carbon::today())
            ->where('reservation_date', '<=', Carbon::today()->addDays(7))
            ->whereIn('status', ['approved', 'pending'])
            ->orderBy('reservation_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->limit(5)
            ->get();
        
        // Recent Reservations
        $recentReservations = Reservation::with('laboratory')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('user.dashboard', compact(
            'stats',
            'upcomingReservations', 
            'recentReservations'
        ));
    }
    
    /**
     * Get calendar events for user dashboard
     */
    public function calendarEvents()
    {
        $user = Auth::user();
        
        $reservations = Reservation::with(['laboratory'])
            ->where('user_id', $user->id)
            ->get();

        $events = $reservations->map(function ($reservation) {
            $startDateTime = $reservation->reservation_date->format('Y-m-d') . 'T' . $reservation->start_time;
            $endDateTime = $reservation->reservation_date->format('Y-m-d') . 'T' . $reservation->end_time;
            
            return [
                'id' => $reservation->id,
                'title' => $reservation->laboratory->name,
                'start' => $startDateTime,
                'end' => $endDateTime,
                'backgroundColor' => $this->getStatusColor($reservation->status),
                'borderColor' => $this->getStatusColor($reservation->status),
                'textColor' => '#ffffff',
                'allDay' => false,
                'extendedProps' => [
                    'laboratory' => $reservation->laboratory->name,
                    'purpose' => $reservation->purpose ?? 'Tidak ada tujuan',
                    'participant_count' => $reservation->participant_count ?? 0,
                    'status' => ucfirst($reservation->status),
                    'reservation_id' => $reservation->id,
                    'location' => $reservation->laboratory->location ?? 'Lokasi tidak tersedia'
                ]
            ];
        });

        return response()->json($events);
    }
    
    /**
     * Get color based on reservation status
     */
    private function getStatusColor($status)
    {
        return match($status) {
            'approved' => '#28a745',    // Green
            'pending' => '#ffc107',     // Yellow
            'rejected' => '#dc3545',    // Red
            'completed' => '#17a2b8',   // Blue
            'cancelled' => '#6c757d',   // Gray
            default => '#6c757d'        // Default Gray
        };
    }
    
    /**
     * Get laboratories for reservation form
     */
    public function getLaboratories()
    {
        $laboratories = Laboratory::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'capacity', 'location']);
            
        return response()->json($laboratories);
    }
    
    /**
     * Check laboratory availability
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'laboratory_id' => 'required|exists:laboratories,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'reservation_id' => 'nullable|exists:reservations,id'
        ]);
        
        $query = Reservation::where('laboratory_id', $request->laboratory_id)
            ->where('reservation_date', $request->date)
            ->whereIn('status', ['approved', 'pending'])
            ->where(function($q) use ($request) {
                $q->whereBetween('start_time', [$request->start_time, $request->end_time])
                  ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                  ->orWhere(function($q2) use ($request) {
                      $q2->where('start_time', '<=', $request->start_time)
                         ->where('end_time', '>=', $request->end_time);
                  });
            });
        
        // Exclude current reservation if editing
        if ($request->reservation_id) {
            $query->where('id', '!=', $request->reservation_id);
        }
        
        $conflictingReservations = $query->count();
        
        return response()->json([
            'available' => $conflictingReservations === 0,
            'conflicts' => $conflictingReservations
        ]);
    }
}
>>>>>>> 92b809e (notifikasi)
