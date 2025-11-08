<?php

namespace Modules\VendorWebsite\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Booking\Models\BookingTransaction;
use Modules\Booking\Models\Booking;

class PaymentRetryController extends Controller
{
    /**
     * Retry failed payment processing
     */
    public function retryPayment(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $gateway = $request->input('gateway');

        if (!$transactionId || !$gateway) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction ID and gateway are required'
            ], 400);
        }

        try {
            Log::info('Payment retry initiated', [
                'transaction_id' => $transactionId,
                'gateway' => $gateway,
                'user_id' => Auth::id()
            ]);

            // Check if payment already exists
            $existingPayment = BookingTransaction::where('external_transaction_id', $transactionId)->first();
            if ($existingPayment) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment already processed successfully'
                ]);
            }

            // Verify payment with gateway
            $verificationResult = $this->verifyPaymentWithGateway($gateway, $transactionId);

            if (!$verificationResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed: ' . $verificationResult['message']
                ], 400);
            }

            // Process the payment
            $result = $this->processPaymentFromWebhook($verificationResult['data'], $gateway, $transactionId);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment processing failed: ' . $result['message']
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Payment retry failed', [
                'transaction_id' => $transactionId,
                'gateway' => $gateway,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment retry failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify payment with the respective gateway
     */
    private function verifyPaymentWithGateway($gateway, $transactionId)
    {
        switch ($gateway) {
            case 'stripe':
                return $this->verifyStripePayment($transactionId);
            case 'paystack':
                return $this->verifyPaystackPayment($transactionId);
            case 'razorpay':
                return $this->verifyRazorpayPayment($transactionId);
            default:
                return [
                    'success' => false,
                    'message' => 'Unsupported payment gateway'
                ];
        }
    }

    /**
     * Verify Stripe payment
     */
    private function verifyStripePayment($paymentIntent)
    {
        try {
            $stripeSecret = getVendorSetting('stripe_secretkey');
            if (!$stripeSecret) {
                return ['success' => false, 'message' => 'Stripe secret key not configured'];
            }

            $stripe = new \Stripe\StripeClient($stripeSecret);
            $intent = $stripe->paymentIntents->retrieve($paymentIntent);

            if ($intent->status === 'succeeded') {
                return [
                    'success' => true,
                    'data' => [
                        'payment_intent' => $intent->id,
                        'amount' => $intent->amount,
                        'status' => $intent->status,
                        'metadata' => $intent->metadata ?? []
                    ]
                ];
            } else {
                return ['success' => false, 'message' => 'Payment not successful'];
            }
        } catch (\Exception $e) {
            Log::error('Stripe payment verification failed', [
                'payment_intent' => $paymentIntent,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Verification failed: ' . $e->getMessage()];
        }
    }

    /**
     * Verify Paystack payment
     */
    private function verifyPaystackPayment($reference)
    {
        try {
            $paystackSecret = getVendorSetting('paystack_secretkey');
            if (!$paystackSecret) {
                return ['success' => false, 'message' => 'Paystack secret key not configured'];
            }

            $response = \Illuminate\Support\Facades\Http::withToken($paystackSecret)
                ->timeout(30)
                ->get("https://api.paystack.co/transaction/verify/{$reference}");

            if (!$response->successful()) {
                return ['success' => false, 'message' => 'API request failed'];
            }

            $data = $response->json();

            if ($data['status'] === true && $data['data']['status'] === 'success') {
                return [
                    'success' => true,
                    'data' => $data['data']
                ];
            } else {
                return ['success' => false, 'message' => 'Payment not successful'];
            }
        } catch (\Exception $e) {
            Log::error('Paystack payment verification failed', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Verification failed: ' . $e->getMessage()];
        }
    }

    /**
     * Verify Razorpay payment
     */
    private function verifyRazorpayPayment($paymentId)
    {
        try {
            $razorpayKeyId = getVendorSetting('razorpay_keyid');
            $razorpayKeySecret = getVendorSetting('razorpay_keysecret');

            if (!$razorpayKeyId || !$razorpayKeySecret) {
                return ['success' => false, 'message' => 'Razorpay keys not configured'];
            }

            $razorpay = new \Razorpay\Api\Api($razorpayKeyId, $razorpayKeySecret);
            $payment = $razorpay->payment->fetch($paymentId);

            if ($payment->status === 'captured') {
                return [
                    'success' => true,
                    'data' => [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'status' => $payment->status,
                        'notes' => $payment->notes ?? []
                    ]
                ];
            } else {
                return ['success' => false, 'message' => 'Payment not captured'];
            }
        } catch (\Exception $e) {
            Log::error('Razorpay payment verification failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Verification failed: ' . $e->getMessage()];
        }
    }

    /**
     * Process payment from webhook data
     */
    private function processPaymentFromWebhook($data, $gateway, $transactionId)
    {
        try {
            return DB::transaction(function () use ($data, $gateway, $transactionId) {
                // Extract metadata based on gateway
                $metadata = $this->extractMetadata($data, $gateway);

                if (!$metadata) {
                    throw new \Exception('Unable to extract payment metadata');
                }

                // Create booking
                $bookingData = [
                    'employee_id' => $metadata['employee_id'] ?? null,
                    'branch_id' => $metadata['branch_id'] ?? null,
                    'date' => $metadata['date'] ?? null,
                    'time' => $metadata['time'] ?? null,
                    'services' => $metadata['services'] ?? [],
                    'coupon_code' => $metadata['coupon_code'] ?? null,
                ];

                $bookingsController = app(\Modules\Booking\Http\Controllers\Backend\API\BookingsController::class);
                $apiRequest = new \Illuminate\Http\Request($bookingData);
                $response = $bookingsController->store($apiRequest);

                if (method_exists($response, 'getData')) {
                    $bookingJson = json_decode(json_encode($response->getData()), true);
                } else {
                    $bookingJson = is_array($response) ? $response : [];
                }

                if (!($bookingJson['status'] ?? false)) {
                    throw new \Exception($bookingJson['message'] ?? 'Booking creation failed');
                }

                $bookingId = $bookingJson['booking_id'] ?? null;
                if (!$bookingId) {
                    throw new \Exception('Booking ID not returned');
                }

                // Create payment record
                $paymentData = [
                    'booking_id' => $bookingId,
                    'payment_method' => $gateway,
                    'tax_percentage' => $metadata['tax_percentage'] ?? [],
                    'tip' => $metadata['tip'] ?? 0,
                    'coupon_code' => $metadata['coupon_code'] ?? null,
                    'discount_amount' => $metadata['discount_amount'] ?? null,
                    'discount_percentage' => $metadata['discount_percentage'] ?? null,
                    'transaction_type' => $gateway,
                    'payment_status' => 1,
                    'external_transaction_id' => $transactionId,
                ];

                $paymentController = app(\Modules\Booking\Http\Controllers\Backend\API\PaymentController::class);
                $apiPaymentRequest = new \Illuminate\Http\Request($paymentData);
                $paymentResponse = $paymentController->savePayment($apiPaymentRequest);

                if (method_exists($paymentResponse, 'getData')) {
                    $paymentSaveJson = json_decode(json_encode($paymentResponse->getData()), true);
                } else {
                    $paymentSaveJson = is_array($paymentResponse) ? $paymentResponse : [];
                }

                if (!($paymentSaveJson['status'] ?? false)) {
                    throw new \Exception($paymentSaveJson['message'] ?? 'Payment save failed');
                }

                Log::info('Payment retry successful', [
                    'booking_id' => $bookingId,
                    'transaction_id' => $transactionId,
                    'gateway' => $gateway
                ]);

                return ['success' => true, 'booking_id' => $bookingId];
            });
        } catch (\Exception $e) {
            Log::error('Payment processing from webhook failed', [
                'error' => $e->getMessage(),
                'gateway' => $gateway,
                'transaction_id' => $transactionId
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Extract metadata from gateway data
     */
    private function extractMetadata($data, $gateway)
    {
        switch ($gateway) {
            case 'stripe':
                return [
                    'employee_id' => $data['metadata']['employee_id'] ?? null,
                    'branch_id' => $data['metadata']['branch_id'] ?? null,
                    'date' => $data['metadata']['date'] ?? null,
                    'time' => $data['metadata']['time'] ?? null,
                    'services' => json_decode($data['metadata']['services'] ?? '[]', true),
                    'coupon_code' => $data['metadata']['coupon_code'] ?? null,
                    'tax_percentage' => json_decode($data['metadata']['tax_percentage'] ?? '[]', true),
                    'tip' => $data['metadata']['tip'] ?? 0,
                    'discount_amount' => $data['metadata']['discount_amount'] ?? null,
                    'discount_percentage' => $data['metadata']['discount_percentage'] ?? null,
                ];
            case 'paystack':
                return [
                    'employee_id' => $data['metadata']['employee_id'] ?? null,
                    'branch_id' => $data['metadata']['branch_id'] ?? null,
                    'date' => $data['metadata']['date'] ?? null,
                    'time' => $data['metadata']['time'] ?? null,
                    'services' => json_decode($data['metadata']['services'] ?? '[]', true),
                    'coupon_code' => $data['metadata']['coupon_code'] ?? null,
                    'tax_percentage' => json_decode($data['metadata']['tax_percentage'] ?? '[]', true),
                    'tip' => $data['metadata']['tip'] ?? 0,
                    'discount_amount' => $data['metadata']['discount_amount'] ?? null,
                    'discount_percentage' => $data['metadata']['discount_percentage'] ?? null,
                ];
            case 'razorpay':
                return [
                    'employee_id' => $data['notes']['employee_id'] ?? null,
                    'branch_id' => $data['notes']['branch_id'] ?? null,
                    'date' => $data['notes']['date'] ?? null,
                    'time' => $data['notes']['time'] ?? null,
                    'services' => json_decode($data['notes']['services'] ?? '[]', true),
                    'coupon_code' => $data['notes']['coupon_code'] ?? null,
                    'tax_percentage' => json_decode($data['notes']['tax_percentage'] ?? '[]', true),
                    'tip' => $data['notes']['tip'] ?? 0,
                    'discount_amount' => $data['notes']['discount_amount'] ?? null,
                    'discount_percentage' => $data['notes']['discount_percentage'] ?? null,
                ];
            default:
                return null;
        }
    }
}
