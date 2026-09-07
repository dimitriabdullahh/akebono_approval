<?php

namespace App\Console\Commands;

use App\Models\Supplier;
use Illuminate\Console\Command;

class UpdateSupplierEscalationEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supplier:update-escalation-emails 
                            {--supplier-id= : Update specific supplier by ID}
                            {--all : Update all suppliers with template emails}
                            {--list : List all suppliers and their escalation emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update supplier escalation emails (supervisor, head dept, GM, director)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('list')) {
            return $this->listSuppliers();
        }

        if ($this->option('all')) {
            return $this->updateAllSuppliers();
        }

        if ($this->option('supplier-id')) {
            return $this->updateSingleSupplier($this->option('supplier-id'));
        }

        $this->info('Please specify an option:');
        $this->line('  --list                  : List all suppliers');
        $this->line('  --all                   : Update all suppliers with template emails');
        $this->line('  --supplier-id=<id>      : Update specific supplier');
        
        return 0;
    }

    /**
     * List all suppliers and their escalation emails
     */
    private function listSuppliers()
    {
        $suppliers = Supplier::all();

        $this->info("📋 Total Suppliers: {$suppliers->count()}");
        $this->newLine();

        foreach ($suppliers as $supplier) {
            $this->line("ID: {$supplier->id} | {$supplier->name} ({$supplier->vendor_code})");
            $this->line("  PIC:        {$supplier->pic_email}");
            $this->line("  Supervisor: " . ($supplier->supervisor_email ?: '❌ NOT SET'));
            $this->line("  Head Dept:  " . ($supplier->head_dept_email ?: '❌ NOT SET'));
            $this->line("  GM:         " . ($supplier->gm_email ?: '❌ NOT SET'));
            $this->line("  Director:   " . ($supplier->director_email ?: '❌ NOT SET'));
            $this->newLine();
        }

        return 0;
    }

    /**
     * Update all suppliers with template emails
     */
    private function updateAllSuppliers()
    {
        $suppliers = Supplier::all();

        if (!$this->confirm("Update escalation emails for {$suppliers->count()} suppliers?", true)) {
            $this->info('Cancelled.');
            return 0;
        }

        $updated = 0;

        foreach ($suppliers as $supplier) {
            $picEmail = $supplier->pic_email;
            
            if (!$picEmail) {
                $this->warn("Skipping {$supplier->name} - No PIC email");
                continue;
            }

            // Generate email based on PIC email domain
            $domain = substr(strrchr($picEmail, "@"), 1);
            $picName = $supplier->pic_name ?: 'PIC';
            
            $supplier->update([
                'supervisor_email' => $supplier->supervisor_email ?: "supervisor@{$domain}",
                'supervisor_name' => $supplier->supervisor_name ?: "Supervisor - {$supplier->name}",
                'head_dept_email' => $supplier->head_dept_email ?: "head.dept@{$domain}",
                'head_dept_name' => $supplier->head_dept_name ?: "Head of Department - {$supplier->name}",
                'gm_email' => $supplier->gm_email ?: "gm@{$domain}",
                'gm_name' => $supplier->gm_name ?: "General Manager - {$supplier->name}",
                'director_email' => $supplier->director_email ?: "director@{$domain}",
                'director_name' => $supplier->director_name ?: "Director - {$supplier->name}",
            ]);

            $updated++;
            $this->info("✅ Updated: {$supplier->name}");
        }

        $this->newLine();
        $this->info("✅ Successfully updated {$updated} suppliers");

        return 0;
    }

    /**
     * Update single supplier interactively
     */
    private function updateSingleSupplier($supplierId)
    {
        $supplier = Supplier::find($supplierId);

        if (!$supplier) {
            $this->error("Supplier with ID {$supplierId} not found!");
            return 1;
        }

        $this->info("Updating: {$supplier->name} ({$supplier->vendor_code})");
        $this->newLine();

        $this->line("Current values:");
        $this->line("  PIC:        {$supplier->pic_email}");
        $this->line("  Supervisor: " . ($supplier->supervisor_email ?: '❌ NOT SET'));
        $this->line("  Head Dept:  " . ($supplier->head_dept_email ?: '❌ NOT SET'));
        $this->line("  GM:         " . ($supplier->gm_email ?: '❌ NOT SET'));
        $this->line("  Director:   " . ($supplier->director_email ?: '❌ NOT SET'));
        $this->newLine();

        // Ask for each email
        $supervisorEmail = $this->ask('Supervisor Email', $supplier->supervisor_email);
        $supervisorName = $this->ask('Supervisor Name', $supplier->supervisor_name ?: "Supervisor - {$supplier->name}");
        
        $headDeptEmail = $this->ask('Head Department Email', $supplier->head_dept_email);
        $headDeptName = $this->ask('Head Department Name', $supplier->head_dept_name ?: "Head of Department - {$supplier->name}");
        
        $gmEmail = $this->ask('GM Email', $supplier->gm_email);
        $gmName = $this->ask('GM Name', $supplier->gm_name ?: "General Manager - {$supplier->name}");
        
        $directorEmail = $this->ask('Director Email', $supplier->director_email);
        $directorName = $this->ask('Director Name', $supplier->director_name ?: "Director - {$supplier->name}");

        // Confirm
        if (!$this->confirm('Save these changes?', true)) {
            $this->info('Cancelled.');
            return 0;
        }

        // Update
        $supplier->update([
            'supervisor_email' => $supervisorEmail,
            'supervisor_name' => $supervisorName,
            'head_dept_email' => $headDeptEmail,
            'head_dept_name' => $headDeptName,
            'gm_email' => $gmEmail,
            'gm_name' => $gmName,
            'director_email' => $directorEmail,
            'director_name' => $directorName,
        ]);

        $this->info('✅ Supplier updated successfully!');

        return 0;
    }
}
