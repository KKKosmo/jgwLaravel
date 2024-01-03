<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class ReportCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $report;

    public function __construct($report)
    {
        $this->report = $report;
    }
}
