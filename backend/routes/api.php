<?php

use App\Http\Controllers\CrmController;
use Illuminate\Support\Facades\Route;

Route::post('/crm/submit', [CrmController::class, 'submit']);
Route::get('/crm/status', function () {
    $token = \App\Models\ZohoToken::first();
    return response()->json(['authorized' => (bool) $token]);
});