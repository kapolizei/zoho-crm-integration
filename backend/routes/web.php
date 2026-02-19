<?php

use App\Http\Controllers\ZohoAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/zoho/auth', [ZohoAuthController::class, 'redirect']);
Route::get('/zoho/callback', [ZohoAuthController::class, 'callback']);

