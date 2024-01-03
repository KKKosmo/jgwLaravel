<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MainCreated implements ShouldBroadcast
{
    use SerializesModels;

    public $main;

    public function __construct($main)
    {
        $this->main = $main;
    }

    public function broadcastOn()
    {
        return ['main'];
    }
}
