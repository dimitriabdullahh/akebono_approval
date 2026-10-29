<?php

namespace App\Events;

use App\Models\RequestDocument;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentRevisionRequested
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public RequestDocument $requestDocument,
        public User $requestedBy,
        public string $division,
        public string $notes
    ) {
    }
}
