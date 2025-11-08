<div class="top-header-box d-flex flex-column flex-md-row align-items-center justify-content-between mb-3">
    <h4 class="font-size-21-3 mb-0">{{ __('vendorwebsite.wallet_balance') }}</h4>
    <div class="d-flex align-items-center gap-lg-5 gap-3">
        <a href="#" class="btn btn-link font-size-16" data-bs-toggle="modal"
            data-bs-target="#withdrawModal">{{ __('vendorwebsite.withdrawal') }}</a>
        <a href="#" class="btn btn-link font-size-16" data-bs-toggle="modal"
            data-bs-target="#topUpModal">{{ __('vendorwebsite.top_up') }}</a>
    </div>
    <!-- Withdraw Modal -->
    <div class="modal fade" id="withdrawModal" tabindex="-1" aria-labelledby="withdrawModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered pop-up-box">
            <div class="modal-content">
                <div class="modal-header pb-0">
                    <h3 class="modal-title font-size-21-3" id="withdrawModalLabel">{{ __('vendorwebsite.withdrawal') }}
                        </h5>
                        <button type="button" class="btn-close close-btn" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div
                        class="balance-section d-flex flex-column flex-md-row align-items-center justify-content-between rounded-3 p-3 mb-4">
                        <div>
                            <p class="mb-0">{{ __('vendorwebsite.total_balance') }}</p>
                        </div>
                        <h5 class="text-success">{{ \Currency::format(optional(auth()->user()->wallet)->amount) }}</h5>
                    </div>

                    <form id="withdrawForm" action="{{ route('wallet.withdraw') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="withdrawAmount"
                                class="form-label">{{ __('vendorwebsite.enter_amount') }}</label>
                            <input type="number" step="0.01" id="withdrawAmount" name="amount"
                                class="form-control @error('amount') is-invalid @enderror" placeholder="eg. $150">
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <div
                                class="heading-box d-flex
                            justify-content-between align-items-center">
                                <label for="chooseBank" class="form-label d-flex justify-content-between">
                                    {{ __('vendorwebsite.choose_bank') }}
                                </label>
                                <a href="#" class="btn btn-link font-size-12 fw-semibold"
                                    onclick="openBankModal(); return false;">{{ __('vendorwebsite.add_bank') }}</a>
                            </div>
                            {{-- <select id="chooseBank" class="form select2 @error('bank_id') is-invalid @enderror"
                                name="bank_id">
                                <option value="" disabled>{{ __('vendorwebsite.select_bank') }}</option>
                                @php $defaultBankId = $banks->firstWhere('is_default', 1)?->id; @endphp
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->id }}"
                                        @if ($defaultBankId == $bank->id) selected @endif>{{ $bank->bank_name }} -
                                        {{ substr($bank->account_no, -4) }}</option>
                                @endforeach
                            </select> --}}

                            <select id="chooseBank" class="form select2 @error('bank_id') is-invalid @enderror" name="bank_id">
                                <option value="" disabled selected>{{ __('vendorwebsite.select_bank') }}</option>
                                @php $defaultBankId = $banks->firstWhere('is_default', 1)?->id; @endphp
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->id }}"
                                        @if ($defaultBankId == $bank->id) selected @endif>{{ $bank->bank_name }} -
                                        {{ substr($bank->account_no, -4) }}</option>
                                @endforeach
                            </select>


                            <div id="bank-error-feedback" class="invalid-feedback"></div>
                            @error('bank_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                    </form>
                </div>

                <div class="modal-footer d-flex flex-wrap align-items-center gap-lg-4 gap-2">
                    <button type="button" class="btn btn-secondary font-size-14 fw-semibold"
                        data-bs-dismiss="modal">{{ __('vendorwebsite.cancel') }}</button>
                    <button type="submit" form="withdrawForm"
                        class="btn btn-primary font-size-14 fw-semibold">{{ __('vendorwebsite.submit') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Top-Up Modal -->
    <div class="modal fade" id="topUpModal" tabindex="-1" aria-labelledby="topUpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered pop-up-box">
            <div class="modal-content">
                <div class="modal-header pb-0">
                    <h6 class="title-text" id="topUpModalLabel">{{ __('vendorwebsite.top_up') }}</h6>
                    <button type="button" class="btn-close close-btn" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div
                        class="balance-section d-flex flex-column flex-md-row align-items-center justify-content-between rounded-3 p-3 mb-5">
                        <div>
                            <p class="mb-0">{{ __('vendorwebsite.available_balance') }}</p>
                        </div>
                        <div class="balance-amount font-size-18 text-success">
                            {{ \Currency::vendorCurrencyFormate(optional(auth()->user()->wallet)->amount) }}</div>
                    </div>
                    <div class="mt-3">
                        <h6>{{ __('vendorwebsite.top_up_wallet') }}</h6>
                        <p class="font-size-12">{{ __('vendorwebsite.what_amount_would_you_prefer_to_top_up_with') }}
                        </p>
                        <div class="amount-box d-flex flex-column align-items-start justify-content-center rounded-2">
                            <div class="mb-3">
                                <label for="topUpAmount"
                                    class="form-label">{{ __('vendorwebsite.enter_or_selected_amount') }}</label>
                                <input type="number" class="form-control" id="topUpAmount" name="topUpAmount"
                                    value="0" min="1">
                                <span id="top-up-amount-error" class="text-danger"></span>
                            </div>

                            <div class="d-flex gap-2 mb-3">
                                <button id="" class="btn amt-btn font-size-14">150</button>
                                <button class="btn amt-btn font-size-14">200</button>
                                <button class="btn amt-btn font-size-14">500</button>
                                <button class="btn amt-btn font-size-14">1000</button>
                                <button class="btn amt-btn font-size-14">5000</button>
                            </div>
                        </div>
                    </div>


                    @php
                        $is_stripe = getVendorSetting('str_payment_method');

                        $is_razorpay = getVendorSetting('razor_payment_method');

                    @endphp

                    @if ($is_stripe == 0 && $is_razorpay == 0)


                        <span class="text-danger mt-5"> {{ __('vendorwebsite.no_payment_methods_available') }}</span>
                    @else
                        <div class="mt-5">
                            <h6>{{ __('vendorwebsite.payment_method') }}</h6>
                            <p class="font-size-12">
                                {{ __('vendorwebsite.select_your_payment_method_to_add_balance') }}</p>
                            <span id="top-up-amount-error" class="text-danger"></span>


                            <div class="payments-container bg-gray-800 rounded mt-3">
                                <a class="d-flex justify-content-between align-items-center gap-3 payments-show-list"
                                    href="#booking-payments-method" data-bs-toggle="collapse" aria-expanded="true">
                                    <div class="d-flex align-items-center gap-2">
                                        <img id="selected-payment-icon"
                                            src="{{ asset('img/vendorwebsite/stripe-payment.png') }}"
                                            alt="payment-method" class="img-fluid flex-shrink-0">
                                        <span id="selected-payment-text"
                                            class="flex-shrink-0 font-size-14 fw-medium heading-color">{{ __('messages.payment_methods') }}</span>
                                    </div>
                                    <i class="ph ph-caret-down"></i>
                                </a>
                            </div>

                            <div id="booking-payments-method"
                                class="bg-gray-800 rounded booking-payment-method mt-3 collapse show">
                                @if ($is_stripe == 1)
                                    <div class="form-check payment-method-items p-0 d-flex justify-content-between align-items-center gap-3 payment-method-card"
                                        data-payment-method="Stripe">
                                        <label class="form-check-label d-flex gap-2 align-items-center w-100"
                                            for="method-Stripe">
                                            <img src="{{ asset('img/vendorwebsite/stripe.svg') }}" alt="Stripe"
                                                class="avatar avatar-20">
                                            <span class="h6 fw-semibold m-0">{{ __('vendorwebsite.stripe') }}</span>
                                        </label>
                                        <input class="form-check-input payment-radio" type="radio"
                                            name="payment_method" value="Stripe" id="method-Stripe">
                                    </div>
                                @endif



                                @if ($is_razorpay == 1)
                                    <div class="form-check payment-method-items p-0 d-flex justify-content-between align-items-center gap-3 payment-method-card"
                                        data-payment-method="Razorpay">
                                        <label class="form-check-label d-flex gap-2 align-items-center w-100"
                                            for="method-Razorpay">
                                            <img src="{{ asset('img/vendorwebsite/razorpay.svg') }}" alt="Razorpay"
                                                class="avatar avatar-20">
                                            <span class="h6 fw-semibold m-0">{{ __('vendorwebsite.razorpay') }}</span>
                                        </label>
                                        <input class="form-check-input payment-radio" type="radio"
                                            name="payment_method" value="Razorpay" id="method-Razorpay">
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    <span id="payment-method-error" class="text-danger"></span>
                </div>
                <div class="modal-footer d-flex flex-wrap align-items-center gap-lg-4 gap-2">
                    <button type="button" class="btn btn-secondary m-0"
                        data-bs-dismiss="modal">{{ __('vendorwebsite.cancel') }}</button>
                    <button type="button" id="proceedTopUp"
                        class="btn btn-primary m-0">{{ __('vendorwebsite.proceed') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="balance-section d-flex flex-column flex-md-row align-items-center justify-content-between rounded-3">
    <div>
        <p class="mb-0">{{ __('vendorwebsite.total_balance') }}</p>
    </div>
    <div class="balance-amount fs-4 text-success">
        @php
            $user = auth()->user();
            $amount = $user ? $user->getWalletBalance() : 0;
        @endphp
        {{ \Currency::vendorCurrencyFormate($amount) }}
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                dropdownParent: $('#withdrawModal'),
                width: '100%',
                placeholder: 'Select Bank'
            });

            // Auto-select Stripe when top-up modal opens
            $('#topUpModal').on('shown.bs.modal', function() {
                // Select Stripe radio button by default
                const stripeRadio = document.querySelector('input[name="payment_method"][value="Stripe"]');
                if (stripeRadio) {
                    stripeRadio.checked = true;
                    // Update dropdown header
                    updatePaymentMethodHeader('Stripe');
                }
            });

            // Handle form submission
            $('#withdrawForm').on('submit', function(e) {
                e.preventDefault();

                // Clear previous error messages
                $('.invalid-feedback').hide();
                $('.is-invalid').removeClass('is-invalid');
                $('#bank-error-feedback').text('').hide();

                // Validate form fields
                let isValid = true;
                let errorMessage = '';

                // Validate amount
                const amount = $('#withdrawAmount').val().trim();
                if (!amount) {
                    $('#withdrawAmount').addClass('is-invalid');
                    $('#withdrawAmount').after(
                        '<div class="invalid-feedback">{{ __('vendorwebsite.amount_is_required') }}</div>'
                    );
                    isValid = false;
                } else if (isNaN(amount) || parseFloat(amount) <= 0) {
                    $('#withdrawAmount').addClass('is-invalid');
                    $('#withdrawAmount').after(
                        '<div class="invalid-feedback">{{ __('vendorwebsite.amount_must_be_greater_than_0') }}</div>'
                    );
                    isValid = false;
                }

                // Validate bank selection
                const bankId = $('#chooseBank').val();
                if (!bankId) {
                    $('#chooseBank').addClass('is-invalid');
                    $('#bank-error-feedback').text('{{ __('vendorwebsite.please_select_a_bank') }}')
                        .show();
                    isValid = false;
                }

                if (!isValid) {
                    return false;
                }

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        // Close the modal
                        $('#withdrawModal').modal('hide');

                        // Show success snackbar
                        window.successSnackbar(
                            '{{ __('vendorwebsite.withdrawal_request_submitted_successfully') }}'
                        );

                        // Reset form
                        $('#withdrawForm')[0].reset();

                        // Reload page after a short delay
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function(xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            // Handle validation errors by showing them as field errors
                            if (xhr.status === 422) {
                                // Clear previous errors
                                $('.invalid-feedback').hide();
                                $('.is-invalid').removeClass('is-invalid');
                                $('#bank-error-feedback').text('').hide();

                                // Show validation errors as field errors
                                if (xhr.responseJSON.errors) {
                                    $.each(xhr.responseJSON.errors, function(field, errors) {
                                        if (field === 'amount') {
                                            $('#withdrawAmount').addClass('is-invalid');
                                            $('#withdrawAmount').after(
                                                '<div class="invalid-feedback">' +
                                                errors[0] + '</div>');
                                        } else if (field === 'bank_id') {
                                            $('#chooseBank').addClass('is-invalid');
                                            $('#bank-error-feedback').text(errors[0])
                                                .show();
                                        }
                                    });
                                } else {
                                    // If no specific field errors, show general message
                                    $('#withdrawAmount').addClass('is-invalid');
                                    $('#withdrawAmount').after(
                                        '<div class="invalid-feedback">' + xhr.responseJSON
                                        .message + '</div>');
                                }
                            } else {
                                // For non-validation errors, show SweetAlert
                                $('#withdrawModal').modal('hide');
                                setTimeout(function() {
                                    Swal.fire({
                                        icon: 'error',
                                        title: '{{ __('vendorwebsite.withdrawal_error') }}',
                                        text: xhr.responseJSON.message,
                                        confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                        customClass: {
                                            confirmButton: 'btn btn-primary'
                                        },
                                        buttonsStyling: false,
                                    });
                                }, 400);
                            }
                        } else {
                            window.errorSnackbar(
                                '{{ __('vendorwebsite.error_submitting_withdrawal_request') }}'
                            );
                        }
                    }
                });
            });
        });
    </script>

    <script>
        document.querySelectorAll('.amt-btn').forEach(button => {
            button.addEventListener('click', function() {
                const selectedAmount = this.innerText;

                const amountInput = document.getElementById('topUpAmount');
                amountInput.value = selectedAmount;

                document.querySelectorAll('.amt-btn').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
            });
        });

        document.getElementById('proceedTopUp').addEventListener('click', function() {
            const amount = document.getElementById('topUpAmount').value.trim();
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;

            // Clear previous errors
            $('#top-up-amount-error').text('');

            $('#payment-method-error').text('');

            let isValid = true;

            if (!amount) {
                $('#top-up-amount-error').text('{{ __('vendorwebsite.amount_is_required') }}');
                isValid = false;
            } else if (isNaN(amount) || parseFloat(amount) <= 0) {
                $('#top-up-amount-error').text('{{ __('vendorwebsite.amount_must_be_greater_than_0') }}');
                isValid = false;
            }

            if (!paymentMethod) {
                $('#payment-method-error').text('{{ __('vendorwebsite.payment_method_is_required') }}');
                isValid = false;
            }

            if (!isValid) return;


            $.ajax({
                url: '{{ route('wallet.topup') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    amount: amount,
                    payment_method: paymentMethod
                },
                beforeSend: function() {
                    $('#proceedTopUp').prop('disabled', true).text(
                        '{{ __('vendorwebsite.processing') }}...');
                },
                success: function(response) {
                    $('#proceedTopUp').prop('disabled', false).text(
                        '{{ __('vendorwebsite.proceed') }}');

                    if (response.status && response.redirect_url) {

                        if (paymentMethod == 'Stripe') {

                            window.location.href = response.redirect_url;

                        } else if (paymentMethod == 'Razorpay') {

                            openRazorpay(response)

                        } else {

                            $('#topUpModal').modal('hide');
                            Swal.fire({
                                title: '{{ __('vendorwebsite.error') }}',
                                text: response.message ||
                                    '{{ __('vendorwebsite.payment_failed') }}',
                                icon: 'error',
                                confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },

                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = '{{ route('wallet') }}';
                                }
                            });

                        }

                    } else {
                        $('#topUpModal').modal('hide');
                        Swal.fire({
                            title: '{{ __('vendorwebsite.error') }}',
                            text: response.message ||
                                '{{ __('vendorwebsite.payment_failed') }}',
                            icon: 'error',
                            confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '{{ route('wallet') }}';
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    $('#topUpModal').modal('hide');
                    $('#proceedTopUp').prop('disabled', false).text(
                        '{{ __('vendorwebsite.proceed') }}');

                    // Improved error handling with detailed information
                    let errorMessage = '{{ __('vendorwebsite.server_error') }}';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 500) {
                        errorMessage = '{{ __('vendorwebsite.internal_server_error') }}';
                    } else if (xhr.status === 422) {
                        errorMessage = '{{ __('vendorwebsite.validation_error') }}';
                    } else if (xhr.status === 404) {
                        errorMessage = 'Service not found. Please contact support.';
                    }

                    console.error('TopUp Error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });

                    Swal.fire({
                        title: '{{ __('vendorwebsite.error') }}',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '{{ route('wallet') }}';
                        }
                    });
                }
            });
        });

        window.addEventListener('DOMContentLoaded', () => {
            const defaultPayment = document.querySelector('input[name="payment_method"][value="Stripe"]');
            if (defaultPayment) {
                defaultPayment.checked = true;
            }

            // Add click handlers for payment method cards
            document.querySelectorAll('.payment-method-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    // Don't trigger if clicking on the radio button itself
                    if (e.target.type === 'radio') {
                        return;
                    }

                    // Find the radio button within this card
                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) {
                        radio.checked = true;

                        // Remove active class from all cards
                        document.querySelectorAll('.payment-method-card').forEach(c => {
                            c.classList.remove('active');
                        });

                        // Add active class to clicked card
                        this.classList.add('active');
                    }
                });
            });

            // Handle radio button change to update card styling and dropdown header
            document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    // Remove active class from all cards
                    document.querySelectorAll('.payment-method-card').forEach(card => {
                        card.classList.remove('active');
                    });

                    // Add active class to the card containing the checked radio
                    if (this.checked) {
                        const card = this.closest('.payment-method-card');
                        if (card) {
                            card.classList.add('active');
                        }

                        // Update dropdown header
                        updatePaymentMethodHeader(this.value);
                    }
                });
            });

            // Set initial active state
            const checkedRadio = document.querySelector('input[name="payment_method"]:checked');
            if (checkedRadio) {
                const card = checkedRadio.closest('.payment-method-card');
                if (card) {
                    card.classList.add('active');
                }
                // Update dropdown header with initial selection
                updatePaymentMethodHeader(checkedRadio.value);
            }

            // Function to update payment method header
            function updatePaymentMethodHeader(selectedMethod) {
                const iconElement = document.getElementById('selected-payment-icon');
                const textElement = document.getElementById('selected-payment-text');

                if (selectedMethod === 'Stripe') {
                    iconElement.src = "{{ asset('img/vendorwebsite/stripe-payment.png') }}";
                    textElement.textContent = "{{ __('vendorwebsite.stripe') }}";
                } else if (selectedMethod === 'Razorpay') {
                    iconElement.src = "{{ asset('img/vendorwebsite/razorpay.svg') }}";
                    textElement.textContent = "{{ __('vendorwebsite.razorpay') }}";
                }
            }
        });

        function openRazorpay(options) {

            var razorpay = new Razorpay({
                key: options.key,
                amount: options.amount, // Backend now sends the correct amount in smallest unit
                currency: options.formattedCurrency,
                name: options.name,
                description: 'Wallet Top-Up',
                order_id: options.order_id,
                handler: function(response) {

                    axios.post(options.redirect_url, {
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_signature: response.razorpay_signature,
                        transaction_id: options.transaction_id
                    }).then(res => {
                        window.location.reload();
                    }).catch(err => {
                        Swal.fire({
                            title: '{{ __('vendorwebsite.error') }}',
                            text: '{{ __('vendorwebsite.payment_failed') }}',
                            icon: 'error',
                            confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '{{ route('wallet') }}';
                            }
                        });
                    });
                },
                prefill: {
                    name: options.name,
                    email: options.email,
                    contact: options.contact
                },
                theme: {
                    color: "#0D6EFD"
                }
            });

            try {
                razorpay.open();
            } catch (error) {
                console.error('Razorpay error:', error);
                Swal.fire({
                    title: '{{ __('vendorwebsite.error') }}',
                    text: '{{ __('vendorwebsite.payment_gateway_error') }}',
                    icon: 'error',
                    confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ route('wallet') }}';
                    }
                });
            }
        }

        function openBankModal() {
            // Reset the form first
            resetModalForAdd();

            // Temporarily hide the withdraw modal
            $('#withdrawModal').modal('hide');

            // Remove any existing backdrop that might interfere
            $('.modal-backdrop').remove();

            // Open the bank modal
            $('#bankInfoModal').modal('show');

            // Ensure modal is properly positioned and interactive
            setTimeout(function() {
                $('#bankInfoModal input:first').focus();
                // Force enable all form elements
                $('#bankInfoModal input, #bankInfoModal select, #bankInfoModal textarea, #bankInfoModal button')
                    .prop('disabled', false);
            }, 200);
        }

        function resetModalForAdd() {
            // Reset form fields
            $('#bankForm')[0].reset();
            $('#bankId').val('');
            $('#modalTitle').text('{{ __('Bank.add_bank') }}');

            // Clear error messages
            $('.invalid-feedback').hide();
            $('.is-invalid').removeClass('is-invalid');

            // Reset submit button
            $('#submitButton').text('{{ __('Bank.save') }}');
        }

        function closeBankModal() {
            // Close the bank modal
            $('#bankInfoModal').modal('hide');

            // Show the withdraw modal again
            setTimeout(function() {
                $('#withdrawModal').modal('show');
            }, 200);
        }

        // Handle bank form submission and modal events
        $(document).ready(function() {
            // Ensure bank modal is interactive when shown
            $('#bankInfoModal').on('shown.bs.modal', function() {
                $(this).find('input:first').focus();
                $(this).find('input, select, textarea, button').prop('disabled', false);
            });

            $('#bankForm').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.status) {
                            // Close bank modal
                            $('#bankInfoModal').modal('hide');

                            // Show success message
                            window.successSnackbar(
                                '{{ __('vendorwebsite.bank_added_successfully') }}');

                            // Show withdraw modal again
                            setTimeout(function() {
                                $('#withdrawModal').modal('show');

                                // Update bank list without page reload
                                $.ajax({
                                    url: '{{ route('wallet') }}',
                                    method: 'GET',
                                    success: function(htmlResponse) {
                                        // Extract the bank select options from the response
                                        const tempDiv = $('<div>').html(
                                            htmlResponse);
                                        const newBankSelect = tempDiv.find(
                                            '#chooseBank');

                                        if (newBankSelect.length > 0) {
                                            // Update the bank select options
                                            $('#chooseBank').html(
                                                newBankSelect.html());

                                            // Refresh select2
                                            $('#chooseBank').select2(
                                                'destroy').select2({
                                                dropdownParent: $(
                                                    '#withdrawModal'
                                                ),
                                                width: '100%',
                                                placeholder: '{{ __('vendorwebsite.select_bank') }}'
                                            });

                                            // Select the newly added bank (first option after the placeholder)
                                            $('#chooseBank option:eq(1)')
                                                .prop('selected', true);
                                            $('#chooseBank').trigger(
                                                'change');
                                        }
                                    },
                                    error: function() {
                                        // If AJAX fails, reload the page as fallback
                                        setTimeout(function() {
                                            window.location
                                                .reload();
                                        }, 1000);
                                    }
                                });
                            }, 200);
                        } else {
                            // Show error message
                            window.errorSnackbar(response.message ||
                                '{{ __('vendorwebsite.error_adding_bank') }}');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            // Clear previous errors
                            $('.invalid-feedback').hide();
                            $('.is-invalid').removeClass('is-invalid');

                            // Show validation errors
                            $.each(xhr.responseJSON.errors, function(field, errors) {
                                $('#' + field + '_error').text(errors[0]).show();
                                $('#' + field).addClass('is-invalid');
                            });
                        } else {
                            window.errorSnackbar(
                                '{{ __('vendorwebsite.error_adding_bank') }}');
                        }
                    }
                });
            });
        });
    </script>
