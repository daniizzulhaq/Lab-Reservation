<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Reservation;
use App\Models\Laboratory;
use App\Models\User;
use Carbon\Carbon;

class SampleReservationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cari laboratories dan users
        $laboratories = Laboratory::all();
        $users = User::whereIn('role', ['user', 'dosen', 'mahasiswa'])->get();

        if ($laboratories->isEmpty()) {
            $this->command->error('No laboratories found! Please seed laboratories first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->error('No users found! Please seed users first.');
            return;
        }

        $statuses = ['pending', 'approved', 'rejected', 'cancelled', 'completed'];
        $purposes = [
            'Praktikum Pemrograman Web',
            'Workshop Machine Learning',
            'Pelatihan Database Design',
            'Seminar Teknologi',
            'Penelitian Skripsi',
            'Ujian Praktikum',
            'Demo Aplikasi',
            'Training Software',
            'Meeting Tim Proyek',
            'Presentasi Tugas Akhir'
        ];

        $descriptions = [
            'Kegiatan praktikum untuk mahasiswa semester 4',
            'Workshop tentang implementasi ML dengan Python',
            'Pelatihan desain database untuk sistem informasi',
            'Seminar perkembangan teknologi terbaru',
            'Penelitian untuk penyelesaian skripsi',
            'Ujian praktikum pemrograman',
            'Demo aplikasi hasil pengembangan',
            'Pelatihan penggunaan software development',
            'Meeting koordinasi tim pengembang',
            'Presentasi hasil tugas akhir'
        ];

        // Buat reservasi untuk 2 minggu ke depan dan 1 minggu yang lalu
        $reservations = [];
        
        // Generate reservations for the past week
        for ($day = -7; $day <= 14; $day++) {
            $date = Carbon::now()->addDays($day);
            
            // Skip weekends for some variety
            if ($date->isWeekend() && rand(0, 2) == 0) {
                continue;
            }
            
            // Create 1-3 reservations per day
            $reservationsPerDay = rand(1, 3);
            
            for ($i = 0; $i < $reservationsPerDay; $i++) {
                $laboratory = $laboratories->random();
                $user = $users->random();
                
                // Generate time slots (8:00-17:00)
                $startHour = rand(8, 15);
                $duration = rand(1, 3); // 1-3 hours
                $endHour = min($startHour + $duration, 17);
                
                $startTime = sprintf('%02d:00:00', $startHour);
                $endTime = sprintf('%02d:00:00', $endHour);
                
                // Determine status based on date
                $status = 'approved';
                if ($date->isFuture()) {
                    $status = $statuses[array_rand($statuses)];
                } elseif ($date->isPast()) {
                    $status = rand(0, 1) ? 'completed' : 'approved';
                }
                
                $reservations[] = [
                    'user_id' => $user->id,
                    'laboratory_id' => $laboratory->id,
                    'reservation_date' => $date->format('Y-m-d'),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'purpose' => $purposes[array_rand($purposes)],
                    'description' => $descriptions[array_rand($descriptions)],
                    'participant_count' => rand(5, 30),
                    'status' => $status,
                    'admin_notes' => $status === 'rejected' ? 'Laboratory tidak tersedia pada waktu tersebut' : 
                                   ($status === 'approved' ? 'Reservasi disetujui' : null),
                    'approved_by' => $status === 'approved' ? 1 : null, // Assuming admin user ID = 1
                    'approved_at' => $status === 'approved' ? $date->subHours(rand(1, 48)) : null,
                    'created_at' => $date->subHours(rand(1, 72)),
                    'updated_at' => $date->subHours(rand(1, 24))
                ];
            }
        }
        
        // Insert reservations in batches
        $chunks = array_chunk($reservations, 50);
        
        foreach ($chunks as $chunk) {
            try {
                Reservation::insert($chunk);
            } catch (\Exception $e) {
                $this->command->error('Error inserting reservation chunk: ' . $e->getMessage());
            }
        }
        
        $totalCreated = count($reservations);
        $this->command->info("Successfully created {$totalCreated} sample reservations!");
        
        // Show status breakdown
        $statusCounts = array_count_values(array_column($reservations, 'status'));
        $this->command->info("Status breakdown:");
        foreach ($statusCounts as $status => $count) {
            $this->command->info("- {$status}: {$count}");
        }
    }
}