<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Main extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'dateInserted',
        'pax',
        'vehicle',
        'pets',
        'videoke',
        'partial_payment',
        'full_payment',
        'paid',
        'checkIn',
        'checkOut',
        'room',
        'user',
        'created_at',
        'updated_at',
    ];
    protected $table = 'main'; // Set the correct table name
}
