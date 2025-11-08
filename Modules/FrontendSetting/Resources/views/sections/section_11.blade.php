<!-- CSRF Meta -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="section-content" style="text-align: left;">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="form-group">
                    <div
                        class="form-check form-switch d-flex justify-content-between align-items-center p-2 border rounded">
                        <div>
                            <h4 class="text-xl font-bold">{{ __('frontend.Blog_Section') }}</h4>

                        </div>
                        <input type="checkbox" id="section_11" class="form-check-input section_11" name="status"
                            data-type="section_11" style="width: 3em; height: 1.5em;">
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button id="saveButton" class="btn btn-primary">{{ __('frontend.Save') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Snackbar -->
<div id="snackbar" class="snackbar-container snackbar-pos bottom-left">
    <span id="snackbar-message"></span>
</div>
<script>
    $(document).ready(function() {
        const $sectionToggle = $('#section_11');
        const $sectionContent = $('#enable_section_11');
        const $statusLabel = $('#statusLabel');
        const $snackbar = $('#snackbar');

        function toggleSectionDisplay(isChecked) {
            $statusLabel.text(isChecked ? 'Enabled' : 'Disabled');
            if (isChecked) {
                $sectionContent.removeClass('d-none').hide().slideDown(200);
            } else {
                $sectionContent.slideUp(200, function() {
                    $(this).addClass('d-none');
                });
            }
        }

        // Initial state
        toggleSectionDisplay($sectionToggle.prop('checked'));

        // Toggle change handler
        $sectionToggle.on('change', function() {
            toggleSectionDisplay($(this).prop('checked'));
        });

        function getConfig(type) {
            const _token = $('meta[name="csrf-token"]').attr('content');
            const page = "{{ $tabpage }}";
            const route = "{{ route('getLandingLayoutPageConfig') }}";

            $.ajax({
                url: route,
                type: "POST",
                data: {
                    type: 'landing-page-setting',
                    key: 'section_11',
                    _token,
                    page
                },

                success: function(response) {
                    let config = {};

                    try {
                        const raw = response?.data?.value;
                        config = typeof raw === 'string' ? JSON.parse(raw) : raw || {};
                    } catch (e) {


                    }

                    $('#title_id').val(config.title_id || '');
                    $('#subtitle_id').val(config.subtitle_id || '');
                    $('#select_blog_id').val(config.select_blog_id || '');

                    const isEnabled = response?.data?.status === 1;
                    $sectionToggle.prop('checked', isEnabled);
                    toggleSectionDisplay(isEnabled);
                },
                error: function(err) {
                    if (err.status === 403) {
                        if (window.Swal && Swal.fire) {
                            Swal.fire({
                                title: 'Upgrade required',
                                text: 'This feature is not available in your current plan. Upgrade now?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Upgrade Plan',
                                cancelButtonText: 'Cancel',
                                reverseButtons: true,
                            }).then(function(result) {
                                if (result.isConfirmed) {
                                    window.location.href =
                                        "{{ route('backend.upgrade-plan') }}";
                                }
                            });
                        } else {
                            window.errorSnackbar('Access denied. Please check your subscription.');
                        }
                        return;
                    }

                    const message = err.responseJSON && err.responseJSON.message ?
                        err.responseJSON.message :
                        'Failed to load configuration.';
                    window.errorSnackbar(message);
                }
            });
        }

        // Initial config load
        const sectionType = $sectionToggle.data('type');
        if (sectionType) getConfig(sectionType);

        $('#saveButton').click(function(e) {
            e.preventDefault();

            const _token = $('meta[name="csrf-token"]').attr('content');
            const type = $sectionToggle.data('type');
            const page = "{{ $tabpage }}";

            const isEnabled = $sectionToggle.prop('checked');

            const dataToSend = {
                _token,
                type,
                page,
                status: isEnabled ? 1 : 0,
                title_id: isEnabled ? $('#title_id').val() : '',
                subtitle_id: isEnabled ? $('#subtitle_id').val() : '',
                select_blog_id: isEnabled ? $('#select_blog_id').val() : '',
            };

            $(this).prop('disabled', true).text('Saving...');

            $.ajax({
                url: "{{ route('saveLandingLayoutPageConfig') }}",
                type: "POST",
                data: dataToSend,
                success: function(response) {
                    window.successSnackbar(response.success ?
                        'Blog section saved successfully.' :
                        'Failed to save.');
                },
                error: function(error) {
                    if (error.status === 403) {
                        if (window.Swal && Swal.fire) {
                            Swal.fire({
                                title: 'Upgrade required',
                                text: 'This feature is not available in your current plan. Upgrade now?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Upgrade Plan',
                                cancelButtonText: 'Cancel',
                                reverseButtons: true,
                            }).then(function(result) {
                                if (result.isConfirmed) {
                                    window.location.href =
                                        "{{ route('backend.upgrade-plan') }}";
                                }
                            });
                        } else {
                            window.errorSnackbar(
                                'Access denied. Please check your subscription.');
                        }
                        return;
                    }

                    let message = 'An error occurred while saving.';
                    if (error.responseJSON && error.responseJSON.message) {
                        message = error.responseJSON.message;
                    } else if (error.status === 500) {
                        message = 'Server error occurred. Please try again later.';
                    }
                    window.errorSnackbar(message);
                },
                complete: function() {
                    $('#saveButton').prop('disabled', false).text('Save');
                }
            });
        });
    });
</script>
