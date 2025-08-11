<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laboratory;
<<<<<<< HEAD
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
=======
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with statistics and data
     */
    public function index()
    {
        // Statistics
        $stats = [
            'total_labs' => Laboratory::count(),
            'total_laboratories' => Laboratory::count(),
            'total_users' => User::where('role', '!=', 'admin')->count(),
            'total_reservations' => Reservation::count(),
            'pending_reservations' => Reservation::where('status', 'pending')->count(),
            'approved_reservations' => Reservation::where('status', 'approved')->count(),
            'rejected_reservations' => Reservation::where('status', 'rejected')->count(),
            'completed_reservations' => Reservation::where('status', 'completed')->count(),
            'cancelled_reservations' => Reservation::where('status', 'cancelled')->count(),
            'today_reservations' => Reservation::whereDate('reservation_date', Carbon::today())->count(),
            'this_month_reservations' => Reservation::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
            'active_labs' => Laboratory::where('status', 'active')->count(),
            'maintenance_labs' => Laboratory::where('status', 'maintenance')->count(),
            'inactive_labs' => Laboratory::where('status', 'inactive')->count(),
        ];
        
        // Recent Reservations
        $recentReservations = Reservation::with(['user', 'laboratory'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Most Popular Laboratories
        $popularLaboratories = Laboratory::withCount('reservations')
            ->orderBy('reservations_count', 'desc')
            ->limit(5)
            ->get();
        
        // Weekly Statistics
        $weeklyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $weeklyStats[] = [
                'date' => $date->format('M d'),
                'reservations' => Reservation::whereDate('created_at', $date)->count()
            ];
        }
        
        return view('admin.dashboard', compact(
            'stats', 
            'recentReservations', 
            'popularLaboratories', 
            'weeklyStats'
        ));
    }
    
    /**
     * Get calendar events for admin dashboard
     */
    public function calendarEvents()
    {
        $reservations = Reservation::with(['laboratory', 'user'])
            ->get();

        $events = $reservations->map(function ($reservation) {
            return [
                'id' => $reservation->id,
                'title' => $reservation->laboratory->name . ' - ' . $reservation->user->name,
                'start' => $reservation->reservation_date->format('Y-m-d') . 'T' . $reservation->start_time,
                'end' => $reservation->reservation_date->format('Y-m-d') . 'T' . $reservation->end_time,
                'backgroundColor' => $this->getStatusColor($reservation->status),
                'borderColor' => $this->getStatusColor($reservation->status),
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'laboratory' => $reservation->laboratory->name,
                    'user' => $reservation->user->name,
                    'user_role' => ucfirst($reservation->user->role),
                    'purpose' => $reservation->purpose,
                    'participant_count' => $reservation->participant_count,
                    'status' => ucfirst($reservation->status),
                    'reservation_id' => $reservation->id
                ]
            ];
        });

        return response()->json($events);
    }
    
    /**
     * Get chart data for dashboard
     */
    public function getChartData()
    {
        // Monthly reservation data for the last 12 months
        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::today()->startOfMonth()->subMonths($i);
            $monthlyData[] = [
                'month' => $date->format('M Y'),
                'reservations' => Reservation::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count()
            ];
        }
        
        // Status distribution
        $statusData = [
            'pending' => Reservation::where('status', 'pending')->count(),
            'approved' => Reservation::where('status', 'approved')->count(),
            'rejected' => Reservation::where('status', 'rejected')->count(),
            'completed' => Reservation::where('status', 'completed')->count(),
        ];
        
        // Laboratory usage data
        $laboratoryData = Laboratory::withCount('reservations')
            ->orderBy('reservations_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($lab) {
                return [
                    'name' => $lab->name,
                    'count' => $lab->reservations_count
                ];
            });
        
        return response()->json([
            'monthly' => $monthlyData,
            'status' => $statusData,
            'laboratory' => $laboratoryData
        ]);
    }

    /**
     * Display laboratories index page
     */
    public function laboratories()
    {
>>>>>>> 92b809e (notifikasi)
        $laboratories = Laboratory::withCount('reservations')->paginate(10);
        return view('admin.laboratories.index', compact('laboratories'));
    }

<<<<<<< HEAD
    public function create()
=======
    /**
     * Show form for creating a new laboratory
     */
    public function createLaboratory()
>>>>>>> 92b809e (notifikasi)
    {
        return view('admin.laboratories.create');
    }

<<<<<<< HEAD
    public function store(Request $request)
=======
    /**
     * Store a newly created laboratory
     */
    public function storeLaboratory(Request $request)
>>>>>>> 92b809e (notifikasi)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:laboratories',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'facilities' => 'nullable|string',
            'status' => 'required|in:active,maintenance,inactive',
            'location' => 'nullable|string',
        ]);

        Laboratory::create($validated);

        return redirect()->route('admin.laboratories.index')
            ->with('success', 'Laboratorium berhasil ditambahkan.');
    }

<<<<<<< HEAD
    public function show(Laboratory $laboratory)
=======
    /**
     * Display the specified laboratory
     */
    public function showLaboratory(Laboratory $laboratory)
>>>>>>> 92b809e (notifikasi)
    {
        $laboratory->load(['reservations' => function($query) {
            $query->with('user')->orderBy('reservation_date', 'desc');
        }]);

        return view('admin.laboratories.show', compact('laboratory'));
    }

<<<<<<< HEAD
    public function edit(Laboratory $laboratory)
=======
    /**
     * Show form for editing the specified laboratory
     */
    public function editLaboratory(Laboratory $laboratory)
>>>>>>> 92b809e (notifikasi)
    {
        return view('admin.laboratories.edit', compact('laboratory'));
    }

<<<<<<< HEAD
    public function update(Request $request, Laboratory $laboratory)
=======
    /**
     * Update the specified laboratory
     */
    public function updateLaboratory(Request $request, Laboratory $laboratory)
>>>>>>> 92b809e (notifikasi)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:laboratories,code,' . $laboratory->id,
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'facilities' => 'nullable|string',
            'status' => 'required|in:active,maintenance,inactive',
            'location' => 'nullable|string',
        ]);

        $laboratory->update($validated);

        return redirect()->route('admin.laboratories.index')
            ->with('success', 'Laboratorium berhasil diperbarui.');
    }

<<<<<<< HEAD
    public function destroy(Laboratory $laboratory)
=======
    /**
     * Remove the specified laboratory
     */
    public function destroyLaboratory(Laboratory $laboratory)
>>>>>>> 92b809e (notifikasi)
    {
        if ($laboratory->reservations()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus laboratorium yang memiliki reservasi.');
        }

        $laboratory->delete();

        return redirect()->route('admin.laboratories.index')
            ->with('success', 'Laboratorium berhasil dihapus.');
    }
<<<<<<< HEAD
}
=======
    
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
}
>>>>>>> 92b809e (notifikasi)
