<?php

namespace Database\Seeders;

use App\Models\Laboratory;
use Illuminate\Database\Seeder;

class LaboratorySeeder extends Seeder
{
    public function run(): void
    {
        $laboratories = [
            [
                'name' => 'Laboratorium Keperawatan Dasar',
                'code' => 'LAB-KPD-001',
                'description' => 'Laboratorium untuk praktikum keperawatan dasar',
                'capacity' => 30,
                'facilities' => 'Manekin, Bed Pasien, Alat Vital Sign, Phantom',
                'status' => 'active',
                'location' => 'Gedung A Lantai 2',
            ],
            [
                'name' => 'Laboratorium Keperawatan Medikal Bedah',
                'code' => 'LAB-KMB-001',
                'description' => 'Laboratorium untuk praktikum keperawatan medikal bedah',
                'capacity' => 25,
                'facilities' => 'Manekin Dewasa, Alat Suction, Monitor, Infusion Pump',
                'status' => 'active',
                'location' => 'Gedung A Lantai 3',
            ],
            [
                'name' => 'Laboratorium Keperawatan Anak',
                'code' => 'LAB-KA-001',
                'description' => 'Laboratorium untuk praktikum keperawatan anak',
                'capacity' => 20,
                'facilities' => 'Manekin Anak, Incubator, Phototherapy, Nebulizer',
                'status' => 'active',
                'location' => 'Gedung B Lantai 2',
            ],
            [
                'name' => 'Laboratorium Keperawatan Maternitas',
                'code' => 'LAB-KM-001',
                'description' => 'Laboratorium untuk praktikum keperawatan maternitas',
                'capacity' => 20,
                'facilities' => 'Manekin Ibu Hamil, Phantom Melahirkan, CTG, Doppler',
                'status' => 'active',
                'location' => 'Gedung B Lantai 3',
            ],
            [
                'name' => 'Laboratorium Komputer',
                'code' => 'LAB-KOMP-001',
                'description' => 'Laboratorium komputer untuk pembelajaran teknologi kesehatan',
                'capacity' => 40,
                'facilities' => '40 Unit PC, Projector, AC, WiFi',
                'status' => 'active',
                'location' => 'Gedung C Lantai 1',
            ],
        ];

        foreach ($laboratories as $lab) {
            Laboratory::create($lab);
        }
    }
}
