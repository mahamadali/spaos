<form id="promotion-form" method="POST" action="{{ route('backend.promotions.store') }}">
    @csrf

    <div class="offcanvas offcanvas-end custom-offcanvas-width" tabindex="-1" id="form-offcanvas" aria-labelledby="form-offcanvasLabel">
        {{-- Header --}}
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="formOffcanvasLabel">
                {{ $createTitle ?? __('messages.new') . ' ' . __('promotion.singular_title') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body">

            {{-- Name --}}
            <div class="form-group col-md-12">
                <label class="form-label">{{ __('promotion.lbl_name') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" placeholder="{{ __('service.enter_name') }}"
                    value="">
                <span class="text-danger name_error"></span>
            </div>

            {{-- Description --}}
            <div class="form-group col-md-12">
                <label class="form-label">{{ __('promotion.description') }} <span class="text-danger">*</span></label>
                <textarea class="form-control" name="description" placeholder="{{ __('messages.placeholder_description') }}"></textarea>
                <span class="text-danger description_error"></span>
            </div>

            {{-- Start Date --}}
            <div class="form-group col-md-12">
                <label class="form-label">{{ __('promotion.start_datetime') }}</label>
                <input type="text" class="form-control flatpickr" id="start_date_time" name="start_date_time"
                    placeholder="Select Start Date">
                <span class="text-danger start_date_time_error"></span>
            </div>

            {{-- End Date --}}
            <div class="form-group col-md-12">
                <label class="form-label">{{ __('promotion.end_datetime') }}</label>
                <input type="text" class="form-control flatpickr" id="end_date_time" name="end_date_time"
                    placeholder="Select End Date">
                <span class="text-danger end_date_time_error"></span>
            </div>

            {{-- Coupon Code --}}
            <div class="form-group col-md-12" id="coupon-code-field">
                <label class="form-label">{{ __('promotion.coupon_code') }} <span class="text-danger">*</span></label>
                <input type="hidden" name="coupon_type" value="custom">
                <input type="hidden" name="number_of_coupon" value="1">
                <input type="text" class="form-control" name="coupon_code" value="" placeholder="{{ __('promotion.enter_coupon_code') }}">
                <span class="text-danger coupon_code_error"></span>
            </div>

            {{-- Discount Type --}}
            <div class="form-group col-md-12">
                <label class="form-label">{{ __('promotion.percent_or_fixed') }}</label>
                <select class="form-control select2" name="discount_type">
                    <option value="percent">{{ __('product.percent') }}</option>
                    <option value="fixed" selected>{{ __('messages.lbl_fixed') }}</option>
                </select>
                <span class="text-danger discount_type_error"></span>
            </div>

            {{-- Discount Value --}}
            <div class="form-group col-md-12" id="discount_percent_field" style="display:none;">
                <label class="form-label">{{ __('promotion.discount_percentage') }} <span class="text-danger">*</span></label>
                <input type="number" step="any" class="form-control" name="discount_percentage" value="" placeholder="{{ __('promotion.enter_discount_percentage') }}">
                <span class="text-danger discount_percentage_error"></span>
            </div>
            <div class="form-group col-md-12" id="discount_fixed_field" style="display:block;">
                <label class="form-label">{{ __('promotion.discount_amount') }} <span class="text-danger">*</span></label>
                <input type="number" step="any" class="form-control" name="discount_amount" value="" placeholder="{{ __('promotion.enter_discount_amount') }}">
                <span class="text-danger discount_amount_error"></span>
            </div>


            {{-- Plan (if SuperAdmin) --}}
            <div class="form-group col-md-12" id="plan_field" style="display:none;">
                <label class="form-label">{{ __('messages.select_plan') }} <span class="text-danger">*</span></label>
                <select class="form-control select2" name="plan_id[]" multiple>
                    <option value="">{{ __('messages.select_plan') }}</option>
                </select>
                <span class="text-danger plan_id_error"></span>
            </div>


            {{-- Use Limit (if Admin) --}}
            <div class="form-group col-md-12"id="use_limit_field" style="display:none;">
                <label class="form-label">{{ __('promotion.use_limit') }}</label>
                <input type="number" class="form-control" name="use_limit" value="1">
                <span class="text-danger use_limit_error"></span>
            </div>


            {{-- Status --}}
            <div class="form-group col-md-12">
                <label class="form-label">{{ __('service.lbl_status') }}</label>
                <div class="form-check form-switch">
                    <input type="hidden" name="status" value="0">
                    <input type="checkbox" class="form-check-input"   id="status" name="status" value="1" {{ old('status', $promotion->status ?? 1) ? 'checked' : '' }}>
                    {{-- <input type="checkbox" class="form-check-input" name="status"  > --}}
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="offcanvas-footer p-3 border-top">
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">{{ __('messages.cancel') }}</button>
                <button type="submit" form="promotion-form" class="btn btn-primary" id="saveBtn">
                    <i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save') }}
                </button>
            </div>
        </div>
    </div>
</form>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('promotion-form');

        let ISREADONLY = false;
        let IS_SUBMITED = false;


        const couponType = document.getElementById('coupon_type');
        const couponCodeField = document.getElementById('coupon_code_field');
        const numberOfCouponField = document.getElementById('number_of_coupon_field');

        const discountTypeSelect = form.querySelector('[name="discount_type"]');
        const discountPercentField = form.querySelector('#discount_percent_field');
        const discountFixedField = form.querySelector('#discount_fixed_field');

        function toggleDiscountFields() {
            const type = discountTypeSelect.value;
            discountPercentField.style.display = type === 'percent' ? 'block' : 'none';
            discountFixedField.style.display = type === 'fixed' ? 'block' : 'none';
        }

        // Initial toggle on page load
        if (discountTypeSelect) {
            toggleDiscountFields();
            $(discountTypeSelect).on('select2:select', function() {
                clearFieldError('discount_percentage');
                clearFieldError('discount_amount');
                toggleDiscountFields();
            });
        }
        // Flatpickr for date fields
        const today = new Date();
        const tomorrow = new Date();
        tomorrow.setDate(today.getDate() + 1);

        const flatpickrConfigToday = {
            enableTime: false,
            dateFormat: "Y-m-d",
            minDate: "today",
            defaultDate: today, // default to today
        };
        const flatpickrConfigTomorrow = {
            enableTime: false,
            dateFormat: "Y-m-d",
            minDate: "today",
            defaultDate: tomorrow, // default to today
        };
        flatpickr("#start_date_time", flatpickrConfigToday);
        flatpickr("#end_date_time", flatpickrConfigTomorrow);

        // Initialize Select2
        $('.select2').select2({
            width: '100%',
            placeholder: "Select an option",
            allowClear: true,
            dropdownParent: $('#form-offcanvas')
        });

        // ---------- Role wise field show ----------
        const ROLES = JSON.parse(document.querySelector('meta[name="auth_user_roles"]').getAttribute(
            'content') || '[]');

        const isSuperAdmin = ROLES.includes('super admin');
        const isAdmin = ROLES.includes('admin');

        if (isSuperAdmin) {
            document.getElementById('plan_field').style.display = 'block';
        }
        if (isAdmin) {
            document.getElementById('use_limit_field').style.display = 'block';
        }

        // ---------- Coupon Type Toggle ----------
        // function toggleCouponFields(){
        //     const type = couponType.value;
        //     couponCodeField.style.display = type==='custom' ? 'block':'none';
        //     numberOfCouponField.style.display = type==='bulk' ? 'block':'none';
        // }
        // couponType.addEventListener('change', toggleCouponFields);

        const planField = document.querySelector('#plan_field');
        const planSelect = planField.querySelector('[name="plan_id[]"]');

        if (isSuperAdmin) {
                // Show the field
                planField.style.display = 'block';

                // Fetch plans
                fetch("{{ url('app/subscription/plans/plan_list') }}") // optional search param
                    .then(res => res.json())
                    .then(plans => {
                        // Clear existing options
                        planSelect.innerHTML = '<option value="">{{ __('messages.select_plan') }}</option>';

                        // Append new options
                        plans.forEach(plan => {
                            const opt = document.createElement("option");
                            opt.value = plan.id;
                            opt.textContent = plan.name;
                            planSelect.appendChild(opt);
                        });

                        // Reinitialize Select2 if active
                        if ($(planSelect).hasClass("select2")) {
                            $(planSelect).select2();
                        }
                    })
                    .catch(console.error);
        }
        // ---------- Reset Form ----------
        const newBtn = document.getElementById('newPromotionBtn');
        const offcanvasEl = document.getElementById('form-offcanvas');
        
        if (newBtn) {
            newBtn.addEventListener('click', function(e) {
                console.log('Debug - New button clicked, resetting form');
                
                // Clear all form fields
                form.reset();
                clearErrors();
                ISREADONLY = false;
                IS_SUBMITED = false;
                
                // Reset form action to create URL
                form.action = "{{ route('backend.promotions.store') }}";
                
                // Remove any existing method input (for edit)
                const existingMethodInput = form.querySelector('input[name="_method"]');
                if (existingMethodInput) {
                    existingMethodInput.remove();
                }
                
                // Reset title
                const titleEl = document.getElementById('formOffcanvasLabel');
                if (titleEl) {
                    titleEl.textContent = @json($createTitle ?? __('messages.new') . ' ' . __('promotion.singular_title'));
                }
                
                // Reset specific fields to empty
                form.querySelector('[name="name"]').value = '';
                form.querySelector('[name="description"]').value = '';
                form.querySelector('[name="coupon_code"]').value = '';
                form.querySelector('[name="discount_percentage"]').value = '';
                form.querySelector('[name="discount_amount"]').value = '';
                form.querySelector('[name="use_limit"]').value = '1';
                
                // Reset status to active
                form.querySelector('#status').checked = true;
                
                // Reset date fields to today and tomorrow
                flatpickr("#start_date_time").setDate(today);
                flatpickr("#end_date_time").setDate(tomorrow);
                
                // Reset discount type to percent
                form.querySelector('[name="discount_type"]').value = 'percent';
                $(form.querySelector('[name="discount_type"]')).val('percent').trigger('change');
                toggleDiscountFields();
                
                // Reset plan selection - clear all selections
                const planSelect = form.querySelector('[name="plan_id[]"]');
                if (planSelect) {
                    $(planSelect).val(null).trigger('change');
                    // Also clear the select2 display
                    $(planSelect).select2('val', null);
                }
                
                // Reset coupon type to custom
                const couponTypeSelect = form.querySelector('[name="coupon_type"]');
                if (couponTypeSelect) {
                    couponTypeSelect.value = 'custom';
                    $(couponTypeSelect).val('custom').trigger('change');
                }
                
                // Show/hide appropriate fields for new promotion
                const couponCodeField = document.getElementById('coupon-code-field');
                const numberOfCouponField = document.getElementById('number_of_coupon_field');
                if (couponCodeField) couponCodeField.style.display = 'block';
                if (numberOfCouponField) numberOfCouponField.style.display = 'none';
                
                // Show use limit field for admin
                const useLimitField = document.getElementById('use_limit_field');
                if (useLimitField && isAdmin) {
                    useLimitField.style.display = 'block';
                }
                
                // Show plan field for super admin
                const planField = document.getElementById('plan_field');
                if (planField && isSuperAdmin) {
                    planField.style.display = 'block';
                }
                
                console.log('Debug - Form reset complete for new promotion');
                
                // Ensure offcanvas opens
                if (offcanvasEl) {
                    const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                    offcanvas.show();
                }
            });
        }

        // Helper: clear all errors
        function clearErrors() {
            form.querySelectorAll('.text-danger').forEach(span => span.textContent = '');
            form.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));
        }

        // Helper: clear error for a specific field
        function clearFieldError(field) {
            const span = form.querySelector('.' + field + '_error');
            if (span) {
                span.textContent = '';
                span.style.display = 'none';
            }
            const input = form.querySelector('[name="' + field + '"]');
            if (input) {
                input.classList.remove('is-invalid');
            }
        }

        // Helper: show error
        function showError(field, message) {
            const span = form.querySelector('.' + field + '_error');
            if (span) {
                span.textContent = message;
                span.style.display = 'block';
            }
            // Also add is-invalid class to the input field
            const input = form.querySelector('[name="' + field + '"]');
            if (input) {
                input.classList.add('is-invalid');
            }
        }

        // Clear errors when fields are changed
        const fieldsToWatch = ['name', 'description', 'start_date_time', 'end_date_time', 'coupon_code', 
                               'discount_percentage', 'discount_amount', 'use_limit'];
        
        fieldsToWatch.forEach(fieldName => {
            const field = form.querySelector('[name="' + fieldName + '"]');
            if (field) {
                // Clear error on input/change
                field.addEventListener('input', function() {
                    clearFieldError(fieldName);
                });
                field.addEventListener('change', function() {
                    clearFieldError(fieldName);
                });
            }
        });

        // Note: Discount type change handler is already set up above with Select2

        // Handle date fields change
        const startDateField = form.querySelector('[name="start_date_time"]');
        const endDateField = form.querySelector('[name="end_date_time"]');
        if (startDateField) {
            startDateField.addEventListener('change', function() {
                clearFieldError('start_date_time');
                // Re-validate end date if needed
                if (endDateField.value) {
                    clearFieldError('end_date_time');
                }
            });
        }
        if (endDateField) {
            endDateField.addEventListener('change', function() {
                clearFieldError('end_date_time');
            });
        }

        const couponInput = form.querySelector('[name="coupon_code"]');
        if (couponInput) {
            couponInput.addEventListener('blur', function() {
                const code = couponInput.value.trim();
                if (!code) return;

                fetch("{{ url('app/promotions/unique_coupon') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            coupon_code: code
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        // Clear previous error first
                        form.querySelector('.coupon_code_error').textContent = '';

                        if (!data.isUnique) {
                            showError('coupon_code', 'Coupon code already exists');
                        }
                    })
                    .catch(console.error);
            });
        }

        // ---------- Edit Form Population ----------
        document.addEventListener('click', function(e) {
            // Check if clicked element or its parent has data-promotion-id
            const btn = e.target.closest('[data-promotion-id]');
            if (!btn) return;

            e.preventDefault();
            e.stopPropagation();

            const id = btn.getAttribute('data-promotion-id');
            if (!id) return;

            // Clear form and errors first
            form.reset();
            clearErrors();
            ISREADONLY = false;
            IS_SUBMITED = false;

            // Remove any existing _method input
            const existingMethodInput = form.querySelector('input[name="_method"]');
            if (existingMethodInput) {
                existingMethodInput.remove();
            }

            // Update title
            const titleEl = document.getElementById('formOffcanvasLabel');
            if (titleEl) {
                titleEl.textContent = @json($editTitle ?? __('messages.edit') . ' ' . __('promotion.singular_title'));
            }

            // Show loading state
            const btnIcon = btn.querySelector('i');
            const originalBtnHtml = btn.innerHTML;
            if (btnIcon) {
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
            }

            fetch(`{{ url('app/promotions') }}/${id}/edit`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => {
                    if (btnIcon) {
                        btn.innerHTML = originalBtnHtml;
                    }
                    if (!res.ok) {
                        throw new Error('Failed to fetch promotion data');
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.status && data.data) {
                        const p = data.data;
                        ISREADONLY = true;
                        
                        // Populate form fields
                        form.querySelector('[name="name"]').value = p.name || '';
                        form.querySelector('[name="description"]').value = p.description || '';
                        form.querySelector('[name="start_date_time"]').value = p.start_date_time || '';
                        form.querySelector('[name="end_date_time"]').value = p.end_date_time || '';
                        
                        // Handle coupon data
                        if (p.coupon) {
                            form.querySelector('[name="discount_amount"]').value = p.coupon.discount_amount || '';
                            form.querySelector('[name="discount_percentage"]').value = p.coupon.discount_percentage || '';
                            const discountTypeValue = p.coupon.discount_type || 'percent';
                            form.querySelector('[name="discount_type"]').value = discountTypeValue;
                            $(form.querySelector('[name="discount_type"]')).val(discountTypeValue).trigger('change');
                            toggleDiscountFields();

                            const useLimitInput = form.querySelector('[name="use_limit"]');
                            if(useLimitInput){
                                useLimitInput.value = p.coupon.use_limit ?? 1;
                                useLimitInput.disabled = true; // readonly in edit mode
                            }
                            const useCouponInput = form.querySelector('[name="coupon_code"]');
                            if(useCouponInput){
                                useCouponInput.value = p.coupon.coupon_code || '';
                                useCouponInput.disabled = true; // readonly in edit mode
                            }
                        }

                        form.querySelector('#status').checked = p.status == 1;

                        // Show plan field for super admin and populate it
                        const planField = document.getElementById('plan_field');
                        if (planField && isSuperAdmin) {
                            planField.style.display = 'block';
                        }
                        
                        // Populate plan selection
                        const planSelect = form.querySelector('[name="plan_id[]"]');
                        if (planSelect) {
                            fetch("{{ url('app/subscription/plans/plan_list') }}")
                                .then(res => res.json())
                                .then(plans => {
                                    planSelect.innerHTML = ''; // clear existing options

                                    plans.forEach(plan => {
                                        const opt = document.createElement('option');
                                        opt.value = plan.id;
                                        opt.textContent = plan.name;

                                        // Check if this plan is selected
                                        const selectedPlanIds = p.plan_ids || p.coupon?.plan_ids || [];
                                        
                                        if (Array.isArray(selectedPlanIds) && selectedPlanIds.includes(plan.id)) {
                                            opt.selected = true;
                                        }
                                        planSelect.appendChild(opt);
                                    });

                                    // Initialize / refresh Select2
                                    $(planSelect).select2({
                                        width: '100%',
                                        placeholder: "Select Plan",
                                        dropdownParent: $('#form-offcanvas')
                                    });
                                })
                                .catch(console.error);
                        }

                        // Update date fields with flatpickr
                        if (p.start_date_time) {
                            flatpickr("#start_date_time").setDate(p.start_date_time);
                        }
                        if (p.end_date_time) {
                            flatpickr("#end_date_time").setDate(p.end_date_time);
                        }

                        // Update form action and method
                        form.action = `{{ url('app/promotions') }}/${id}`;
                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'PUT';
                        form.appendChild(methodInput);

                        // Show offcanvas
                        const offcanvasEl = document.getElementById('form-offcanvas');
                        if (offcanvasEl) {
                            const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                            offcanvas.show();
                        }
                    } else {
                        window.errorSnackbar('Failed to load promotion data');
                    }
                })
                .catch(err => {
                    if (btnIcon) {
                        btn.innerHTML = originalBtnHtml;
                    }
                    console.error('Error loading promotion:', err);
                    window.errorSnackbar('Failed to load promotion data');
                });
        });

        // ---------- Form Submit ----------
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            clearErrors();

            let valid = true;

            // Get field values
            const name = form.querySelector('[name="name"]').value.trim();
            const description = form.querySelector('[name="description"]').value.trim();
            const start_date_time = form.querySelector('[name="start_date_time"]').value.trim();
            const end_date_time = form.querySelector('[name="end_date_time"]').value.trim();
            const use_limit = form.querySelector('[name="use_limit"]')?.value.trim();
            const discount_type = form.querySelector('[name="discount_type"]')?.value;
            const discount_amount = form.querySelector('[name="discount_amount"]')?.value.trim();
            const discount_percentage = form.querySelector('[name="discount_percentage"]')?.value.trim();
            // const coupon_type = form.querySelector('[name="coupon_type"]')?.value;
            const coupon_code = form.querySelector('[name="coupon_code"]')?.value.trim();
            const number_of_coupon = form.querySelector('[name="number_of_coupon"]')?.value.trim();
            // Get selected plan IDs from Select2 multi-select
            const planInput = form.querySelector('[name="plan_id[]"]');
            let plan_id = [];
            if (planInput) {
                // For Select2 multi-select, get selected values
                if ($(planInput).hasClass('select2-hidden-accessible')) {
                    plan_id = $(planInput).val() || [];
                } else {
                    // Fallback for regular select
                    plan_id = planInput.value.split(',').filter(v => v);
                }
            }


            // Name
            if (!name) {
                showError('name', 'Name is required field');
                valid = false;
            }
            // else { clearFieldError('name'); }

            // Description
            if (!description) {
                showError('description', 'Description is required field');
                valid = false;
            }
            // Start date
            if (!start_date_time) {
                showError('start_date_time', 'Start date is required field');
                valid = false;
            }

            // End date
            if (!end_date_time) {
                showError('end_date_time', 'End date is required field');
                valid = false;
            } else if (new Date(end_date_time) < new Date()) {
                showError('end_date_time', 'End date cannot be in the past');
                valid = false;
            }

            // Use limit (if admin)
            if (isAdmin && (!use_limit || parseInt(use_limit) < 1 || isNaN(use_limit))) {
                showError('use_limit', 'Use limit is required and must be >= 1');
                valid = false;
            }
            // Plan (if super admin)
            if (isSuperAdmin && (!plan_id || plan_id.length === 0)) {
                showError('plan_id', 'Please select a plan');
                valid = false;
            }

            // Coupon validations
            // if (coupon_type === 'custom') {
            //     if (!coupon_code) { showError('coupon_code', 'Coupon code is required'); valid = false; }
            // } else if (coupon_type === 'bulk') {
            //     if (!number_of_coupon || !/^[1-9]\d*$/.test(number_of_coupon)) {
            //         showError('number_of_coupon', 'Number of coupons must be a positive number'); valid = false;
            //     }
            // }
            if (!coupon_code) {
                showError('coupon_code', 'Coupon code is required field');
                valid = false;
            }

            // Discount
            if (discount_type === 'percent') {
                if (!discount_percentage || discount_percentage.trim() === '') {
                    showError('discount_percentage', 'Discount percentage is required');
                    valid = false;
                } else if (isNaN(discount_percentage)) {
                    showError('discount_percentage', 'Discount percentage must be a number');
                    valid = false;
                } else {
                    const percentValue = parseFloat(discount_percentage);
                    if (percentValue < 0 || percentValue > 100) {
                        showError('discount_percentage', 'Discount percentage must be between 0 and 100');
                        valid = false;
                    }
                }
            } else if (discount_type === 'fixed') {
                if (!discount_amount || discount_amount.trim() === '') {
                    showError('discount_amount', 'Discount amount is required');
                    valid = false;
                } else if (isNaN(discount_amount)) {
                    showError('discount_amount', 'Discount amount must be a number');
                    valid = false;
                } else {
                    const amountValue = parseFloat(discount_amount);
                    if (amountValue < 1) {
                        showError('discount_amount', 'Discount amount must be >= 1');
                        valid = false;
                    }
                }
            }

            if (!valid) return;
            if (IS_SUBMITED) return;
            IS_SUBMITED = true;
            
            // Disable submit button and show loading
            const saveBtn = document.getElementById('saveBtn');
            const originalBtnText = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> {{ __("messages.saving") }}...';
            
            const formData = new FormData(form);
            
            // Ensure plan_id is properly included for Select2 multi-select
            if (planInput && plan_id.length > 0) {
                // Remove any existing plan_id entries
                formData.delete('plan_id[]');
                // Add each selected plan ID
                plan_id.forEach(id => {
                    formData.append('plan_id[]', id);
                });
            }
            
            // Determine HTTP method - use POST for both create and update (Laravel uses _method field for PUT)
            const httpMethod = form.querySelector('input[name="_method"]') ? 'POST' : 'POST';
            
            fetch(form.action, {
                    method: httpMethod,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                }).then(res => {
                    // Re-enable button
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalBtnText;
                    IS_SUBMITED = false;
                    
                    // Check if response is ok
                    if (!res.ok) {
                        return res.json().then(data => {
                            throw { data, status: res.status };
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.status) {
                        window.successSnackbar(data.message || '{{ __("messages.save_form", ["form" => __("promotion.singular_title")]) }}');
                        const offcanvasInstance = bootstrap.Offcanvas.getInstance(document.getElementById('form-offcanvas'));
                        if (offcanvasInstance) {
                            offcanvasInstance.hide();
                        }
                        // Reload datatable
                        if (typeof window.renderedDataTable !== 'undefined' && window.renderedDataTable) {
                            window.renderedDataTable.ajax.reload(null, false);
                        } else {
                            // Fallback: reload page
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        }
                        form.reset();
                        clearErrors();
                    } else {
                        // Handle errors
                        if (data.errors) {
                            for (const field in data.errors) {
                                const errorMessage = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                                showError(field, errorMessage);
                            }
                        } else {
                            window.errorSnackbar(data.message || '{{ __("messages.error") }}');
                        }
                    }
                })
                .catch(err => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalBtnText;
                    IS_SUBMITED = false;
                    
                    if (err.data) {
                        // Handle validation errors
                        if (err.data.errors) {
                            for (const field in err.data.errors) {
                                const errorMessage = Array.isArray(err.data.errors[field]) ? err.data.errors[field][0] : err.data.errors[field];
                                showError(field, errorMessage);
                            }
                        } else {
                            window.errorSnackbar(err.data.message || '{{ __("messages.error") }}');
                        }
                    } else {
                        console.error('Form submission error:', err);
                        window.errorSnackbar('{{ __("messages.error") }}');
                    }
                });
        });
    });
    const offcanvasEl = document.getElementById('form-offcanvas');
