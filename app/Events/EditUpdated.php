<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class EditUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $edit;

    public function __construct($edit)
    {
        $this->edit = $edit;
    }
}
