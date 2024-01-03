<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class MainUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $main;

    public function __construct($main)
    {
        $this->main = $main;
    }
}
