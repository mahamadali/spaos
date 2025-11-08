<?php
namespace App\Http\Controllers;
use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Modules\Subscriptions\Models\Plan;

class PayPalController extends Controller
{
    private $apiContext;

    public function __construct()
    {
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential(
                env('PAYPAL_PUBLIC_KEY'),
                env('PAYPAL_SECRET_KEY')
            )
        );

        $this->apiContext->setConfig([
            'mode' => 'sandbox',
        ]);
    }

    public function pay($plan_id)
    {
        $plan = Plan::findOrFail($plan_id);

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new Amount();
        $amount->setTotal(floatval($plan->total_price)); // Ensure it's numeric
        $amount->setCurrency('INR');

        $transaction = new Transaction();
        $transaction->setAmount($amount)
                    ->setDescription('Payment Description');

        $transactions = [$transaction]; // Explicit array

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(URL::route('paypal.payment.success'))
                    ->setCancelUrl(URL::route('paypal.payment.cancel'));

        $payment = new Payment();
        $payment->setIntent('sale')
                ->setPayer($payer)
                ->setTransactions($transactions) // Ensure this is correctly set
                ->setRedirectUrls($redirectUrls);
        try {
            $payment->create($this->apiContext);
            return redirect($payment->getApprovalLink());
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            \Log::error('PayPal Connection Exception: ' . $ex->getMessage());
            return redirect()->route('paypal.payment.error');
        }
    }


    public function success(Request $request)
    {
        $paymentId = $request->get('paymentId');
        $payerId = $request->get('PayerID');

        $payment = Payment::get($paymentId, $this->apiContext);
        $execution = new PaymentExecution();
        $execution->setPayerId($payerId);

        try {
            $result = $payment->execute($execution, $this->apiContext);
            // Payment successful
            Session::flash('success', 'Payment successful');
            return redirect()->route('home');
        } catch (\Exception $ex) {
            // Payment failed
            Session::flash('error', 'Payment failed');
            return redirect()->route('paypal.payment.error');
        }
    }

    public function cancel()
    {
        Session::flash('error', 'Payment canceled');
        return redirect()->route('home');
    }

    public function error()
    {
        return view('paypal.payment.error');
    }
}
