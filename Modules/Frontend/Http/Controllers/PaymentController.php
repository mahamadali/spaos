<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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


class PaymentController extends Controller
{
    use SubscriptionTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('frontend::index');
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

        $paymentMethod = $request->input('payment_method');
        $price = $request->input('total_price_amount');
        $coupon_id = $request->input('coupon_id');


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


    protected function StripePayment(Request $request)
    {

        $baseURL = env('APP_URL');

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
            'success_url' => $baseURL . '/payment/success?gateway=stripe&session_id={CHECKOUT_SESSION_ID}'
        ]);

        return redirect($checkout_session->url);
    }

    protected function RazorpayPayment(Request $request, $price)
    {
        $baseURL = env('APP_URL');
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
                'success_url' => route('payment.success'),
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
        $baseURL = env('APP_URL');
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
            'callback_url' => $baseURL . '/payment/success?gateway=paystack',
            'metadata' => [
                'plan_id' => $plan_id,
            ],
        ]);

        $responseBody = $response->json();

        if ($responseBody['status']) {
            return redirect($responseBody['data']['authorization_url']);
        } else {
            return response()->json(['error' => __('messages.choose_different_method')], 400);
        }
    }

    protected function PayPalPayment(Request $request)
    {
        $baseURL = env('APP_URL');
        $price = $request->input('total_price_amount');
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
                        return redirect($link['href']);
                    }
                }
            }

            return redirect()->back()->withErrors(__('messages.payment_failed'));
        } catch (\Exception $ex) {
            return redirect()->back()->withErrors(__('messages.payment_failed') . $ex->getMessage());
        }
    }

    protected function FlutterwavePayment(Request $request)
    {
        try {
            $baseURL = env('APP_URL');
            $flutterwaveKey = GetpaymentMethod('flutterwave_secretkey');

            $price = $request->input('total_price_amount') ?? $request->input('price');
            $plan_id = $request->input('plan_id');

            // Ensure price is in the correct format
            $amount = number_format($price, 2, '.', '');

            $data = [
                'tx_ref' => 'FLW_' . uniqid() . time(),
                'amount' => $amount,
                'currency' => strtoupper(GetcurrentCurrency()),
                'payment_type' => "mobile_money_ghana",
                'redirect_url' => route('payment.success', ['gateway' => 'flutterwave']),
                'customer' => [
                    'email' => auth()->user()->email,
                    'name' => auth()->user()->name,
                    'phonenumber' => auth()->user()->phone ?? ''
                ],
                'meta' => [
                    'plan_id' => $plan_id
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
                return redirect()->back()->withErrors(__('messages.payment_failed'));
            }

            $responseBody = $response->json();

            if (isset($responseBody['status']) && $responseBody['status'] === 'success') {
                // Redirect directly to Flutterwave checkout page
                return redirect($responseBody['data']['link']);
            }

            return redirect()->back()->withErrors($responseBody['message'] ?? __('messages.payment_failed'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(__('messages.payment_failed'));
        }
    }

    protected function CinetPayment(Request $request)
    {
        $baseURL = env('APP_URL');
        $cinetApiKey = GetpaymentMethod('cinet_Secret_key');
        $price = $request->input('total_price_amount') ?? $request->input('price');
        $plan_id = $request->input('plan_id');
        $priceInCents = $price * 100;

        $data = [
            'amount' => $priceInCents,
            'currency' => 'USD',
            'plan_id' => $plan_id,
            'callback_url' => $baseURL . '/payment/success?gateway=cinet',
            'user_email' => auth()->user()->email,
        ];
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $cinetApiKey,
        ])->post('https://api.cinet.com/payment', $data);

        $responseBody = $response->json();

        if ($response->successful() && isset($responseBody['payment_url'])) {
            return redirect($responseBody['payment_url']);
        } else {
            return redirect()->back()->withErrors(__('messages.payment_failed') . ($responseBody['message'] ?? 'Unknown error'));
        }
    }

    protected function SadadPayment(Request $request)
    {
        $baseURL = env('APP_URL');
        $price = $request->input('total_price_amount') ?? $request->input('price');
        $plan_id = $request->input('plan_id');
        $response = $this->makeSadadPaymentRequest($price, $plan_id);
        if ($response->isSuccessful()) {
            return redirect($response->redirect_url);
        } else {
            return redirect()->back()->withErrors(__('messages.payment_failed') . $response->message);
        }
    }

    protected function AirtelPayment(Request $request)
    {
        $baseURL = env('APP_URL');
        $price = $request->input('total_price_amount') ?? $request->input('price');
        $plan_id = $request->input('plan_id');

        $response = $this->makeAirtelPaymentRequest($price, $plan_id);

        if ($response->isSuccessful()) {
            return redirect($response->redirect_url);
        } else {
            return redirect()->back()->withErrors(__('messages.payment_failed') . $response->message);
        }
    }

    protected function PhonePePayment(Request $request)
    {
        $baseURL = env('APP_URL');
        $price = $request->input('total_price_amount') ?? $request->input('price');
        $plan_id = $request->input('plan_id');

        $response = $this->makePhonePePaymentRequest($price, $plan_id);

        if ($response->isSuccessful()) {
            return redirect($response->payment_url);
        } else {
            return redirect()->back()->withErrors(__('messages.payment_failed') . $response->message);
        }
    }

    protected function MidtransPayment(Request $request)
    {
        Config::$serverKey = GetpaymentMethod(' midtrans_client_id');

        $price = $request->input('total_price_amount') ?? $request->input('price');
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
            return redirect()->back()->withErrors(__('messages.payment_failed') . $e->getMessage());
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
                    'return_url' => $baseURL . '/payment/success?gateway=paypal',
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

        // Calculate base amount with discount if applicable
        if ($plan->has_discount) {
            if ($plan->discount_type === 'percentage') {
                $discount_amount = $plan->price * ($plan->discount_value / 100);
            } else {
                $discount_amount = $plan->discount_value;
            }
            $base_amount = $plan->price - $discount_amount;
        } else {
            $base_amount = $plan->price;
            $discount_amount = 0;
        }

        // Calculate tax
        $totalTax = 0;
        foreach ($taxes as $tax) {
            if (strtolower($tax->type) == 'fixed') {
                $totalTax += $tax->value;
            } elseif (strtolower($tax->type) == 'percentage') {
                $totalTax += ($base_amount * $tax->value) / 100;
            }
        }

        // Calculate final amount
        $amount = $base_amount + $totalTax;

        // Create the subscription with correct amounts
        $subscription = Subscription::create([
            'plan_id' => $plan_id,
            'user_id' => auth()->id(),
            'start_date' => now(),
            'end_date' => $end_date,
            'status' => 'active',
            'amount' => $base_amount,
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
            'discount_data' => isset($promotions) ? json_encode($promotions) : null,
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
                    Log::error($e->getMessage());
                }
            }
        }





        return redirect('/')->with('success', __('messages.payment_success'));
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


            return redirect('/')->with('error', __('messages.payment_failed') . $e->getMessage());
        }
    }

    protected function handleRazorpaySuccess(Request $request)
    {
        $paymentId = $request->input('razorpay_payment_id');
        $razorpayOrderId = session('razorpay_order_id');
        $plan_id = $request->input('plan_id');


        // Initialize Razorpay API
        $razorpayKey = GetpaymentMethod('razorpay_publickey');
        $razorpaySecret = GetpaymentMethod('razorpay_secretkey');
        $api = new \Razorpay\Api\Api($razorpayKey, $razorpaySecret);

        $payment = $api->payment->fetch($paymentId);

        if ($payment['status'] == 'captured') {

            return $this->handlePaymentSuccess($plan_id, $payment['amount'] / 100, 'razorpay', $paymentId);
        } else {
            return redirect('/')->with('error', __('messages.payment_failed') . $payment['error_description']);
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
            return redirect('/')->with('error', __('messages.payment_failed') . $responseBody['message']);
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
                return redirect('/')->with('error', 'Payment not approved.');
            }
        } catch (\Exception $e) {
            return redirect('/')->with('error', __('messages.payment_failed') . $e->getMessage());
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
            return redirect('/')->with('error', __('messages.payment_failed') . $responseBody['message']);
        }
    }
    protected function handleCinetSuccess(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $paymentStatus = $request->input('status');
        $planId = $request->input('plan_id');

        if ($paymentStatus !== 'success') {
            return redirect('/')->with('error', __('messages.payment_failed'));
        }

        return $this->handlePaymentSuccess($planId, $request->input('amount'), 'cinet', $transactionId);
    }

    protected function handleSadadSuccess(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $paymentStatus = $request->input('status');
        $plan_id = $request->input('plan_id');

        if ($paymentStatus !== 'success') {
            return redirect('/')->with('error', __('messages.payment_failed'));
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
            'callback_url' => env('APP_URL') . '/payment/success?gateway=sadad',
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
            return redirect('/')->with('error', __('messages.payment_failed'));
        }

        return $this->handlePaymentSuccess($planId, $request->input('amount'), 'airtel', $transactionId);
    }

    protected function handlePhonePeSuccess(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $paymentStatus = $request->input('status');
        $planId = $request->input('plan_id');

        if ($paymentStatus !== 'success') {
            return redirect('/')->with('error', __('messages.payment_failed'));
        }

        return $this->handlePaymentSuccess($planId, $request->input('amount'), 'phonepe', $transactionId);
    }

    protected function makePhonePePaymentRequest($price, $plan_id)
    {
        $url = 'https://api.phonepe.com/apis/hermes/pg/v1/pay';
        $data = [
            'amount' => $price,
            'plan_id' => $plan_id,
            'callbackUrl' => env('APP_URL') . '/payment/success?gateway=phonepe',
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
            'callback_url' => env('APP_URL') . '/payment/success?gateway=airtel',
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

        if ($subscription) {
            $planDetails = json_decode($subscription->plan_details);
            $subscription->planDetails = $planDetails;
        }
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
        $pdf = Pdf::loadHTML($view);
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
        // Return the generated PDF as a download
        // return $pdf->download('invoice.pdf');
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
