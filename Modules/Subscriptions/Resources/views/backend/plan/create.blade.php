@extends('backend.layouts.app')

@section('title')
{{ isset($plan) ? __('Edit') : __('Create') }} {{ __($module_title) }}@endsection
@php
    // Convert the permission IDs to an array once
   // Clean the permissionIds
$permissionIds = isset($plan) && $plan->permission_ids 
    ? array_map(function($item) {
        // Remove the extra brackets and quotes
        return trim(preg_replace('/[\"\[\]]/', '', $item)); 
    }, explode(',', $plan->permission_ids)) 
    : [];


@endphp

@push('after-styles')
    <link rel="stylesheet" href="{{ mix('modules/constant/style.css') }}">
@endpush
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex gap-3 flex-wrap align-items-center justify-content-between mb-3">
                <h4 id="form-offcanvasLabel">{{ isset($plan) ? __('frontend.edit_plan') : __('frontend.create_plan') }}</h4>
                <a href="{{ route('backend.subscription.plans.index') }}" class="btn btn-primary">{{__('frontend.back')}}</a>
            </div>
            <form id="plan-form" method="POST" action="{{ route('backend.subscription.plans.store') }}">
                @csrf
                <input type="hidden" name="plan_type" value="paid_plan" hidden>
                <div class="mt-4">
                    <ul class="nav nav-tabs gap-2 list-inline" id="planTypeTabs" role="tablist">
                        <!-- Paid Plan Tab -->
                        <li class="nav-item">
                            <a class="nav-link {{ (isset($plan) && $plan->price == 0 ) ? '' : 'active'}}" id="paid-plan-tab" data-toggle="tab" href="#paid-plan" role="tab" aria-controls="paid-plan" aria-selected="false">{{__('frontend.paid_plan')}}</a>
                        </li>
                        @if($freeplan === null )
                            <!-- Free Plan Tab -->
                            <li class="nav-item">
                                <a class="nav-link {{ (isset($plan) && $plan->price == 0 ) ? 'active' : ''}}" id="free-plan-tab" data-toggle="tab" href="#free-plan" role="tab" aria-controls="free-plan" aria-selected="true">{{__('frontend.free_plan')}}</a>
                            </li>
                        @endif
                        
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content">
                         <!-- Paid Plan Tab Content -->
                         <div class="tab-pane fade {{ (isset($plan) && $plan->price == 0 ) ? '' : 'show active'}}" id="paid-plan" role="tabpanel" aria-labelledby="paid-plan-tab">
                            <div class="mt-4">
                                <ul class="nav nav-tabs gap-2 mb-4 list-inline" id="planTabs" role="tablist">
                                    <!-- Details Tab -->
                                    <li class="nav-item">
                                        <a class="nav-link active" id="details-tab" data-toggle="tab" href="#details" role="tab" aria-controls="details" aria-selected="true">{{__('frontend.details')}}</a>
                                    </li>
                                    <!-- Permissions Tab -->
                                    <li class="nav-item">
                                        <a class="nav-link" id="permissions-tab" data-toggle="tab" href="#permissions" role="tab" aria-controls="permissions" aria-selected="false">{{__('frontend.permissions')}}</a>
                                    </li>
                                    <!-- Limits Tab -->
                                    <li class="nav-item">
                                        <a class="nav-link" id="limits-tab" data-toggle="tab" href="#limits" role="tab" aria-controls="limits" aria-selected="false">{{__('frontend.limits')}}</a>
                                    </li>
                                </ul>

                                <!-- Tab Content -->
                                <div class="tab-content">
                                    <!-- Details Tab Content -->
                                    <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                                        <div class="row">
                                            <!-- Name Input -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="name">{{__('frontend.name')}} <span class="text-danger">*</span></label>
                                                    <input type="hidden" class="form-control" name="id" value="{{ old('id', $plan->id ?? null) }}" hidden>
                                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $plan->name ?? '') }}" required>
                                                    <span class="error text-danger"></span>
                                                    @error('name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                                </div>
                                            </div>

                                            <!-- Type Dropdown -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="type">{{__('frontend.interval')}} <span class="text-danger">*</span></label>
                                                    <select class="form-select select2" id="type" name="type" onchange="toggleDurationField()" required>
                                                        <option value="" disabled selected>{{ __('messages.select_type') }}</option>
                                                        <option value="Monthly" {{ old('type', $plan->type ?? '') == 'Monthly' ? 'selected' : '' }}> {{__('messages.monthly')}}</option>
                                                        <option value="Yearly" {{ old('type', $plan->type ?? '') == 'Yearly' ? 'selected' : '' }}> {{__('messages.yearly')}}</option>
                                                    </select>
                                                    <span class="error text-danger"></span>
                                                </div>
                                            </div>

                                            <div class="col-md-6" id="durationField" style="display: {{ (isset($plan) &&  $plan->type !== 'Yearly') ? 'block' : 'none'}};">
                                                <div class="form-group">
                                                    <label class="form-label" for="duration">{{__('frontend.duration')}}</label>
                                                    <input type="number" min="1" name="duration" class="form-control" placeholder="{{ __('messages.enter_duration') }}" value="{{ isset($plan) ? $plan->duration : 1 }}">
                                                    <span class="error text-danger"></span>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="price"> {{__('frontend.price')}} ({{ defaultCurrencySymbol() ?? '' }})
                                                        <span class="text-danger">*</span></label>
                                                    <input type="number" min="1" class="form-control" id="price" name="price" value="{{ old('price', $plan->price ?? '') }}" required>
                                                    <span class="error text-danger"></span>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">{{__('frontend.discount')}}</label>

                                                    
                                                     <div class=" d-flex align-items-center justify-content-between form-control">
                                                        <label class="form-check-label" for="has_discount" >{{__('frontend.discount')}}</label>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" name="has_discount" type="checkbox" name="has_discount" id="has_discount"  {{ old('has_discount', $plan->has_discount ?? '') ? 'checked' : '' }}>
                                                        </div>
                                                    </div>
                                                    <!-- <div class="form-check form-switch mt-2">
                                                        <input class="form-check-input"  id="has_discount" type="checkbox"
                                                            {{ old('has_discount', $plan->has_discount ?? '') ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="has_discount">
                                                            {{__('frontend.enable_discount_help')}}
                                                        </label>
                                                    </div>  -->
                                                </div>
                                            </div>

                                            <div id="discount-fields" class="row {{ old('has_discount', $plan->has_discount ?? '') ? '' : 'd-none' }}">

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="form-label">
                                                            {{__('frontend.discount_type')}}
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <div class="d-flex gap-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="discount_type" 
                                                                    id="discount_type_fixed" value="fixed" 
                                                                    {{ old('discount_type', $plan->discount_type ?? '') == 'fixed' ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="discount_type_fixed">
                                                                    {{__('frontend.fixed')}}
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="discount_type" 
                                                                    id="discount_type_percentage" value="percentage"
                                                                    {{ old('discount_type', $plan->discount_type ?? '') == 'percentage' ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="discount_type_percentage">
                                                                    {{__('frontend.percentage')}}
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <span class="error text-danger"></span>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="form-label" for="discount_value">
                                                            {{__('frontend.discount_value')}} 
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="number" min="0" step="0.01" class="form-control" 
                                                            id="discount_value" name="discount_value" 
                                                            value="{{ old('discount_value', $plan->discount_value ?? '') }}">
                                                        <span class="error text-danger"></span>
                                                    </div>
                                                </div>


                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="form-label" for="discounted_price">
                                                            {{__('frontend.discounted_price')}}
                                                            <span class="text-danger">*</span>
                                                            <span class="text-muted">({{ defaultCurrencySymbol() ?? '' }})</span>
                                                        </label>
                                                        <input type="text" class="form-control" id="discounted_price" 
                                                            name="discounted_price" readonly>
                                                        <span class="error text-danger"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Description -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="description">{{__('frontend.description')}}</label>
                                                    <textarea class="form-control" id="description" name="description">{{ old('description', $plan->description ?? '') }}</textarea>
                                                    <span class="error text-danger"></span>
                                                </div>
                                            </div>

                                            <!-- Status Checkbox -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="feature">{{__('frontend.features')}}</label>
                                                    <div id="features-container">
                                                        <!-- Default input field -->
                                                        @forelse ($features as $feature)
                                                            <div class="row">
                                                                <div class="col-md-8 col-7">
                                                                    <input type="text" name="features[]" class="form-control mb-2" value="{{ $feature->title }}" placeholder="{{ __('messages.enter_feature') }}">
                                                                </div>
                                                                <div class="col-md-4 col-5">
                                                                    <button type="button" class="btn btn-success btn-add px-md-4 px-3">+</button>
                                                                    <button type="button" class="btn btn-danger btn-remove px-md-4 px-3">-</button>
                                                                </div>
                                                            </div>
                                                        @empty
                                                            <div class="row">
                                                                <div class="col-md-8 col-7">
                                                                    <input type="text" name="features[]" class="form-control mb-2" placeholder="{{ __('messages.enter_feature') }}">
                                                                </div>
                                                                <div class="col-md-4 col-5">
                                                                    <button type="button" class="btn btn-success btn-add  px-md-4 px-3">
                                                                        <i class="zmdi zmdi-plus"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-danger btn-remove  px-md-4 px-3">
                                                                        <i class="zmdi zmdi-minus"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="checkbox inlineblock">
                                                    <input name="status" type="checkbox" {{ (isset($plan) && $plan->status == 0) ? '' : 'checked' }}>
                                                    <label for="remember_me_3">{{__('frontend.status')}}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Permissions Tab Content -->
                                    <div class="tab-pane fade" id="permissions" role="tabpanel" aria-labelledby="permissions-tab">
                                        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-2">
                                            <div>
                                                <span class="error text-danger"></span>
                                            </div>
                                            <div class="form-check form-switch">
                                                <label class="form-label">{{__('messages.all')}}</label>
                                                <input class="form-check-input permission_all" type="checkbox">
                                            </div>
                                        </div>
                                      <div class="col-12">
                                         @foreach ($menus as $menu)
                                             <div class="form-group">
                                                 <div class="d-flex justify-content-between align-items-center border p-3 rounded">
                                                     <label class="form-label mb-0" for="permission_{{ $menu->id }}">{{ __($menu->title) }}</label>
                                                     <div class="form-check form-switch">
                                                         <input class="form-check-input permission-checkbox" name="permission_ids[]" id="permission_{{ $menu->id }}" type="checkbox" value="{{ $menu->id }}"
                                                         {{ empty(array_diff($menu->permission, $permissionIds)) ? 'checked' : '' }}>
                                                     </div>
                                                 </div>
                                             </div>
                                         @endforeach
                                     </div>

                                    </div>

                                    <!-- Limits Tab Content -->
                                    <div class="tab-pane fade" id="limits" role="tabpanel" aria-labelledby="limits-tab">
                                        <div class="form-group form-group-inline-dotted">
                                            <label class="form-label" for="max_appointment">{{ __('messages.lbl_max_appointment') }} <span class="text-danger">*</span></label>
                                            <input type="number" min="1" class="form-control" name="max_appointment" value="{{ isset($plan) ? $plan->max_appointment : 1 }}">
                                            <span class="error text-danger"></span>
                                        </div>
                                        <div class="form-group form-group-inline-dotted">
                                            <label class="form-label" for="max_branch">{{ __('messages.lbl_max_branch') }} <span class="text-danger">*</span></label>
                                            <input type="number" min="1" class="form-control" name="max_branch" value="{{ isset($plan) ? $plan->max_branch : 1 }}">
                                            <span class="error text-danger"></span>
                                        </div>
                                        <div class="form-group form-group-inline-dotted">
                                            <label class="form-label" for="max_service">{{ __('messages.lbl_max_service') }} <span class="text-danger">*</span></label>
                                            <input type="number" min="1" class="form-control" name="max_service" value="{{ isset($plan) ? $plan->max_service : 1 }}">
                                            <span class="error text-danger"></span>
                                        </div>
                                        <div class="form-group form-group-inline-dotted">
                                            <label class="form-label" for="max_staff">{{ __('messages.lbl_max_staff') }} <span class="text-danger">*</span></label>
                                            <input type="number" min="1" class="form-control" name="max_staff" value="{{ isset($plan) ? $plan->max_staff : 1 }}">
                                            <span class="error text-danger"></span>
                                        </div>
                                        <div class="form-group form-group-inline-dotted">
                                            <label class="form-label" for="max_customer">{{ __('messages.lbl_max_customer') }} <span class="text-danger">*</span></label>
                                            <input type="number" min="1" class="form-control" name="max_customer" value="{{ isset($plan) ? $plan->max_customer : 1 }}">
                                            <span class="error text-danger"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Free Plan Tab Content -->
                        <div class="tab-pane fade {{ (isset($plan) && $plan->price == 0 ) ? 'show active' : ''}}" id="free-plan" role="tabpanel" aria-labelledby="free-plan-tab">
                        <div class="row">
                            <!-- Type Dropdown -->
                            <div class="form-group col-md-6">
                                <label class="form-label" for="free_plan_type">{{__('frontend.interval')}}  <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="free_plan_type" name="free_plan_type" required>
                                        <option value="Daily" {{ old('type', $plan->type ?? '') == 'Daily' ? 'selected' : '' }}>{{ __('messages.daily') }}</option>
                                        <option value="Weekly" {{ old('type', $plan->type ?? '') == 'Weekly' ? 'selected' : '' }}>{{ __('messages.weekly') }}</option>
                                        <option value="Monthly" {{ old('type', $plan->type ?? '') == 'Monthly' ? 'selected' : '' }}>{{ __('messages.monthly') }}</option>
                                        <option value="Yearly" {{ old('type', $plan->type ?? '') == 'Yearly' ? 'selected' : '' }}>{{ __('messages.yearly') }}</option>
                                </select>
                                <span class="error text-danger"></span>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="form-label" for="free_plan_duration">{{ __('messages.lbl_duration') }}</label>
                                    <input type="number" min="1" name="free_plan_duration" class="form-control" placeholder="{{ __('messages.enter_duration') }}" value="{{ isset($plan) ? $plan->duration : 1 }}">
                                <span class="error text-danger"></span>
                        </div>
                    </div>
                </div>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="button" class="btn btn-primary mt-4" id="submit_btn">
                            {{ isset($plan) ? __('messages.update') . ' ' . __('messages.lbl_plan') : __('messages.create') . ' ' . __('messages.lbl_plan') }}
                        </button>                    </div>
                </div>
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
        plan_type = "{{ (isset($plan) && $plan->price == 0 ) ? 'free_plan' : 'paid_plan'}}";

        function toggleDurationField() {
            var type = document.getElementById('type').value;
            var durationField = document.getElementById('durationField');

            if (type !== 'Yearly') {
                durationField.style.display = 'block';
            } else {
                durationField.style.display = 'none';
            }
        }

        $(document).ready(function() {
            // Handle tab click
            $('a[data-toggle="tab"]').on('click', function (e) {
                e.preventDefault();
                $(this).tab('show');
            });

            $(document).ready(function () {
                $(document).on("click", "#planTabs a", function (e) {
                    e.preventDefault();
                    $(this).tab("show");
                });
            });

            function updateAllCheckboxState() {
                // Check if all permission-checkboxes are checked
                var allChecked = $('.permission-checkbox').length === $('.permission-checkbox:checked').length;

                // Update the "All" checkbox based on the condition
                $('.permission_all').prop('checked', allChecked);
            }

            // Initially update the state of the "All" checkbox when the page loads
            updateAllCheckboxState();


            $(document).on("click", ".permission_all", function () {
                var isChecked = $(this).prop("checked");
                $(".permission-checkbox").prop("checked", isChecked);
            });

            $('.permission-checkbox').change(function() {
                updateAllCheckboxState();
            });

            $('#planTypeTabs .nav-link').on('click', function() {
                // Check if the "Paid Plan" tab is clicked
                if ($(this).attr('id') === 'paid-plan-tab') {
                    plan_type = 'paid_plan';  // Assign the paid plan value
                }
                // Check if the "Free Plan" tab is clicked
                else if ($(this).attr('id') === 'free-plan-tab') {
                    plan_type = 'free_plan';  // Assign the free plan value
                }

                $('input[name=plan_type]').val(plan_type);
            });
        });

        $(document).on('click', '#submit_btn', function(event) {
            event.preventDefault();
            
            // Reset all error messages
            $('.error').text('');
            
            // Basic validations
            let isValid = true;
            let name = $('input[name="name"]');
            let type = $('select[name="type"]');
            let duration = $('input[name="duration"]');
            let price = $('input[name="price"]');
            
            // Validate based on plan type
            if (plan_type === 'paid_plan') {
                // Required fields validation
                if (!name.val()) {
                    name.next('.error').text('Name is required.');
                    isValid = false;
                }
                
                if (!type.val()) {
                    type.next('.error').text('Type is required.');
                    isValid = false;
                }
                
                if (!price.val() || parseFloat(price.val()) <= 0) {
                    price.next('.error').text('Price must be greater than 0.');
                    isValid = false;
                }
                
                // Duration validation for non-yearly plans
                if (type.val() && type.val() !== 'Yearly' && (!duration.val() || duration.val() <= 0)) {
                    duration.next('.error').text('Duration must be greater than 0.');
                    isValid = false;
                }

                // Validate permissions
                if (!$('input[name="permission_ids[]"]:checked').length) {
                    $('#permissions .error').text('Please select at least one permission.');
                    $('#permissions-tab').tab('show');
                    isValid = false;
                }

                // Validate limits
                const limits = ['max_appointment', 'max_branch', 'max_service', 'max_staff', 'max_customer'];
                limits.forEach(limit => {
                    const input = $(`input[name="${limit}"]`);
                    if (!input.val() || parseInt(input.val()) <= 0) {
                        input.next('.error').text('Value must be at least 1.');
                        $('#limits-tab').tab('show');
                        isValid = false;
                    }
                });

                // Discount validation
                if ($('#has_discount').is(':checked')) {
                    const discountValue = $('#discount_value');
                    const discountType = $('input[name="discount_type"]:checked').val();
                    
                    if (!discountValue.val()) {
                        discountValue.next('.error').text('Discount value is required.');
                        isValid = false;
                    } else {
                        const dValue = parseFloat(discountValue.val());
                        const pValue = parseFloat(price.val());
                        
                        if (discountType === 'percentage' && (dValue <= 0 || dValue > 100)) {
                            discountValue.next('.error').text('Percentage must be between 1 and 100.');
                            isValid = false;
                        } else if (discountType === 'fixed' && (dValue <= 0 || dValue >= pValue)) {
                            discountValue.next('.error').text('Fixed discount must be less than price.');
                            isValid = false;
                        }
                    }
                    
                    if (!discountType) {
                        $('#discount-fields .error').first().text('Please select discount type.');
                        isValid = false;
                    }
                }
            }

            // If validation passes, submit the form
            if (isValid) {
                // Enable all fields before submission
                $('#discount_value, #discount_type, #discounted_price').prop('disabled', false);
                
                // Get the form
                const form = $('#plan-form');
                
                // Add plan type to form data
                $('<input>').attr({
                    type: 'hidden',
                    name: 'plan_type',
                    value: plan_type
                }).appendTo(form);
                
                // Submit the form
                form.submit();
            }

            
        });

        // Initialize select2
        // $('.select2').select2();

        // Initialize tab functionality
        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            const target = $(e.target).attr('href');
            localStorage.setItem('activeTab', target);
        });

        // Restore active tab
        const activeTab = localStorage.getItem('activeTab');
        if (activeTab) {
            $('a[href="' + activeTab + '"]').tab('show');
        }
    </script>
    <script>
        $(document).ready(function () {
            // Handle the "+" button click
            $(document).on('click', '.btn-add', function () {
                const newFeatureRow = `
                    <div class="row">
                        <div class="col-md-8 col-7">
                            <input type="text" name="features[]" class="form-control mb-2" placeholder="{{ __('messages.enter_feature') }}">
                        </div>
                        <div class="col-md-4 col-5">
                            <button type="button" class="btn btn-success btn-add px-md-4 px-3"><i class="zmdi zmdi-plus"></i></button>
                            <button type="button" class="btn btn-danger btn-remove px-md-4 px-3"><i class="zmdi zmdi-minus"></i></button>
                        </div>
                    </div>`;
                $('#features-container').append(newFeatureRow);
            });

            // Handle the "-" button click
            $(document).on('click', '.btn-remove', function () {
                // Ensure at least one input field remains
                if ($('#features-container .row').length > 1) {
                    $(this).closest('.row').remove();
                } else {
                    alert("At least one feature is required.");
                }
            });
        });

         // Add this to your existing JavaScript
         $(document).ready(function() {
            // Handle discount toggle
            $('#has_discount').change(function() {
                $('#discount-fields').toggleClass('d-none', !this.checked);
                if (!this.checked) {
                    $('#discount_value').val('');
                    $('#discounted_price').val('');
                } else {
                    calculateDiscountedPrice();
                }
            });

            // Calculate discounted price
            function calculateDiscountedPrice() {
                const price = parseFloat($('#price').val()) || 0;
                const discountValue = parseFloat($('#discount_value').val()) || 0;
                const discountType = $('#discount_type').val();
                
                let discountedPrice = price;
                
                if (discountType === 'percentage') {
                    if (discountValue > 100) {
                        $('#discount_value').val(100);
                        discountedPrice = 0;
                    } else {
                        discountedPrice = price - (price * (discountValue / 100));
                    }
                } else { // fixed
                    if (discountValue > price) {
                        $('#discount_value').val(price);
                        // discountedPrice = 0;
                    } else {
                        discountedPrice = price - discountValue;
                    }
                }
                
                $('#discounted_price').val(new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(discountedPrice));
            }

            // Add event listeners
            $('#price, #discount_value').on('input', calculateDiscountedPrice);
            $('#discount_type').on('change', calculateDiscountedPrice);
        });
    </script>
    <script>
        $(document).ready(function() {
            // Prevent negative values in price input
            $('#price').on('input', function() {
                let value = parseFloat($(this).val());
                if (value < 0) {
                    $(this).val(0);
                }
            });

            // Handle discount value input
            $('#discount_value').on('input', function() {
                let value = parseFloat($(this).val());
                if (value < 0) {
                    $(this).val(0);
                }
                calculateDiscountedPrice();
            });

            // Calculate discounted price function
            function calculateDiscountedPrice() {
                const price = parseFloat($('#price').val()) || 0;
                const discountValue = parseFloat($('#discount_value').val()) || 0;
                const discountType = $('input[name="discount_type"]:checked').val();
                
                let discountedPrice = price;
                
                if (discountType === 'percentage') {
                    // For percentage discount
                    const maxPercentage = 100;
                    const validDiscount = Math.min(discountValue, maxPercentage);
                    discountedPrice = price - (price * (validDiscount / 100));
                    
                    if (discountValue > maxPercentage) {
                        $('#discount_value').val(maxPercentage);
                    }
                } else {
                    // For fixed discount
                    const maxFixed = price;
                    const validDiscount = Math.min(discountValue, maxFixed);
                    discountedPrice = price - validDiscount;
                }
                
                // Ensure discounted price is never negative
                discountedPrice = Math.max(0, discountedPrice);
                
                // Format and display the discounted price
                $('#discounted_price').val(new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(discountedPrice));
            }

            // Add event listeners
            $('#price').on('input', calculateDiscountedPrice);
            $('#discount_value').on('input', calculateDiscountedPrice);
            $('input[name="discount_type"]').on('change', calculateDiscountedPrice);

            // Handle discount toggle
            $('#has_discount').change(function() {
                $('#discount-fields').toggleClass('d-none', !this.checked);
                if (!this.checked) {
                    $('#discount_value').val('');
                    $('#discounted_price').val('');
                } else {
                    calculateDiscountedPrice();
                }
            });

            // Initial calculation if discount is enabled
            if ($('#has_discount').is(':checked')) {
                calculateDiscountedPrice();
            }
        });
    </script>



@endpush
