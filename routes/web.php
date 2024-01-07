<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EditsController;

Route::get('/', function () {
    return view('welcome');
});