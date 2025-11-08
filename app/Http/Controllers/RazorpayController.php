<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Subscriptions\Models\Plan;
use Modules\Subscriptions\Models\Subscription;
use Razorpay\Api\Api as RazorpayApi;

class RazorpayController extends Controller
{
    private $api;
    public $api_key;
    public $secret_key;

    public function __construct()
    {
        // Fetch both keys in one query
        $razorpaySettings = Setting::whereIn('name', ['razorpay_publickey', 'razorpay_secretkey'])->pluck('val', 'name');

        // Assign values from the fetched settings
        $this->api_key = $razorpaySettings['razorpay_publickey'] ?? null;
        $this->secret_key = $razorpaySettings['razorpay_secretkey'] ?? null;

        // Initialize Razorpay API if both keys are available
        if ($this->api_key && $this->secret_key) {
            $this->api = new RazorpayApi($this->api_key, $this->secret_key);
        } else {
            // Handle missing API keys as needed (e.g., throw an exception or log an error)
            throw new \Exception(__('messages.razorpay_keys_missing'));
        }
    }


    public function pay(Request $request, $plan_id)
    {
        $plan = Plan::findOrFail($plan_id);
        $currency = strtoupper($plan->currency);
        $orderData = [
            'receipt' => 'receipt#' . uniqid(),
            'amount' => $plan->total_price * 100, // Amount in paise
            'currency' => $currency,
            'notes' => [
                'plan_id' => $plan->id,
                'user_id' => Auth::id(),
            ],
        ];

        $order = $this->api->order->create($orderData);

        session(['razorpay_order_id' => $order->id]);

        return view('payments.razorpay.checkout', ['order' => $order]);
    }

    public function success(Request $request)
    {
        $paymentId = $request->input('payment_id');
        $orderId = $request->input('order_id');

        try {
            $payment = $this->api->payment->fetch($paymentId);

            if (in_array($payment->status, ['captured', 'authorized'])) {
                $planId = $payment->notes['plan_id'];
                $userId = $payment->notes['user_id'];
                $plan = Plan::findOrFail($planId);

                $plan->givePermissionToUser(Auth::id());

                $subscription = new Subscription;
                $subscription->plan_id = $plan->id;
                $subscription->user_id = $userId;
                $subscription->amount = $payment->amount / 100; // Convert from paise to INR;
                $subscription->transaction_id = $payment->id;
                $subscription->max_appointment = $plan->max_appointment;
                $subscription->max_branch = $plan->max_branch;
                $subscription->max_service = $plan->max_service;
                $subscription->max_staff = $plan->max_staff;
                $subscription->max_customer = $plan->max_customer;
                $subscription->currency = $payment->currency;
                $subscription->status = 'paid';
                $subscription->gateway_type = 'razorpay';
                $subscription->gateway_response = json_encode($payment->toArray());

                $subscription->save();

                $subscription->start_date = $subscription->startDate();
                $subscription->end_date = $subscription->endDate();
                $subscription->save();

                //  Notification For Subscription
                $subscription->sendNotificationOnPlanPurchase();

                return redirect()->route('backend.subscription.plans.index')->with('success', __('messages.plan_purchased_successfully'));
            } else {
                return view('payment.failure', ['payment' => $payment]);
            }
        } catch (\Exception $e) {
            return view('payment.failure', ['error' => $e->getMessage()]);
        }
    }

    public function cancel(Request $request)
    {
        // Logic for canceling a payment or subscription can go here.
        // This is a placeholder implementation.

        // Optionally retrieve the order ID or payment ID from the request
        $razorpayOrderId = $request->input('order_id');

        // You might want to log the cancellation or take further actions
        // For now, let's redirect with a cancellation message.
        return redirect()->route('backend.subscription.plans.index')->with('error', __('messages.payment_was_cancelled'));
    }
}
