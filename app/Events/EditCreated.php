<?php

// EditCreated.php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class EditCreated implements ShouldBroadcast
{
    use SerializesModels;

    public $edit;

    public function __construct($edit)
    {
        $this->edit = $edit;
    }

    public function broadcastOn()
    {
        // Specify the channel to broadcast on
        return ['edits'];
    }
}
