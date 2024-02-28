<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $fillable = [
        'record_id',
        'type',
        'summary',
        'user',
    ];
    protected $table = 'events'; // Set the correct table name
}
