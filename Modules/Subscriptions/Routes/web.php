<?php

use Illuminate\Support\Facades\Route;
use Modules\Subscriptions\Http\Controllers\Backend\PlanController;
use Modules\Subscriptions\Http\Controllers\Backend\PlanLimitationController;
use Modules\Subscriptions\Http\Controllers\Backend\SubscriptionController;

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
/*
*
* Backend Routes
*
* --------------------------------------------------------------------
*/
Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth']], function () {
    /*
    * These routes need view-backend permission
    * (good if you want to allow more than one group in the backend,
    * then limit the backend features by different roles or permissions)
    *
    * Note: Administrator has all permissions so you do not have to specify the administrator role everywhere.
    */

    /*
     *
     *  Backend  plan  Routes
     *
     * ---------------------------------------------------------------------
     */
    // Planlimitation Routes

    Route::group(['prefix' => 'subscriptions', 'as' => 'subscriptions.'], function () {
        Route::get('/index_data', [SubscriptionController::class, 'index_data'])->name('index_data');
        Route::get('/pending', [SubscriptionController::class, 'pending'])->name('pending')->middleware(['auth', 'check.menu.permission']);
        Route::get('/pending_subscription', [SubscriptionController::class, 'pending_subscription'])->name('pending_subscription');
        Route::get('/expired', [SubscriptionController::class, 'expired'])->name('expired')->middleware(['auth', 'check.menu.permission']);
        Route::get('/all_subscription', [SubscriptionController::class, 'allSubscription'])->name('all_subscription');
        Route::get('all_subscription_data', [SubscriptionController::class, 'allSubscriptionData'])->name('all_subscription_data');
    });

    Route::resource('subscriptions', SubscriptionController::class)->middleware(['auth', 'check.menu.permission']);

    Route::get('check/plan/date', [PlanController::class, 'checkPlanStartDate'])->name('check.plan.start_date');

    // subscription Plan Routes

    Route::group(['prefix' => 'subscription', 'as' => 'subscription.'], function () {
        Route::group(['prefix' => 'plans', 'as' => 'plans.'], function () {
            Route::get('/index_list', [PlanController::class, 'index_list'])->name('index_list');
            Route::get('/plan_list', [PlanController::class, 'plan_list'])->name('plan_list');

            
            Route::get('/index_data', [PlanController::class, 'index_data'])->name('index_data');
            Route::get('/trashed', [PlanController::class, 'trashed'])->name('trashed');
            Route::get('/trashed/{id}', [PlanController::class, 'restore'])->name('restore');
            Route::post('bulk-action', [PlanController::class, 'bulk_action'])->name('bulk_action');
            Route::post('update-status/{id}', [PlanController::class, 'update_status'])->name('update_status');
        });

        Route::resource('plans', PlanController::class)->middleware(['auth', 'check.menu.permission']);
        Route::delete('plans/delete/{id}',[ PlanController::class,'destroy'])->name('plans.delete');

        Route::get('subscriptions/export', [SubscriptionController::class, 'export'])
            ->name('subscriptions.export');
    });

  
    Route::get('subscriptions/export', [SubscriptionController::class, 'export'])
    ->name('backend.subscriptions.export');
    
    Route::group(['prefix' => 'subscription', 'as' => 'subscription.'], function () {
        Route::group(['prefix' => '/account', 'as' => 'account.'], function () {
        });

        Route::resource('account', AccountController::class);
    });
});
