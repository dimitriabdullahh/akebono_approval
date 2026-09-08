<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('part_submission_warrants', function (Blueprint $table) {
            // Step 1: Engineering Information
            if (!Schema::hasColumn('part_submission_warrants', 'safety_government_regulation')) {
                $table->boolean('safety_government_regulation')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'engineering_drawing_change_level')) {
                $table->string('engineering_drawing_change_level')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'engineering_drawing_change_date')) {
                $table->date('engineering_drawing_change_date')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'additional_engineering_changes')) {
                $table->text('additional_engineering_changes')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'additional_engineering_changes_date')) {
                $table->date('additional_engineering_changes_date')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'shown_on_drawing_no')) {
                $table->string('shown_on_drawing_no')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'purchase_order_no')) {
                $table->string('purchase_order_no')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'weight_kg')) {
                $table->decimal('weight_kg', 10, 3)->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'checking_aid_no')) {
                $table->string('checking_aid_no')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'process_engineering_change_level')) {
                $table->string('process_engineering_change_level')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'process_engineering_change_date')) {
                $table->date('process_engineering_change_date')->nullable();
            }
            
            // Step 2: Supplier & Submission Information
            if (!Schema::hasColumn('part_submission_warrants', 'supplier_code')) {
                $table->string('supplier_code')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'street_address')) {
                $table->string('street_address')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'city')) {
                $table->string('city')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'state')) {
                $table->string('state')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'zip')) {
                $table->string('zip')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'customer_name_division')) {
                $table->string('customer_name_division')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'buyer_buyer_code')) {
                $table->string('buyer_buyer_code')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'application')) {
                $table->text('application')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'submission_information')) {
                $table->json('submission_information')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'contains_prohibited_substances')) {
                $table->boolean('contains_prohibited_substances')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'plastic_has_iso_marking')) {
                $table->boolean('plastic_has_iso_marking')->nullable();
            }
            
            // Step 3: Reason & Results
            if (!Schema::hasColumn('part_submission_warrants', 'reason_for_submission')) {
                $table->json('reason_for_submission')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'reason_other_text')) {
                $table->string('reason_other_text')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'submission_results')) {
                $table->json('submission_results')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'meets_all_requirements')) {
                $table->boolean('meets_all_requirements')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'explanation_if_not_meet')) {
                $table->text('explanation_if_not_meet')->nullable();
            }
            
            // Step 4: Declaration & Sign
            if (!Schema::hasColumn('part_submission_warrants', 'explanation_comments')) {
                $table->text('explanation_comments')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'print_name')) {
                $table->string('print_name')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'title')) {
                $table->string('title')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'phone_no')) {
                $table->string('phone_no')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'fax_no')) {
                $table->string('fax_no')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'supplier_authorized_signature')) {
                $table->string('supplier_authorized_signature')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'signature_date')) {
                $table->date('signature_date')->nullable();
            }
            
            // Internal Akebono Section
            if (!Schema::hasColumn('part_submission_warrants', 'internal_approved_by')) {
                $table->string('internal_approved_by')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'internal_approved_date')) {
                $table->date('internal_approved_date')->nullable();
            }
            if (!Schema::hasColumn('part_submission_warrants', 'internal_notes')) {
                $table->text('internal_notes')->nullable();
            }
            
            // Update submitted_at if not exists
            if (!Schema::hasColumn('part_submission_warrants', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('part_submission_warrants', function (Blueprint $table) {
            // Drop the columns we added
            $columnsToCheck = [
                'safety_government_regulation',
                'engineering_drawing_change_level',
                'engineering_drawing_change_date',
                'additional_engineering_changes',
                'additional_engineering_changes_date',
                'shown_on_drawing_no',
                'purchase_order_no',
                'weight_kg',
                'checking_aid_no',
                'process_engineering_change_level',
                'process_engineering_change_date',
                'supplier_code',
                'street_address',
                'city',
                'state',
                'zip',
                'customer_name_division',
                'buyer_buyer_code',
                'application',
                'submission_information',
                'contains_prohibited_substances',
                'plastic_has_iso_marking',
                'reason_for_submission',
                'reason_other_text',
                'submission_results',
                'meets_all_requirements',
                'explanation_if_not_meet',
                'explanation_comments',
                'print_name',
                'title',
                'phone_no',
                'fax_no',
                'supplier_authorized_signature',
                'signature_date',
                'internal_approved_by',
                'internal_approved_date',
                'internal_notes',
            ];
            
            $existingColumns = [];
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('part_submission_warrants', $column)) {
                    $existingColumns[] = $column;
                }
            }
            
            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
