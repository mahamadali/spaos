<?php

namespace Modules\VendorWebsite\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'vendorwebsite');

        Blade::component('vendorwebsite::components.partials.horizontal_nav', 'horizontal_nav');
        Blade::component('vendorwebsite::components.partials.logo', 'logo');
        Blade::component('vendorwebsite::components.section.banner', 'banner');
        Blade::component('vendorwebsite::components.section.quick_booking', 'quick_booking');
        Blade::component('vendorwebsite::components.section.refer_section', 'refer_section');
        Blade::component('vendorwebsite::components.section.category_section', 'category_section');
        Blade::component('vendorwebsite::components.section.branch_section', 'branch_section');
        Blade::component('vendorwebsite::components.section.package_section', 'package_section');
        Blade::component('vendorwebsite::components.section.membership_section', 'membership_section');
        Blade::component('vendorwebsite::components.section.expert_section', 'expert_section');
        Blade::component('vendorwebsite::components.section.faq_section', 'faq_section');
        Blade::component('vendorwebsite::components.section.testimonial_section', 'testimonial_section');
        Blade::component('vendorwebsite::components.section.blog_section', 'blog_section');
        Blade::component('vendorwebsite::components.section.product_section', 'product_section');
        Blade::component('vendorwebsite::components.card.branch_card', 'branch_card');
        Blade::component('vendorwebsite::components.section.breadcrumb', 'breadcrumb');
        Blade::component('vendorwebsite::components.section.booking_section', 'booking_section');
        Blade::component('vendorwebsite::components.card.booking_card', 'booking_card');
        Blade::component('vendorwebsite::components.card.blog_card', 'blog_card');
        Blade::component('vendorwebsite::components.card.category_card', 'category_card');
        Blade::component('vendorwebsite::components.card.wishlist_card', 'wishlist_card');
        Blade::component('vendorwebsite::components.card.expert_card', 'expert_card');
        Blade::component('vendorwebsite::components.card.choose_expert_card', 'choose_expert_card');
        Blade::component('vendorwebsite::components.card.product_card', 'product_card');
        Blade::component('vendorwebsite::components.card.testimonial_card', 'testimonial_card');
        Blade::component('vendorwebsite::components.section.bookingdetails_section', 'bookingdetails_section');
        Blade::component('vendorwebsite::components.card.service_card', 'service_card');
        Blade::component('vendorwebsite::components.card.search_service_card', 'search_service_card');
        Blade::component('vendorwebsite::components.section.service_section', 'service_section');
        Blade::component('vendorwebsite::components.section.branchdetails_section', 'branchdetails_section');
        Blade::component('vendorwebsite::components.section.branchreview_section', 'branchreview_section');
        Blade::component('vendorwebsite::components.card.branchgallery_card', 'branchgallery_card');
        Blade::component('vendorwebsite::components.section.branchgallery_section', 'branchgallery_section');
        Blade::component('vendorwebsite::components.section.expertdetails_section', 'expertdetails_section');
        Blade::component('vendorwebsite::components.section.expertreview_section', 'expertreview_section');
        Blade::component('vendorwebsite::components.card.subcategory_card', 'subcategory_card');
        Blade::component('vendorwebsite::components.section.subcategory_section', 'subcategory_section');
        Blade::component('vendorwebsite::components.section.usepoint_section', 'usepoint_section');
        Blade::component('vendorwebsite::components.section.removepoint_section', 'removepoint_section');
        Blade::component('vendorwebsite::components.section.profile_section', 'profile_section');
        Blade::component('vendorwebsite::components.section.balance_section', 'balance_section');
        Blade::component('vendorwebsite::components.section.history_section', 'history_section');
        Blade::component('vendorwebsite::components.section.banklist_section', 'banklist_section');
        Blade::component('vendorwebsite::components.section.referral_section', 'referral_section');
        Blade::component('vendorwebsite::components.section.howitwork_section', 'howitwork_section');
        Blade::component('vendorwebsite::components.section.loyaltypoint_section', 'loyaltypoint_section');
        Blade::component('vendorwebsite::components.section.mymembership_section', 'mymembership_section');
        Blade::component('vendorwebsite::components.section.changepassword_section', 'changepassword_section');
        Blade::component('vendorwebsite::components.section.mypackage_section', 'mypackage_section');
        Blade::component('vendorwebsite::components.section.cart_section', 'cart_section');
        Blade::component('vendorwebsite::components.section.ordersummary_section', 'ordersummary_section');
        Blade::component('vendorwebsite::components.card.package_card', 'package_card');
        Blade::component('vendorwebsite::components.section.payment_section', 'payment_section');
        Blade::component('vendorwebsite::components.section.myorder_section', 'myorder_section');
        Blade::component('vendorwebsite::components.card.myorder_card', 'myorder_card');
        Blade::component('vendorwebsite::components.section.address_section', 'address_section');
        Blade::component('vendorwebsite::components.section.mywishlist_section', 'mywishlist_section');
        Blade::component('vendorwebsite::components.section.shop_section', 'shop_section');
        Blade::component('vendorwebsite::components.section.productdetails_section', 'productdetails_section');
        Blade::component('vendorwebsite::components.section.saleproduct_section', 'saleproduct_section');
        Blade::component('vendorwebsite::components.section.groupproduct_section', 'groupproduct_section');
        Blade::component('vendorwebsite::components.section.checkout_section', 'checkout_section');
        Blade::component('vendorwebsite::components.section.location_section', 'location_section');
        Blade::component('vendorwebsite::components.section.leave_section', 'leave_section');
        Blade::component('vendorwebsite::components.section.blogdetails_section', 'blogdetails_section');
        Blade::component('vendorwebsite::components.section.about_section', 'about_section');
        Blade::component('vendorwebsite::components.section.ratenow_section', 'ratenow_section');
        Blade::component('vendorwebsite::components.card.membership_card', 'membership_card');
        Blade::component('vendorwebsite::components.section.mybooking_section', 'mybooking_section');
        Blade::component('vendorwebsite::components.card.mybooking_card', 'mybooking_card');
        Blade::component('vendorwebsite::components.card.category_card', 'category_card');
        Blade::component('vendorwebsite::components.section.contact_banner_section', 'contact_banner_section');
    }


    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->group(base_path('Modules/VendorWebsite/routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('Modules/VendorWebsite/routes/api.php'));
    }
}
