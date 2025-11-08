<?php

namespace Modules\VendorWebsite\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingTransaction;
use Stripe\StripeClient;
use Modules\VendorWebsite\Http\Controllers\Backend\ProductController;
use Modules\Product\Http\Controllers\Backend\API\OrdersController;
use Modules\Product\Http\Requests\OrderRequest;


class PaymentController extends Controller
{
    // Show payment method selection and summary
    public function checkout(Request $request)
    {
        $price = $request->input('price');
        $methods = ['stripe', 'paypal', 'razorpay', 'cash'];
        return view('payment.checkout', compact('price', 'methods'));
    }

    // Handle payment initiation
    public function ProductPaymentProccess(Request $request)
    {
        $method = strtolower($request->input('payment_method'));
        switch ($method) {
            case 'stripe':
                return $this->stripeCheckout($request);
            case 'paypal':
                return $this->paypalCheckout($request);
            case 'razorpay':
                return $this->razorpayCheckout($request);
            case 'paystack':
                return $this->paystackCheckout($request);
            case 'flutterwave':
                return $this->flutterwaveCheckout($request);
            case 'cash':
                return $this->cashCheckout($request);
            default:
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Invalid payment method.'], 400);
                }
                return back()->withErrors('Invalid payment method.');
        }
    }

    // Stripe checkout
    public function stripeCheckout(Request $request)
    {
        $stripeSecret = getVendorSetting('stripe_secretkey');
        $stripepublic = getVendorSetting('stripe_publickey');
        $currency = GetVendorcurrentCurrency();


        $productController = new ProductController();
        $summary = $productController->cartSummary($request);

        $data = $summary->getData(true);
        $totalWithDelivery = $data['total_with_delivery'] ?? 0;

        if (!$stripeSecret) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe secret key not configured.'
            ], 500);
        }

        $amount = $totalWithDelivery;

        $stripe = new \Stripe\StripeClient($stripeSecret);

        try {
            $session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => 'Product Payment',
                        ],
                        'unit_amount' => intval($amount * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('product.payment.stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
                // 'cancel_url' => route('payment.cancel', ['gateway' => 'stripe']),
                'metadata' => [
                    'shipping_address_id'      => $request->input('shipping_address_id'),
                    'billing_address_id'       => $request->input('billing_address_id'),
                    'chosen_logistic_zone_id'  => $request->input('chosen_logistic_zone_id'),
                    'payment_method'           => $request->input('payment_method'),
                    'shipping_delivery_type'   => $request->input('shipping_delivery_type'),
                    'payment_status'           => $request->input('payment_status'),
                    'amount'                   => $amount,
                ],
            ]);

            return response()->json(['success' => true, 'redirect' => $session->url]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function productStripeSuccess(Request $request)
    {
        $sessionId = $request->input('session_id');
        $stripeSecret = getVendorsetting('stripe_secretkey');

        if (!$stripeSecret) {
            return redirect()->route('payment.checkout')->with('error', 'Stripe secret key not configured.');
        }

        $stripe = new \Stripe\StripeClient($stripeSecret);

        try {
            $session = $stripe->checkout->sessions->retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return redirect()->route('payment.checkout')->with('error', 'Payment was not completed successfully.');
            }

            $meta = $session->metadata ?? [];

            $request_data = new Request([
                'employee_id' => $meta['employee_id'] ?? null,
                'shipping_address_id'      => $meta['shipping_address_id'] ?? null,
                'billing_address_id'       => $meta['billing_address_id'] ?? null,
                'chosen_logistic_zone_id'  => $meta['chosen_logistic_zone_id'] ?? null,
                'payment_method'           => $meta['payment_method'] ?? null,
                'shipping_delivery_type'   => $meta['shipping_delivery_type'] ?? null,
                'payment_status'           => 'paid',
                'amount'                   => $meta['amount'] ?? null,

            ]);


            $response = $this->afterPaymentSuccess($request_data, 'stripe', $session->payment_intent);

            // Handle redirect response
            if ($response instanceof \Illuminate\Http\RedirectResponse) {
                return $response;
            }

            // Handle JSON response from OrdersController
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                $responseData = $response->getData(true); // Get array data from JsonResponse

                if (isset($responseData['status']) && $responseData['status'] === true) {
                    return redirect()->route('myorder')->with('success', $responseData['message'] ?? 'Booking and payment successful!');
                }
                return redirect()->back()->with('error', $responseData['message'] ?? 'Something went wrong with your order. Please try again.');
            }

            // Fallback for array response
            $responseData = is_array($response) ? $response : [];

            if (isset($responseData['status']) && $responseData['status'] === true) {
                return redirect()->route('myorder')->with('success', $responseData['message'] ?? 'Booking and payment successful!');
            }
            return redirect()->back()->with('error', $responseData['message'] ?? 'Something went wrong with your order. Please try again.');
        } catch (\Exception $e) {
            return redirect()->route('payment.checkout')->with('error', 'Stripe verification error: ' . $e->getMessage());
        }
    }

    // After payment is successful, create booking and payment records
    protected function afterPaymentSuccess(Request $request, $gateway, $transactionId)
    {

        $ordersController = new OrdersController();

        $orderRequest = new OrderRequest($request->all());



        $orderData = $ordersController->store($orderRequest);

        return $orderData;
    }


    public function razorpayCheckout(Request $request)
    {

        $razorpayKey = getVendorSetting('razorpay_publickey');
        $razorpaySecret = getVendorSetting('razorpay_secretkey');
        $currency = GetVendorcurrentCurrency();
        $supportedCurrencies = ['INR', 'USD', 'EUR', 'GBP', 'SGD', 'AED'];
        $formattedCurrency = strtoupper($currency);

        $productController = new ProductController();
        $summary = $productController->cartSummary($request);

        $data = $summary->getData(true);
        $totalWithDelivery = $data['total_with_delivery'] ?? 0;

        try {


            $roundedPrice = round($totalWithDelivery, 2);

            $amount = intval($roundedPrice * 100);

            if ($amount <= 0) {
                return response()->json([
                    'error' => 'Invalid amount. Amount must be greater than 0.'
                ], 400);
            }

            $orderData = [
                'receipt'         => 'order_' . uniqid(),
                'amount'          => $amount,
                'currency'        => $formattedCurrency,
                'payment_capture' => 1
            ];

            $api = new \Razorpay\Api\Api($razorpayKey, $razorpaySecret);
            $order = $api->order->create($orderData);

            return response()->json([
                'key' => $razorpayKey,
                'amount' => $amount,
                'currency' => $formattedCurrency,
                'name' => config('app.name'),
                'description' => 'Order Payment',
                'order_id' => $order['id'],
                'success_url' => route('product.razorpay.success', [
                    'shipping_address_id'      => $request->input('shipping_address_id'),
                    'billing_address_id'       => $request->input('billing_address_id'),
                    'chosen_logistic_zone_id'  => $request->input('chosen_logistic_zone_id'),
                    'payment_method'           => $request->input('payment_method'),
                    'shipping_delivery_type'   => $request->input('shipping_delivery_type'),
                    'payment_status'           =>  'paid',
                    'amount'                   => $amount,
                ]),
                'prefill' => [
                    'name' => Auth::user()->name ?? '',
                    'email' => Auth::user()->email ?? '',
                    'contact' => Auth::user()->phone ?? ''
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Razorpay error: ' . $e->getMessage()
            ], 400);
        }
    }

    public function paystackCheckout(Request $request)
    {
        \Log::info('Paystack checkout method called', ['request_data' => $request->all()]);

        try {
            $paystackPublicKey = getVendorSetting('paystack_publickey');
            $paystackSecret = getVendorSetting('paystack_secretkey');

            \Log::info('Paystack settings', [
                'has_public_key' => !empty($paystackPublicKey),
                'has_secret_key' => !empty($paystackSecret)
            ]);
            $currency = GetVendorcurrentCurrency();

            if (empty($paystackPublicKey) || empty($paystackSecret)) {
                return response()->json([
                    'error' => 'Paystack configuration missing. Please contact support.'
                ], 400);
            }


            $productController = new ProductController();
            $summary = $productController->cartSummary($request);



            // Get order data
            $orderData = $summary->getData(true);
            $amount = $orderData['total_with_delivery'];

            // Generate unique reference
            $reference = 'paystack_' . uniqid() . '_' . time();

            // Get user data
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'error' => 'User authentication required.'
                ], 401);
            }

            // Return data for Paystack initialization
            $responseData = [
                'success' => true,
                'public_key' => $paystackPublicKey,
                'reference' => $reference,
                'amount' => $amount,
                'currency' => $currency,
                'email' => $user->email,
                'order_id' => null, // Will be created after payment success
                'metadata' => [
                    'shipping_address_id' => $request->input('shipping_address_id'),
                    'billing_address_id' => $request->input('billing_address_id'),
                    'chosen_logistic_zone_id' => $request->input('chosen_logistic_zone_id'),
                    'shipping_delivery_type' => $request->input('shipping_delivery_type'),
                    'payment_status' => 'paid',
                    'amount' => $amount,
                ]
            ];

            \Log::info('Paystack checkout response', ['response_data' => $responseData]);

            return response()->json($responseData);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Paystack error: ' . $e->getMessage()
            ], 400);
        }
    }

    public function flutterwaveCheckout(Request $request)
    {
        try {
            $flutterwavePublicKey = getVendorSetting('flutterwave_publickey');
            $flutterwaveSecret = getVendorSetting('flutterwave_secretkey');
            $currency = GetVendorcurrentCurrency();

            if (empty($flutterwavePublicKey) || empty($flutterwaveSecret)) {
                return response()->json([
                    'error' => 'Flutterwave configuration missing. Please contact support.'
                ], 400);
            }

            $productController = new ProductController();
            $summary = $productController->cartSummary($request);

            // Get order data
            $orderData = $summary->getData(true);
            $amount = $orderData['total_with_delivery'];

            // Generate unique transaction reference
            $txRef = 'FLW-' . uniqid() . '-' . time();

            // Get user data
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'error' => 'User authentication required.'
                ], 401);
            }

            // Return data for Flutterwave initialization
            return response()->json([
                'success' => true,
                'public_key' => $flutterwavePublicKey,
                'tx_ref' => $txRef,
                'amount' => $amount,
                'currency' => $currency,
                'country' => 'NG',
                'payment_options' => 'card',
                'customer' => [
                    'email' => $user->email,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'phonenumber' => $user->mobile ?? ''
                ],
                'customizations' => [
                    'title' => 'Product Order Payment',
                    'description' => 'Payment for product order',
                    'logo' => ''
                ],
                'meta' => [
                    'shipping_address_id' => $request->input('shipping_address_id'),
                    'billing_address_id' => $request->input('billing_address_id'),
                    'chosen_logistic_zone_id' => $request->input('chosen_logistic_zone_id'),
                    'shipping_delivery_type' => $request->input('shipping_delivery_type'),
                    'payment_status' => 'paid',
                    'amount' => $amount,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Flutterwave error: ' . $e->getMessage()
            ], 400);
        }
    }

    public function productRazorpaySuccess(Request $request)
    {

        // Log the received data for debugging

        $razorpayKey = getVendorSetting('razorpay_publickey');
        $razorpaySecret = getVendorSetting('razorpay_secretkey');
        $paymentId = $request->input('razorpay_payment_id');
        $orderId = $request->input('razorpay_order_id');

        if (empty($razorpayKey) || empty($razorpaySecret) || empty($paymentId)) {
            \Log::error('Missing Razorpay parameters', [
                'has_key' => !empty($razorpayKey),
                'has_secret' => !empty($razorpaySecret),
                'has_payment_id' => !empty($paymentId),
                'request_data' => $request->all()
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Missing required payment parameters.'], 400);
            }
            return redirect('/')->with('error', 'Missing required payment parameters.');
        }

        try {
            $api = new \Razorpay\Api\Api($razorpayKey, $razorpaySecret);
            $payment = $api->payment->fetch($paymentId);



            if ($payment['status'] === 'authorized') {
                $payment = $payment->capture([
                    'amount' => $payment['amount'],
                    'currency' => $payment['currency'],
                ]);
            }

            if ($payment['status'] === 'captured') {


                $request_data = new Request([
                    'shipping_address_id'      => $request->input('shipping_address_id'),
                    'billing_address_id'       => $request->input('billing_address_id'),
                    'chosen_logistic_zone_id'  => $request->input('chosen_logistic_zone_id'),
                    'payment_method'           => $request->input('payment_method'),
                    'shipping_delivery_type'   => $request->input('shipping_delivery_type'),
                    'payment_status'           => 'paid',
                    'amount'                   => $payment['amount'] / 100, // Convert from paise to rupees
                ]);

                $result = $this->afterPaymentSuccess($request_data, 'razorpay', $paymentId);

                // Handle redirect response
                if ($result instanceof \Illuminate\Http\RedirectResponse) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Payment successful',
                            'redirect' => route('myorder')
                        ]);
                    }
                    return $result;
                }

                // Handle JSON response from OrdersController
                if ($result instanceof \Illuminate\Http\JsonResponse) {
                    $responseData = $result->getData(true); // Get array data from JsonResponse

                    if ($request->expectsJson()) {
                        if (isset($responseData['status']) && $responseData['status'] === true) {
                            return response()->json([
                                'success' => true,
                                'message' => $responseData['message'] ?? 'Payment successful',
                                'redirect' => route('myorder')
                            ]);
                        }
                        return response()->json([
                            'success' => false,
                            'message' => $responseData['message'] ?? 'Something went wrong with your order'
                        ], 400);
                    }

                    if (isset($responseData['status']) && $responseData['status'] === true) {
                        return redirect()->route('myorder')->with('success', $responseData['message'] ?? 'Booking and payment successful!');
                    }
                    return redirect()->back()->with('error', $responseData['message'] ?? 'Something went wrong with your order. Please try again.');
                }

                // Fallback for array response
                $responseData = is_array($result) ? $result : [];

                // Return JSON response for AJAX requests
                if ($request->expectsJson()) {
                    if (isset($responseData['status']) && $responseData['status'] === true) {
                        return response()->json([
                            'success' => true,
                            'message' => $responseData['message'] ?? 'Payment successful',
                            'redirect' => route('myorder')
                        ]);
                    }
                    return response()->json([
                        'success' => false,
                        'message' => $responseData['message'] ?? 'Something went wrong with your order'
                    ], 400);
                }

                if (isset($responseData['status']) && $responseData['status'] === true) {
                    return redirect()->route('myorder')->with('success', $responseData['message'] ?? 'Booking and payment successful!');
                }
                return redirect()->back()->with('error', $responseData['message'] ?? 'Something went wrong with your order. Please try again.');
            }


            if ($request->expectsJson()) {
                return response()->json(['error' => 'Payment verification failed. Status: ' . $payment['status']], 400);
            }
            return redirect('/')->with('error', 'Payment verification failed. Status: ' . $payment['status']);
        } catch (\Exception $e) {


            if ($request->expectsJson()) {
                return response()->json(['error' => 'Payment processing error: ' . $e->getMessage()], 500);
            }
            return redirect('/')->with('error', 'Payment processing error: ' . $e->getMessage());
        }
    }



    // PayPal checkout
    public function paypalCheckout(Request $request)
    {
        $price = $request->input('price');
        $paypalUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_xclick&business=' . urlencode(config('services.paypal.email')) . '&amount=' . $price . '&currency_code=USD&item_name=Booking+Payment&return=' . urlencode(route('payment.success', ['gateway' => 'paypal'])) . '&cancel_return=' . urlencode(route('payment.cancel', ['gateway' => 'paypal'])) . '&notify_url=' . urlencode(route('payment.success', ['gateway' => 'paypal'])) . '&no_shipping=1';
        return redirect($paypalUrl);
    }

    // Cash checkout
    public function cashCheckout(Request $request)
    {
        try {
            // If user info is provided, find or create user (as in QuickBooking)
            $userRequest = $request->user;
            $user = null;
            if ($userRequest && isset($userRequest['email'])) {
                $user = \App\Models\User::where('email', $userRequest['email'])->first();
                if (!isset($user)) {
                    $userRequest['password'] = \Hash::make('12345678');
                    $user = \App\Models\User::create($userRequest);
                    $roles = ['user'];
                    $user->syncRoles($roles);
                    \Artisan::call('cache:clear');
                    event(new \App\Events\Backend\UserCreated($user));
                    try {
                        $user->notify(new \App\Notifications\UserAccountCreated(['password' => '12345678']));
                    } catch (\Exception $e) {
                    }
                }
            }
            // Build booking data
            $bookingData = $request->booking ?? $request->all();
            if ($user) {
                $bookingData['user_id'] = $user->id;
                $bookingData['created_by'] = $user->id;
                $bookingData['updated_by'] = $user->id;
            }
            $booking = \Modules\Booking\Models\Booking::create($bookingData);
            // If you have a method to update services, call it here (as in QuickBooking)
            if (method_exists($this, 'updateBookingService') && isset($bookingData['services'])) {
                $this->updateBookingService($bookingData['services'], $booking->id);
            }
            // Optionally send notification (as in QuickBooking)
            try {
                $notify_type = 'cancel_booking';
                $messageTemplate = 'New booking #[[booking_id]] has been booked.';
                $notify_message = str_replace('[[booking_id]]', $booking->id, $messageTemplate);
                if (method_exists($this, 'sendNotificationOnBookingUpdate')) {
                    $this->sendNotificationOnBookingUpdate($notify_type, $notify_message, $booking);
                }
            } catch (\Exception $e) {
            }
            // Create payment record
            \Modules\Booking\Models\BookingTransaction::create([
                'booking_id' => $booking->id,
                'payment_method' => 'cash',
                'amount' => $bookingData['price'] ?? $bookingData['amount'] ?? 0,
                'status' => 1,
                'transaction_id' => 'cash_txn_id',
            ]);
            // Always return JSON for AJAX/fetch
            if ($request->ajax() || $request->wantsJson() || $request->isJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Booking and payment successful!',
                    'booking_id' => $booking->id,
                    'data' => $booking
                ]);
            }
            // For normal form, redirect
            return redirect()->route('index')->with('success', 'Booking and payment successful!');
        } catch (\Illuminate\Session\TokenMismatchException $e) {
            // CSRF error
            return response()->json(['success' => false, 'message' => 'Session expired. Please refresh and try again.'], 419);
        } catch (\Exception $e) {

            if ($request->ajax() || $request->wantsJson() || $request->isJson() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Booking/payment failed: ' . $e->getMessage()], 500);
            }
            return back()->withErrors('Booking/payment failed: ' . $e->getMessage());
        }
    }

    // Success callback
    public function success(Request $request, $gateway)
    {
        if ($gateway === 'stripe') {
            $sessionId = $request->input('session_id');
            $stripeSecret = config('services.stripe.secret');
            $stripe = new StripeClient($stripeSecret);
            try {
                $session = $stripe->checkout->sessions->retrieve($sessionId);
                $meta = $session->metadata ?? [];
                $fakeRequest = new Request([
                    'employee_id' => $meta['employee_id'] ?? null,
                    'branch_id' => $meta['branch_id'] ?? null,
                    'date' => $meta['date'] ?? null,
                    'time' => $meta['time'] ?? null,
                    'services' => json_decode($meta['services'] ?? '[]', true),
                    'price' => $meta['price'] ?? null,
                ]);
                $response = $this->afterPaymentSuccess($fakeRequest, 'stripe', $session->payment_intent);

                // Handle redirect response
                if ($response instanceof \Illuminate\Http\RedirectResponse) {
                    return $response;
                }

                // Handle JSON response from OrdersController
                if ($response instanceof \Illuminate\Http\JsonResponse) {
                    $responseData = $response->getData(true); // Get array data from JsonResponse

                    if (isset($responseData['status']) && $responseData['status'] === true) {
                        return redirect()->route('myorder')->with('success', $responseData['message'] ?? 'Booking and payment successful!');
                    }
                    return redirect()->back()->with('error', $responseData['message'] ?? 'Something went wrong with your order. Please try again.');
                }

                // Fallback for array response
                $responseData = is_array($response) ? $response : [];

                if (isset($responseData['status']) && $responseData['status'] === true) {
                    return redirect()->route('myorder')->with('success', $responseData['message'] ?? 'Booking and payment successful!');
                }
                return redirect()->back()->with('error', $responseData['message'] ?? 'Something went wrong with your order. Please try again.');
            } catch (\Exception $e) {
                return back()->withErrors('Stripe verification error: ' . $e->getMessage());
            }
        }
        // Razorpay and PayPal can be handled similarly (implement as needed)
        return view('payment.success', compact('gateway'));
    }

    // Cancel/failure callback
    public function cancel(Request $request, $gateway)
    {
        return view('payment.failure', compact('gateway'));
    }


    public function productStripeCheckout(Request $request)
    {
        $stripeSecret = getVendorSetting('stripe_secretkey');
        $stripepublic = getVendorSetting('stripe_publickey');
        $currency = GetVendorcurrentCurrency();

        $productController = new ProductController();
        $summary = $productController->cartSummary($request);

        $data = $summary->getData(true);

        $totalWithDelivery = $data['total_with_delivery'] ?? 0;

        if (!$stripeSecret) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe secret key not configured.'
            ], 500);
        }
        $amount = $totalWithDelivery;

        $stripe = new \Stripe\StripeClient($stripeSecret);

        try {
            $session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => 'Product Payment',
                        ],
                        'unit_amount' => intval($amount * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('product.payment.stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.cancel', ['gateway' => 'stripe']),
            ]);

            return response()->json(['success' => true, 'redirect' => $session->url]);
        } catch (\Exception $e) {

            return response()->json(['success' => false, 'message' => 'Stripe error: ' . $e->getMessage()], 500);
        }
    }

    public function productRazorpayCheckout(Request $request)
    {

        $razorpayKey = \DB::table('settings')->where('name', 'razorpay_publickey')->value('val');
        $razorpaySecret = \DB::table('settings')->where('name', 'razorpay_secretkey')->value('val');
        $currency = 'INR';
        $amount = $request->input('amount');

        if (!$razorpayKey || !$razorpaySecret) {

            return response()->json(['success' => false, 'message' => 'Razorpay key/secret not configured.'], 500);
        }
        if (!is_numeric($amount) || $amount < 1) {

            return response()->json(['success' => false, 'message' => 'Order amount must be a valid number and at least ₹1. Received: ' . $amount], 400);
        }
        try {
            $api = new \Razorpay\Api\Api($razorpayKey, $razorpaySecret);
            $order = $api->order->create([
                'receipt' => 'product_order_' . uniqid(),
                'amount' => round($amount * 100),
                'currency' => $currency,
                'payment_capture' => 1
            ]);

            if (
                $request->ajax() ||
                $request->wantsJson() ||
                $request->isJson() ||
                $request->expectsJson() ||
                strpos($request->header('Accept'), 'application/json') !== false
            ) {
                return response()->json([
                    'success' => true,
                    'order' => $order,
                    'razorpayKey' => $razorpayKey,
                    'amount' => $amount,
                    'currency' => $currency,
                    'prefill' => [
                        'name' => Auth::user()->name ?? '',
                        'email' => Auth::user()->email ?? '',
                        'contact' => Auth::user()->phone ?? ''
                    ]
                ]);
            }
            // Otherwise, return the view
            return view('payment.razorpay', [
                'order' => $order,
                'razorpayKey' => $razorpayKey,
                'amount' => $amount,
                'currency' => $currency,
            ]);
        } catch (\Exception $e) {

            if (
                $request->ajax() ||
                $request->wantsJson() ||
                $request->isJson() ||
                $request->expectsJson() ||
                strpos($request->header('Accept'), 'application/json') !== false
            ) {
                return response()->json(['success' => false, 'message' => 'Razorpay error: ' . $e->getMessage()], 500);
            }
            return back()->withErrors('Razorpay error: ' . $e->getMessage());
        }
    }

    public function productSuccess(Request $request)
    {
        $sessionId = $request->input('session_id');
        $stripeSecret = \DB::table('settings')->where('name', 'stripe_secrectkey')->value('val');
        if (empty($stripeSecret)) {
            $stripeSecret = \DB::table('settings')->where('name', 'stripe_secretkey')->value('val');
        }
        if (empty($stripeSecret) || !is_string($stripeSecret)) {
            return back()->withErrors('Stripe secret key is not configured.');
        }
        $stripe = new \Stripe\StripeClient($stripeSecret);
        try {
            $session = $stripe->checkout->sessions->retrieve($sessionId);
            $meta = $session->metadata ?? [];
            // You can store order/payment info here as needed
            // Example: mark product order as paid, etc.
            // $meta['product_id'], $meta['user_id'], etc. if you set them in metadata
            return redirect()->route('myorder')->with('success', 'Product payment successful!');
        } catch (\Exception $e) {
            return back()->withErrors('Stripe verification error: ' . $e->getMessage());
        }
    }




    public function productRazorpaySuccess1(Request $request)
    {


        $razorpayPaymentId = $request->input('razorpay_payment_id');
        $razorpayOrderId = $request->input('razorpay_order_id');

        if (empty($razorpayPaymentId) || empty($razorpayOrderId)) {

            return back()->withErrors('Payment verification failed: Missing payment information.');
        }

        $user = Auth::user();
        if (!$user) {

            return back()->withErrors('User not authenticated.');
        }

        $now = now();
        $cartItems = \Modules\Product\Models\Cart::where('user_id', $user->id)->get();



        if ($cartItems->isEmpty()) {

            return back()->withErrors('Cart is empty. Please add items to cart before checkout.');
        }

        try {
            // Create order
            $order = \Modules\Product\Models\Order::create([
                'order_group_id' => 0,
                'user_id' => $user->id,
                'guest_user_id' => null,
                'location_id' => null,
                'delivery_status' => 'pending',
                'payment_status' => 'paid',
                'applied_coupon_code' => null,
                'coupon_discount_amount' => 0,
                'admin_earning_percentage' => 0,
                'total_admin_earnings' => 0,
                'logistic_id' => null,
                'logistic_name' => null,
                'pickup_or_delivery' => 'delivery',
                'pickup_hub_id' => null,
                'shipping_cost' => 0,
                'tips_amount' => 0,
                'reward_points' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);


            // Create order items
            $orderItemsCreated = 0;
            foreach ($cartItems as $item) {
                $product = \Modules\Product\Models\Product::find($item->product_id);
                $variation = \Modules\Product\Models\ProductVariation::where('product_id', $item->product_id)->first();

                if ($product && $variation) {
                    // Calculate unit price with discount
                    $unitPrice = $product->max_price;
                    if ($product->discount_type === 'percent' && $product->discount_value > 0) {
                        $unitPrice = $unitPrice - ($unitPrice * $product->discount_value / 100);
                    } elseif ($product->discount_type === 'fixed' && $product->discount_value > 0) {
                        $unitPrice = $unitPrice - $product->discount_value;
                    }

                    $orderItem = \Modules\Product\Models\OrderItem::create([
                        'order_id' => $order->id,
                        'product_variation_id' => $variation->id,
                        'qty' => $item->qty,
                        'location_id' => null,
                        'unit_price' => $unitPrice,
                        'total_tax' => 0,
                        'total_price' => $unitPrice * $item->qty,
                        'reward_points' => 0,
                        'is_refunded' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $orderItemsCreated++;
                }
            }



            // Clear the cart
            $cartDeleted = \Modules\Product\Models\Cart::where('user_id', $user->id)->delete();



            return redirect()->route('myorder')->with('success', 'Product payment successful!');
        } catch (\Exception $e) {
            Log::error('Product payment processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return back()->withErrors('Payment succeeded but server did not record it. Please contact support. Error: ' . $e->getMessage());
        }
    }

    // Product cash checkout method
    public function productCashCheckout(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return back()->withErrors('User not authenticated.');
            }

            $now = now();
            $cartItems = \Modules\Product\Models\Cart::where('user_id', $user->id)->get();
            if ($cartItems->isEmpty()) {
                return back()->withErrors('Cart is empty.');
            }



            // Calculate order totals
            $subtotal = 0;
            $discount = 0;
            $deliveryCharge = 0;
            foreach ($cartItems as $item) {
                $product = $item->product;
                $price = $product->max_price;
                if ($product->discount_type === 'percent' && $product->discount_value > 0) {
                    $itemDiscount = ($price * $product->discount_value / 100) * $item->qty;
                    $discount += $itemDiscount;
                    $price = $price - ($price * $product->discount_value / 100);
                } elseif ($product->discount_type === 'fixed' && $product->discount_value > 0) {
                    $itemDiscount = $product->discount_value * $item->qty;
                    $discount += $itemDiscount;
                    $price = $price - $product->discount_value;
                }
                $subtotal += $price * $item->qty;
            }

            // Get addressId from session or request
            $addressId = session('checkout_address_id') ?? $request->address_id ?? null;
            $orderGroup = null;
            if ($addressId) {
                $orderGroup = \Modules\Product\Models\OrderGroup::create([
                    'user_id' => $user->id,
                    'shipping_address_id' => $addressId,
                    'billing_address_id' => $addressId,
                    'sub_total_amount' => $subtotal,
                    'total_shipping_cost' => $deliveryCharge,
                    'grand_total_amount' => $subtotal + $deliveryCharge,
                ]);
            }

            // Create order
            $order = \Modules\Product\Models\Order::create([
                'order_group_id' => $orderGroup ? $orderGroup->id : 0,
                'user_id' => $user->id,
                'guest_user_id' => null,
                'location_id' => null,
                'delivery_status' => 'pending',
                'payment_status' => 'paid',
                'applied_coupon_code' => null,
                'coupon_discount_amount' => $discount,
                'admin_earning_percentage' => 0,
                'total_admin_earnings' => 0,
                'logistic_id' => null,
                'logistic_name' => null,
                'pickup_or_delivery' => 'delivery',
                'pickup_hub_id' => null,
                'shipping_cost' => $deliveryCharge,
                'tips_amount' => 0,
                'reward_points' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Create order items
            foreach ($cartItems as $item) {
                $product = \Modules\Product\Models\Product::find($item->product_id);
                $variation = \Modules\Product\Models\ProductVariation::where('product_id', $item->product_id)->first();

                if ($product && $variation) {
                    // Calculate unit price with discount
                    $unitPrice = $product->max_price;
                    if ($product->discount_type === 'percent' && $product->discount_value > 0) {
                        $unitPrice = $unitPrice - ($unitPrice * $product->discount_value / 100);
                    } elseif ($product->discount_type === 'fixed' && $product->discount_value > 0) {
                        $unitPrice = $unitPrice - $product->discount_value;
                    }

                    \Modules\Product\Models\OrderItem::create([
                        'order_id' => $order->id,
                        'product_variation_id' => $variation->id,
                        'qty' => $item->qty,
                        'location_id' => null,
                        'unit_price' => $unitPrice,
                        'total_tax' => 0,
                        'total_price' => $unitPrice * $item->qty,
                        'reward_points' => 0,
                        'is_refunded' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            // Clear the cart
            \Modules\Product\Models\Cart::where('user_id', $user->id)->delete();

            \DB::commit();

            if ($request->ajax() || $request->wantsJson() || $request->isJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product order successful!',
                    'order_id' => $order->id,
                    'data' => $order
                ]);
            }

            return redirect()->route('myorder')->with('success', 'Product order successful!');
        } catch (\Exception $e) {
            \DB::rollBack();

            if ($request->ajax() || $request->wantsJson() || $request->isJson() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Order failed: ' . $e->getMessage()], 500);
            }

            return back()->withErrors('Order failed: ' . $e->getMessage());
        }
    }

    public function productPaystackSuccess(Request $request)
    {
        // Log the received data for debugging

        $paystackSecret = getVendorSetting('paystack_secretkey');
        $reference = $request->input('reference');
        $transactionId = $request->input('transaction_id');

        if (empty($paystackSecret) || empty($reference)) {
            \Log::error('Missing Paystack parameters', [
                'has_secret' => !empty($paystackSecret),
                'has_reference' => !empty($reference),
                'request_data' => $request->all()
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Missing required payment parameters.'], 400);
            }
            return redirect('/')->with('error', 'Missing required payment parameters.');
        }

        try {
            // Verify payment with Paystack API
            $response = \Illuminate\Support\Facades\Http::withToken($paystackSecret)
                ->get("https://api.paystack.co/transaction/verify/{$reference}");



            $data = $response->json();

            if (!$response->successful() || $data['status'] !== true || $data['data']['status'] !== 'success') {

                DD($response);
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Payment verification failed.'], 400);
                }
                return redirect('/')->with('error', 'Payment verification failed.');
            }

            $paymentData = $data['data'];

            // Payment verified successfully, now process the order
            $request_data = new Request([
                'shipping_address_id'      => $request->input('shipping_address_id'),
                'billing_address_id'       => $request->input('billing_address_id'),
                'chosen_logistic_zone_id'  => $request->input('chosen_logistic_zone_id'),
                'payment_method'           => 'paystack',
                'shipping_delivery_type'   => $request->input('shipping_delivery_type'),
                'payment_status'           => 'paid',
                'amount'                   => $paymentData['amount'] / 100, // Convert from kobo to naira
            ]);

            $result = $this->afterPaymentSuccess($request_data, 'paystack', $reference);

            // Payment was verified successfully, so return success regardless of OrdersController response format
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => true,
                    'success' => true,
                    'message' => 'Your Order has been Placed',
                    'redirect' => route('myorder'),
                    'order' => $result
                ]);
            }

            // For non-AJAX requests, redirect to success page
            return redirect()->route('myorder')->with('success', 'Your Order has been Placed');
        } catch (\Exception $e) {

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'success' => false,
                    'error' => 'Payment processing error: ' . $e->getMessage()
                ], 500);
            }
            return redirect('/')->with('error', 'Payment processing error: ' . $e->getMessage());
        }
    }

    public function productFlutterwaveSuccess(Request $request)
    {
        $flutterwaveSecret = getVendorSetting('flutterwave_secretkey');
        $txRef = $request->input('tx_ref');
        $transactionId = $request->input('transaction_id');

        if (empty($flutterwaveSecret) || empty($txRef)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'success' => false,
                    'error' => 'Missing required Flutterwave parameters.'
                ], 400);
            }
            return redirect('/')->with('error', 'Missing required Flutterwave parameters.');
        }

        try {
            // Verify payment with Flutterwave API
            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://api.flutterwave.com/v3/transactions/{$transactionId}/verify", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $flutterwaveSecret,
                    'Content-Type' => 'application/json'
                ]
            ]);

            $paymentData = json_decode($response->getBody(), true);

            if ($paymentData['status'] !== 'success' || $paymentData['data']['status'] !== 'successful') {
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => false,
                        'success' => false,
                        'error' => 'Payment verification failed.'
                    ], 400);
                }
                return redirect('/')->with('error', 'Payment verification failed.');
            }

            // Verify transaction reference matches
            if ($paymentData['data']['tx_ref'] !== $txRef) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => false,
                        'success' => false,
                        'error' => 'Transaction reference mismatch.'
                    ], 400);
                }
                return redirect('/')->with('error', 'Transaction reference mismatch.');
            }

            // Create request data for order processing
            $request_data = new \Illuminate\Http\Request([
                'shipping_address_id'      => $paymentData['data']['meta']['shipping_address_id'] ?? null,
                'billing_address_id'       => $paymentData['data']['meta']['billing_address_id'] ?? null,
                'chosen_logistic_zone_id'  => $paymentData['data']['meta']['chosen_logistic_zone_id'] ?? null,
                'payment_method'           => 'flutterwave',
                'shipping_delivery_type'   => $paymentData['data']['meta']['shipping_delivery_type'] ?? 'regular',
                'payment_status'           => 'paid',
                'amount'                   => $paymentData['data']['amount'],
            ]);

            $result = $this->afterPaymentSuccess($request_data, 'flutterwave', $txRef);

            // Payment was verified successfully, so return success regardless of OrdersController response format
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => true,
                    'success' => true,
                    'message' => 'Your Order has been Placed',
                    'redirect' => route('myorder'),
                    'order' => $result
                ]);
            }

            // For non-AJAX requests, redirect to success page
            return redirect()->route('myorder')->with('success', 'Your Order has been Placed');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'success' => false,
                    'error' => 'Payment processing error: ' . $e->getMessage()
                ], 500);
            }
            return redirect('/')->with('error', 'Payment processing error: ' . $e->getMessage());
        }
    }
}
