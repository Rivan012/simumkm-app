<?php

use App\Http\Controllers\CallBackController;
use Illuminate\Support\Facades\Route;
Route::post('/midtrans/callback', [CallBackController::class, 'callback']);