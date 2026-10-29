<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeds 6 employees for PPAP multi-division approval system:
     * - QA Division: 1 Checker + 1 Approver
     * - PCC Division: 1 Checker + 1 Approver
     * - Purchasing Division: 1 Checker + 1 Approver
     * 
     * This seeder is IDEMPOTENT - can be run multiple times without creating duplicates
     *
     * @return void
     */
    public function run()
    {
        $employees = [
            // QA Division
            [
                'name' => 'Ardi Pratama',
                'npk' => 'QA001',
                'division' => Employee::DIVISION_QA,
                'role' => Employee::ROLE_CHECKER,
                'email' => 'ardi.pratama@akebono.co.id',
                'password' => Hash::make('password'), // Change in production
                'is_active' => true,
            ],
            [
                'name' => 'Rizky Saputra',
                'npk' => 'QA002',
                'division' => Employee::DIVISION_QA,
                'role' => Employee::ROLE_APPROVER,
                'email' => 'rizky.saputra@akebono.co.id',
                'password' => Hash::make('password'), // Change in production
                'is_active' => true,
            ],
            
            // PCC Division
            [
                'name' => 'Dimas Setiawan',
                'npk' => 'PCC001',
                'division' => Employee::DIVISION_PCC,
                'role' => Employee::ROLE_CHECKER,
                'email' => 'dimas.setiawan@akebono.co.id',
                'password' => Hash::make('password'), // Change in production
                'is_active' => true,
            ],
            [
                'name' => 'Fajar Nugroho',
                'npk' => 'PCC002',
                'division' => Employee::DIVISION_PCC,
                'role' => Employee::ROLE_APPROVER,
                'email' => 'fajar.nugroho@akebono.co.id',
                'password' => Hash::make('password'), // Change in production
                'is_active' => true,
            ],
            
            // Purchasing Division
            [
                'name' => 'Budi Kurniawan',
                'npk' => 'PUR001',
                'division' => Employee::DIVISION_PURCHASING,
                'role' => Employee::ROLE_CHECKER,
                'email' => 'budi.kurniawan@akebono.co.id',
                'password' => Hash::make('password'), // Change in production
                'is_active' => true,
            ],
            [
                'name' => 'Arif Hidayat',
                'npk' => 'PUR002',
                'division' => Employee::DIVISION_PURCHASING,
                'role' => Employee::ROLE_APPROVER,
                'email' => 'arif.hidayat@akebono.co.id',
                'password' => Hash::make('password'), // Change in production
                'is_active' => true,
            ],
        ];

        foreach ($employees as $employeeData) {
            // Use updateOrCreate to make this seeder idempotent
            // If employee with NPK exists, update; otherwise create
            Employee::updateOrCreate(
                ['npk' => $employeeData['npk']], // Search by NPK
                $employeeData // Data to create or update
            );
        }

        $this->command->info('✓ Successfully seeded 6 employees:');
        $this->command->table(
            ['NPK', 'Name', 'Division', 'Role'],
            [
                ['QA001', 'Ardi Pratama', 'QA', 'Checker'],
                ['QA002', 'Rizky Saputra', 'QA', 'Approver'],
                ['PCC001', 'Dimas Setiawan', 'PCC', 'Checker'],
                ['PCC002', 'Fajar Nugroho', 'PCC', 'Approver'],
                ['PUR001', 'Budi Kurniawan', 'Purchasing', 'Checker'],
                ['PUR002', 'Arif Hidayat', 'Purchasing', 'Approver'],
            ]
        );
        $this->command->info('Default password for all employees: password');
        $this->command->warn('⚠ Remember to change passwords in production!');
    }
}
