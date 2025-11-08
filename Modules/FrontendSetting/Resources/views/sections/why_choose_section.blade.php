<!-- Choose Us Section -->
<div class="row mb-4">
    <div class="col-md-12">

        <div class="row">
            <div class="form-group">
                <div class="form-check form-switch d-flex justify-content-between align-items-center p-2 border rounded">
                    <div>
                        <h4 class="mb-3 text-start">{{ __('frontend.why_choose') }}</h4>
                    </div>
                </div>
            </div>
            <div class="settings-box bg-body rounded p-3">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @php

                    $whyChoose = \Modules\FrontendSetting\Models\WhyChoose::where('created_by', auth()->user()->id)
                        ->latest()
                        ->first();
                    // Fallback seeder data
                    $fallback = [
                        'title' => 'Why Choose Frezka',
                        'subtitle' => 'why frezka',
                        'description' =>
                            'With an intuitive booking system, expert selection, & exclusive offers, our all-in-one platform ensures seamless operations while enhancing customer loyalty.',
                        'image' => asset('/why_choose/why_choose.png'),
                        'features' => [
                            [
                                'title' => 'Quick & Easy Booking',

                                'image' => asset('/why_choose_features/appointment_booking.jpg'),
                            ],
                            [
                                'title' => 'Enhance Client Satisfaction',

                                'image' => asset('/why_choose_features/quick_easy_booking.jpg'),
                            ],
                            [
                                'title' => 'Discover trends with analytics',

                                'image' => asset('/why_choose_features/Discover_trends_with_analytics.jpg'),
                            ],
                        ],
                    ];
                    if ($whyChoose) {
                        $data = $whyChoose->toArray();
                        $data['image'] = $whyChoose->image
                        ? url(Storage::url($whyChoose->image))
                            : asset('/why_choose/why_choose.png');
                    } else {
                        $data = $fallback;
                    }
                    if ($whyChoose) {
                        $features = \Modules\FrontendSetting\Models\WhyChooseFeature::where(
                            'why_choose_id',
                            $whyChoose->id,
                        )
                            ->where('created_by', auth()->user()->id)
                            ->get()
                            ->map(function ($feature) {
                                $feature->image = $feature->image
                                    ? url(Storage::url($feature->image))
                                    : asset('images/default.png'); // fallback if needed
                                return $feature;
                            });
                    } else {
                        $features = $fallback['features'];
                    }

                @endphp
                <form id="whyChooseForm" method="POST" action="{{ route('why_choose_setting.store') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label d-block ms-5 text-start"
                                    for="chooseUs_image">{{ __('frontend.select_image') }}</label>
                                <div class="d-flex flex-column align-items-center">
                                    <div class="image-box">
                                        <img class="image-preview img-fluid"
                                            src="{{ isset($data['image']) && !empty($data['image']) ? $data['image'] : asset(product_feature_image()) }}"
                                            style="width: 250px; height: 150px; object-fit: cover;">
                                        <div class="d-flex justify-content-center gap-3 mt-3">
                                            <button type="button"
                                                class="btn btn-sm btn-primary upload-image">{{ __('frontend.upload') }}</button>
                                            <button type="button"
                                                class="btn btn-sm btn-danger remove-image">{{ __('frontend.remove') }}</button>
                                            <input type="file" class="file-input form-control" name="chooseUs_image"
                                                accept=".jpg,.jpeg,.png" style="display: none;">
                                            <input type="hidden" name="existing_image"
                                                value="{{ $data['image'] ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row gy-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label d-block text-start"
                                            for="chooseUs_title">{{ __('frontend.choose_us_title') }}</label>
                                        <input type="text" name="chooseUs_title" class="form-control"
                                            placeholder="{{ __('frontend.choose_title') }}"
                                            value="{{ $data['title'] ?? '' }}">
                                    </div>
                                </div>
                                {{-- <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label d-block text-start"
                                            for="chooseUs_subtitle">{{ __('frontend.choose_us_subtitle') }}</label>
                                        <input type="text" name="chooseUs_subtitle" class="form-control"
                                            placeholder="{{ __('frontend.choose_subtitle') }}"
                                            value="{{ $data['subtitle'] ?? '' }}">
                                    </div>
                                </div> --}}
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label text-start d-block"
                                            for="chooseUs_description">{{ __('frontend.choose_us_description') }}</label>
                                        <textarea name="chooseUs_description" class="form-control" rows="3">{{ $data['description'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                            @if ($features && count($features))
                                <div class="row mt-4">
                                    @foreach ($features as $feature)
                                        @php
                                            $title = is_array($feature)
                                                ? $feature['title'] ?? ''
                                                : $feature->title ?? '';
                                            $subtitle = is_array($feature)
                                                ? $feature['subtitle'] ?? ''
                                                : $feature->subtitle ?? '';
                                            $image = is_array($feature)
                                                ? $feature['image'] ?? null
                                                : $feature->image ?? null;
                                        @endphp
                                        <div class="col-md-4 mb-3">
                                            <div class="card card-body text-center position-relative">
                                                @if ($image)
                                                    <img src="{{ $image }}" alt="{{ $title }}"
                                                        style="width:120px; height:120px; object-fit:cover; margin-bottom:10px;">
                                                @else
                                                    <img src="{{ asset(product_feature_image()) }}" alt="feature image"
                                                        style="width:120px; height:120px; object-fit:cover; margin-bottom:10px; opacity:0.5;">
                                                @endif
                                                <div class="fw-bold">{{ $title }}</div>
                                                <div class="text-muted">{{ $subtitle }}</div>
                                                @if (!is_array($feature))
                                                    <button type="button"
                                                        class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 delete-existing-feature"
                                                        data-feature-id="{{ $feature->id }}" title="Delete Feature">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <div class="mt-3 text-end">
                                <button type="button" class="btn btn-primary"
                                    id="add-more-btn">{{ __('frontend.Add_More') }}</button>
                            </div>
                            <div id="add-more-forms" class="mt-3"></div>
                        </div>

                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="spinner-border spinner-border-sm d-none" id="submitSpinner" role="status"
                                aria-hidden="true"></span>
                            <span id="submitText">{{ __('frontend.Save') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        // File validation function
        function validateImageFile(file) {
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            const maxSize = 10 * 1024 * 1024; // 10MB

            if (!allowedTypes.includes(file.type)) {
                return 'Please select a valid image file (JPG, JPEG, PNG only).';
            }

            if (file.size > maxSize) {
                return 'Image size must be less than 10MB.';
            }

            return null;
        }

        // Handle form submission with jQuery AJAX
        $('#whyChooseForm').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $submitBtn = $('#submitBtn');
            var $spinner = $('#submitSpinner');
            var $submitText = $('#submitText');

            // Clear previous alerts
            $('.alert').remove();

            // Frontend validation
            var hasErrors = false;
            var errorMessages = [];

            // Validate main image if title is provided
            var mainTitle = $('input[name="chooseUs_title"]').val().trim();
            var mainImage = $('input[name="chooseUs_image"]')[0].files[0];
            var existingImage = $('input[name="existing_image"]').val();

            if (mainTitle && !mainImage && !existingImage) {
                errorMessages.push('Image is required when title is provided.');
                hasErrors = true;
            }

            // Validate main image file type
            if (mainImage) {
                var validationError = validateImageFile(mainImage);
                if (validationError) {
                    errorMessages.push('Main image: ' + validationError);
                    hasErrors = true;
                }
            }

            // Validate add more features
            $('input[name="add_more_title[]"]').each(function(index) {
                var title = $(this).val().trim();
                var image = $('input[name="add_more_image[]"]')[index];

                if (title && (!image.files[0])) {
                    errorMessages.push('Image is required for feature: ' + title);
                    hasErrors = true;
                }

                if (image.files[0]) {
                    var validationError = validateImageFile(image.files[0]);
                    if (validationError) {
                        errorMessages.push('Feature ' + (index + 1) + ' image: ' + validationError);
                        hasErrors = true;
                    }
                }
            });

            // Show validation errors under specific fields
            if (hasErrors) {
                // Clear previous field errors
                $('.field-error').remove();

                // Show main image error
                if (mainTitle && !mainImage && !existingImage) {
                    $('input[name="chooseUs_image"]').closest('.form-group').append('<div class="field-error text-danger mt-1">Image is required when title is provided.</div>');
                }

                // Show main image file type error
                if (mainImage) {
                    var validationError = validateImageFile(mainImage);
                    if (validationError) {
                        $('input[name="chooseUs_image"]').closest('.form-group').append('<div class="field-error text-danger mt-1">' + validationError + '</div>');
                    }
                }

                // Show add more feature errors
                $('input[name="add_more_title[]"]').each(function(index) {
                    var title = $(this).val().trim();
                    var image = $('input[name="add_more_image[]"]')[index];

                    if (title && (!image.files[0])) {
                        $(image).closest('.form-group').append('<div class="field-error text-danger mt-1">Image is required for feature: ' + title + '</div>');
                    }

                    if (image.files[0]) {
                        var validationError = validateImageFile(image.files[0]);
                        if (validationError) {
                            $(image).closest('.form-group').append('<div class="field-error text-danger mt-1">' + validationError + '</div>');
                        }
                    }
                });

                return;
            }

            // Show loading state
            $submitBtn.prop('disabled', true);
            $spinner.removeClass('d-none');
            $submitText.text('Saving...');

            // Submit form via AJAX
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (window.successSnackbar) {
                            window.successSnackbar(response.message ||
                                'Why Choose Us section updated successfully.');
                        }
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        if (window.errorSnackbar) {
                            window.errorSnackbar(response.message || 'An error occurred while saving.');
                        }
                    }
                },
                error: function(xhr) {
                    var errorMessage = 'An error occurred while processing your request.';
                    try {
                        var response = xhr.responseJSON;
                        if (response && response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        console.error('Error parsing error response:', e);
                    }
                    if (window.errorSnackbar) {
                        window.errorSnackbar(errorMessage);
                    }
                },
                complete: function() {
                    // Reset button state
                    $submitBtn.prop('disabled', false);
                    $spinner.addClass('d-none');
                    $submitText.text($submitBtn.data('original-text') || 'Save');
                }
            });
        });

        // Handle delete existing feature
        $(document).on('click', '.delete-existing-feature', function(e) {
            e.preventDefault();

            const featureId = $(this).data('feature-id');
            const $featureCard = $(this).closest('.col-md-4');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'primary',
                cancelButtonColor: 'secondary',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-secondary'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('why_choose_feature.delete', ':id') }}'.replace(':id',
                            featureId),
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                $featureCard.fadeOut(300, function() {
                                    $(this).remove();
                                    // Re-enable add button if we're now below the limit
                                    existingFeaturesCount--;
                                    maxAddMore = maxTotalFeatures -
                                        existingFeaturesCount;
                                    if (maxAddMore > addMoreCount && addMoreBtn
                                        .disabled) {
                                        addMoreBtn.disabled = false;
                                        addMoreBtn.textContent =
                                            '{{ __('frontend.Add_More') }}';
                                    }
                                });
                                Swal.fire(
                                    'Deleted!',
                                    response.message || 'Feature has been deleted.',
                                    'success'
                                );
                            } else {
                                Swal.fire(
                                    'Error!',
                                    response.message || 'Failed to delete feature.',
                                    'error'
                                );
                            }
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error!',
                                'Something went wrong. Please try again.',
                                'error'
                            );
                        }
                    });
                }
            });
        });

        // Global variables for feature management
        var addMoreBtn = document.getElementById('add-more-btn');
        var addMoreFormsContainer = document.getElementById('add-more-forms');
        var addMoreCount = 0;
        var maxTotalFeatures = 3;


        var existingFeaturesCount = {{ isset($features) ? (is_array($features) ? 0 : $features->count()) : 0 }};

        var maxAddMore = maxTotalFeatures - existingFeaturesCount;

        // Disable button initially if already at limit
        if (maxAddMore <= 0) {
            addMoreBtn.disabled = true;
            addMoreBtn.textContent = 'Maximum 3 features allowed';
        }

        (function() {
            document.querySelectorAll('.upload-image').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    this.closest('.image-box').querySelector('.file-input').click();
                });
            });
            document.querySelectorAll('.file-input').forEach(function(input) {
                input.addEventListener('change', function(e) {
                    const file = this.files[0];

                    // Clear previous error for this field
                    $(this).closest('.form-group').find('.field-error').remove();

                    if (file) {
                        // Validate file type and size
                        const validationError = validateImageFile(file);
                        if (validationError) {
                            $(this).closest('.form-group').append('<div class="field-error text-danger mt-1">' + validationError + '</div>');
                            this.value = ''; // Clear the input
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            input.closest('.image-box').querySelector('.image-preview').src = e
                                .target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            });
            document.querySelectorAll('.remove-image').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const box = this.closest('.image-box');
                    box.querySelector('.image-preview').src = box.querySelector('.image-preview')
                        .getAttribute('data-default');
                    box.querySelector('.file-input').value = '';
                    box.querySelector('input[name="existing_image"]').value = '';
                });
            });

            addMoreBtn.addEventListener('click', function() {
                if (addMoreCount < maxAddMore) {
                    addMoreCount++;
                    var formHtml = `
                <div class="card card-body mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label d-block text-start" for="add_more_title">Title</label>
                                <input type="text" class="form-control" name="add_more_title[]" placeholder="Enter title">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label d-block text-start" for="add_more_image">Image</label>
                                <div class="d-flex flex-column align-items-center">
                                    <div class="image-box mb-2" style="display: none;">
                                        <img class="feature-image-preview img-fluid"
                                            src=""
                                            style="width: 120px; height: 120px; object-fit: cover;  border-radius: 8px;">
                                    </div>
                                    <input type="file" class="form-control feature-file-input" name="add_more_image[]" accept=".jpg,.jpeg,.png">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
                    addMoreFormsContainer.insertAdjacentHTML('beforeend', formHtml);

                    // Add simple image preview for the newly added file input
                    const latestCard = addMoreFormsContainer.lastElementChild;
                    const latestInput = latestCard.querySelector('.feature-file-input');

                    // Simple file input change for image preview with validation
                    latestInput.addEventListener('change', function(e) {
                        const file = this.files[0];
                        const imageBox = latestCard.querySelector('.image-box');
                        const preview = latestCard.querySelector('.feature-image-preview');

                        // Clear previous error for this field
                        $(this).closest('.form-group').find('.field-error').remove();

                        if (file) {
                            // Validate file type and size
                            const validationError = validateImageFile(file);
                            if (validationError) {
                                $(this).closest('.form-group').append('<div class="field-error text-danger mt-1">' + validationError + '</div>');
                                this.value = ''; // Clear the input
                                imageBox.style.display = 'none';
                                preview.src = '';
                                return;
                            }

                            const reader = new FileReader();
                            reader.onload = function(e) {
                                preview.src = e.target.result;
                                imageBox.style.display = 'block'; // Show preview box
                            };
                            reader.readAsDataURL(file);
                        } else {
                            imageBox.style.display = 'none'; // Hide preview box if no file
                            preview.src = '';
                        }
                    });

                    if (addMoreCount === maxAddMore) {
                        addMoreBtn.disabled = true;
                    }
                }
            });
        })();
    </script>
