<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Laboratory;
use App\Models\User;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class CreateSampleData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:sample-data {--clear : Clear existing data first}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sample data for testing the laboratory reservation system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clear = $this->option('clear');
        
        if ($clear) {
            if ($this->confirm('This will delete all existing reservations, users, and laboratories. Are you sure?')) {
                $this->info('Clearing existing data...');
                Reservation::truncate();
                User::where('role', '!=', 'admin')->delete();
                Laboratory::truncate();
                $this->info('✅ Existing data cleared!');
            } else {
                $this->info('Operation cancelled.');
                return;
            }
        }

        $this->info('Creating sample data...');
        
        // Create sample laboratories
        $this->createLaboratories();
        
        // Create sample users
        $this->createUsers();
        
        // Create sample reservations
        $this->createReservations();
        
        $this->info('🎉 Sample data created successfully!');
        $this->displaySummary();
    }

    private function createLaboratories()
    {
        if (Laboratory::count() > 0) {
            $this->info('Laboratories already exist, skipping...');
            return;
        }

        $laboratories = [
            [
                'name' => 'Lab Komputer 1',
                'description' => 'Laboratorium komputer dengan 30 unit PC untuk praktikum pemrograman',
                'capacity' => 30,
                'location' => 'Gedung Teknik Lantai 2',
                'facilities' => json_encode(['Proyektor', 'AC', 'Whiteboard', 'Komputer', 'Internet'])
            ],
            [
                'name' => 'Lab Komputer 2', 
                'description' => 'Laboratorium komputer dengan spesifikasi tinggi untuk pengembangan aplikasi',
                'capacity' => 25,
                'location' => 'Gedung Teknik Lantai 3',
                'facilities' => json_encode(['Proyektor', 'AC', 'Whiteboard', 'Komputer Gaming', 'Internet Fiber'])
            ],
            [
                'name' => 'Lab Multimedia',
                'description' => 'Laboratorium multimedia untuk editing video dan audio',
                'capacity' => 20,
                'location' => 'Gedung Teknik Lantai 1',
                'facilities' => json_encode(['Proyektor 4K', 'AC', 'Sound System', 'Komputer High-End', 'Software Adobe'])
            ],
            [
                'name' => 'Lab Jaringan',
                'description' => 'Laboratorium jaringan komputer dengan peralatan cisco',
                'capacity' => 24,
                'location' => 'Gedung Teknik Lantai 2',
                'facilities' => json_encode(['Router Cisco', 'Switch', 'Kabel UTP', 'Crimping Tool', 'Network Analyzer'])
            ],
            [
                'name' => 'Lab Database',
                'description' => 'Laboratorium khusus untuk praktikum basis data',
                'capacity' => 28,
                'location' => 'Gedung Teknik Lantai 3',
                'facilities' => json_encode(['Server Database', 'Proyektor', 'AC', 'Komputer', 'Software DBMS'])
            ]
        ];

        foreach ($laboratories as $lab) {
            Laboratory::create($lab);
        }

        $this->info('✅ Created ' . count($laboratories) . ' sample laboratories');
    }

    private function createUsers()
    {
        if (User::whereIn('role', ['dosen', 'mahasiswa'])->count() > 0) {
            $this->info('Users already exist, skipping...');
            return;
        }

        // Create admin user if not exists
        $admin = User::where('email', 'admin@example.com')->first();
        if (!$admin) {
            User::create([
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'admin'
            ]);
            $this->info('✅ Created admin user (admin@example.com / password)');
        }

        // Create sample lecturers
        $lecturers = [
            ['name' => 'Dr. Ahmad Susanto, S.Kom, M.Kom', 'email' => 'ahmad.susanto@university.edu'],
            ['name' => 'Prof. Sri Wahyuni, S.T, M.T', 'email' => 'sri.wahyuni@university.edu'], 
            ['name' => 'Dr. Budi Raharjo, S.Kom, M.Sc', 'email' => 'budi.raharjo@university.edu'],
            ['name' => 'Ir. Siti Nurhaliza, M.Kom', 'email' => 'siti.nurhaliza@university.edu'],
            ['name' => 'Dr. Eko Prasetyo, S.T, M.T', 'email' => 'eko.prasetyo@university.edu']
        ];

        foreach ($lecturers as $lecturer) {
            User::create([
                'name' => $lecturer['name'],
                'email' => $lecturer['email'],
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'dosen'
            ]);
        }

        // Create sample students
        $students = [
            ['name' => 'Andi Firmansyah', 'email' => 'andi.firmansyah@student.university.edu'],
            ['name' => 'Sari Dewi Lestari', 'email' => 'sari.dewi@student.university.edu'],
            ['name' => 'Muhammad Rizki', 'email' => 'muhammad.rizki@student.university.edu'],
            ['name' => 'Fitri Handayani', 'email' => 'fitri.handayani@student.university.edu'],
            ['name' => 'Dimas Pratama', 'email' => 'dimas.pratama@student.university.edu'],
            ['name' => 'Indah Permatasari', 'email' => 'indah.permata@student.university.edu'],
            ['name' => 'Fajar Nugroho', 'email' => 'fajar.nugroho@student.university.edu'],
            ['name' => 'Maya Sari', 'email' => 'maya.sari@student.university.edu'],
            ['name' => 'Rendi Kurniawan', 'email' => 'rendi.kurniawan@student.university.edu'],
            ['name' => 'Dewi Anggraini', 'email' => 'dewi.anggraini@student.university.edu']
        ];

        foreach ($students as $student) {
            User::create([
                'name' => $student['name'],
                'email' => $student['email'],
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'mahasiswa'
            ]);
        }

        $this->info('✅ Created ' . count($lecturers) . ' lecturers and ' . count($students) . ' students');
    }

    private function createReservations()
    {
        if (Reservation::count() > 0) {
            $this->info('Reservations already exist, skipping...');
            return;
        }

        $laboratories = Laboratory::all();
        $users = User::whereIn('role', ['dosen', 'mahasiswa'])->get();

        if ($laboratories->isEmpty() || $users->isEmpty()) {
            $this->error('No laboratories or users found!');
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

        $reservations = [];

        // Generate reservations for past week and next 2 weeks
        for ($day = -7; $day <= 14; $day++) {
            $date = Carbon::now()->addDays($day);
            
            // Skip some weekends
            if ($date->isWeekend() && rand(0, 2) == 0) {
                continue;
            }
            
            // Create 1-4 reservations per day
            $reservationsPerDay = rand(1, 4);
            
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
                    'participant_count' => rand(5, min(30, $laboratory->capacity)),
                    'status' => $status,
                    'admin_notes' => $status === 'rejected' ? 'Laboratory tidak tersedia pada waktu tersebut' : 
                                   ($status === 'approved' ? 'Reservasi disetujui' : null),
                    'approved_by' => $status === 'approved' ? 1 : null,
                    'approved_at' => $status === 'approved' ? $date->subHours(rand(1, 48)) : null,
                    'created_at' => $date->subHours(rand(1, 72)),
                    'updated_at' => $date->subHours(rand(1, 24))
                ];
            }
        }

        // Insert reservations
        Reservation::insert($reservations);
        
        $this->info('✅ Created ' . count($reservations) . ' sample reservations');
        
        // Show status breakdown
        $statusCounts = array_count_values(array_column($reservations, 'status'));
        foreach ($statusCounts as $status => $count) {
            $this->info("   - {$status}: {$count}");
        }
    }

    private function displaySummary()
    {
        $this->info('');
        $this->info('📊 SUMMARY:');
        $this->info('- Laboratories: ' . Laboratory::count());
        $this->info('- Users: ' . User::count());
        $this->info('- Reservations: ' . Reservation::count());
        $this->info('');
        $this->info('🔐 LOGIN CREDENTIALS:');
        $this->info('Admin: admin@example.com / password');
        $this->info('Lecturer: ahmad.susanto@university.edu / password');
        $this->info('Student: andi.firmansyah@student.university.edu / password');
        $this->info('');
        $this->info('✨ You can now test the calendar with real data!');
    }
}