<!DOCTYPE html>
<html>
<head>
    <title>{{__('messages.razorpay_checkout')}}</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
    <h2>{{__('messages.complete_payment')}}</h2>

    <script>
        var options = {
            "key": "{{ env('RAZORPAY_PUBLIC_KEY') }}", // Your Razorpay key
            "amount": "{{ $order->amount }}", // Amount in paise
            "currency": "INR",
            "name": "{{ __('messages.company_name') }}",
            "description": "{{ __('messages.plan_purchase') }}",
            "order_id": "{{ $order->id }}", // This is the Razorpay order ID
            "handler": function (response) {

                var successUrl = "{{ route('razorpay.payment.success') }}?payment_id="+response.razorpay_payment_id+"&order_id="+response.razorpay_order_id;
                window.location.href = successUrl;
            },
            "prefill": {
                "name": "John Doe",
                "email": "john@example.com",
            },
            "theme": {
                "color": "#F37254"
            }
        };

        var rzp = new Razorpay(options);
        rzp.open(); // Open the Razorpay payment dialog
    </script>
</body>
</html>
