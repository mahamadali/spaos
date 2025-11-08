<?php

use App\Http\Controllers\Auth\PasswordResetLinkController;
use Illuminate\Support\Facades\Route;
use Modules\Frontend\Http\Controllers\Auth\UserController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/login-page', [UserController::class, 'login'])->name('user.login');
Route::get('/forgotpassword-page', [UserController::class, 'forgetpassword'])->name('user.forgetpassword');
Route::get('/register', [UserController::class, 'registration'])->name('user.register');
Route::get('/otp', [UserController::class, 'otp_verify'])->name('otp-verify');
Route::post('/verify-otp', [UserController::class, 'verifyOtp'])->name('verify-otp');
Route::post('/resend-otp', [UserController::class, 'resendOtp'])->name('resend-otp');
Route::post('/store', [UserController::class, 'store'])->name('store-data');

Route::match(['get', 'post'], '/admin-login', [UserController::class, 'adminLogin'])->name('admin-login');


