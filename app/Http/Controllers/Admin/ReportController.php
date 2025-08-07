<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Laboratory;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReservationExport;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Base query
        $query = Reservation::with(['laboratory', 'user']);
        
        // Apply filters jika ada
        if ($request->filled('start_date')) {
            $query->where('reservation_date', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->where('reservation_date', '<=', $request->end_date);
        }
        
        if ($request->filled('laboratory_id')) {
            $query->where('laboratory_id', $request->laboratory_id);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Clone query untuk statistik
        $statsQuery = clone $query;
        
        // Hitung statistik dengan query yang sama
        $totalReservations = $statsQuery->count();
        $completedReservations = (clone $query)->where('status', 'completed')->count();
        $pendingReservations = (clone $query)->where('status', 'pending')->count();
        $approvedReservations = (clone $query)->where('status', 'approved')->count();
        $cancelledReservations = (clone $query)->where('status', 'cancelled')->count();
        
        // Get reservations untuk tabel
        $reservations = $query->latest()->paginate(10);
        
        // Total laboratories
        $totalLaboratories = Laboratory::count();
        $laboratories = Laboratory::all();
        
        // Data untuk chart
        $chartData = $this->getChartData($request);
        
        return view('admin.reports.index', compact(
            'reservations',
            'laboratories',
            'totalReservations',
            'completedReservations',
            'pendingReservations',
            'approvedReservations',
            'cancelledReservations',
            'totalLaboratories',
            'chartData'
        ));
    }
    
    public function export(Request $request)
    {
        $filters = [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'laboratory_id' => $request->laboratory_id,
            'status' => $request->status,
        ];
        
        $filename = 'laporan_reservasi_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new ReservationExport($filters), $filename);
    }
    
    public function exportPdf(Request $request)
    {
        // Get filtered data
        $query = Reservation::with(['laboratory', 'user']);
        
        // Apply same filters as index method
        if ($request->filled('start_date')) {
            $query->where('reservation_date', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->where('reservation_date', '<=', $request->end_date);
        }
        
        if ($request->filled('laboratory_id')) {
            $query->where('laboratory_id', $request->laboratory_id);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Get data
        $reservations = $query->orderBy('reservation_date', 'desc')
                            ->orderBy('start_time', 'asc')
                            ->get();
        
        // Get laboratories for filter display
        $laboratories = Laboratory::all();
        $selectedLab = null;
        if ($request->filled('laboratory_id')) {
            $selectedLab = $laboratories->find($request->laboratory_id);
        }
        
        // Prepare filter info
        $filterInfo = [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'laboratory' => $selectedLab,
            'status' => $request->status,
            'generated_at' => now(),
            'generated_by' => auth()->user()->name
        ];
        
        // Calculate statistics
        $statistics = [
            'total' => $reservations->count(),
            'pending' => $reservations->where('status', 'pending')->count(),
            'approved' => $reservations->where('status', 'approved')->count(),
            'completed' => $reservations->where('status', 'completed')->count(),
            'rejected' => $reservations->where('status', 'rejected')->count(),
            'cancelled' => $reservations->where('status', 'cancelled')->count(),
        ];
        
        // Create PDF
        $pdf = Pdf::loadView('admin.reports.pdf', compact('reservations', 'filterInfo', 'statistics'));
        $pdf->setPaper('A4', 'landscape');
        
        $filename = 'laporan_reservasi_' . date('Y-m-d_H-i-s') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    public function exportExcel(Request $request)
    {
        // This method is kept for backward compatibility
        return $this->export($request);
    }
    
    private function getChartData($request)
    {
        $query = Reservation::select('laboratory_id')
            ->selectRaw('count(*) as count')
            ->with('laboratory');
            
        // Apply same filters
        if ($request->filled('start_date')) {
            $query->where('reservation_date', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->where('reservation_date', '<=', $request->end_date);
        }
        
        if ($request->filled('laboratory_id')) {
            $query->where('laboratory_id', $request->laboratory_id);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $data = $query->groupBy('laboratory_id')->get();
        
        $chartData = [];
        foreach ($data as $item) {
            $labName = $item->laboratory ? $item->laboratory->name : 'Unknown';
            $chartData[$labName] = $item->count;
        }
        
        return $chartData;
    }
}