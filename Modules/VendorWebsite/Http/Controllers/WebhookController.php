<?php

namespace Modules\VendorWebsite\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Models\BookingTransaction;
use Modules\Booking\Models\Booking;

class WebhookController extends Controller
{
    /**
     * Handle Stripe webhooks
     */
    public function stripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = getVendorSetting('stripe_webhook_secret');

        if (!$endpointSecret) {
            Log::error('Stripe webhook secret not configured');
            return response()->json(['error' => 'Webhook secret not configured'], 400);
        }

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Stripe webhook invalid payload', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        Log::info('Stripe webhook received', ['type' => $event->type]);

        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleStripeCheckoutCompleted($event->data->object);
                break;
            case 'payment_intent.succeeded':
                $this->handleStripePaymentSucceeded($event->data->object);
                break;
            default:
                Log::info('Stripe webhook unhandled event type', ['type' => $event->type]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle Paystack webhooks
     */
    public function paystackWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Paystack-Signature');
        $secret = getVendorSetting('paystack_secretkey');

        if (!$secret) {
            Log::error('Paystack secret key not configured for webhook');
            return response()->json(['error' => 'Secret key not configured'], 400);
        }

        // Verify signature
        $computedSignature = hash_hmac('sha512', $payload, $secret);
        if (!hash_equals($signature, $computedSignature)) {
            Log::error('Paystack webhook signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);
        Log::info('Paystack webhook received', ['event' => $event['event'] ?? 'unknown']);

        switch ($event['event'] ?? '') {
            case 'charge.success':
                $this->handlePaystackChargeSuccess($event['data']);
                break;
            default:
                Log::info('Paystack webhook unhandled event', ['event' => $event['event'] ?? 'unknown']);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle Razorpay webhooks
     */
    public function razorpayWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        $webhookSecret = getVendorSetting('razorpay_webhook_secret');

        if (!$webhookSecret) {
            Log::error('Razorpay webhook secret not configured');
            return response()->json(['error' => 'Webhook secret not configured'], 400);
        }

        // Verify signature
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
        if (!hash_equals($signature, $expectedSignature)) {
            Log::error('Razorpay webhook signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);
        Log::info('Razorpay webhook received', ['event' => $event['event'] ?? 'unknown']);

        switch ($event['event'] ?? '') {
            case 'payment.captured':
                $this->handleRazorpayPaymentCaptured($event['payload']);
                break;
            default:
                Log::info('Razorpay webhook unhandled event', ['event' => $event['event'] ?? 'unknown']);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle Stripe checkout session completed
     */
    private function handleStripeCheckoutCompleted($session)
    {
        try {
            $paymentIntent = $session->payment_intent;

            // Check if already processed
            $existingPayment = BookingTransaction::where('external_transaction_id', $paymentIntent)->first();
            if ($existingPayment) {
                Log::info('Stripe payment already processed via webhook', ['payment_intent' => $paymentIntent]);
                return;
            }

            Log::info('Stripe checkout completed', [
                'session_id' => $session->id,
                'payment_intent' => $paymentIntent,
                'payment_status' => $session->payment_status
            ]);

            // You can add additional processing here if needed
            // The main processing should be done in the success callback

        } catch (\Exception $e) {
            Log::error('Stripe webhook processing failed', [
                'error' => $e->getMessage(),
                'session_id' => $session->id ?? 'unknown'
            ]);
        }
    }

    /**
     * Handle Stripe payment intent succeeded
     */
    private function handleStripePaymentSucceeded($paymentIntent)
    {
        try {
            Log::info('Stripe payment intent succeeded', [
                'payment_intent' => $paymentIntent->id,
                'amount' => $paymentIntent->amount,
                'status' => $paymentIntent->status
            ]);

            // Additional processing if needed

        } catch (\Exception $e) {
            Log::error('Stripe payment intent processing failed', [
                'error' => $e->getMessage(),
                'payment_intent' => $paymentIntent->id ?? 'unknown'
            ]);
        }
    }

    /**
     * Handle Paystack charge success
     */
    private function handlePaystackChargeSuccess($data)
    {
        try {
            $reference = $data['reference'] ?? null;

            if (!$reference) {
                Log::error('Paystack webhook missing reference');
                return;
            }

            // Check if already processed
            $existingPayment = BookingTransaction::where('external_transaction_id', $reference)->first();
            if ($existingPayment) {
                Log::info('Paystack payment already processed via webhook', ['reference' => $reference]);
                return;
            }

            Log::info('Paystack charge successful', [
                'reference' => $reference,
                'amount' => $data['amount'] ?? 'unknown',
                'status' => $data['status'] ?? 'unknown'
            ]);

            // Additional processing if needed

        } catch (\Exception $e) {
            Log::error('Paystack webhook processing failed', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }
    }

    /**
     * Handle Razorpay payment captured
     */
    private function handleRazorpayPaymentCaptured($payload)
    {
        try {
            $payment = $payload['payment']['entity'] ?? null;

            if (!$payment) {
                Log::error('Razorpay webhook missing payment data');
                return;
            }

            $paymentId = $payment['id'] ?? null;

            if (!$paymentId) {
                Log::error('Razorpay webhook missing payment ID');
                return;
            }

            // Check if already processed
            $existingPayment = BookingTransaction::where('external_transaction_id', $paymentId)->first();
            if ($existingPayment) {
                Log::info('Razorpay payment already processed via webhook', ['payment_id' => $paymentId]);
                return;
            }

            Log::info('Razorpay payment captured', [
                'payment_id' => $paymentId,
                'amount' => $payment['amount'] ?? 'unknown',
                'status' => $payment['status'] ?? 'unknown'
            ]);

            // Additional processing if needed

        } catch (\Exception $e) {
            Log::error('Razorpay webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
        }
    }
}
