<?php

namespace App\Events;

use App\Models\RequestDocument;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentSubmitted
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public RequestDocument $requestDocument,
        public string $documentType = 'UNKNOWN'
    ) {
    }
}