if (offcanvasEl) {
    offcanvasEl.addEventListener('hidden.bs.offcanvas', function () {
        console.log('Debug - Offcanvas closed, resetting form');
        
        // Reset title
        const titleEl = document.getElementById('formOffcanvasLabel');
        if (titleEl) {
            titleEl.textContent = @json($createTitle ?? 'New Promotion');
        }

        // Reset form action to create URL
        form.action = "{{ route('backend.promotions.store') }}";
        
        // Remove any existing method input (for edit)
        const existingMethodInput = form.querySelector('input[name="_method"]');
        if (existingMethodInput) {
            existingMethodInput.remove();
        }

        // Reset form completely
        form.reset();
        clearErrors();
        form.querySelector('#status').checked = true;
        ISREADONLY = false;
        IS_SUBMITED = false;

        // Reset specific fields to empty
        form.querySelector('[name="name"]').value = '';
        form.querySelector('[name="description"]').value = '';
        form.querySelector('[name="coupon_code"]').value = '';
        form.querySelector('[name="discount_percentage"]').value = '';
        form.querySelector('[name="discount_amount"]').value = '';
        form.querySelector('[name="use_limit"]').value = '1';

        // Reset Flatpickr dates
        flatpickr("#start_date_time").setDate(today);
        flatpickr("#end_date_time").setDate(tomorrow);
        
        // Reset discount type to percent
        form.querySelector('[name="discount_type"]').value = 'percent';
        $(form.querySelector('[name="discount_type"]')).val('percent').trigger('change');
        toggleDiscountFields();
        
        // Reset plan selection
        const planSelect = form.querySelector('[name="plan_id[]"]');
        if (planSelect) {
            $(planSelect).val(null).trigger('change');
            $(planSelect).select2('val', null);
        }
        
        // Reset coupon type to custom
        const couponTypeSelect = form.querySelector('[name="coupon_type"]');
        if (couponTypeSelect) {
            couponTypeSelect.value = 'custom';
            $(couponTypeSelect).val('custom').trigger('change');
        }
        
        console.log('Debug - Form reset complete after offcanvas close');
    });
}

</script>
