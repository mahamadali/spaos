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
        <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
            <h4 id="form-offcanvasLabel" class="mb-0">
                {{ isset($tax) ? __('frontend.edit_tax') : __('frontend.create_tax') }}
            </h4>
            <a href="{{ route('backend.plan.tax.index') }}" class="btn btn-primary">{{ __('frontend.back') }}</a>
        </div>
        <form id="tax-form" enctype="multipart/form-data" method="POST" action="{{ route('backend.plan.tax.store') }}">
            @csrf
            <input type="hidden" name="id" value="{{ isset($tax) ? $tax->id : null }}">

            <div class="form-group">
                <label class="form-label" for="title">{{ __('frontend.title') }} <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title" class="form-control" placeholder="{{ __('frontend.enter_title') }}" value="{{ isset($tax) ? $tax->title : '' }}">
                <span class="error text-danger"></span>
            </div>


            <div class="form-group">
                <label class="form-label" for="type">{{ __('frontend.select_type') }} <span class="text-danger">*</span></label>
                <div class="input-container">
                    <select class="form-control select2" id="type" name="type">
                        <option value="">{{ __('frontend.select_type') }}</option>
                        <option value="Percentage" {{ (isset($tax) && $tax->type == 'Percentage') ? 'selected' : '' }}>{{ __('frontend.percentage') }}</option>
                        <option value="Fixed" {{ (isset($tax) && $tax->type == 'Fixed') ? 'selected' : '' }}>{{ __('frontend.fixed') }}</option>
                    </select>
                </div>
                <span class="error text-danger"></span>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="value">{{ __('frontend.value') }} <span class="text-danger">*</span></label>
                <input type="number" name="value" id="value" class="form-control" placeholder="{{ __('frontend.enter_value') }}" value="{{ isset($tax) ? $tax->value : '' }}" min="0">
                <span class="error text-danger"></span>
            </div>

            

            <div class="form-group">
                <label class="form-label" for="plan_id">{{ __('frontend.plans') }} <span class="text-danger">*</span></label>
                <div class="input-container">
                    <select class="form-control" id="plan_id" name="plan_ids[]" multiple>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" {{ (isset($tax) && in_array($plan->id, $tax->plan_ids ? explode(",", $tax->plan_ids) : [])) ? 'selected' : '' }}>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <span class="error text-danger"></span>
            </div>

            <div class="form-check form-switch">
                <label class="form-label">{{ __('frontend.status') }}</label>
                <input class="form-check-input" name="status" type="checkbox" {{ (isset($tax) && $tax->status == 0) ? '' : 'checked' }}>
            </div>

            <button type="submit" id="tax-submit-btn" class="btn btn-primary mt-4">{{ __('frontend.submit') }}</button>
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
      $(document).ready(function() {
    $('#plan_id').select2({
        placeholder: 'Select Plans',
        allowClear: true,
        width: '100%'
    });

    // Add custom validation method for no spaces only
    $.validator.addMethod("noSpacesOnly", function(value, element) {
        return this.optional(element) || (value && value.trim().length > 0);
    }, "Title cannot contain only spaces.");

    $("#tax-form").validate({
        rules: {
            title: { 
                required: true,
                noSpacesOnly: true
            },
            value: { required: true },
            type: { required: true },
            "plan_ids[]": { required: true },
        },
        messages: {
            title: {
                required: "Title is required.",
                noSpacesOnly: "Title cannot contain only spaces."
            }
        },
        errorElement: "span",
        errorClass: "error text-danger",
        errorPlacement: function(error, element) {
            // Place error messages after the input/select element
            if (element.is("select")) {
                error.insertAfter(element.closest('.input-container'));
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function (element) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element) {
            $(element).removeClass('is-invalid');
        },
        submitHandler: function (form) {
            // Prevent double-clicking
            const submitBtn = $('#tax-submit-btn');
            if (submitBtn.prop('disabled')) {
                return false; // Already submitted, prevent duplicate submission
            }
            
            // Disable button and show loading state
            submitBtn.prop('disabled', true);
            const originalText = submitBtn.text();
            submitBtn.html('<i class="fa fa-spinner fa-spin"></i> {{ __("frontend.saving") }}...');
            
            $(form).find('.error').remove();
            
            // Set a timeout to re-enable the button if submission takes too long
            setTimeout(function() {
                if (submitBtn.prop('disabled')) {
                    console.log('Tax form submission timeout - re-enabling button');
                    submitBtn.prop('disabled', false);
                    submitBtn.html('{{ __("frontend.submit") }}');
                }
            }, 10000); // 10 second timeout
            
            // Let the form submit naturally - don't trigger submit again
            return true;
        }
    });
});
    </script>
@endpush
