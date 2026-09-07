<?php

namespace Database\Seeders;

use App\Models\RevisionConfig;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RevisionConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RevisionConfig::create([
            'key' => 'revision_deadline_days',
            'value' => '2',
            'description' => 'Durasi default untuk deadline revisi dokumen (dalam hari)',
            'updated_by' => null,
        ]);

        RevisionConfig::create([
            'key' => 'reminder_days_before',
            'value' => '2',
            'description' => 'Pengingat revisi dikirim berapa hari sebelum deadline (dalam hari)',
            'updated_by' => null,
        ]);

        RevisionConfig::create([
            'key' => 'max_file_size_mb',
            'value' => '10',
            'description' => 'Ukuran file maksimal untuk upload dokumen (dalam MB)',
            'updated_by' => null,
        ]);

        RevisionConfig::create([
            'key' => 'allowed_file_types',
            'value' => 'pdf,jpg,jpeg,png',
            'description' => 'Tipe file yang diizinkan untuk upload (comma-separated)',
            'updated_by' => null,
        ]);
    }
}
