<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seed employees dengan role structure:
     * - Purchasing: 1 Approver
     * - PPC: 1 Approver
     * - QA: 3 roles (Checker, Approver, Receiver)
     *
     * @return void
     */
    public function run()
    {
        // Default password untuk semua employee
        $defaultPassword = Hash::make('password123');

        $employees = [
            // ========== PURCHASING DIVISION ==========
            [
                'name' => 'Purchasing Approver',
                'npk' => 'PUR001',
                'division' => 'Purchasing',
                'role' => 'Approver',
                'email' => 'purchasing.approver@akebono.co.id',
                'password' => $defaultPassword,
                'is_active' => true,
            ],

            // ========== PPC DIVISION ==========
            [
                'name' => 'PPC Approver',
                'npk' => 'PPC001',
                'division' => 'PCC',
                'role' => 'Approver',
                'email' => 'ppc.approver@akebono.co.id',
                'password' => $defaultPassword,
                'is_active' => true,
            ],

            // ========== QA DIVISION (3 ROLES) ==========
            [
                'name' => 'QA Checker',
                'npk' => 'QA001',
                'division' => 'QA',
                'role' => 'Checker',
                'email' => 'qa.checker@akebono.co.id',
                'password' => $defaultPassword,
                'is_active' => true,
            ],
            [
                'name' => 'QA Approver',
                'npk' => 'QA002',
                'division' => 'QA',
                'role' => 'Approver',
                'email' => 'qa.approver@akebono.co.id',
                'password' => $defaultPassword,
                'is_active' => true,
            ],
            [
                'name' => 'QA Receiver',
                'npk' => 'QA003',
                'division' => 'QA',
                'role' => 'Receiver',
                'email' => 'qa.receiver@akebono.co.id',
                'password' => $defaultPassword,
                'is_active' => true,
            ],
        ];

        foreach ($employees as $employeeData) {
            Employee::updateOrCreate(
                ['npk' => $employeeData['npk']],
                $employeeData
            );
        }

        $this->command->info('✓ Employee roles seeded successfully!');
        $this->command->info('  - Purchasing: 1 Approver');
        $this->command->info('  - PPC: 1 Approver');
        $this->command->info('  - QA: 3 roles (Checker, Approver, Receiver)');
        $this->command->info('');
        $this->command->info('Default credentials:');
        $this->command->info('  NPK: [PUR001|PPC001|QA001|QA002|QA003]');
        $this->command->info('  Password: password123');
    }
}
