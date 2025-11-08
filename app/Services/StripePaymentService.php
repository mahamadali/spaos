<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Token;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Log;

class StripePaymentService
{
    private string $secret;

    public function __construct()
    {
        $this->setSecretKey();
    }

    private function setSecretKey(): void
    {
        $this->secret = env('PAYMENT_MODE') === 'test'
            ? env('STRIPE_TEST_SECRET_KEY')
            : env('STRIPE_LIVE_SECRET_KEY');

        Stripe::setApiKey($this->secret);
    }

    public function generateToken(array $cardDetails): ?Token
    {
        try {
            return Token::create(['card' => $this->sanitizeCardDetails($cardDetails)]);
        } catch (ApiErrorException $e) {
            Log::error($e->getMessage(), [
                'cardDetails' => $cardDetails,
                'error' => $e
            ]);
            return null;
        }
    }

    public function charge(float $amount, string $source, string $description, array $metadata = []): ?Charge
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException(__('messages.amount_must_be_greater_than_zero'));
        }

        try {
            return Charge::create([
                'amount' => (int)($amount * 100), // Amount in cents
                'currency' => 'USD',
                'source' => $source,
                'metadata' => $metadata,
                'description' => $description,
            ]);
        } catch (ApiErrorException $e) {
            Log::error($e->getMessage(), [
                'amount' => $amount,
                'source' => $source,
                'description' => $description,
                'metadata' => $metadata,
                'error' => $e
            ]);
            return null;
        }
    }

    private function sanitizeCardDetails(array $cardDetails): array
    {
        return [
            'number' => $cardDetails['number'],
            'exp_month' => substr($cardDetails['expiry'], 0, 2),
            'exp_year' => substr($cardDetails['expiry'], 2, 2),
            'cvc' => $cardDetails['cvv'],
            'name' => $cardDetails['name'],
        ];
    }
}
