<?php

namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;

class ShowEmployees extends Command
{
    protected $signature = 'employees:show';
    protected $description = 'Show all employees';

    public function handle()
    {
        $this->info('👥 Employees List:');
        $this->newLine();

        $employees = Employee::orderBy('division')->orderBy('role')->get();

        if ($employees->isEmpty()) {
            $this->warn('No employees found!');
            return 1;
        }

        $tableData = [];
        foreach ($employees as $emp) {
            $tableData[] = [
                $emp->npk,
                $emp->name,
                $emp->email,
                $emp->division,
                $emp->role,
                $emp->is_active ? '✓ Active' : '○ Inactive',
            ];
        }

        $this->table(
            ['NPK', 'Name', 'Email', 'Division', 'Role', 'Status'],
            $tableData
        );

        $this->newLine();
        $this->info('✅ Total: ' . $employees->count() . ' employees');
        $this->info('💡 Default password: password');

        return 0;
    }
}
