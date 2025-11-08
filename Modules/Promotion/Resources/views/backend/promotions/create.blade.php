@extends('backend.layouts.app')

@section('title')
    {{ __('messages.create') }} {{ $module_title }}
@endsection

@push('after-styles')
    <link rel="stylesheet" href="{{ mix('modules/constant/style.css') }}">
@endpush

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between gap-4 flex-wrap mb-4">
                <h4 id="form-offcanvasLabel">{{ isset($promotion) ? __('messages.edit') . ' ' . $module_title : __('messages.create') . ' ' . $module_title }}</h4>
                <a href="{{ route('backend.promotions.index') }}" class="btn btn-primary">{{ __('promotion.back') }}</a>
            </div>
            <form id="promotion-form" enctype="multipart/form-data" method="POST" action="{{ isset($promotion) ? route('backend.promotions.update', $promotion->id) : route('backend.promotions.store') }}">
                @csrf
                @if(isset($promotion))
                    @method('PUT')
                @endif
                <input type="hidden" name="id" value="{{ isset($promotion) ? $promotion->id : null }}">
                <input type="hidden" name="coupon_type" value="custom">


                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="name">{{ __('promotion.lbl_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="{{ __('service.enter_name') }}" value="{{ isset($promotion) ? $promotion->name : '' }}">
                            <span class="error text-danger"></span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="coupon_code">{{ __('promotion.coupon_code') }} <span class="text-danger">*</span></label>
                            <input type="text" name="coupon_code" id="coupon_code" class="form-control" placeholder="{{ __('promotion.enter_coupon_code') }}" value="{{ isset($promotion) && $promotion->coupon ? $promotion->coupon->coupon_code : '' }}">
                            <span class="error text-danger"></span>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label" for="description">{{ __('promotion.description') }} <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="{{ __('messages.placeholder_description') }}">{{ isset($promotion) ? $promotion->description : '' }}</textarea>
                            <span class="error text-danger"></span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="start_date_time">{{ __('promotion.start_date') }}</label>
                            <input type="text" name="start_date_time" id="start_date_time" class="form-control flatpickr" placeholder="{{ __('promotion.select_start_date') }}" value="{{ isset($promotion) && $promotion->coupon ? \Carbon\Carbon::parse($promotion->coupon->start_date_time)->format('Y-m-d') : '' }}">
                            <span class="error text-danger"></span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="end_date_time">{{ __('promotion.end_date') }}</label>
                            <input type="text" name="end_date_time" id="end_date_time" class="form-control flatpickr" placeholder="{{ __('promotion.select_end_date') }}" value="{{ isset($promotion) && $promotion->coupon ? \Carbon\Carbon::parse($promotion->coupon->end_date_time)->format('Y-m-d') : '' }}">
                            <span class="error text-danger"></span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="discount_type">{{ __('promotion.percent_or_fixed') }}</label>
                            <select class="form-control select2" name="discount_type" id="discount_type">
                                <option value="percent" {{ (isset($promotion) && $promotion->coupon && $promotion->coupon->discount_type == 'percent') ? 'selected' : '' }}>{{ __('product.percent') }}</option>
                                <option value="fixed" {{ (isset($promotion) && $promotion->coupon && $promotion->coupon->discount_type == 'fixed') ? 'selected' : '' }}>{{ __('messages.lbl_fixed') }}</option>
                            </select>
                            <span class="error text-danger"></span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group" id="discount_percent_field" style="{{ (isset($promotion) && $promotion->coupon && $promotion->coupon->discount_type == 'percent') || !isset($promotion) ? 'display:block;' : 'display:none;' }}">
                            <label class="form-label" for="discount_percentage">{{ __('promotion.discount_percentage') }} <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="discount_percentage" id="discount_percentage" class="form-control" placeholder="{{ __('promotion.enter_discount_percentage') }}" value="{{ isset($promotion) && $promotion->coupon ? $promotion->coupon->discount_percentage : '' }}">
                            <span class="error text-danger"></span>
                        </div>
                        <div class="form-group" id="discount_fixed_field" style="{{ (isset($promotion) && $promotion->coupon && $promotion->coupon->discount_type == 'fixed') ? 'display:block;' : 'display:none;' }}">
                            <label class="form-label" for="discount_amount">{{ __('promotion.discount_amount') }} <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="discount_amount" id="discount_amount" class="form-control" placeholder="{{ __('promotion.enter_discount_amount') }}" value="{{ isset($promotion) && $promotion->coupon ? $promotion->coupon->discount_amount : '' }}">
                            <span class="error text-danger"></span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="use_limit">{{ __('promotion.use_limit') }}</label>
                            <input type="number" name="use_limit" id="use_limit" class="form-control" placeholder="{{ __('promotion.enter_use_limit') }}" value="{{ isset($promotion) && $promotion->coupon ? $promotion->coupon->use_limit : '1' }}">
                            <span class="error text-danger"></span>
                        </div>
                    </div>

                    <div class="col-md-6 form-group">
                        <label class="form-label">{{ __('service.lbl_status') }}</label>
                        <div class="form-control form-check form-switch">
                            <div class="d-flex align-items-center justify-content-between">
                                <label class="form-label">{{ __('service.lbl_status') }}</label>
                                <input class="form-check-input" name="status" type="checkbox" value="1" {{ (isset($promotion) && $promotion->status == 0) ? '' : 'checked' }}>
                            </div>
                            <span class="error text-danger"></span>
                        </div>
                    </div>

                    @if(auth()->user()->hasRole('super admin'))
                    <div class="col-md-12 form-group">
                        <label class="form-label" for="plan_id">{{ __('promotion.Select_Plan') }} <span class="text-danger">*</span></label>
                        <select class="form-control select2" name="plan_id[]" id="plan_id" multiple>
                            @if(isset($plans) && count($plans) > 0)
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" 
                                        {{ (isset($promotion) && isset($promotion['plan_ids']) && in_array($plan->id, $promotion['plan_ids'])) ? 'selected' : '' }}>
                                        {{ $plan->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <span class="error text-danger"></span>
                    </div>
                    @endif
                </div>
                <button type="submit" id="promotion-submit-btn" class="btn btn-primary mt-4">{{ __('promotion.submit') }}</button>
            </form>
        </div>
    </div>
@endsection

@push('after-styles')
    <!-- DataTables Core and Extensions -->
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push('after-scripts')
    <script src="{{ mix('js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize flatpickr for date fields
            flatpickr("#start_date_time", {
                enableTime: false,
                dateFormat: "Y-m-d",
                minDate: "today" // Disable past dates
            });
            
            flatpickr("#end_date_time", {
                enableTime: false,
                dateFormat: "Y-m-d",
                minDate: "today" // Disable past dates
            });

            // Handle discount type change
            $('#discount_type').change(function() {
                const discountType = $(this).val();
                if (discountType === 'percent') {
                    $('#discount_percent_field').show();
                    $('#discount_fixed_field').hide();
                } else {
                    $('#discount_percent_field').hide();
                    $('#discount_fixed_field').show();
                }
            }).trigger('change'); // Trigger on load to set initial state

            // Add custom validation method for end date
            $.validator.addMethod("greaterThanStartDate", function(value, element) {
                var startDate = $('#start_date_time').val();
                if (!startDate || !value) {
                    return true; // Let required validation handle empty values
                }
                
                var startDateTime = new Date(startDate);
                var endDateTime = new Date(value);
                
                return endDateTime > startDateTime;
            }, "End date must be after start date");

            // Add custom validation method for duplicate coupon code
            $.validator.addMethod("uniqueCouponCode", function(value, element) {
                var couponCode = value;
                var promotionId = $('input[name="id"]').val(); // Get current promotion ID (null for new)
                
                if (!couponCode) {
                    return true; // Let required validation handle empty values
                }
                
                // Make AJAX request to check if coupon code exists
                var isValid = true;
                $.ajax({
                    url: "{{ route('backend.promotions.check_coupon_code') }}",
                    method: 'POST',
                    async: false, // Make it synchronous for validation
                    data: {
                        _token: "{{ csrf_token() }}",
                        coupon_code: couponCode,
                        promotion_id: promotionId
                    },
                    success: function(response) {
                        isValid = response.available;
                    },
                    error: function() {
                        isValid = false;
                    }
                });
                
                return isValid;
            }, "This coupon code is already taken");

            // Add custom validation method for spaces-only coupon code
            $.validator.addMethod("noSpacesOnly", function(value, element) {
                if (!value) {
                    return true; // Let required validation handle empty values
                }
                // Check if the value contains only spaces
                return value.trim().length > 0;
            }, "Coupon code cannot contain only spaces");

            // Add real-time validation for date fields
            $('#start_date_time, #end_date_time').on('change', function() {
                // Re-validate end date when start date changes
                if ($(this).attr('id') === 'start_date_time') {
                    $('#end_date_time').valid();
                }
                // Re-validate end date when end date changes
                if ($(this).attr('id') === 'end_date_time') {
                    $(this).valid();
                }
            });

            // Add real-time validation for coupon code
            $('#coupon_code').on('blur', function() {
                $(this).valid();
            });

            // Form validation
            $("#promotion-form").validate({
                rules: {
                    name: {
                        required: true,
                        maxlength: 255
                    },
                    description: {
                        required: true,
                        maxlength: 1000
                    },
                    coupon_code: {
                        required: true,
                        maxlength: 50,
                        uniqueCouponCode: true,
                        noSpacesOnly: true
                    },
                    discount_percentage: {
                        required: function() {
                            return $('#discount_type').val() === 'percent';
                        },
                        min: 0,
                        max: 100
                    },
                    discount_amount: {
                        required: function() {
                            return $('#discount_type').val() === 'fixed';
                        },
                        min: 0
                    },
                    use_limit: {
                        required: true,
                        min: 1
                    },
                    start_date_time: {
                        required: true
                    },
                    end_date_time: {
                        required: true,
                        greaterThanStartDate: true
                    },
                    'plan_id[]': {
                        required: function() {
                            return {{ auth()->user()->hasRole('super admin') ? 'true' : 'false' }};
                        }
                    }
                },
                messages: {
                    name: {
                        required: "{{ __('promotion.lbl_name') }} is required",
                        maxlength: "{{ __('promotion.lbl_name') }} cannot exceed 255 characters"
                    },
                    description: {
                        required: "{{ __('promotion.description') }} is required",
                        maxlength: "{{ __('promotion.description') }} cannot exceed 1000 characters"
                    },
                    coupon_code: {
                        required: "{{ __('promotion.coupon_code') }} is required",
                        maxlength: "{{ __('promotion.coupon_code') }} cannot exceed 50 characters",
                        noSpacesOnly: "{{ __('promotion.coupon_code') }} cannot contain only spaces"
                    },
                    discount_percentage: {
                        required: "{{ __('promotion.discount_percentage') }} is required",
                        min: "{{ __('promotion.discount_percentage') }} must be at least 0",
                        max: "{{ __('promotion.discount_percentage') }} cannot exceed 100"
                    },
                    discount_amount: {
                        required: "{{ __('promotion.discount_amount') }} is required",
                        min: "{{ __('promotion.discount_amount') }} must be at least 0"
                    },
                    use_limit: {
                        required: "{{ __('promotion.use_limit') }} is required",
                        min: "{{ __('promotion.use_limit') }} must be at least 1"
                    },
                    start_date_time: {
                        required: "{{ __('promotion.start_date') }} is required"
                    },
                    end_date_time: {
                        required: "{{ __('promotion.end_date') }} is required",
                        greaterThanStartDate: "{{ __('promotion.end_date') }} must be after {{ __('promotion.start_date') }}"
                    },
                    'plan_id[]': {
                        required: "{{ __('promotion.Select_Plan') }} is required"
                    }
                },
                errorElement: "span",
                errorClass: "error text-danger",
                highlight: function(element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element) {
                    $(element).removeClass('is-invalid');
                },
                submitHandler: function(form) {
                    console.log('Form validation passed, submitting...');
                    
                    // Prevent double-clicking
                    const submitBtn = $('#promotion-submit-btn');
                    if (submitBtn.prop('disabled')) {
                        console.log('Form already submitted, preventing duplicate');
                        return false;
                    }
                    
                    // Disable button and show loading state
                    submitBtn.prop('disabled', true);
                    const originalText = submitBtn.text();
                    submitBtn.html('<i class="fa fa-spinner fa-spin"></i> {{ __("promotion.saving") }}...');
                    
                    $(form).find('.error').remove();
                    
                    console.log('Form data being submitted:', $(form).serialize());
                    console.log('Submitting form to:', form.action);
                    
                    // Let the form submit naturally
                    return true;
                },
            });
        });
    </script>
@endpush
