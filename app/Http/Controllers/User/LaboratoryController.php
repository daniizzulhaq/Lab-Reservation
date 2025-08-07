<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LaboratoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Laboratory::where('status', 'active');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->capacity) {
            $query->where('capacity', '>=', $request->capacity);
        }

        $laboratories = $query->paginate(12);

        return view('user.laboratories.index', compact('laboratories'));
    }

    public function show(Laboratory $laboratory)
    {
        $laboratory->load(['reservations' => function($query) {
            $query->where('status', 'approved')
                  ->where('reservation_date', '>=', today())
                  ->orderBy('reservation_date')
                  ->orderBy('start_time');
        }]);

        return view('user.laboratories.show', compact('laboratory'));
    }

    public function checkAvailability(Request $request, Laboratory $laboratory)
    {
        $date = $request->date ?: today()->format('Y-m-d');
        
        $reservations = $laboratory->reservations()
            ->where('status', 'approved')
            ->whereDate('reservation_date', $date)
            ->orderBy('start_time')
            ->get(['start_time', 'end_time', 'user_id', 'purpose']);

        return response()->json([
            'reservations' => $reservations,
            'laboratory' => $laboratory->only(['name', 'capacity', 'facilities'])
        ]);
    }

    public function search(Request $request)
    {
        $laboratories = Laboratory::where('status', 'active')
            ->where(function($query) use ($request) {
                $query->where('name', 'like', '%' . $request->term . '%')
                      ->orWhere('code', 'like', '%' . $request->term . '%');
            })
            ->take(10)
            ->get(['id', 'name', 'code', 'capacity', 'location']);

        return response()->json($laboratories);
    }
}
