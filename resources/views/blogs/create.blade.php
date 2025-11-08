@extends('backend.layouts.app')

@section('title')
    {{ __($module_action) }} {{ __($module_title) }}
@endsection


@push('after-styles')
    <link rel="stylesheet" href="{{ mix('modules/constant/style.css') }}">
@endpush
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between gap-4 flex-wrap mb-4">
                <h4 id="form-offcanvasLabel">{{ isset($blog) ? __('frontend.edit_blog') : __('frontend.create_blog') }}</h4>
                <a href="{{ route('backend.blog.index') }}" class="btn btn-primary">{{ __('frontend.back') }}</a>
            </div>
            <form id="blog-form" enctype="multipart/form-data" method="POST" action="{{ route('backend.blog.store') }}">
                @csrf
                <input type="hidden" name="id" value="{{ isset($blog) ? $blog->id : null }}">

                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="title">{{ __('frontend.title') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control"
                                placeholder="{{ __('frontend.enter_title') }}"
                                value="{{ isset($blog) ? $blog->title : '' }}" maxlength="65535">
                            <span class="error text-danger"></span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="auther_id">{{ __('frontend.select_author') }} <span
                                    class="text-danger">*</span></label>
                            <select class="form-select select2" id="auther_id" name="auther_id">
                                <option value="" disabled selected>{{ __('frontend.select_author') }}</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ isset($blog) && $blog->auther_id == $user->id ? 'selected' : '' }}>
                                        {{ $user->getFullNameAttribute() }}</option>
                                @endforeach
                            </select>
                            <span class="error text-danger"></span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="image">{{ __('frontend.image') }}</label>
                            <input type="file" id="image" name="image" class="form-control">
                            <span class="error text-danger"></span>
                        </div>
                    </div>

                    <div class="col-md-6 form-group">
                        <label class="form-label">{{ __('frontend.status') }}</label>
                        <div class="form-control form-check form-switch">
                            <div class="d-flex align-items-center justify-content-between">
                                <label class="form-label">{{ __('frontend.status') }}</label>
                                <input class="form-check-input" name="status" type="checkbox"
                                    {{ isset($blog) && $blog->status == 0 ? '-' : 'checked' }}>
                            </div>
                            <span class="error text-danger"></span>
                        </div>
                    </div>

                    <div class="form-group col-12">
                        <label class="form-label" for="description">{{ __('frontend.description') }}</label>
                        <textarea id="descriptiontextarea" name="description">{{ isset($blog) ? $blog->description : '' }}</textarea>
                    </div>
                </div>
                <button type="submit" id="blog-submit-btn" class="btn btn-primary mt-4">{{ __('frontend.submit') }}</button>
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

    <script src="{{ mix('js/tinymce/tinymce.min.js') }}"></script>
    <script type="text/javascript">
        tinymce.init({
            selector: "#descriptiontextarea"
        });
    </script>
    <script type="text/javascript">
        tinymce.init({
            selector: "#descriptiontextarea"
        });
    </script>
    <script>
        $(document).ready(function() {
            // Add form submission debugging and timeout handling
            $('#blog-form').on('submit', function(e) {
                console.log('Form submission started');
                console.log('Form data:', $(this).serialize());
                
                // Set a timeout to re-enable the button if submission takes too long
                setTimeout(function() {
                    const submitBtn = $('#blog-submit-btn');
                    if (submitBtn.prop('disabled')) {
                        console.log('Form submission timeout - re-enabling button');
                        submitBtn.prop('disabled', false);
                        submitBtn.html('{{ __("frontend.submit") }}');
                    }
                }, 10000); // 10 second timeout
            });
            $.validator.addMethod("imageExtension", function(value, element) {
                if (element.files.length === 0) {
                    return true;
                }
                var allowedExtensions = ["jpg", "jpeg", "png", "gif", "webp"];
                var fileExtension = value.split(".").pop().toLowerCase();
                return allowedExtensions.includes(fileExtension);
            }, "Only image files (jpg, jpeg, png, gif, webp) are allowed.");

            $("#blog-form").validate({
                rules: {
                    title: {
                        required: true,
                        maxlength: 65535
                    },
                    auther_id: {
                        required: true,
                    },
                    image: {
                        imageExtension: true
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
                    const submitBtn = $('#blog-submit-btn');
                    if (submitBtn.prop('disabled')) {
                        console.log('Form already submitted, preventing duplicate');
                        return false; // Already submitted, prevent duplicate submission
                    }
                    
                    // Sync TinyMCE content before submission
                    if (typeof tinymce !== 'undefined') {
                        console.log('Syncing TinyMCE content...');
                        tinymce.triggerSave();
                    }
                    
                    // Disable button and show loading state
                    submitBtn.prop('disabled', true);
                    const originalText = submitBtn.text();
                    submitBtn.html('<i class="fa fa-spinner fa-spin"></i> {{ __("frontend.saving") }}...');
                    
                    $(form).find('.error').remove();
                    
                    console.log('Form data being submitted:', $(form).serialize());
                    console.log('Submitting form to:', form.action);
                    
                    // Let the form submit naturally - don't trigger submit again
                    return true;
                },
            });
        });
    </script>
@endpush
