<?php

use App\Http\Controllers\Backend\BackendController;
use App\Http\Controllers\Backend\BackupController;
use App\Http\Controllers\Backend\BranchController;
use App\Http\Controllers\Backend\NotificationsController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\Backend\InquiryController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PlanTaxController;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermission;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\WebsiteSettingController;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\Frontend\Http\Controllers\FrontendController;
use App\Http\Controllers\UpgradePlanController;
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

Route::get('/', [FrontendController::class, 'index'])->name('user.login');
// Auth Routes
require __DIR__ . '/auth.php';
Route::get('storage-link', function () {
    return Artisan::call('storage:link');
});

Route::post('/branch/assign-employee', [App\Http\Controllers\Backend\BranchController::class, 'assignEmployee'])->name('branch.assign_employee');
Route::post('/employee/store', [Modules\Employee\Http\Controllers\Backend\EmployeesController::class, 'store'])->name('employee.store');
Route::post('branch', [BranchController::class, 'store'])->name('branch.store');
Route::put('branch/{branch}', [BranchController::class, 'update'])->name('branch.update');
Route::group(['middleware' => ['auth']], function () {

});


Route::group(['middleware' => ['auth', 'check.admin.plan']], function () {
    Route::get('/apps', [BackendController::class, 'index'])->name('app.home');
    Route::get('notification-list', [NotificationsController::class, 'notificationList'])->name('notification.list');
    Route::get('notification-counts', [NotificationsController::class, 'notificationCounts'])->name('notification.counts');
});

