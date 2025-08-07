<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
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
