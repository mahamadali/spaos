<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\API\AuthController;
use App\Http\Controllers\Backend\UserController;



/*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
*/

Route::middleware(['auth:sanctum'])->prefix('v1')->name('api.')->group(function () {
    Route::get('frontend', fn (Request $request) => $request->user())->name('frontend');
    Route::post('update-profile', [AuthController::class, 'updateProfile'])->name('updateProfile');
    Route::post('my-profile/change-password', [UserController::class, 'change_password'])->name('change_password');
});