Route::group(['prefix' => 'app', 'middleware' => ['auth', 'check.admin.plan']], function () {


    Route::get('/date-time-formats', function () {
        return response()->json([
            'date_formats' => dateFormatList(),
            'time_formats' => timeFormatList(),
        ]);
    });
    // Language Switch
    Route::get('/get-currency-symbol', [BackendController::class, 'getCurrencySymbol'])->name('get.currency.symbol');
    Route::get('language/{language}', [LanguageController::class, 'switch'])->name('language.switch');
    Route::post('set-user-setting', [BackendController::class, 'setUserSetting'])->name('backend.setUserSetting');

    // Add frontendsetting route alias for menu compatibility
    Route::get('frontendsetting', [WebsiteSettingController::class, 'index'])->middleware(['auth', 'check.menu.permission'])->name('frontendsetting.index');

    // Add inquiries route alias for menu compatibility
    Route::get('inquiries', function() {
        return redirect()->route('backend.home');
    })->middleware(['auth', 'check.menu.permission'])->name('backend.inquiries.index');

    Route::group(['as' => 'backend.', 'middleware' => ['auth']], function () {
        Route::get('get_search_data', [SearchController::class, 'get_search_data'])->name('get_search_data');

        // Sync Role & Permission
        Route::get('/permission-role', [RolePermission::class, 'index'])->name('permission-role.list')->middleware('password.confirm');
        Route::post('/permission-role/store/{role_id}', [RolePermission::class, 'store'])->name('permission-role.store');
        Route::get('/permission-role/reset/{role_id}', [RolePermission::class, 'reset_permission'])->name('permission-role.reset');
        // Role & Permissions Crud
        Route::resource('permission', PermissionController::class);
        Route::resource('role', RoleController::class);

        Route::group(['prefix' => 'module', 'as' => 'module.'], function () {
            Route::get('index_data', [ModuleController::class, 'index_data'])->name('index_data');
            Route::post('update-status/{id}', [ModuleController::class, 'update_status'])->name('update_status');
        });

        Route::resource('module', ModuleController::class);

        /*
          *
          *  Settings Routes
          *
          * ---------------------------------------------------------------------
          */
        Route::group(['middleware' => []], function () {
            Route::get('settings/{vue_capture?}', [SettingController::class, 'index'])->middleware(['auth', 'check.menu.permission'])->name('settings')->where('vue_capture', '^(?!storage).*$');
            Route::get('settings-data', [SettingController::class, 'index_data']);
            Route::post('settings', [SettingController::class, 'store'])->name('settings.store');
            Route::post('setting-update', [SettingController::class, 'update'])->name('setting.update');
            Route::get('clear-cache', [SettingController::class, 'clear_cache'])->name('clear-cache');
            Route::post('verify-email', [SettingController::class, 'verify_email'])->name('verify-email');
        });

        /*
        *
        *  Notification Routes
        *
        * ---------------------------------------------------------------------
        */
        Route::group(['prefix' => 'notifications', 'as' => 'notifications.'], function () {
            Route::get('/', [NotificationsController::class, 'index'])->name('index')->middleware(['auth', 'check.menu.permission']);
            Route::get('/markAllAsRead', [NotificationsController::class, 'markAllAsRead'])->name('markAllAsRead');
            Route::delete('/deleteAll', [NotificationsController::class, 'deleteAll'])->name('deleteAll');
            Route::get('/{id}', [NotificationsController::class, 'show'])->name('show');
        });

        /*
        *
        *  Backup Routes
        *
        * ---------------------------------------------------------------------
        */

        Route::get('daily-booking-report', [ReportsController::class, 'daily_booking_report'])->middleware(['auth', 'check.menu.permission'])->name('reports.daily-booking-report');
        Route::get('daily-booking-report-index-data', [ReportsController::class, 'daily_booking_report_index_data'])->name('reports.daily-booking-report.index_data');
        Route::get('overall-booking-report', [ReportsController::class, 'overall_booking_report'])->middleware(['auth', 'check.menu.permission'])->name('reports.overall-booking-report');
        Route::get('overall-booking-report-index-data', [ReportsController::class, 'overall_booking_report_index_data'])->name('reports.overall-booking-report.index_data');
        Route::get('payout-report', [ReportsController::class, 'payout_report'])->middleware(['auth', 'check.menu.permission'])->name('reports.payout-report');
        Route::get('payout-report-index-data', [ReportsController::class, 'payout_report_index_data'])->name('reports.payout-report.index_data');
        Route::get('staff-report', [ReportsController::class, 'staff_report'])->middleware(['auth', 'check.menu.permission'])->name('reports.staff-report');
        Route::get('staff-report-index-data', [ReportsController::class, 'staff_report_index_data'])->name('reports.staff-report.index_data');

        Route::get('order-report', [ReportsController::class, 'order_report'])->middleware(['auth', 'check.menu.permission'])->name('reports.order-report');
        Route::get('order-report-index-data', [ReportsController::class, 'order_report_index_data'])->name('reports.order-report.index_data');

        // Review Routes
        Route::get('daily-booking-report-review', [ReportsController::class, 'daily_booking_report_review'])->name('reports.daily-booking-report-review');
        Route::get('overall-booking-report-review', [ReportsController::class, 'overall_booking_report_review'])->name('reports.overall-booking-report-review');
        Route::get('payout-report-review', [ReportsController::class, 'payout_report_review'])->name('reports.payout-report-review');
        Route::get('staff-report-review', [ReportsController::class, 'staff_report_review'])->name('reports.staff-report-review');
        Route::get('order_booking_report_review', [ReportsController::class, 'order_booking_report_review'])->name('reports.order_booking_report_review');
    });

    /*
    *
    * Backend Routes
    * These routes need view-backend permission
    * --------------------------------------------------------------------
    */

    Route::middleware(['checkInstallation'])->group(function () {

        Route::group(['as' => 'backend.', 'middleware' => ['auth']], function () {
            /**
             * Backend Dashboard
             * Namespaces indicate folder structure.
             */
            Route::get('/', [BackendController::class, 'index'])->name('home');

            Route::post('set-current-branch/{branch_id}', [BackendController::class, 'setCurrentBranch'])->name('set-current-branch');
            Route::post('reset-branch', [BackendController::class, 'resetBranch'])->name('reset-branch');

            Route::group(['prefix' => ''], function () {
                Route::get('dashboard', [BackendController::class, 'index'])->middleware(['auth', 'check.menu.permission'])->name('dashboard');

                /**
                 * Branch Routes
                 */
                Route::group(['prefix' => 'branch', 'as' => 'branch.'], function () {
                    Route::get('index_list', [\App\Http\Controllers\Backend\BranchController::class, 'index_list'])->name('index_list');
                    Route::get('assign/{id}', [\App\Http\Controllers\Backend\BranchController::class, 'assign_list'])->name('assign_list');
                    Route::post('assign/{id}', [\App\Http\Controllers\Backend\BranchController::class, 'assign_update'])->name('assign_update');
                    Route::get('index_data', [\App\Http\Controllers\Backend\BranchController::class, 'index_data'])->name('index_data');
                    Route::get('trashed', [\App\Http\Controllers\Backend\BranchController::class, 'trashed'])->name('trashed');
                    Route::patch('trashed/{id}', [\App\Http\Controllers\Backend\BranchController::class, 'restore'])->name('restore');
                    // Branch Gallery Images
                    Route::get('gallery-images/{id}', [\App\Http\Controllers\Backend\BranchController::class, 'getGalleryImages']);
                    Route::post('gallery-images/{id}', [\App\Http\Controllers\Backend\BranchController::class, 'uploadGalleryImages']);
                    Route::post('bulk-action', [\App\Http\Controllers\Backend\BranchController::class, 'bulk_action'])->name('bulk_action');
                    Route::post('update-status/{id}', [\App\Http\Controllers\Backend\BranchController::class, 'update_status'])->name('update_status');
                    Route::post('update-select-value/{id}/{action_type}', [\App\Http\Controllers\Backend\BranchController::class, 'update_select'])->name('update_select');
                    Route::post('branch-setting', [\App\Http\Controllers\Backend\BranchController::class, 'UpdateBranchSetting'])->name('branch_setting');
                    Route::get('export', [\App\Http\Controllers\Backend\BranchController::class, 'export'])->name('export');
                });
                Route::get('branch-info', [\App\Http\Controllers\Backend\BranchController::class, 'branchData'])->name('branchData');
                Route::resource('branch', \App\Http\Controllers\Backend\BranchController::class)->middleware(['auth', 'check.menu.permission']);

                /*
                *
                *  Users Routes
                *
                * ---------------------------------------------------------------------
                */
                Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
                    Route::get('user-list', [UserController::class, 'user_list'])->name('user_list');
                    Route::get('emailConfirmationResend/{id}', [UserController::class, 'emailConfirmationResend'])->name('emailConfirmationResend');
                    Route::post('create-customer', [UserController::class, 'create_customer'])->name('create_customer');
                    Route::post('information', [UserController::class, 'update'])->name('information');
                    Route::post('change-password', [UserController::class, 'change_password'])->name('change_password');
                    Route::post('check-unique', [UserController::class, 'checkUnique'])->name('check_unique');
                });
            });
            Route::get('my-profile/{vue_capture?}', [UserController::class, 'myProfile'])->name('my-profile')->where('vue_capture', '^(?!storage).*$');
            Route::get('my-info', [UserController::class, 'authData'])->name('authData');
            Route::post('my-profile/change-password', [UserController::class, 'change_password'])->name('change_password');
            Route::prefix('payment')->name('payment.')->group(function () {
                Route::get('/', [PaymentController::class, 'index'])->middleware(['auth', 'check.menu.permission'])->name('index');
                Route::get('index_data', [PaymentController::class, 'index_data'])->name('index_data');
                Route::get('create', [PaymentController::class, 'create'])->name('create');
                Route::get('edit/{id}', [PaymentController::class, 'edit'])->name('edit');
                Route::post('store', [PaymentController::class, 'store'])->name('store');
                Route::get('delete', [PaymentController::class, 'delete'])->name('delete');
                Route::post('approve', [PaymentController::class, 'approve'])->name('approve');
            });
            Route::get('/get_revenue_chart_data/{type}', [BackendController::class, 'getRevenuechartData']);
            Route::prefix('taxes')->name('plan.tax.')->group(function () {
                Route::get('/', [PlanTaxController::class, 'index'])->middleware(['auth', 'check.menu.permission'])->name('index');
                Route::get('index_data', [PlanTaxController::class, 'index_data'])->name('index_data');
                Route::get('create', [PlanTaxController::class, 'create'])->name('create');
                Route::get('edit/{id}', [PlanTaxController::class, 'edit'])->name('edit');
                Route::post('store', [PlanTaxController::class, 'store'])->name('store');
                Route::delete('delete/{id}', [PlanTaxController::class, 'delete'])->name('delete');
                Route::post('approve', [PlanTaxController::class, 'approve'])->name('approve');
                Route::post('update-status/{id}', [PlanTaxController::class, 'updateStatus'])->name('update_status');
            });

            Route::prefix('user')->name('users.')->group(function () {
                Route::get('/', [UserController::class, 'index'])->middleware(['auth', 'check.menu.permission'])->name('index');
                Route::get('index_data', [UserController::class, 'index_data'])->name('index_data');
                Route::get('create', [UserController::class, 'create'])->name('create');
                Route::get('edit/{id}', [UserController::class, 'edit'])->name('edit');
                Route::post('store', [UserController::class, 'store'])->name('store');
                Route::DELETE('delete/{id}', [UserController::class, 'delete'])->name('delete');
                Route::post('update-status/{id}', [UserController::class, 'updateStatus'])->name('update_status');
                Route::post('bulk-action', [UserController::class, 'bulk_action'])->name('bulk_action');
            });

            Route::prefix('faq')->name('faq.')->group(function () {
                Route::get('/', [FaqController::class, 'index'])->middleware(['auth', 'check.menu.permission'])->name('index');
                Route::get('index_data', [FaqController::class, 'index_data'])->name('index_data');
                Route::get('create', [FaqController::class, 'create'])->name('create');
                Route::get('edit/{id}', [FaqController::class, 'edit'])->name('edit');
                Route::post('store', [FaqController::class, 'store'])->name('store');
                Route::delete('delete/{id}', [FaqController::class, 'delete'])->name('delete');
                Route::post('update-status/{id}', [FaqController::class, 'updateStatus'])->name('update_status');
            });

            Route::group(['prefix' => 'inquiries', 'as' => 'inquiries.'], function () {
                Route::get('/', [InquiryController::class, 'index'])->name('index');
                Route::get('/index_list', [InquiryController::class, 'index_list'])->name('index_list');
                Route::get('/index_data', [InquiryController::class, 'index_data'])->name('index_data');
                Route::post('/bulk-action', [InquiryController::class, 'bulk_action'])->name('bulk_action');
                Route::delete('/deleteAll', [InquiryController::class, 'deleteAll'])->name('deleteAll');
                Route::get('/{id}', [InquiryController::class, 'show'])->name('show');
                Route::delete('/{id}', [InquiryController::class, 'destroy'])->name('destroy');
            });


            Route::prefix('blog')->name('blog.')->group(function () {
                Route::get('/', [BlogController::class, 'index'])->middleware(['auth', 'check.menu.permission'])->name('index');
                Route::get('index_data', [BlogController::class, 'index_data'])->name('index_data');
                Route::get('create', [BlogController::class, 'create'])->name('create');
                Route::get('edit/{id}', [BlogController::class, 'edit'])->name('edit');
                Route::post('store', [BlogController::class, 'store'])->name('store');
                Route::delete('delete/{id}', [BlogController::class, 'delete'])->name('delete');
                Route::post('update-status/{id}', [BlogController::class, 'updateStatus'])->name('update_status');
            });

            Route::prefix('website-setting')->name('website-setting.')->group(function () {
                Route::get('/', [WebsiteSettingController::class, 'index'])->middleware(['auth', 'check.menu.permission'])->name('index');
                Route::post('store', [WebsiteSettingController::class, 'store'])->name('store');
            });
            Route::get('upgrade-plan', [UpgradePlanController::class, 'upgradePlan'])->name('upgrade-plan');
            Route::get('pricing-plan', [UpgradePlanController::class, 'pricingplan'])->name('pricing-plan');
            Route::post('/payment-process', [UpgradePlanController::class, 'processPayment'])->name('payment-process');
            Route::get('/payment/success', [UpgradePlanController::class, 'paymentSuccess'])->name('payment.success');

            Route::middleware(['role:admin'])->group(function () {
                Route::get('/subscription-detail', [FrontendController::class, 'subscriptiondetail'])->name('subscription_detail');
                Route::get('billing-page', [UpgradePlanController::class, 'billingPage'])->name('billing-page');
            });
        });
    });
    // Stripe payment route
    Route::get('/stripe/pay/{plan_id}', [StripeController::class, 'pay'])->name('stripe.pay');
    Route::get('stripe/payment/success', [StripeController::class, 'success'])->name('stripe.payment.success');
    Route::get('stripe/payment/cancel', [StripeController::class, 'cancel'])->name('stripe.payment.cancel');

    // Razorpay payment route
    Route::get('/razorpay/pay/{plan_id}', [RazorpayController::class, 'pay'])->name('razorpay.pay');
    Route::get('razorpay/payment/success', [RazorpayController::class, 'success'])->name('razorpay.payment.success');
    Route::get('razorpay/payment/cancel', [RazorpayController::class, 'cancel'])->name('razorpay.payment.cancel');

    // PayPal payment route
    Route::get('/paypal/pay/{plan_id}', [PaypalController::class, 'pay'])->name('paypal.pay');
    Route::get('paypal/payment/success', [PaypalController::class, 'success'])->name('paypal.payment.success');
    Route::get('paypal/payment/cancel', [PaypalController::class, 'cancel'])->name('paypal.payment.cancel');
    Route::get('paypal/payment/error', [PaypalController::class, 'error'])->name('paypal.payment.error');
});
Route::get('migrate', [BlogController::class, 'migration']);
