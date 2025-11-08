<?php

use Illuminate\Support\Facades\Route;
use Modules\Frontend\Http\Controllers\FrontendController;
use Modules\Frontend\Http\Controllers\PricingController;
use Modules\Frontend\Http\Controllers\Auth\UserController;
use Modules\Frontend\Http\Controllers\PaymentController;

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

// Routes requiring installation check
Route::middleware(['checkInstallation'])->group(function () {

    // Public routes
    Route::get('/', [FrontendController::class, 'index'])->name('index');
    Route::get('/feature', [FrontendController::class, 'feature'])->name('feature');
    Route::get('/all-feature', [FrontendController::class, 'allfeature'])->name('allfeature');
    Route::get('/resource', [FrontendController::class, 'resource'])->name('resource');
    Route::get('/about-us', [FrontendController::class, 'aboutus'])->name('about_us');
    Route::get('/page/{slug}', [FrontendController::class, 'pageSlugs'])->name('page_slugs');
    Route::get('/contact-us', [FrontendController::class, 'contactus'])->name('contact_us');
    Route::get('/faqs', [FrontendController::class, 'faqs'])->name('faqs');
    Route::get('/blogs', [FrontendController::class, 'blogs'])->name('blogs');
    Route::get('/blogs/{author_id}', [FrontendController::class, 'author_blogs'])->name('author_blogs');
    Route::get('/blog-detail/{id}', [FrontendController::class, 'blogDetail'])->name('blog_detail');
    Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');

    // Authentication-required routes
    Route::middleware('auth')->group(function () {
        Route::get('/edit-profile', [UserController::class, 'editProfile'])->name('edit-profile');
        Route::get('/payment-history', [FrontendController::class, 'PaymentHistory'])->name('payment_history');
        Route::post('/calculate-discount', [PricingController::class, 'calculate_discount'])->name('calculate_discount');
        Route::get('/payment-details', [PricingController::class, 'PaymentDetails'])->name('payment-details');
        Route::post('/cancel-subscription', [FrontendController::class, 'cancelSubscription'])->name('cancelSubscription');
    });
    Route::get('/pricing-plan', [PricingController::class, 'pricing_plan'])->name('pricing_plan');

    // Payment routes
    Route::get('invoice-download', [PaymentController::class, 'downloadInvoice'])->name('downloadinvoice');
    Route::post('/process-payment', [PaymentController::class, 'processPayment'])->name('process-payment');
    Route::get('/payment/success', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
});

// Frontend resource routes
Route::resource('frontend', FrontendController::class)->names('frontend');



