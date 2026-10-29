<?php

namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetupEmployeeRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:setup-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup employee roles for approval workflow (Purchasing, PPC, QA with Checker/Approver/Receiver)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Setting up Employee Roles for Approval Workflow...');
        $this->newLine();

        // Default password
        $defaultPassword = Hash::make('password123');

        $employees = [
            // PURCHASING DIVISION
            [
                'name' => 'Purchasing Approver',
                'npk' => 'PUR001',
                'division' => 'Purchasing',
                'role' => 'Approver',
                'email' => 'purchasing.approver@akebono.co.id',
                'password' => $defaultPassword,
                'is_active' => true,
            ],

            // PPC DIVISION
            [
                'name' => 'PPC Approver',
                'npk' => 'PPC001',
                'division' => 'PCC',
                'role' => 'Approver',
                'email' => 'ppc.approver@akebono.co.id',
                'password' => $defaultPassword,
                'is_active' => true,
            ],

            // QA DIVISION (3 ROLES)
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

        $this->withProgressBar($employees, function ($employeeData) {
            Employee::updateOrCreate(
                ['npk' => $employeeData['npk']],
                $employeeData
            );
        });

        $this->newLine(2);
        $this->info('✅ Employee roles setup completed!');
        $this->newLine();

        // Display summary table
        $this->table(
            ['Division', 'Role', 'NPK', 'Name', 'Email'],
            [
                ['Purchasing', 'Approver', 'PUR001', 'Purchasing Approver', 'purchasing.approver@akebono.co.id'],
                ['PPC', 'Approver', 'PPC001', 'PPC Approver', 'ppc.approver@akebono.co.id'],
                ['QA', 'Checker', 'QA001', 'QA Checker', 'qa.checker@akebono.co.id'],
                ['QA', 'Approver', 'QA002', 'QA Approver', 'qa.approver@akebono.co.id'],
                ['QA', 'Receiver', 'QA003', 'QA Receiver', 'qa.receiver@akebono.co.id'],
            ]
        );

        $this->newLine();
        $this->warn('📝 Default Password: password123');
        $this->warn('⚠️  Please change passwords after first login!');

        return Command::SUCCESS;
    }
}
