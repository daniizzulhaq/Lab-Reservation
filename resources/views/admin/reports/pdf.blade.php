<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Reservasi Laboratorium</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #4e73df;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 18px;
            color: #4e73df;
            margin-bottom: 5px;
        }
        
        .header h2 {
            font-size: 14px;
            color: #666;
            font-weight: normal;
        }
        
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .info-left, .info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .info-box {
            background-color: #f8f9fc;
            padding: 10px;
            margin-right: 10px;
            border-left: 3px solid #4e73df;
        }
        
        .info-box h4 {
            color: #4e73df;
            margin-bottom: 8px;
            font-size: 11px;
        }
        
        .info-item {
            margin-bottom: 4px;
            font-size: 9px;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 90px;
        }
        
        .statistics {
            margin-bottom: 20px;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px;
        }
        
        .stats-row {
            display: table-row;
        }
        
        .stat-card {
            display: table-cell;
            background-color: #f8f9fc;
            padding: 12px;
            text-align: center;
            border-radius: 4px;
            width: 16.66%;
        }
        
        .stat-number {
            font-size: 16px;
            font-weight: bold;
            color: #4e73df;
            display: block;
        }
        
        .stat-label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
            margin-top: 2px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .data-table th {
            background-color: #4e73df;
            color: white;
            padding: 8px 4px;
            font-size: 9px;
            text-align: center;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        
        .data-table td {
            padding: 6px 4px;
            border: 1px solid #ddd;
            font-size: 8px;
            vertical-align: top;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f8f9fc;
        }
        
        .data-table tr:hover {
            background-color: #eaecf4;
        }
        
        .text-center {
            text-align: center;
        }
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending {
            background-color: #f6c23e;
            color: #000;
        }
        
        .status-approved {
            background-color: #1cc88a;
            color: #fff;
        }
        
        .status-completed {
            background-color: #36b9cc;
            color: #fff;
        }
        
        .status-rejected {
            background-color: #e74a3b;
            color: #fff;
        }
        
        .status-cancelled {
            background-color: #6c757d;
            color: #fff;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .no-data {
            text-align: center;
            padding: 30px;
            color: #999;
            font-style: italic;
        }
        
        /* Column widths */
        .col-no { width: 3%; }
        .col-date { width: 8%; }
        .col-time { width: 10%; }
        .col-lab { width: 15%; }
        .col-name { width: 15%; }
        .col-purpose { width: 25%; }
        .col-status { width: 8%; }
        .col-created { width: 12%; }
        .col-email { width: 4%; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN RESERVASI LABORATORIUM</h1>
        <h2>Sistem Manajemen Reservasi Lab</h2>
    </div>
    
    <!-- Information Section -->
    <div class="info-section">
        <div class="info-left">
            <div class="info-box">
                <h4>INFORMASI LAPORAN</h4>
                <div class="info-item">
                    <span class="info-label">Tanggal Export:</span>
                    {{ $filterInfo['generated_at']->format('d/m/Y H:i:s') }}
                </div>
                <div class="info-item">
                    <span class="info-label">Dibuat Oleh:</span>
                    {{ $filterInfo['generated_by'] }}
                </div>
                <div class="info-item">
                    <span class="info-label">Total Data:</span>
                    {{ $statistics['total'] }} reservasi
                </div>
            </div>
        </div>
        <div class="info-right">
            <div class="info-box" style="margin-right: 0;">
                <h4>FILTER YANG DITERAPKAN</h4>
                <div class="info-item">
                    <span class="info-label">Periode:</span>
                    @if($filterInfo['start_date'] && $filterInfo['end_date'])
                        {{ date('d/m/Y', strtotime($filterInfo['start_date'])) }} - {{ date('d/m/Y', strtotime($filterInfo['end_date'])) }}
                    @elseif($filterInfo['start_date'])
                        Dari {{ date('d/m/Y', strtotime($filterInfo['start_date'])) }}
                    @elseif($filterInfo['end_date'])
                        Sampai {{ date('d/m/Y', strtotime($filterInfo['end_date'])) }}
                    @else
                        Semua Periode
                    @endif
                </div>
                <div class="info-item">
                    <span class="info-label">Laboratorium:</span>
                    {{ $filterInfo['laboratory'] ? $filterInfo['laboratory']->name : 'Semua Laboratorium' }}
                </div>
                <div class="info-item">
                    <span class="info-label">Status:</span>
                    {{ $filterInfo['status'] ? ucfirst($filterInfo['status']) : 'Semua Status' }}
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics -->
    <div class="statistics">
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stat-card">
                    <span class="stat-number">{{ $statistics['total'] }}</span>
                    <span class="stat-label">Total</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number">{{ $statistics['pending'] }}</span>
                    <span class="stat-label">Pending</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number">{{ $statistics['approved'] }}</span>
                    <span class="stat-label">Disetujui</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number">{{ $statistics['completed'] }}</span>
                    <span class="stat-label">Selesai</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number">{{ $statistics['rejected'] }}</span>
                    <span class="stat-label">Ditolak</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number">{{ $statistics['cancelled'] }}</span>
                    <span class="stat-label">Dibatalkan</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Data Table -->
    @if($reservations->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-date">Tanggal</th>
                    <th class="col-time">Waktu</th>
                    <th class="col-lab">Laboratorium</th>
                    <th class="col-name">Peminjam</th>
                    <th class="col-purpose">Keperluan</th>
                    <th class="col-status">Status</th>
                    <th class="col-created">Dibuat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reservations as $index => $reservation)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($reservation->reservation_date)->format('d/m/Y') }}</td>
                        <td class="text-center">{{ $reservation->start_time }}<br>{{ $reservation->end_time }}</td>
                        <td>{{ $reservation->laboratory->name ?? '-' }}</td>
                        <td>
                            <strong>{{ $reservation->user->name ?? $reservation->name ?? '-' }}</strong>
                            @if($reservation->user && $reservation->user->email)
                                <br><small>{{ $reservation->user->email }}</small>
                            @endif
                        </td>
                        <td>{{ $reservation->purpose }}</td>
                        <td class="text-center">
                            @switch($reservation->status)
                                @case('pending')
                                    <span class="status-badge status-pending">Pending</span>
                                    @break
                                @case('approved')
                                    <span class="status-badge status-approved">Disetujui</span>
                                    @break
                                @case('rejected')
                                    <span class="status-badge status-rejected">Ditolak</span>
                                    @break
                                @case('completed')
                                    <span class="status-badge status-completed">Selesai</span>
                                    @break
                                @case('cancelled')
                                    <span class="status-badge status-cancelled">Dibatalkan</span>
                                    @break
                                @default
                                    <span class="status-badge status-cancelled">{{ ucfirst($reservation->status) }}</span>
                            @endswitch
                        </td>
                        <td class="text-center">{{ $reservation->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <p>Tidak ada data reservasi yang sesuai dengan filter yang diterapkan.</p>
        </div>
    @endif
    
    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh Sistem Manajemen Reservasi Lab pada {{ $filterInfo['generated_at']->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>