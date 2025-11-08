<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Modules\Subscriptions\Models\Plan;
use Modules\Subscriptions\Models\SubscriptionTransactions;
use Modules\Subscriptions\Models\Subscription;
use Modules\Subscriptions\Trait\SubscriptionTrait;
use Modules\Tax\Models\Tax;
use GuzzleHttp\Client;
use PayPal\Api\Payment;
use Stripe\StripeClient;
use PayPal\Api\PaymentExecution;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Midtrans\Snap;
use Midtrans\Config;
use  Modules\Subscriptions\Transformers\SubscriptionResource;
use Modules\Subscriptions\Transformers\PlanResource;
use Modules\Subscriptions\Transformers\PlanlimitationMappingResource;
use App\Mail\SubscriptionDetail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\PlanTax;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Promotion\Models\Promotion;
use Modules\MenuBuilder\Models\MenuBuilder;
use Modules\Promotion\Models\PromotionsCouponPlanMapping;

class upgradePlanController extends Controller
{

    use SubscriptionTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('frontend::index');
    }


    public function billingPage()
    {
        $data['plan'] = Plan::with('features')->get();

        $activeSubscriptions = Subscription::where('user_id', auth()->id())->where('status', 'active')->where('end_date', '>', now())->with('plan')->orderBy('id', 'desc')->first();

        $currentPlanId = $activeSubscriptions ? $activeSubscriptions->plan_id : null;
        $subscriptions = Subscription::with('subscription_transaction')
            ->where('user_id', auth()->id())
            ->whereHas('plan', function ($query) {
                $query->where('identifier', '!=', 'free');
            })
            ->orderBy('created_at', 'desc')
            ->get();
        $module_title = __('frontend.billing');

        $data['activeSubscriptions'] = $activeSubscriptions;

        $expiredSubscription = null;
        $cancelledSubscription = null;
        if (!$activeSubscriptions) {
            $cancelledSubscription = Subscription::where('user_id', auth()->id())
                ->where('status', 'cancel') // Only expired subscriptions
                ->with('plan')
                ->orderBy('end_date', 'desc') // Get the latest expired subscription
                ->first();

            if (!$cancelledSubscription) {
                $expiredSubscription = Subscription::where('user_id', auth()->id())
                    ->where('end_date', '<', now()) // Only expired subscriptions
                    ->with('plan')
                    ->orderBy('end_date', 'desc') // Get the latest expired subscription
                    ->first();
            }
        }
        $data['cancelledSubscription'] = $cancelledSubscription;
        $data['expiredSubscription'] = $expiredSubscription;
        $data['currentPlanId'] = $currentPlanId;
        $data['subscriptionStatus'] = $activeSubscriptions ? 'active' : ($expiredSubscription ? 'expired' : null);
        $data['subscriptions'] = $subscriptions;
        $data['bread_crumb'] = "Pricing";
        return view('backend.billingpage.index', compact('data', 'module_title'));
    }

    public function upgradePlan()
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

        $excludedTitles = ['sidebar.main', 'sidebar.company', 'sidebar.users', 'sidebar.finance', 'sidebar.reports', 'sidebar.system', 'Plans', 'Payments', 'Subscriptions', 'sidebar.plans', 'sidebar.payments', 'sidebar.product', 'sidebar.variations', 'sidebar.orders', 'sidebar.orders_report', 'sidebar.supply', 'sidebar.reviews',];
        $menus = MenuBuilder::whereNull('parent_id')->where('menu_type', 'vertical')
            ->whereNotIn('title', $excludedTitles)
            ->get();


        $limits = ['Appointments', 'Branches', 'Services', 'Staff', 'Customer',];
        $module_title = __('frontend.pricing_plan');
        return view('backend.billingpage.upgrade', compact('module_title', 'data', 'menus', 'limits'));
    }

    public function pricingPlan(Request $request)
    {

        $selected_plan = $request->id;
        $module_title = __('frontend.pricing_plan');
        $activeSubscriptions = Subscription::where('user_id', auth()->id())
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->orderBy('id', 'desc')
            ->pluck('plan_id');


        $data['plan'] = Plan::with('features')
            ->get();

        $data['currentPlanId'] = $activeSubscriptions->first() ?? null; // Current active plan
        $data['selected_plan'] = $selected_plan;

        $data['plan_details'] = Plan::find($selected_plan);

        $planTaxes = PlanTax::where(function ($query) use ($selected_plan) {
            $query->whereNotNull('plan_ids')
                ->whereRaw('FIND_IN_SET(?, plan_ids)', [$selected_plan]);
        })->where('status', 1)->get();


        if (isset($data['plan_details']['price'])) {
            $basePrice = $data['plan_details']['price'];
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
            $basePrice = 0; // Default value if 'price' is not set
        }


        $data['promotions'] = Promotion::where('status', 1)->whereHas('promotionCouponPlanMappings', function ($query) use ($data) {
            $query->where('plan_id', $data['selected_plan']);
        })
            ->where(function ($query) {
                $query->where('start_date_time', '<=', now())
                    ->where('end_date_time', '>=', now());
            })
            ->whereHas('coupon') // Ensure the coupon relationship is not null
            ->with('coupon') // Eager load the coupon relationship
            ->get()
            ->filter(function ($promotion) use ($basePrice) {
                if (!isset($promotion->coupon)) {
                    return false; // Skip promotions without a coupon
                }

                if ($promotion->coupon->discount_type === 'percent') {
                    $discount = ($basePrice * $promotion->coupon->discount_percentage) / 100;
                } else {
                    $discount = $promotion->coupon->discount_amount;
                }

                return $discount <= $basePrice; // Only keep promotions where discount is valid
            })
            ->values();


        $totalTaxAmount = 0;
        $taxDetails = [];





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

        $data['tax_details'] = $taxDetails;
        $data['total_tax'] = $totalTaxAmount;
        $data['total_amount'] = $basePrice + $totalTaxAmount;
        return view('backend.billingpage.payment', compact('module_title', 'data'));
    }



    public function selectPlan(Request $request)
    {
        $planId = $request->input('plan_id');
        $planName = $request->input('plan_name');
        $plan = Plan::all();

        $plans = PlanResource::collection($plan);

        $activeSubscriptions = Subscription::where('user_id', auth()->id())->where('status', 'active')->where('end_date', '>', now())->orderBy('id', 'desc')->first();
        $currentPlanId = $activeSubscriptions ? $activeSubscriptions->plan_id : null;


        $planId = $planId ?? $currentPlanId ?? Plan::first()->id ?? null;

        $view = view('frontend::subscriptionPayment', compact('plans', 'planId', 'currentPlanId'))->render();
        return response()->json(['success' => true, 'view' => $view]);
    }

    public function processPayment(Request $request)
    {
        // Check if this is a GET request (direct access)
        if ($request->isMethod('get')) {
            return redirect()->route('pricing-plan')->with('error', 'Please select a plan and payment method first.');
        }

        $planId = $request->input('plan_id');
        $price = $request->input('total_price_amount');
        $coupon_id = $request->input('coupon_id');

        // Validate required parameters
        if (empty($planId) || empty($price)) {
            return redirect()->back()->withErrors('Missing required payment information.');
        }

        // Handle free plans directly without payment processing
        if ($price == 0 || $price == '0.00' || $price == '0') {
            return $this->handleFreePlanAssignment($planId, $coupon_id);
        }

        $paymentMethod = $request->input('payment_method');

        // Validate payment method
        if (empty($paymentMethod)) {
            return redirect()->back()->withErrors('Please select a payment method.');
        }

        $paymentHandlers = [
            'stripe' => 'StripePayment',
            'razorpay' => 'RazorpayPayment',
            'paystack' => 'PaystackPayment',
            'paypal' => 'PayPalPayment',
            'flutterwave' => 'FlutterwavePayment',
            'cinet' => 'CinetPayment',
            'sadad' => 'SadadPayment',
            'airtel' => 'AirtelPayment',
            'phonepe' => 'PhonePePayment',
            'midtrans' => 'MidtransPayment',
        ];

        if (array_key_exists($paymentMethod, $paymentHandlers)) {
            return $this->{$paymentHandlers[$paymentMethod]}($request, $price, $coupon_id);
        }

        return redirect()->back()->withErrors(__('messages.invalid_payment_method'));
    }

    /**
     * Handle free plan assignment directly without payment processing
     */
    protected function handleFreePlanAssignment($planId, $coupon_id = null)
    {
        try {
            $plan = Plan::findOrFail($planId);
            
            // Check if plan is actually free
            if ($plan->price > 0) {
                return redirect()->back()->withErrors(__('messages.invalid_free_plan'));
            }

            \Log::info('Creating free subscription', [
                'plan_id' => $planId,
                'user_id' => auth()->id(),
                'plan_name' => $plan->name
            ]);

            // Create subscription directly for free plan
            $subscription = Subscription::create([
                'plan_id' => $planId,
                'user_id' => auth()->id(),
                'start_date' => now(),
                'end_date' => $plan->type == 'Monthly' ? now()->addMonth() : 
                             ($plan->type == 'Weekly' ? now()->addWeek() : 
                             ($plan->type == 'Yearly' ? now()->addYear() : now()->addMonth())),
                'status' => 'active',
                'amount' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'plan_details' => json_encode($plan),
                'gateway_type' => 'free',
                'transaction_id' => 'FREE_' . time() . '_' . auth()->id(),
                'name' => $plan->name,
                'identifier' => $plan->identifier,
                'type' => $plan->type,
                'duration' => $plan->duration,
                'payment_id' => null,
                'max_appointment' => $plan->max_appointment,
                'max_branch' => $plan->max_branch,
                'max_service' => $plan->max_service,
                'max_staff' => $plan->max_staff,
                'max_customer' => $plan->max_customer,
                'is_active' => 1,
            ]);

            // Deactivate other subscriptions
            Subscription::where('user_id', auth()->id())
                ->where('id', '!=', $subscription->id)
                ->update(['status' => 'inactive', 'is_active' => 0]);

            // Give permissions to user
            $plan->givePermissionToUser(auth()->id());

            \Log::info('Free subscription created successfully', [
                'subscription_id' => $subscription->id,
                'plan_name' => $plan->name
            ]);

            return redirect()->route('backend.subscription.plans.index')
                ->with('success', __('messages.free_plan_activated_successfully'));

        } catch (\Exception $e) {
            \Log::error('Error creating free subscription', [
                'error' => $e->getMessage(),
                'plan_id' => $planId,
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->withErrors(__('messages.free_plan_activation_failed'));
        }
    }

    protected function StripePayment(Request $request)
    {

        $baseURL = url('/');


        $stripe_secret_key = GetpaymentMethod('stripe_secretkey');

        $currency = GetcurrentCurrency();

        $stripe = new StripeClient($stripe_secret_key);
        $price = $request->input('total_price_amount'); // Get the price from the request
        $plan_id = $request->input('plan_id');
        $promotional_id = $request->input('coupon_id');
        $priceInCents = intval(round($price * 100));
        $checkout_session = $stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => 'Subscription Plan',
                    ],
                    'unit_amount' => $priceInCents,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'metadata' => [
                'plan_id' => $plan_id,
                'promotion_id' => $promotional_id
            ],
            'success_url' => $baseURL . '/app/payment/success?gateway=stripe&session_id={CHECKOUT_SESSION_ID}'
        ]);

        return redirect($checkout_session->url);
    }

    protected function RazorpayPayment(Request $request, $price)
    {
        $baseURL = url('/');
        $razorpayKey = GetpaymentMethod('razorpay_publickey');
        $razorpaySecret = GetpaymentMethod('razorpay_secretkey');
        $plan_id = $request->input('plan_id');
        $priceInPaise = $price * 100;

        try {

            $amount = $price * 100; // Convert to paisa
            $razorpayKey = GetpaymentMethod('razorpay_publickey');

            return response()->json([
                'key' => $razorpayKey,
                'amount' => $amount,
                'currency' => strtoupper(GetcurrentCurrency()),
                'name' => config('app.name'),
                'description' => 'Subscription Payment',
                'plan_id' => $plan_id,
                'order_id' => null,
                'success_url' => $baseURL . '/app/payment/success?gateway=razorpay',
                'prefill' => [
                    'name' => auth()->user()->name ?? '',
                    'email' => auth()->user()->email ?? '',
                    'contact' => auth()->user()->phone ?? ''
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    protected function PaystackPayment(Request $request)
    {
        $baseURL =  url('/');
        $paystackSecretKey = GetpaymentMethod('paystack_secretkey');
        $price = $request->input('total_price_amount');
        $plan_id = $request->input('plan_id');
        $priceInKobo = $price * 100; // Paystack uses kobo

        // Create a new Paystack payment
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $paystackSecretKey,
        ])->post('https://api.paystack.co/transaction/initialize', [
            'email' => auth()->user()->email, // Get user email from authenticated user
            'amount' => $priceInKobo,
            'currency' => strtoupper(GetcurrentCurrency()),
            'callback_url' => $baseURL . '/app/payment/success?gateway=paystack',
            'metadata' => [
                'plan_id' => $plan_id,
            ],
        ]);

        $responseBody = $response->json();

        if ($responseBody['status']) {
            return redirect($responseBody['data']['authorization_url']);
        } else {
            return response()->json(['error' => __('messages.something_wrong_choose_another')], 400);
        }
    }

    protected function PayPalPayment(Request $request)
    {
        $baseURL = env('APP_URL');
        $price = $request->input('price');
        $plan_id = $request->input('plan_id');

        // Validate price
        if (!is_numeric($price) || $price <= 0) {
            return redirect()->back()->withErrors(__('messages.invalid_price_value'));
        }

        try {
            // Get Access Token
            $accessToken = $this->getAccessToken();

            // Create Payment
            $payment = $this->createPayment($accessToken, $price, $plan_id);

            if (isset($payment['links'])) {
                foreach ($payment['links'] as $link) {
                    if ($link['rel'] === 'approval_url') {
                        return response()->json(['success' => true, 'redirect' => $link['href']]);
                    }
                }
            }

            return redirect()->back()->withErrors(__('messages.payment_creation_failed'));
        } catch (\Exception $ex) {
            return redirect()->back()->withErrors(__('messages.payment_processing_failed') . $ex->getMessage());
        }
    }

    protected function FlutterwavePayment(Request $request)
    {
        try {
            $baseURL = env('APP_URL');
            $flutterwaveKey = GetpaymentMethod('flutterwave_secretkey');

            // Validate Flutterwave key
            if (empty($flutterwaveKey)) {
                return response()->json([
                    'error' => 'Flutterwave payment method not properly configured'
                ], 400);
            }

            $price = $request->input('total_price_amount') ?? $request->input('price');
            $plan_id = $request->input('plan_id');

            // Validate required fields
            if (empty($price) || empty($plan_id)) {
                return response()->json([
                    'error' => 'Missing required payment information'
                ], 400);
            }

            $priceInKobo = $price * 100;

            $data = [
                'tx_ref' => 'FLW_' . uniqid() . time(),
                'email' => auth()->user()->email,
                'amount' => $priceInKobo,
                "currency" => strtoupper(GetcurrentCurrency()),
                'redirect_url' => $baseURL . '/app/payment/success?gateway=flutterwave',
                'customer' => [
                    'email' => auth()->user()->email,
                    'name' => auth()->user()->name,
                    'phonenumber' => auth()->user()->phone ?? ''
                ],
                'meta' => [
                    'plan_id' => $plan_id,
                ],
                'customizations' => [
                    'title' => config('app.name') . ' Payment',
                    'description' => 'Subscription Payment',
                    'logo' => asset('logo.png')
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $flutterwaveKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.flutterwave.com/v3/payments', $data);

            if (!$response->successful()) {
                return response()->json([
                    'error' => 'Payment initialization failed. Please try again.'
                ], $response->status());
            }

            $responseBody = $response->json();

            if (isset($responseBody['status']) && $responseBody['status'] === 'success') {
                // Redirect directly to Flutterwave checkout page
                return redirect($responseBody['data']['link']);
            }

            return redirect()->back()->withErrors($responseBody['message'] ?? 'Payment initialization failed');
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Payment processing failed. Please try again.'
            ], 500);
        }
    }

    protected function CinetPayment(Request $request)
    {
        $baseURL = env('APP_URL');
        $cinetApiKey = GetpaymentMethod('cinet_Secret_key');
        $price = $request->input('price');
        $plan_id = $request->input('plan_id');
        $priceInCents = $price * 100;

        $data = [
            'amount' => $priceInCents,
            'currency' => 'USD',
            'plan_id' => $plan_id,
            'callback_url' => $baseURL . '/app/payment/success?gateway=cinet',
            'user_email' => auth()->user()->email,
        ];
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $cinetApiKey,
        ])->post('https://api.cinet.com/payment', $data);

        $responseBody = $response->json();

        if ($response->successful() && isset($responseBody['payment_url'])) {
            return redirect($responseBody['payment_url']);
        } else {
            return redirect()->back()->withErrors(__('messages.payment_initialization_failed') . ($responseBody['message'] ?? __('messages.unknown_error')));
        }
    }

    protected function SadadPayment(Request $request)
    {
        $baseURL = env('APP_URL');
        $price = $request->input('price');
        $plan_id = $request->input('plan_id');
        $response = $this->makeSadadPaymentRequest($price, $plan_id);
        if ($response->isSuccessful()) {
            return redirect($response->redirect_url);
        } else {
            return redirect()->back()->withErrors(__('messages.payment_initialization_failed') . $response->message);
        }
    }

    protected function AirtelPayment(Request $request)
    {
        $baseURL = env('APP_URL');
        $price = $request->input('price');
        $plan_id = $request->input('plan_id');

        $response = $this->makeAirtelPaymentRequest($price, $plan_id);

        if ($response->isSuccessful()) {
            return redirect($response->redirect_url);
        } else {
            return redirect()->back()->withErrors(__('messages.airtel_initiation_failed', ['message' => $response->message]));
        }
    }

    protected function PhonePePayment(Request $request)
    {
        $baseURL = env('APP_URL');
        $price = $request->input('price');
        $plan_id = $request->input('plan_id');

        $response = $this->makePhonePePaymentRequest($price, $plan_id);

        if ($response->isSuccessful()) {
            return redirect($response->payment_url);
        } else {
            return redirect()->back()->withErrors(__('messages.phonepe_initiation_failed', ['message' => $response->message]));
        }
    }

    protected function MidtransPayment(Request $request)
    {
        Config::$serverKey = GetpaymentMethod(' midtrans_client_id');

        $price = $request->input('price');
        $plan_id = $request->input('plan_id');
        $transactionDetails = [
            'order_id' => uniqid(),
            'gross_amount' => $price,
        ];

        $customerDetails = [
            'first_name' => auth()->user()->name,
            'email' => auth()->user()->email,
        ];

        $transaction = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
        ];

        try {
            $snapToken = Snap::getSnapToken($transaction);
            return response()->json(['snapToken' => $snapToken]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(__('messages.payment_initialization_failed') . $e->getMessage());
        }
    }

    private function getAccessToken()
    {
        $clientId =  GetpaymentMethod('paypal_clientid');
        $clientSecret = GetpaymentMethod('paypal_secretkey');

        $client = new Client();
        $response = $client->post('https://api.sandbox.paypal.com/v1/oauth2/token', [
            'auth' => [$clientId, $clientSecret],
            'form_params' => [
                'grant_type' => 'client_credentials',
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['access_token'];
    }

    private function createPayment($accessToken, $price, $planId)
    {
        $baseURL = env('APP_URL');
        $client = new Client();
        $response = $client->post('https://api.sandbox.paypal.com/v1/payments/payment', [
            'headers' => [
                'Authorization' => "Bearer $accessToken",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'intent' => 'sale',
                'payer' => [
                    'payment_method' => 'paypal',
                ],
                'transactions' => [[
                    'amount' => [
                        'total' => $price,
                        'currency' => 'USD',
                    ],
                    'description' => 'Payment for plan ID: ' . $planId,
                ]],
                'redirect_urls' => [
                    'return_url' => $baseURL . '/app/payment/success?gateway=paypal',
                    'cancel_url' => $baseURL . '/payment/cancel',
                ],
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function paymentSuccess(Request $request)
    {


        $gateway = $request->query('gateway');

        switch ($gateway) {
            case 'stripe':
                return $this->handleStripeSuccess($request);
            case 'razorpay':
                return $this->handleRazorpaySuccess($request);
            case 'paystack':
                return $this->handlePaystackSuccess($request);
            case 'paypal':
                return $this->handlePayPalSuccess($request);
            case 'flutterwave':
                return $this->handleFlutterwaveSuccess($request);
            case 'cinet':
                return $this->handleCinetSuccess($request);
            case 'sadad':
                return $this->handleSadadSuccess($request);
            case 'airtel':
                return $this->handleAirtelSuccess($request);
            case 'phonepe':
                return $this->handlePhonePeSuccess($request);
            case 'midtrans':
                return $this->MidtransPayment($request);
            default:
                return redirect('/')->with('error', __('messages.invalid_payment_gateway'));
        }
    }

    protected function handlePaymentSuccess($plan_id, $amount, $payment_type, $transaction_id, $promotion_id = null)
    {

        $plan = Plan::findOrFail($plan_id);

        $user = Auth::user();

        $start_date = now();
        $end_date = $this->get_plan_expiration_date($start_date, $plan->type, $plan->duration);
        $taxes = PlanTax::where(function ($query) use ($plan_id) {
            $query->whereNotNull('plan_ids')
                ->whereRaw('FIND_IN_SET(?, plan_ids)', [$plan_id]);
        })->where('status', 1)->get();



        $promotions = Promotion::where('id', $promotion_id)->with('coupon')->first();


        $discount_amount = 0;

        if ($promotion_id != null && $promotions) {

            $coupon_data = $promotions->coupon;

            if ($coupon_data) {

                if ($coupon_data->discount_type == 'percent') {

                    $discount_amount = $plan->price * $coupon_data->discount_percentage / 100;
                } else {

                    $discount_amount = $coupon_data->discount_amount;
                }
            }
        }

        // Calculate base amount considering discounted price if available
        $base_amount = $plan->has_discount == 1 ? $plan->discounted_price : $plan->price;
        $base_amount = $base_amount - $discount_amount; // Apply any additional coupon discount

        // Calculate tax on the discounted amount
        $totalTax = 0;
        foreach ($taxes as $tax) {
            if (strtolower($tax->type) == 'fixed') {
                $totalTax += $tax->value;
            } elseif (strtolower($tax->type) == 'percentage') {
                $totalTax += ($base_amount * $tax->value) / 100;
            }
        }

        $amount = $base_amount + $totalTax;



        // Create the subscription
        $subscription = Subscription::create([
            'plan_id' => $plan_id,
            'user_id' => auth()->id(),
            'start_date' => now(),
            'end_date' => $end_date,
            'status' => 'active',
            'amount' => $plan->has_discount == 1 ? $plan->discounted_price : $plan->price,
            'tax_amount' => $totalTax,
            'discount_amount' => $discount_amount,
            'total_amount' => $amount,
            'plan_details' => json_encode($plan),
            'gateway_type' => $payment_type,
            'transaction_id' => $transaction_id,
            'name' => $plan->name,
            'identifier' => $plan->identifier,
            'type' => $plan->type,
            'duration' => $plan->duration,
            'payment_id' => null,
            'max_appointment' => $plan->max_appointment,
            'max_branch' => $plan->max_branch,
            'max_service' => $plan->max_service,
            'max_staff' => $plan->max_staff,
            'max_customer' => $plan->max_customer,
            'is_active' => 1,

        ]);


        $currency = strtolower(GetcurrentCurrency());
        $plan->givePermissionToUser(auth()->id());

        $store_payment_data = $this->StorepaymentData(auth()->id(), $plan_id, $amount, $currency, $subscription->id);


        // Create a subscription transaction
        SubscriptionTransactions::create([
            'user_id' => auth()->id(),
            'amount' => $amount,
            'payment_type' => $payment_type,
            'payment_status' => 'paid',
            'tax_data' => $taxes->isEmpty() ? null : json_encode($taxes),
            'discount_data' => $promotions ? json_encode($promotions) : null,
            'transaction_id' => $transaction_id,
            'subscriptions_id' => $subscription->id,
        ]);



        Subscription::where('user_id', auth()->id())
            ->where('id', '!=', $subscription->id)
            ->update(['status' => 'inactive', 'is_active' => 0]);

        $response = new SubscriptionResource($subscription);


        auth()->user()->update(['is_subscribe' => 1]);
        try {
            $type = 'new_subscription';
            $messageTemplate = 'New User [[plan_id]] has been subscribed.';
            $notify_message = str_replace('[[plan_id]]', $response->plan_id, $messageTemplate);
            $this->sendNotificationOnsubscription($type, $notify_message, $response);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }

        if (isSmtpConfigured()) {
            if ($user) {
                try {
                    Mail::to($user->email)->send(new SubscriptionDetail($response));
                } catch (\Exception $e) {
                    Log::error(__('messages.failed_to_send_email_to') . $user->email . ': ' . $e->getMessage());
                }
            } else {
                Log::info(__('messages.user_object_is_not_set'));
            }
        } else {
            Log::error(__('messages.smtp_configuration_is_not_set_correctly'));
        }





        return redirect()->route('backend.billing-page')->with('success', __('messages.payment_completed_successfully'));
    }

    protected function handleStripeSuccess(Request $request)
    {

        $sessionId = $request->input('session_id');
        $stripe_secret_key = GetpaymentMethod('stripe_secretkey');
        $stripe = new StripeClient($stripe_secret_key);

        try {
            $session = $stripe->checkout->sessions->retrieve($sessionId);


            return $this->handlePaymentSuccess($session->metadata->plan_id, $session->amount_total / 100, 'stripe', $session->payment_intent, $session->metadata->promotion_id);
        } catch (\Exception $e) {


            return redirect()->route('backend.billing-page')->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }

    protected function handleRazorpaySuccess(Request $request)
    {

        $paymentId = $request->input('razorpay_payment_id');
        $razorpayOrderId = session('razorpay_order_id');
        $plan_id = $request->input('plan_id');

        $razorpayKey = GetpaymentMethod('razorpay_publickey');
        $razorpaySecret = GetpaymentMethod('razorpay_secretkey');

        $api = new \Razorpay\Api\Api($razorpayKey, $razorpaySecret);
        $payment = $api->payment->fetch($paymentId);

        // If payment is authorized but not captured, capture it
        if ($payment['status'] == 'authorized' && !$payment['captured']) {
            $payment = $payment->capture(['amount' => $payment['amount']]); // amount in paise
        }

        if ($payment['status'] == 'captured') {
            return $this->handlePaymentSuccess($plan_id, $payment['amount'] / 100, 'razorpay', $paymentId);
        } else {
            return redirect('/')->with('error', __('messages.payment_processing_failed') . ($payment['error_description'] ?? ''));
        }
    }

    protected function handlePaystackSuccess(Request $request)
    {
        $reference = $request->input('reference');

        $paystackSecretKey = GetpaymentMethod('paystack_secretkey');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $paystackSecretKey,
        ])->get("https://api.paystack.co/transaction/verify/{$reference}");

        $responseBody = $response->json();

        if ($responseBody['status']) {
            return $this->handlePaymentSuccess($responseBody['data']['metadata']['plan_id'], $responseBody['data']['amount'] / 100, 'paystack', $responseBody['data']['id']);
        } else {
            return redirect('/')->with('error', __('messages.payment_verification_failed') . $responseBody['message']);
        }
    }

    protected function handlePayPalSuccess(Request $request)
    {
        $paymentId = $request->input('paymentId');
        $payerId = $request->input('PayerID');

        $paypal_secretkey = GetpaymentMethod('paypal_secretkey');
        $paypal_clientid = GetpaymentMethod('paypal_clientid');


        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $paypal_secretkey,
                $paypal_clientid
            )
        );

        try {
            $payment = get($paymentId, $apiContext);
            $execution = new PaymentExecution();
            $execution->setPayerId($payerId);
            $result = $payment->execute($execution, $apiContext);

            if ($result->getState() == 'approved') {
                $plan_id = $result->transactions[0]->item_list->items[0]->sku;
                return $this->handlePaymentSuccess($plan_id, $result->transactions[0]->amount->total, 'paypal', $paymentId);
            } else {
                return redirect('/')->with('error', __('messages.payment_not_approved'));
            }
        } catch (\Exception $e) {
            return redirect('/')->with('error', __('messages.payment_verification_failed') . $e->getMessage());
        }
    }

    protected function handleFlutterwaveSuccess(Request $request)
    {
        $tx_ref = $request->input('tx_ref');
        $flutterwaveKey = GetpaymentMethod('flutterwave_secretkey');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $flutterwaveKey,
        ])->get("https://api.flutterwave.com/v3/transactions/{$tx_ref}/verify");

        $responseBody = $response->json();

        if ($responseBody['status'] === 'success') {
            return $this->handlePaymentSuccess($responseBody['data']['metadata']['plan_id'], $responseBody['data']['amount'] / 100, 'flutterwave', $responseBody['data']['id']);
        } else {
            return redirect('/')->with('error', __('messages.payment_verification_failed') . $responseBody['message']);
        }
    }
    protected function handleCinetSuccess(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $paymentStatus = $request->input('status');
        $planId = $request->input('plan_id');

        if ($paymentStatus !== 'success') {
            return redirect('/')->with('error', __('messages.lbl_payment_failed'));
        }

        return $this->handlePaymentSuccess($planId, $request->input('amount'), 'cinet', $transactionId);
    }

    protected function handleSadadSuccess(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $paymentStatus = $request->input('status');
        $plan_id = $request->input('plan_id');

        if ($paymentStatus !== 'success') {
            return redirect('/')->with('error', __('messages.lbl_payment_failed'));
        }

        return $this->handlePaymentSuccess($plan_id, $request->input('amount'), 'sadad', $transactionId);
    }

    public function midtransNotification(Request $request)
    {
        $payload = json_decode($request->getContent(), true);

        if ($payload['transaction_status'] === 'settlement') {
            $transactionId = $payload['order_id'];
            $plan_id = $payload['item_details'][0]['id'];
            $amount = $payload['gross_amount'];

            return $this->handlePaymentSuccess($plan_id, $amount, 'midtrans', $transactionId);
        }

        return response()->json(['status' => 'success']);
    }

    protected function makeSadadPaymentRequest($price, $plan_id)
    {
        $sadad_Sadadkey = GetpaymentMethod('sadad_Sadadkey');

        $url = 'https://api.sadad.com/payment';
        $data = [
            'amount' => $price,
            'plan_id' => $plan_id,
            'callback_url' => env('APP_URL') . '/app/payment/success?gateway=sadad',
        ];

        $client = new \GuzzleHttp\Client();
        $response = $client->post($url, [
            'json' => $data,
            'headers' => [
                'Authorization' => 'Bearer ' . $sadad_Sadadkey,
            ]
        ]);

        return json_decode($response->getBody());
    }

    protected function handleAirtelSuccess(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $paymentStatus = $request->input('status');
        $planId = $request->input('plan_id');

        if ($paymentStatus !== 'success') {
            return redirect('/')->with('error', __('messages.lbl_payment_failed'));
        }

        return $this->handlePaymentSuccess($planId, $request->input('amount'), 'airtel', $transactionId);
    }

    protected function handlePhonePeSuccess(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $paymentStatus = $request->input('status');
        $planId = $request->input('plan_id');

        if ($paymentStatus !== 'success') {
            return redirect('/')->with('error', __('messages.lbl_payment_failed'));
        }

        return $this->handlePaymentSuccess($planId, $request->input('amount'), 'phonepe', $transactionId);
    }

    protected function makePhonePePaymentRequest($price, $plan_id)
    {
        $url = 'https://api.phonepe.com/apis/hermes/pg/v1/pay';
        $data = [
            'amount' => $price,
            'plan_id' => $plan_id,
            'callbackUrl' => env('APP_URL') . '/app/payment/success?gateway=phonepe',
            'currency' => 'INR',
        ];
        $client = new Client();
        $response = $client->post($url, [
            'json' => $data,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-VERIFY-TOKEN' => env('PHONEPE_VERIFY_TOKEN'),
            ]
        ]);

        return json_decode($response->getBody());
    }
    protected function makeAirtelPaymentRequest($price, $plan_id)
    {

        $airtel_money_secretkey = GetpaymentMethod('airtel_money_secretkey');


        $url = 'https://api.airtel.com/payment';
        $data = [
            'amount' => $price,
            'plan_id' => $plan_id,
            'callback_url' => env('APP_URL') . '/app/payment/success?gateway=airtel',
        ];

        $client = new Client();
        $response = $client->post($url, [
            'json' => $data,
            'headers' => [
                'Authorization' => 'Bearer ' .  $airtel_money_secretkey,
            ]
        ]);

        return json_decode($response->getBody());
    }

    public function downloadInvoice(Request $request)
    {
        // Retrieve the booking by ID with related services, user, and products
        $subscription = Subscription::with('plan', 'subscription_transaction', 'user')->find($request->id);
        if (!$subscription) {
            return response()->json(['status' => false, 'message' => 'subscription not found'], 404);
        }
        if ($subscription && $subscription->plan) {
            $planTaxes = PlanTax::where(function ($query) use ($subscription) {
                $query->whereNotNull('plan_ids')
                    ->whereRaw('FIND_IN_SET(?, plan_ids)', [$subscription->plan->id]);
            })->where('status', 1)->get();

            $subscription->plan->taxes = $planTaxes;
        }

        // Render the view for the invoice
        $view = view('frontend::components.partials.invoice', ['data' => $subscription])->render();

        // Generate the PDF from the rendered view
        $pdf = Pdf::loadHTML($view)->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => true,
        ]);

        // Return the generated PDF as a download
        // return $pdf->download('invoice.pdf');
        return response()->streamDownload(
            function () use ($pdf) {
                echo $pdf->output();
            },
            "invoice.pdf",
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="invoice.pdf"',
            ]
        );
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
}
