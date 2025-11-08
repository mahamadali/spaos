<?php

namespace App\Services;

use PayPal\Api\Amount;
use PayPal\Api\CreditCard;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
use PayPal\Api\PayPalCreditCardPaymentService;
use PayPal\Api\FundingInstrument;
use PayPal\Exception\PayPalConnectionException;
use Illuminate\Support\Facades\Log;

class PayPalPaymentService
{
    private $apiContext;

    public function __construct()
    {
        $this->setApiContext();
    }

    private function setApiContext(): void
    {
        $this->apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                env('PAYPAL_CLIENT_ID'),     // ClientID
                env('PAYPAL_CLIENT_SECRET')  // ClientSecret
            )
        );

        $this->apiContext->setConfig([
            'mode' => env('PAYPAL_MODE', 'sandbox'), // Can be 'sandbox' or 'live'
            'log.LogEnabled' => true,
            'log.FileName' => storage_path('logs/paypal.log'),
            'log.LogLevel' => 'DEBUG',
        ]);
    }

    public function createPaymentWithCard(float $amount, string $currency = 'USD', array $cardDetails): ?Payment
    {
        $card = new CreditCard();
        $card->setNumber($cardDetails['number'])
             ->setType($cardDetails['type']) // e.g. 'Visa'
             ->setExpireMonth($cardDetails['exp_month'])
             ->setExpireYear($cardDetails['exp_year'])
            //  ->setCvc($cardDetails['cvc'])
             ->setFirstName($cardDetails['first_name'])
             ->setLastName($cardDetails['last_name']);

        $fundingInstrument = new FundingInstrument();
        $fundingInstrument->setCreditCard($card);

        $payer = new \PayPal\Api\Payer();
        $payer->setPaymentMethod('credit_card')
              ->setFundingInstruments([$fundingInstrument]);

        $amountDetail = new Amount();
        $amountDetail->setTotal(number_format($amount, 2, '.', ''))
                     ->setCurrency($currency);

        $transaction = new Transaction();
        $transaction->setAmount($amountDetail)
                    ->setDescription('Payment via Card');

        $payment = new Payment();
        $payment->setIntent('sale')
                ->setPayer($payer)
                ->setTransactions([$transaction]);

        try {
            $payment->create($this->apiContext);
            return $payment;
        } catch (PayPalConnectionException $e) {
            Log::error($e->getMessage(), [
                'amount' => $amount,
                'currency' => $currency,
                'cardDetails' => $cardDetails,
                'error' => $e
            ]);
            return null;
        }
    }

    public function executePayment(string $paymentId, string $payerId): ?Payment
    {
        $payment = Payment::get($paymentId, $this->apiContext);

        $execution = new PaymentExecution();
        $execution->setPayerId($payerId);

        try {
            return $payment->execute($execution, $this->apiContext);
        } catch (PayPalConnectionException $e) {
            Log::error( $e->getMessage(), [
                'paymentId' => $paymentId,
                'payerId' => $payerId,
                'error' => $e
            ]);
            return null;
        }
    }
}
