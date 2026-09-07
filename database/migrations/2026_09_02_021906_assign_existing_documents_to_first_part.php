<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Assign existing documents to first part of each request
     *
     * @return void
     */
    public function up()
    {
        // Get all document requests
        $documentRequests = \App\Models\DocumentRequest::with('requestParts')->get();
        
        foreach ($documentRequests as $request) {
            // Get first part of this request
            $firstPart = $request->requestParts()->first();
            
            if ($firstPart) {
                // Update all documents without request_part_id to use first part
                \App\Models\RequestDocument::where('document_request_id', $request->id)
                    ->whereNull('request_part_id')
                    ->update(['request_part_id' => $firstPart->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Optionally revert: set request_part_id back to null for historical data
        // \App\Models\RequestDocument::whereNotNull('request_part_id')->update(['request_part_id' => null]);
    }
};
