<?php

namespace App\Exports;

use App\Models\Reservation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class ReservationExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Reservation::with(['laboratory', 'user']);

        // Apply filters
        if (!empty($this->filters['start_date'])) {
            $query->where('reservation_date', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->where('reservation_date', '<=', $this->filters['end_date']);
        }

        if (!empty($this->filters['laboratory_id'])) {
            $query->where('laboratory_id', $this->filters['laboratory_id']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('reservation_date', 'desc')
                    ->orderBy('start_time', 'asc')
                    ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Tanggal Reservasi',
            'Waktu Mulai',
            'Waktu Selesai',
            'Laboratorium',
            'Nama Peminjam',
            'Email',
            'Role',
            'Keperluan',
            'Status',
            'Catatan Admin',
            'Tanggal Dibuat',
            'Terakhir Diupdate'
        ];
    }

    /**
     * @param mixed $reservation
     * @return array
     */
    public function map($reservation): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            Carbon::parse($reservation->reservation_date)->format('d/m/Y'),
            $reservation->start_time,
            $reservation->end_time,
            $reservation->laboratory->name ?? '-',
            $reservation->user->name ?? $reservation->name ?? '-',
            $reservation->user->email ?? $reservation->email ?? '-',
            $reservation->user ? ucfirst($reservation->user->role) : '-',
            $reservation->purpose,
            $this->formatStatus($reservation->status),
            $reservation->admin_notes ?? '-',
            $reservation->created_at->format('d/m/Y H:i:s'),
            $reservation->updated_at->format('d/m/Y H:i:s')
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Get the last row with data
        $lastRow = $sheet->getHighestRow();

        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => '4e73df',
                    ],
                ],
                'font' => [
                    'bold' => true,
                    'color' => [
                        'rgb' => 'FFFFFF',
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            
            // All cells border
            'A1:M' . $lastRow => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],

            // Data rows
            'A2:M' . $lastRow => [
                'alignment' => [
                    'wrapText' => true,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 15,  // Tanggal
            'C' => 12,  // Waktu Mulai
            'D' => 12,  // Waktu Selesai
            'E' => 20,  // Laboratorium
            'F' => 20,  // Nama
            'G' => 25,  // Email
            'H' => 12,  // Role
            'I' => 30,  // Keperluan
            'J' => 12,  // Status
            'K' => 25,  // Catatan
            'L' => 18,  // Created
            'M' => 18,  // Updated
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Laporan Reservasi';
    }

    /**
     * Format status for display
     */
    private function formatStatus($status)
    {
        switch ($status) {
            case 'pending':
                return 'Pending';
            case 'approved':
                return 'Disetujui';
            case 'rejected':
                return 'Ditolak';
            case 'completed':
                return 'Selesai';
            case 'cancelled':
                return 'Dibatalkan';
            default:
                return ucfirst($status);
        }
    }
}