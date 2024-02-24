<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EditController extends Controller
{
    
public function someMethod(Request $request)
{
    // ...

    $edit = Edit::create([
        'record_id' => $main->id,
        'edit_timestamp' => now(),
        'type' => 'Create',
        'summary' => $main,
        'user' => $request->input('user'),
    ]);

    // ...
}
}
