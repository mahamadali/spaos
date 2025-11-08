<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Subscriptions\Models\Plan;
use Modules\Subscriptions\Models\Subscription;
use App\Models\PlanTax;
use Modules\MenuBuilder\Models\MenuBuilder;
use Modules\Promotion\Models\Promotion;
use Modules\Promotion\Models\PromotionsCouponPlanMapping;
use Carbon\Carbon;

class PricingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['plan'] = Plan::with('features')->get();

        $activeSubscriptions = Subscription::where('user_id', auth()->id())->where('status', 'active')->where('end_date', '>', now())->orderBy('id', 'desc')->first();
        $currentPlanId = $activeSubscriptions ? $activeSubscriptions->plan_id : null;
        $subscriptions = Subscription::where('user_id', auth()->id())
            ->with('subscription_transaction')
            ->where('end_date', '<', now())
            ->get();

        $data['currentPlanId'] = $currentPlanId;

        $data['subscriptions'] = $subscriptions;
        $data['bread_crumb'] = "Pricing";
        $user = auth()->user();
        $excludedTitles = ['sidebar.main', 'sidebar.company', 'sidebar.users', 'sidebar.finance', 'sidebar.reports', 'sidebar.system', 'Plans', 'Payments', 'Subscriptions', 'sidebar.plans', 'sidebar.payments', 'sidebar.product', 'sidebar.variations', 'sidebar.orders', 'sidebar.orders_report', 'sidebar.supply', 'sidebar.reviews',];
        $menus = MenuBuilder::whereNull('parent_id')->where('menu_type', 'vertical')
            ->whereNotIn('title', $excludedTitles)
            ->get();

        $limits = ['Appointments', 'Branches', 'Services', 'Staff', 'Customer',];
        return view('frontend::pricing', compact('data', 'menus', 'limits'));
    }

    public function pricing_plan(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('user.login');
        }

        $selected_plan = $request->id;
        $activeSubscriptions = Subscription::where('user_id', auth()->id())
            ->where('status', 'active')
            ->where('end_date', '>=', now())
            ->orderBy('id', 'desc')
            ->pluck('plan_id');

        $data['plan'] = Plan::with('features')
            ->get();

        $data['currentPlanId'] = $activeSubscriptions->first() ?? null; // Current active plan
        $data['selected_plan'] = $selected_plan;

        $data['plan_details'] = Plan::find($selected_plan);

        // Calculate base price and discount
        if (isset($data['plan_details'])) {
            $basePrice = $data['plan_details']->price;

            // Handle plan discount if exists
            if ($data['plan_details']->has_discount) {
                $discountAmount = 0;

                if ($data['plan_details']->discount_type === 'percentage') {
                    $discountAmount = ($basePrice * $data['plan_details']->discount_value) / 100;
                } else { // fixed
                    $discountAmount = $data['plan_details']->discount_value;
                }

                // Ensure discount doesn't exceed price
                $discountAmount = min($discountAmount, $basePrice);
                $discountedPrice = $basePrice - $discountAmount;

                $data['discount_details'] = [
                    'has_discount' => true,
                    'discount_type' => $data['plan_details']->discount_type,
                    'discount_value' => $data['plan_details']->discount_value,
                    'discount_amount' => $discountAmount,
                    'discounted_price' => $discountedPrice
                ];

                // Use discounted price for tax calculations
                $basePrice = $discountedPrice;
            }
        } else {
            $basePrice = 0;
        }

        // Get plan taxes
        $planTaxes = PlanTax::where(function ($query) use ($selected_plan) {
            $query->whereNotNull('plan_ids')
                ->whereRaw('FIND_IN_SET(?, plan_ids)', [$selected_plan]);
        })->where('status', 1)->get();

        $totalTaxAmount = 0;
        $taxDetails = [];

        // Calculate taxes based on discounted price if applicable
        foreach ($planTaxes as $tax) {
            if ($tax->type == 'Percentage') {
                $taxAmount = ($basePrice * $tax->value) / 100;
            } else {
                $taxAmount = $tax->value;
            }

            $totalTaxAmount += $taxAmount;
            $taxDetails[] = [
                'title' => $tax->title,
                'type' => $tax->type,
                'value' => $tax->value,
                'amount' => $taxAmount,
            ];
        }

        // Get promotions
        $data['promotions'] = Promotion::whereHas('promotionCouponPlanMappings', function ($query) use ($selected_plan) {
            $query->where('plan_id', $selected_plan);
        })
            ->where(function ($query) {
                $query->where('start_date_time', '<=', now())
                    ->where('end_date_time', '>=', now());
            })
            ->where('status', 1)
            ->whereHas('coupon')
            ->with('coupon')
            ->get();

        // Set final price details
        $data['original_price'] = $data['plan_details']->price ?? 0;
        $data['tax_details'] = $taxDetails;
        $data['total_tax'] = $totalTaxAmount;

        // Calculate final total based on whether there's a discount
        if (isset($data['discount_details'])) {
            $data['total_amount'] = $data['discount_details']['discounted_price'] + $totalTaxAmount;
        } else {
            $data['total_amount'] = $basePrice + $totalTaxAmount;
        }

        return view('frontend::pricing_plan', compact('data'));
    }

    public function calculate_discount(Request $request)
    {


        $selected_plan = $request->plan_id;
        $promotional_id = $request->coupon_id;

        $data['plan_details'] = Plan::where('id', $selected_plan)->first();



        $planTaxes = PlanTax::where(function ($query) use ($selected_plan) {
            $query->whereNotNull('plan_ids')
                ->whereRaw('FIND_IN_SET(?, plan_ids)', [$selected_plan]);
        })->where('status', 1)->get();

        $promotions = Promotion::where('id', $promotional_id)->with('coupon')->first();
        $coupon_data = $promotions->coupon;



        $data['coupon_code'] =  $coupon_data->coupon_code ?? null;
        $data['discount_type'] =  $coupon_data->discount_type ?? null;
        $data['discount_percentage'] =  $coupon_data->discount_percentage ?? null;

        $discount_amount = 0;

        if ($coupon_data->discount_type == 'percent') {

            $discount_amount = $data['plan_details']['price'] * $coupon_data->discount_percentage / 100;
        } else {

            $discount_amount = $coupon_data->discount_amount;
        }


        $totalTaxAmount = 0;
        $taxDetails = [];

        $data['discount_amount'] = $discount_amount;



        if (isset($data['plan_details']['price'])) {
            $basePrice = $data['plan_details']['price'] - $discount_amount;
        } else {
            $basePrice = 0; // Default value if 'price' is not set
        }

        foreach ($planTaxes as $tax) {
            if ($tax->type == 'Percentage') {

                $taxAmount = ($basePrice * $tax->value) / 100;
            } else {

                $taxAmount = $tax->value;
            }

            $totalTaxAmount += $taxAmount;

            $taxDetails[] = [
                'title' => $tax->title,
                'type' => $tax->type,
                'value' => $tax->value,
                'amount' => $taxAmount,
            ];
        }

        $totalAmount = $basePrice + $totalTaxAmount;

        $data['tax_details'] = $taxDetails;
        $data['total_tax'] = $totalTaxAmount;
        $data['total_amount'] = $totalAmount;

        return response()->json($data);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('frontend::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('frontend::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('frontend::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
    public function PaymentDetails(Request $request)
    {
        $selected_plan = $request->id;
        $data['selected_plan'] = $selected_plan;

        // Get plan details
        $data['plan_details'] = Plan::where('id', $selected_plan)->first();


        // Set base price
        if (isset($data['plan_details']['price'])) {
            $basePrice = $data['plan_details']['price'];
        } else {
            $basePrice = 0;
        }
        $data['plan_details']['price'] = $basePrice;

        // Get applicable taxes
        $planTaxes = PlanTax::where(function ($query) use ($selected_plan) {
            $query->whereNotNull('plan_ids')
                ->whereRaw('FIND_IN_SET(?, plan_ids)', [$selected_plan]);
        })->where('status', 1)->get();

        $totalTaxAmount = 0;
        $taxDetails = [];

        $planDiscountAmount = 0;
        if ($data['plan_details']->has_discount) {
            $planDiscountAmount = 0;

            if ($data['plan_details']->discount_type === 'percentage') {
                $planDiscountAmount = ($basePrice * $data['plan_details']->discount_value) / 100;
            } else {
                $planDiscountAmount = $data['plan_details']->discount_value;
            }

            // Ensure plan discount doesn't exceed price
            $planDiscountAmount = min($planDiscountAmount, $basePrice);
            $basePrice -= $planDiscountAmount;

            $data['plan_discount'] = [
                'has_discount' => true,
                'discount_type' => $data['plan_details']->discount_type,
                'discount_value' => $data['plan_details']->discount_value,
                'discount_amount' => $planDiscountAmount
            ];
        }
        foreach ($planTaxes as $tax) {
            $taxAmount = ($tax->type == 'Percentage') ? ($basePrice * $tax->value) / 100 : $tax->value;
            $totalTaxAmount += $taxAmount;

            $taxDetails[] = [
                'title' => $tax->title,
                'type' => $tax->type,
                'value' => $tax->value,
                'amount' => $taxAmount,
            ];
        }

        $totalAmount = $basePrice + $totalTaxAmount;

        // Get promotions **after** base price is defined
        $currentDateTime = Carbon::now();

        $data['promotions'] = Promotion::where('status', 1)
            ->whereHas('promotionCouponPlanMappings', function ($query) use ($selected_plan) {
                $query->where('plan_id', $selected_plan);
            })
            ->where('start_date_time', '<=', $currentDateTime)
            ->where('end_date_time', '>=', $currentDateTime)
            ->with('coupon')
            ->get()
            ->filter(function ($promotion) use ($basePrice) {
                if (!isset($promotion->coupon)) {
                    return false;
                }

                if ($promotion->coupon->discount_type === 'percent') {
                    $discount = ($basePrice * $promotion->coupon->discount_percentage) / 100;
                } else {
                    $discount = $promotion->coupon->discount_amount;
                }

                return $discount <= $basePrice;
            })
            ->values();
        $data['planDiscountAmount'] = $planDiscountAmount;

        $data['tax_details'] = $taxDetails;
        $data['total_tax'] = $totalTaxAmount;
        $data['total_amount'] = $totalAmount;

        return response()->json($data);
    }
}