@endpush

<style>
    .swal2-topup-zindex {
        z-index: 20000 !important;
    }

    /* Ensure bank modal is properly styled */
    #bankInfoModal {
        z-index: 1055 !important;
    }

    #bankInfoModal .modal-dialog {
        z-index: 1056 !important;
    }

    #bankInfoModal .modal-content {
        z-index: 1057 !important;
    }

    /* Ensure form elements are interactive */
    #bankInfoModal input,
    #bankInfoModal select,
    #bankInfoModal textarea,
    #bankInfoModal button {
        pointer-events: auto !important;
    }

    /* Payment method card styles */
    .payment-method-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 8px;
    }

    /* .payment-method-card:hover {
    background-color: rgba(13, 110, 253, 0.05);
    border-color: rgba(13, 110, 253, 0.2);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.payment-method-card.active {
    background-color: rgba(13, 110, 253, 0.1);
    border-color: #0d6efd;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.2);
} */

    .payment-method-card .form-check-input {
        margin-right: 0;
    }

    .payment-method-card .form-check-label {
        cursor: pointer;
        margin-bottom: 0;
    }
</style>

<!-- Add Bank Modal (copied from bank_list.blade.php) -->
<div class="modal fade" id="bankInfoModal" tabindex="-1" aria-labelledby="bankInfoModalLabel" aria-hidden="true"
    data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="bankForm" method="POST" action="{{ route('bank.store') }}">
                @csrf
                <input type="hidden" id="bankId" name="bank_id">
                <div class="modal-body">
                    <h6 id="modalTitle" class="font-size-21-3 mb-3">{{ __('Bank.add_bank') }}</h6>
                    <div class="row gy-4">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="branch_name"
                                    class="form-label fw-medium">{{ __('Bank.branch_name') }}</label>
                                <span class="text-danger">*</span>
                                <div class="input-group custom-input-group">
                                    <input type="text" name="branch_name" id="branch_name" class="form-control"
                                        placeholder="{{ __('Bank.placeholder_branch_name') }}" />
                                    <span class="input-group-text"><i class="ph ph-piggy-bank"></i></span>
                                </div>
                                <div class="invalid-feedback" id="branch_name_error"></div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="bank_name"
                                    class="form-label fw-medium">{{ __('Bank.bank_name') }}</label>
                                <span class="text-danger">*</span>
                                <div class="input-group custom-input-group">
                                    <input type="text" name="bank_name" id="bank_name" class="form-control"
                                        placeholder="{{ __('Bank.placeholder_bank_name') }}" />
                                    <span class="input-group-text"><i class="ph ph-piggy-bank"></i></span>
                                </div>
                                <div class="invalid-feedback" id="bank_name_error"></div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="account_no"
                                    class="form-label fw-medium">{{ __('Bank.account_number') }}</label>
                                <span class="text-danger">*</span>
                                <div class="input-group custom-input-group">
                                    <input type="text" name="account_no" id="account_no" class="form-control"
                                        placeholder="{{ __('Bank.placeholder_account_number') }}" />
                                    <span class="input-group-text"><i class="ph ph-dots-three-circle"></i></span>
                                </div>
                                <div class="invalid-feedback" id="account_no_error"></div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="ifsc_no" class="form-label fw-medium">{{ __('Bank.ifsc_code') }}</label>
                                <span class="text-danger">*</span>
                                <div class="input-group custom-input-group">
                                    <input type="text" name="ifsc_no" id="ifsc_no" class="form-control"
                                        placeholder="{{ __('Bank.eg_SBIN5642310') }}" />
                                    <span class="input-group-text"><i class="ph ph-user"></i></span>
                                </div>
                                <div class="invalid-feedback" id="ifsc_no_error"></div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group d-flex justify-content-between align-items-center">
                                <label for="status"
                                    class="form-label fw-medium mb-0">{{ __('Bank.status') }}</label>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="status" name="status"
                                        value="active" checked>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="d-flex align-items-center justify-content-end gap-lg-4 gap-2 flex-wrap mt-5 pt-lg-3 pt-0">
                        <button type="button" class="btn btn-primary"
                            onclick="closeBankModal()">{{ __('Bank.cancel') }}</button>
                        <button type="submit" id="submitButton"
                            class="btn btn-secondary">{{ __('Bank.save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
