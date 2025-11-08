@extends('backend.layouts.app')

@section('title')
    {{ __($module_action) }} 
@endsection


@push('after-styles')
    <link rel="stylesheet" href="{{ mix('modules/constant/style.css') }}">
@endpush
@section('content')
    <div class="card">
    <div class="card-body">
    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
        <h4 id="form-offcanvasLabel" class="mb-0">{{ isset($payment) ? __('frontend.edit_payment') : __('frontend.create_payment') }}</h4>
        <a href="javascript:history.back()" class="btn btn-primary">{{ __('frontend.back') }}</a>
    </div>
    <form id="payment-form" enctype="multipart/form-data" method="POST" action="{{ route('backend.payment.store')}}">
        @csrf
        <input type="hidden" name="id" value="{{ isset($payment) ? $payment->id : null }}">       
        <div class="form-group">
            <label class="form-label" for="user_id">{{ __('frontend.admin') }} <span class="text-danger">*</span></label>
            <select class="form-select select2" id="user_id" name="user_id">
                <option value="" disabled selected>{{ __('frontend.select_user') }}</option>
                @foreach ($users as $user)
                    <option value="{{ (isset($payment) ? $payment->user : $user->id ) }}" {{ (isset($payment) && $payment->user_id == $user->id) ? 'selected' : '' }}>{{ $user->getFullNameAttribute() }}</option>
                @endforeach
            </select>
            <span class="error text-danger"></span>
        </div>

        
        <div class="form-group">
            <label class="form-label" for="plan_id">{{ __('frontend.plans') }} <span class="text-danger">*</span></label>
            <select class="form-select select2" id="plan_id" name="plan_id">
                <option value="" disabled selected>{{ __('frontend.select_plan') }}</option>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}" 
                            data-price="{{ $plan->total_price }}" 
                            data-formatted-price="{{ \Currency::format($plan->total_price) }}" 
                            data-currency="{{ defaultCurrency() }}" 
                            {{ (isset($payment) && $payment->plan_id == $plan->id) ? 'selected' : '' }}>
                        {{ $plan->name }} ({{ $plan->duration . '-' . str_replace('ly', '', $plan->type) }})
                    </option>
                @endforeach
            </select>
            <span class="error text-danger"></span>
        </div>

        <div class="form-group">
            <label class="form-label" for="amount">{{ __('frontend.amount') }} ({{ defaultCurrencySymbol() ?? '' }})</label>
            <input type="text" id="amount_display" class="form-control" placeholder="{{ __('frontend.enter_amount') }}" value="{{ isset($payment) ? $payment->amount_display : '' }}" readonly>
            <input type="hidden" id="amount" name="amount" value="{{ isset($payment) ? $payment->amount : '' }}" />
            <span class="error text-danger"></span>
        </div>

        {{-- <div class="form-group">
            <label class="form-label" for="payment_method">{{ __('frontend.payment_method') }} <span class="text-danger">*</span></label>
            <select class="form-select" id="payment_method" name="payment_method">
                <option value="1" {{ (isset($payment) && $payment->payment_method == 1) ? 'selected' : '' }}>{{ __('frontend.offline') }}</option>
                <option value="2" {{ (isset($payment) && $payment->payment_method == 2) ? 'selected' : '' }}>{{ __('frontend.online') }}</option>
            </select>
            <span class="error text-danger"></span>
        </div> --}}

        <div class="form-group">
            <label class="form-label" for="payment_date">{{ __('frontend.payment_date') }} <span class="text-danger">*</span></label>
            <input type="date" id="payment_date" name="payment_date" placeholder="{{ __('frontend.payment_date_placeholder') }}" value="{{ isset($payment) ? \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') : '' }}" class="form-control" max="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
        </div>

     
        <button type="submit" class="btn btn-primary mt-4" id="submit-button">
    <span class="spinner-border spinner-border-sm me-2 d-none" id="submit-spinner" role="status" aria-hidden="true"></span>
    {{ __('frontend.submit') }}
</button>

    </form>
</div>
    </div>
@endsection

@push('after-styles')
    <!-- DataTables Core and Extensions -->
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push('after-scripts')
<script>
    // Initialize form validation
    $("#payment-form").validate({
        rules: {
            user_id: { required: true },
            plan_id: { required: true },
            payment_date: { required: true, date: true }
        },
        errorElement: "span",
        errorClass: "error text-danger",
        highlight: function (element) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element) {
            $(element).removeClass('is-invalid');
        },
        errorPlacement: function (error, element) {
                if (element.hasClass("select2-hidden-accessible")) {
                    error.insertAfter(element.next(".select2-container")); // Places error after Select2 dropdown
                } else {
                    error.insertAfter(element);
                }
        },
            submitHandler: function (form) {
                $('#submit-button').attr('disabled', true);
                $('#submit-spinner').removeClass('d-none');

                form.submit(); // Submit the form
            }
    });

    // Plan selection change handler
    $('#plan_id').change(function(){
        var selectedPlan = $('#plan_id option:selected');
        var formattedPrice = selectedPlan.data('formatted-price'); 
        var rawPrice = selectedPlan.data('price');
  
        formattedPrice = formattedPrice.replace(/(\D)\s+(\d)/, '$1$2');

        // Update price display
        $('#amount_display').val(formattedPrice);  
        $('#amount').val(rawPrice);  
    });
    
    // Initialize date picker if flatpickr is available
    document.addEventListener('DOMContentLoaded', function () {
        const paymentDateInput = document.getElementById('payment_date');

    if (typeof flatpickr !== typeof undefined) {
        flatpickr(paymentDateInput, {
            dateFormat: "Y-m-d", // Adjust the date format as needed
            maxDate: "today", // bug fixed Id : #86cxww76d 
            onReady: function(selectedDates, dateStr, instance) {
                // Check if the body has the 'dark' class
                    if (document.body.classList.contains('dark')) {
                        instance.calendarContainer.classList.add('flatpickr-dark'); // Add a custom class for dark mode
                    }
                }
            });
        }
    });

    </script>

@endpush
