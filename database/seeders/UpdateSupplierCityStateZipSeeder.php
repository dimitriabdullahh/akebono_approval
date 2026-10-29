<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateSupplierCityStateZipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $suppliers = [
            [
                'vendor_code' => 'TEST-REMINDER',
                'city' => 'Jakarta',
                'state' => 'DKI Jakarta',
                'zip_code' => '12345',
            ],
            [
                'vendor_code' => 'VND-001',
                'city' => 'Bekasi',
                'state' => 'Jawa Barat',
                'zip_code' => '17520',
            ],
            [
                'vendor_code' => 'VND-002',
                'city' => 'Tangerang',
                'state' => 'Banten',
                'zip_code' => '15810',
            ],
            [
                'vendor_code' => 'VND-003',
                'city' => 'Bekasi',
                'state' => 'Jawa Barat',
                'zip_code' => '17530',
            ],
            [
                'vendor_code' => 'VND-004',
                'city' => 'Banyuwangi',
                'state' => 'Jawa Timur',
                'zip_code' => '68418',
            ],
            [
                'vendor_code' => 'VND-005',
                'city' => 'Surabaya',
                'state' => 'Jawa Timur',
                'zip_code' => '60293',
            ],
            [
                'vendor_code' => 'VND-006',
                'city' => 'Sidoarjo',
                'state' => 'Jawa Timur',
                'zip_code' => '61256',
            ],
            [
                'vendor_code' => 'VND-007',
                'city' => 'Pasuruan',
                'state' => 'Jawa Timur',
                'zip_code' => '67153',
            ],
            [
                'vendor_code' => 'VND-008',
                'city' => 'Gresik',
                'state' => 'Jawa Timur',
                'zip_code' => '61174',
            ],
            [
                'vendor_code' => 'VND-009',
                'city' => 'Malang',
                'state' => 'Jawa Timur',
                'zip_code' => '65126',
            ],
            [
                'vendor_code' => 'VND-010',
                'city' => 'Mojokerto',
                'state' => 'Jawa Timur',
                'zip_code' => '61384',
            ],
        ];

        foreach ($suppliers as $supplierData) {
            DB::table('suppliers')
                ->where('vendor_code', $supplierData['vendor_code'])
                ->update([
                    'city' => $supplierData['city'],
                    'state' => $supplierData['state'],
                    'zip_code' => $supplierData['zip_code'],
                ]);
        }

        $this->command->info('✓ Updated ' . count($suppliers) . ' suppliers with city, state, and zip code!');
    }
}
