<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Subscriptions\Models\Plan;
use Modules\Subscriptions\Models\Subscription;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class StripeController extends Controller
{
    public $api_key;
    public $secret_key;

    public function __construct()
    {
        // Fetch both keys in one query
        $stripeSettings = Setting::whereIn('name', ['stripe_publickey', 'stripe_secretkey'])->pluck('val', 'name');

        // Assign values from the fetched settings
        $this->api_key = $stripeSettings['stripe_publickey'] ?? null;
        $this->secret_key = $stripeSettings['stripe_secretkey'] ?? null;
    }

    public function pay(Request $request, $plan_id)
    {
        $plan = Plan::findOrFail($plan_id);
        $userId = Auth::id();
        $user = User::find($userId);


        Stripe::setApiKey($this->secret_key);

        // Create a Stripe Checkout Session
        $checkoutSession = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $plan->currency,
                    'product_data' => [
                        'name' => 'Plan Purchase',
                    ],
                    'unit_amount' => $plan->total_price * 100, // Amount in paise (1 INR = 100 paise)
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('stripe.payment.success'),
            'cancel_url' => route('stripe.payment.cancel'),
            'metadata' => [
                'plan_id' => $plan->id,
                'user_id' => $userId,
            ],
            'customer_email' => $user->email, // Customer email
            // Add shipping address collection for compliance with Indian regulations
            'shipping_address_collection' => [
                'allowed_countries' => ['IN'], // Restrict the address collection to India
            ],
        ]);

        session(['stripe_checkout_session_id' => $checkoutSession->id]);

        return redirect($checkoutSession->url);
    }

    public function success(Request $request)
    {
        $sessionId = session('stripe_checkout_session_id');

        Stripe::setApiKey($this->secret_key);
        $session = StripeSession::retrieve($sessionId);
        $plan = Plan::findOrFail($session->metadata['plan_id']);

        if ($session->payment_status === 'paid') {
            $this->createSubscription($plan, $session);
            $plan->givePermissionToUser(Auth::id());
            return redirect()->route('backend.subscription.plans.index')->with('success', __('messages.plan_purchased_successfully'));
        }

        return view('payment.failure', ['session' => $session]);
    }

    public function cancel(Request $request)
    {
        // Optionally, you can log the cancellation or perform other actions
        return view('payment.cancelled');
    }

    private function createSubscription($plan, $session)
    {
        $subscription = new Subscription;
        $subscription->plan_id = $plan->id;
        $subscription->user_id = $session->metadata['user_id'];
        $subscription->amount = $session->amount_total / 100;
        $subscription->max_appointment = $plan->max_appointment;
        $subscription->max_branch = $plan->max_branch;
        $subscription->max_service = $plan->max_service;
        $subscription->max_staff = $plan->max_staff;
        $subscription->max_customer = $plan->max_customer;
        $subscription->transaction_id = $session->payment_intent;
        $subscription->currency = $session->currency;
        $subscription->status = 'paid';
        $subscription->gateway_type = 'stripe';
        $subscription->gateway_response = json_encode($session);
        $subscription->save();

        $subscription->start_date = $subscription->startDate();
        $subscription->end_date = $subscription->endDate();
        $subscription->save();

        //  Notification For Subscription
        $subscription->sendNotificationOnPlanPurchase();
    }
}
