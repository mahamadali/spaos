<?php

namespace App\Services;

use Exception;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Log;

class RazorPayPaymentService
{
    private Api $api;

    public function __construct()
    {
        $this->api = new Api($this->getApiKey(), $this->getSecret());
    }

    // Get the appropriate API key based on the payment mode
    private function getApiKey(): string
    {
        return env('PAYMENT_MODE') === 'test'
            ? env('RAZORPAY_TEST_API_KEY')
            : env('RAZORPAY_LIVE_API_KEY');
    }

    // Get the appropriate secret key based on the payment mode
    private function getSecret(): string
    {
        return env('PAYMENT_MODE') === 'test'
            ? env('RAZORPAY_TEST_SECRET_KEY')
            : env('RAZORPAY_LIVE_SECRET_KEY');
    }

    // Create a token from card details
    public function createToken(array $cardDetails): ?string
    {
        try {
            $tokenResponse = $this->api->tokens->create([
                'card' => $this->sanitizeCardDetails($cardDetails),
            ]);
            return $tokenResponse->id; // Return the token ID
        } catch (Exception $e) {
            Log::error($e->getMessage(), [
                'cardDetails' => $cardDetails,
                'error' => $e
            ]);
            return null; // or throw the exception
        }
    }

    // Create a payment using the token
    public function createPayment(int $amount, string $currency, string $token): ?string
    {
        try {
            $paymentResponse = $this->api->payment->create([
                'amount' => $amount, // Amount in paise
                'currency' => $currency,
                'method' => 'card',
                'token' => $token,
            ]);
            return $paymentResponse->id; // Return the payment ID
        } catch (Exception $e) {
            Log::error($e->getMessage(), [
                'amount' => $amount,
                'currency' => $currency,
                'token' => $token,
                'error' => $e
            ]);
            return null; // or throw the exception
        }
    }

    // Capture a payment
    public function capturePayment(string $paymentId, int $amount): ?array
    {
        try {
            $payment = $this->api->payment->fetch($paymentId);
            return $payment->capture(['amount' => $amount]); // Amount in paise
        } catch (Exception $e) {
            Log::error( $e->getMessage(), [
                'paymentId' => $paymentId,
                'amount' => $amount,
                'error' => $e
            ]);
            return null; // or throw the exception
        }
    }

    private function sanitizeCardDetails(array $cardDetails): array
    {
        return [
            'number' => $cardDetails['number'],
            'expiry' => $cardDetails['expiry'], // Format: MMYY
            'cvv' => $cardDetails['cvv'],
            'name' => $cardDetails['name'],
        ];
    }
}
