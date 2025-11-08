@php

    $homepages = Modules\FrontendSetting\Models\VideoSection::where('created_by', auth()->user()->id)->first();
    // Default values for demonstration; replace with dynamic data as needed
    $video_img =
        isset($homepages) && $homepages->video_img
            ? asset(Storage::url($homepages->video_img))
            : asset('video_section/video_section.jpg');
    $video_type = isset($homepages) ? $homepages->video_type : 'youtube';
    $video_url = isset($homepages) ? $homepages->video_url : 'https://www.youtube.com/watch?v=urPq7Qq0lXk';

@endphp



<div class="row">
    <div class="form-group">
        <div class="form-check form-switch d-flex justify-content-between align-items-center p-2 border rounded">
            <div>
                <h4 class="mb-3 text-start">{{ __('frontend.video') }}</h4>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="videoSectionForm" method="POST" action="{{ route('video_section.store') }}" enctype="multipart/form-data">
        @csrf
        <!-- Video Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="settings-box  bg-body rounded">

                    <div class="row gy-4">
                        {{-- <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label" for="video_img">{{ __('frontend.video_img') }}</label>
                            <div class="d-flex flex-column align-items-center">
                                <div class="image-box" style="text-align:center;">
                                    <img id="preview" src="{{ isset($video_img) ? $video_img : asset('img/frontend/video-image.png') }}" style="max-width: 200px; max-height: 200px; display: block; margin: 0 auto;" />
                                    <input type="file" id="video_img" name="video_img" accept="image/*" style="display: none;" onchange="previewImage(event)">
                                    <button type="button" class="btn btn-sm btn-primary upload-image" onclick="document.getElementById('video_img').click();">{{ __('frontend.upload') }}</button>
                                    <button type="button" class="btn btn-sm btn-danger remove-image" onclick="removeImage();">{{ __('frontend.remove') }}</button>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label d-block text-start"
                                    for="video_type">{{ __('frontend.video_type') }}</label>
                                <select name="video_type" id="video_type" class="form-select select2">
                                    <option value="">{{ __('frontend.select_video_type') }}</option>
                                    <option value="mp4" {{ $video_type == 'mp4' ? 'selected' : '' }}>MP4</option>
                                    <option value="youtube" {{ $video_type == 'youtube' ? 'selected' : '' }}>YouTube
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label d-block text-start"
                                    for="video_url">{{ __('frontend.video_URL') }}</label>
                                <input type="url" name="video_url" id="video_url" class="form-control"
                                    placeholder="{{ __('frontend.enter_video_URL') }}" value="{{ $video_url }}">
                                <span id="video_url_error" class="text-danger"
                                    style="display: none;">{{ __('frontend.invalid_videourl') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary" id="saveVideoBtn">
                            <span id="saveBtnText">{{ __('frontend.Save') }}</span>
                            <span id="saveBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status"
                                aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        // CSRF Token setup for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Form submission handler
        $('#videoSectionForm').on('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this);
            var submitBtn = $('#saveVideoBtn');
            var submitBtnText = $('#saveBtnText');
            var submitBtnSpinner = $('#saveBtnSpinner');

            // Disable button and show spinner
            submitBtn.prop('disabled', true);
            submitBtnText.text('Saving...');
            submitBtnSpinner.removeClass('d-none');

            // Clear previous messages

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {

                    if (typeof window.successSnackbar === 'function') {
                        window.successSnackbar(response.message);
                    } else {

                        window.errorSnackbar(response.message);
                    }

                    submitBtn.prop('disabled', false);
                    submitBtnText.text('Save');
                    submitBtnSpinner.addClass('d-none');
                },
                error: function(xhr) {

                    window.errorSnackbar(xhr.responseJSON.message);
                    submitBtn.prop('disabled', false);
                    submitBtnText.text('Save');
                    submitBtnSpinner.addClass('d-none');

                }
            });
        });

        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('preview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        function removeImage() {
            var preview = document.getElementById('preview');
            preview.src = "{{ asset('img/frontend/video-image.png') }}";
            document.getElementById('video_img').value = '';
        }
    </script>
