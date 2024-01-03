<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Edit extends Model
{
    use HasFactory;
    protected $fillable = [
        'record_id',
        'edit_timestamp',
        'summary',
        'user',
    ];
    protected $table = 'edits'; // Set the correct table name
}
