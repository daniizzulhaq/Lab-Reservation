<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupLabReservation extends Command
{
    protected $signature = 'lab:setup';
    protected $description = 'Setup Lab Reservation System';

    public function handle()
    {
        $this->info('Setting up Lab Reservation System...');
        
        // Run migrations
        $this->info('Running migrations...');
        Artisan::call('migrate:fresh');
        
        // Run seeders
        $this->info('Seeding database...');
        Artisan::call('db:seed');
        
        // Clear caches
        $this->info('Clearing caches...');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        
        $this->info('Setup completed successfully!');
        $this->info('Default accounts:');
        $this->info('Admin - NIM/NIP: ADMIN001, Password: password');
        $this->info('Dosen - NIM/NIP: 198001011234567890, Password: password');  
        $this->info('Mahasiswa - NIM/NIP: 2024001, Password: password');
    }
}