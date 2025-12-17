<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Partners\FieldPartnerController;
use Laravel\Passport\Http\Middleware\EnsureClientIsResourceOwner;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\TokenController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::prefix('partners')->group(function () {
    Route::middleware([EnsureClientIsResourceOwner::class])
        ->get('/fields', [FieldPartnerController::class, 'index']);
    Route::middleware([EnsureClientIsResourceOwner::class])
        ->post('/fields', [FieldPartnerController::class, 'store']);
    Route::post('/fields/book', [FieldPartnerController::class, 'book']);
});

Route::middleware('auth:api')->post('/oauth/logout', [TokenController::class, 'revoke']);
Route::middleware('auth:api')->get('/bookings', [
    BookingController::class, 'index'
]);
Route::middleware(['auth:api'])->get('/bookings/{id}',
    [BookingController::class, 'show']);

Route::middleware(['auth:api'])->post('/bookings/{id}',
    [BookingController::class, 'use']);




















// Route::middleware([EnsureClientIsResourceOwner::class])
    //     ->post('/book', [FieldPartnerController::class, 'book']);
    // Route::post('/book', [FieldPartnerController::class, 'book'])
    // ->middleware('auth:api');

