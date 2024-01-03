<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class ReportDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }
}
