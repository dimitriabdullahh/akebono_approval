<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PartSubmissionWarrant;
use App\Models\Supplier;

class TestPswSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a test supplier
        $supplier = Supplier::firstOrCreate(
            ['supplier_code' => 'TEST-SUPPLIER-001'],
            [
                'name' => 'PT Supplier Test',
                'contact_person' => 'John Doe',
                'email' => 'test@supplier.local',
                'phone' => '+62-21-1234567',
            ]
        );

        // Test PSW 1: Basic submission
        PartSubmissionWarrant::create([
            'supplier_id' => $supplier->id,
            'part_name' => 'TEST BRAKE PAD',
            'part_number' => 'BP-2025-001',
            'safety_government_regulation' => true,
            'engineering_drawing_change_level' => 'REV A',
            'engineering_drawing_change_date' => '2025-02-15',
            'additional_engineering_changes' => 'Material spec updated',
            'additional_engineering_changes_date' => '2025-02-10',
            'shown_on_drawing_no' => 'DRG-001',
            'purchase_order_no' => 'PO-88765',
            'weight_kg' => '2.45',
            'checking_aid_no' => 'CHK-001',
            'process_engineering_change_level' => 'PROC-02',
            'process_engineering_change_date' => '2025-01-20',
            'supplier_name' => 'PT SUPPLIER TEST',
            'supplier_code' => 'SUPP-001',
            'street_address' => '123 Industrial St, Jakarta',
            'city' => 'Jakarta',
            'state' => 'DKI',
            'zip' => '13100',
            'customer_name_division' => 'AKEBONO QA',
            'buyer_buyer_code' => 'BUYER-001',
            'application' => 'Brake Assembly',
            'submission_information' => json_encode(['Dimensional', 'Materials/Functional']),
            'contains_prohibited_substances' => false,
            'plastic_has_iso_marking' => true,
            'reason_for_submission' => json_encode(['Initial Submission']),
            'reason_other_text' => null,
            'submission_results' => json_encode(['dimensional measurements', 'material and functional tests']),
            'meets_all_requirements' => true,
            'explanation_if_not_meet' => null,
            'explanation_comments' => 'All samples tested and verified.',
            'print_name' => 'John Doe',
            'title' => 'QA Manager',
            'phone_no' => '+62-21-1234567',
            'fax_no' => '+62-21-1234568',
            'supplier_authorized_signature' => 'signature_image_1',
            'signature_date' => '2025-02-20',
            'status' => 'SUBMITTED',
            'submitted_at' => now(),
        ]);

        // Test PSW 2: Long text with changes
        PartSubmissionWarrant::create([
            'supplier_id' => $supplier->id,
            'part_name' => 'BRAKE ASSEMBLY WITH EXTENDED NAME TO TEST TEXT WRAPPING',
            'part_number' => 'BA-2025-002-LONG-NUMBER',
            'safety_government_regulation' => false,
            'engineering_drawing_change_level' => 'REV B',
            'engineering_drawing_change_date' => '2025-02-10',
            'additional_engineering_changes' => 'Multiple changes including material specification, dimensional tolerance updates, and surface finish requirements',
            'additional_engineering_changes_date' => '2025-02-05',
            'shown_on_drawing_no' => 'DRG-002-A-B-C',
            'purchase_order_no' => 'PO-88766',
            'weight_kg' => '5.75',
            'checking_aid_no' => 'CHK-002',
            'process_engineering_change_level' => 'PROC-05',
            'process_engineering_change_date' => '2025-01-15',
            'supplier_name' => 'PT MANUFACTURING ADVANCED',
            'supplier_code' => 'SUPP-002',
            'street_address' => 'Jl. Industri Raya No. 456, Building B, Floor 3, Jakarta Selatan',
            'city' => 'Jakarta',
            'state' => 'DKI Jakarta',
            'zip' => '12540',
            'customer_name_division' => 'AKEBONO ENGINEERING DIVISION',
            'buyer_buyer_code' => 'BUYER-002',
            'application' => 'Advanced Brake System with ABS Integration',
            'submission_information' => json_encode(['Dimensional', 'Materials/Functional', 'Appearance']),
            'contains_prohibited_substances' => true,
            'plastic_has_iso_marking' => false,
            'reason_for_submission' => json_encode(['Engineering Change', 'Supplier Change']),
            'reason_other_text' => 'Supplier requested change due to material availability',
            'submission_results' => json_encode(['dimensional measurements', 'material and functional tests', 'appearance inspection']),
            'meets_all_requirements' => true,
            'explanation_if_not_meet' => null,
            'explanation_comments' => 'All testing completed with detailed analysis. Some results pending final statistical analysis.',
            'print_name' => 'Jane Smith',
            'title' => 'Engineering Manager',
            'phone_no' => '+62-21-7654321',
            'fax_no' => '+62-21-7654322',
            'supplier_authorized_signature' => 'signature_image_2',
            'signature_date' => '2025-02-18',
            'status' => 'PENDING_REVIEW',
            'submitted_at' => now()->subDays(3),
        ]);

        // Test PSW 3: Minimal data (edge case)
        PartSubmissionWarrant::create([
            'supplier_id' => $supplier->id,
            'part_name' => 'SIMPLE PART',
            'part_number' => 'SP-001',
            'safety_government_regulation' => false,
            'engineering_drawing_change_level' => null,
            'engineering_drawing_change_date' => null,
            'additional_engineering_changes' => null,
            'additional_engineering_changes_date' => null,
            'shown_on_drawing_no' => null,
            'purchase_order_no' => 'PO-88767',
            'weight_kg' => '0.50',
            'checking_aid_no' => null,
            'process_engineering_change_level' => null,
            'process_engineering_change_date' => null,
            'supplier_name' => 'PT SIMPLE SUPPLIER',
            'supplier_code' => 'SUPP-003',
            'street_address' => 'Jalan A No. 1',
            'city' => 'Bandung',
            'state' => 'West Java',
            'zip' => '40001',
            'customer_name_division' => 'QA',
            'buyer_buyer_code' => 'BUYER-003',
            'application' => 'Simple Component',
            'submission_information' => json_encode(['Dimensional']),
            'contains_prohibited_substances' => false,
            'plastic_has_iso_marking' => false,
            'reason_for_submission' => json_encode(['Initial Submission']),
            'reason_other_text' => null,
            'submission_results' => json_encode(['dimensional measurements']),
            'meets_all_requirements' => true,
            'explanation_if_not_meet' => null,
            'explanation_comments' => 'Basic testing completed.',
            'print_name' => 'Bob Johnson',
            'title' => 'QA',
            'phone_no' => '+62-274-1234567',
            'fax_no' => null,
            'supplier_authorized_signature' => null,
            'signature_date' => '2025-02-21',
            'status' => 'DRAFT',
        ]);

        $this->command->info('✓ Test PSW data created successfully');
        $this->command->info('  - Test PSW 1: BP-2025-001 (Basic)');
        $this->command->info('  - Test PSW 2: BA-2025-002-LONG-NUMBER (Long text)');
        $this->command->info('  - Test PSW 3: SP-001 (Minimal data)');
    }
}
