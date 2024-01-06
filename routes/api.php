<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EditsController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\LoginController;

// Routes for SPA
// Route::get('/{any}', function () {
//     return view('spa'); 
// })->where('any', '.*');

// Route::middleware(['auth:sanctum'])->group(function () {
// });

Route::middleware(['auth:sanctum'])->group(function () {
    Route::resource('edits', EditsController::class);
    Route::resource('main', MainController::class);
    Route::resource('reports', ReportsController::class);
    Route::resource('users', UsersController::class);
    Route::post('logout', [LoginController::class, 'logout']);
    Route::get('user', [LoginController::class, 'user']);
});


Route::post('login', [LoginController::class, 'login']);