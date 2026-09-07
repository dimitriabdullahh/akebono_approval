<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'PT Maju Jaya Manufaktur',
                'vendor_code' => 'VND-001',
                'pic_name' => 'Budi Santoso',
                'pic_email' => 'budi.santoso@majujaya.co.id',
                'pic_phone' => '+62-812-3456-7890',
                'address' => 'Jl. Industri No. 45, Karawang, Jawa Barat 41315',
            ],
            [
                'name' => 'CV Precision Engineering',
                'vendor_code' => 'VND-002',
                'pic_name' => 'Siti Nurhaliza',
                'pic_email' => 'siti.nurhaliza@precision-eng.com',
                'pic_phone' => '+62-821-9876-5432',
                'address' => 'Jl. Raya Serang Km 12, Balaraja, Tangerang 15610',
            ],
            [
                'name' => 'PT Indigo Metalindo',
                'vendor_code' => 'VND-003',
                'pic_name' => 'Adi Wibowo',
                'pic_email' => 'adi.wibowo@indigometal.id',
                'pic_phone' => '+62-822-4567-8901',
                'address' => 'Jl. Pergudangan No. 28, Cikarang, Bekasi 17530',
            ],
            [
                'name' => 'PT Sentosa Teknik Nusantara',
                'vendor_code' => 'VND-004',
                'pic_name' => 'Rini Kusuma Dewi',
                'pic_email' => 'rini.kusuma@sentosateknik.com',
                'pic_phone' => '+62-813-5678-9012',
                'address' => 'Jl. Kebumen No. 88, Banyuwangi, Jawa Timur 68418',
            ],
            [
                'name' => 'PT Dinamis Otomotif Indonesia',
                'vendor_code' => 'VND-005',
                'pic_name' => 'Yudi Hermawan',
                'pic_email' => 'yudi.hermawan@dinamis-otomotif.co.id',
                'pic_phone' => '+62-814-6789-0123',
                'address' => 'Jl. Gajah Mada No. 99, Surabaya, Jawa Timur 60181',
            ],
            [
                'name' => 'CV Triwira Logam Mulia',
                'vendor_code' => 'VND-006',
                'pic_name' => 'Hendra Wijaya',
                'pic_email' => 'hendra.wijaya@triwira-logam.net',
                'pic_phone' => '+62-815-7890-1234',
                'address' => 'Jl. Imam Bonjol No. 55, Bandung, Jawa Barat 40132',
            ],
            [
                'name' => 'PT Karya Mitra Sejahtera',
                'vendor_code' => 'VND-007',
                'pic_name' => 'Tini Heriyanti',
                'pic_email' => 'tini.heriyanti@karya-mitra.id',
                'pic_phone' => '+62-816-8901-2345',
                'address' => 'Jl. Raden Inten II No. 77, Jakarta Selatan 12950',
            ],
            [
                'name' => 'PT Gemilang Industri Berkah',
                'vendor_code' => 'VND-008',
                'pic_name' => 'Bambang Setiawan',
                'pic_email' => 'bambang.setiawan@gemilang-industri.co.id',
                'pic_phone' => '+62-817-9012-3456',
                'address' => 'Jl. Yos Sudarso No. 123, Medan, Sumatera Utara 20123',
            ],
            [
                'name' => 'CV Harmoni Teknik Bersama',
                'vendor_code' => 'VND-009',
                'pic_name' => 'Listiana Putri',
                'pic_email' => 'listiana.putri@harmoni-teknik.com',
                'pic_phone' => '+62-818-0123-4567',
                'address' => 'Jl. Kolonel Masturi No. 200, Cimahi, Jawa Barat 40534',
            ],
            [
                'name' => 'PT Inovasi Logam Terpadu',
                'vendor_code' => 'VND-010',
                'pic_name' => 'Sutrisno Wiyono',
                'pic_email' => 'sutrisno.wiyono@inovasi-logam.co.id',
                'pic_phone' => '+62-819-1234-5678',
                'address' => 'Jl. A. Yani No. 333, Semarang, Jawa Tengah 50136',
            ],
        ];

        foreach ($suppliers as $supplierData) {
            // Buat supplier
            $supplier = Supplier::create($supplierData);

            // Buat user untuk supplier dengan email dari pic_email
            User::create([
                'name' => $supplierData['pic_name'],
                'email' => $supplierData['pic_email'],
                'password' => Hash::make('password'), // Default password
                'role' => 'supplier',
                'supplier_id' => $supplier->id,
                'is_active' => true,
            ]);

            $this->command->info("Supplier {$supplier->name} dan user {$supplierData['pic_name']} berhasil dibuat!");
        }

        $this->command->info('10 suppliers dan 10 supplier users berhasil dibuat!');
    }
}
