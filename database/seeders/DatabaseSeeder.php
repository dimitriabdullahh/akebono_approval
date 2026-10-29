<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Seed document types, revision configs, suppliers, dan employees
        $this->call([
            DocumentTypeSeeder::class,
            RevisionConfigSeeder::class,
            SupplierSeeder::class,
            EmployeeSeeder::class,
        ]);
    }
}
