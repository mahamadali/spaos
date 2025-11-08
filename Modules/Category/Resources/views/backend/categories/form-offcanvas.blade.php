<!-- Category Offcanvas -->
<form id="category-form"
    action="{{ isset($category) ? route('backend.categories.update', $category->id) : route('backend.categories.store') }}"
    method="POST" enctype="multipart/form-data">
    @csrf
    @if (isset($category))
        @method('PUT')
    @endif
    <div class="offcanvas offcanvas-end" tabindex="-1" id="categoryOffcanvas" aria-labelledby="form-offcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="form-offcanvasLabel">
                @if (isset($isSubCategory))
                    {{ __('New Subcategory') }}
                @elseif(isset($category))
                    {{ __('New Category') }}
                @endif

            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body">
            <div class="row">
                <div class="col-12">

                    {{-- Image Upload --}}
                    {{-- <div class="form-group text-center">
                        <div class="upload-image-box">
                            <img src="{{ isset($category) && $category->feature_image ? asset($category->feature_image) : default_feature_image()}}"
                                 alt="feature-image"
                                 class="img-fluid mb-2 avatar-140 rounded">

                            @error('feature_image')
                                <div class="text-danger mb-2">{{ $message }}</div>
                            @enderror

                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <input type="file" class="form-control d-none" id="feature_image" name="feature_image" accept=".jpeg,.jpg,.png,.gif">
                                <label class="btn btn-sm btn-primary" for="feature_image">{{ __('Upload') }}</label>

                                @if (isset($category) && $category->feature_image)
                                    <a href="{{ route('categories.removeImage', $category->id) }}" class="btn btn-sm btn-secondary">{{ __('Remove') }}</a>
                                @endif
                            </div>
                        </div>
                    </div> --}}

                    <div class="form-group text-center">
                        <div class="upload-image-box">
                            <img src="{{ isset($category) && $category->feature_image ? asset($category->feature_image) : default_feature_image() }}"
                                alt="feature-image" class="img-fluid mb-2 avatar-140 rounded"
                                data-default-src="{{ default_feature_image() }}">

                            @error('feature_image')
                                <div class="text-danger mb-2">{{ $message }}</div>
                            @enderror

                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <input type="file" class="form-control d-none" id="feature_image"
                                    name="feature_image" accept=".jpeg,.jpg,.png,.gif" onchange="previewImage(event)">
                                <label class="btn btn-sm btn-primary" for="feature_image">{{ __('Upload') }}</label>

                                <button type="button" class="btn btn-sm btn-danger" style="display:none"
                                    onclick="removeImage(event)">{{ __('Remove') }}</button>
                            </div>
                        </div>
                    </div>


                    {{-- Parent Category --}}
                    @if (!empty($isSubCategory) && $isSubCategory)
                        <div class="form-group mt-3">
                            <label for="parent_id" class="form-label">{{ __('Parent Category') }} <span
                                    class="text-danger">*</span></label>
                            <select name="parent_id" id="parent_id" class="form-select select2"
                                data-placeholder="{{ __('Select Category') }}">
                                <option value="">{{ __('Select Category') }}</option>
                                @if (isset($categories) && !empty($categories))
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}"
                                            {{ old('parent_id', $category->parent_id ?? '') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('parent_id')
                                <div class="text-danger ">{{ $message }}</div>
                            @enderror
                            <div class="text-danger fields-error  d-none"></div>

                        </div>
                    @endif

                    {{-- Category Name --}}
                    <div class="form-group mt-3">
                        <label for="name" class="form-label">{{ __('Name') }} <span
                                class="text-danger">*</span></label>
                        <input type="text" name="name" id="name"
                            value="{{ old('name', $category->name ?? '') }}"
                            placeholder="{{ __('Enter category name') }}" class="form-control">
                        @error('name')
                            <div class="text-danger ">{{ $message }}</div>
                        @enderror
                        <div class="text-danger fields-error d-none">Name is required </div>
                    </div>

                    {{-- Status Toggle --}}
                    <div class="form-group mt-3">
                        <div class="d-flex justify-content-between align-items-center form-control">
                            <label for="status" class="form-label mb-0">{{ __('Status') }}</label>
                            <div class="form-check form-switch">
                                <input type="hidden" name="status" value="0">
                                <input class="form-check-input category-status" type="checkbox" id="status"
                                    name="status" value="1"
                                    {{ old('status', $category->status ?? true) ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>

        <div class="offcanvas-footer p-3 border-top">
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="offcanvas">
                    {{ __('messages.cancel') }}
                </button>
                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2" name="submit"
                    id="saveBtn" {{ isset($isSubmitted) && $isSubmitted ? 'disabled' : '' }}>
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    <span id="save-text">{{ __('messages.save') }}</span>
                </button>
            </div>
        </div>
    </div>
</form>

<style>
    /* Hide native select arrow completely */
    #parent_id {
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        appearance: none !important;
        background-image: none !important;
        background: transparent !important;
    }

    #parent_id::-ms-expand {
        display: none !important;
    }

    /* Hide native select when Select2 is initialized */
    #parent_id.select2-hidden-accessible {
        opacity: 0;
        position: absolute;
        width: 1px;
        height: 1px;
        pointer-events: none;
    }

    /* Ensure Select2 container is properly styled */
    #parent_id.select2-hidden-accessible+.select2-container {
        width: 100% !important;
    }

    /* Hide any duplicate Select2 containers */
    #parent_id+.select2-container+.select2-container {
        display: none !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (!window.$ || !$.fn.select2) return;

        function initSingleSelect2(selector, options) {
            const $el = $(selector);
            if (!$el.length) return;

            // Remove stray Select2 containers without a preceding select
            $('.select2.select2-container').each(function() {
                const $c = $(this);
                if (!$c.prev('select').length) $c.remove();
            });

            // If already initialized, destroy then re-init to avoid duplicates
            if ($el.hasClass('select2-hidden-accessible')) {
                try {
                    $el.select2('destroy');
                } catch (e) {
                    /* ignore */
                }
            }

            // Remove any duplicate containers next to this select
            $el.siblings('.select2.select2-container').remove();

            // Initialize once
            $el.select2(options);
        }

        initSingleSelect2('#parent_id', {
            width: '100%',
            placeholder: $('#parent_id').data('placeholder') || '',
            allowClear: true
        });

        // Re-init when offcanvas opens (in case the fragment is injected again)
        const oc = document.getElementById('categoryOffcanvas');
        if (oc) {
            oc.addEventListener('shown.bs.offcanvas', function() {
                initSingleSelect2('#parent_id', {
                    width: '100%',
                    placeholder: $('#parent_id').data('placeholder') || '',
                    allowClear: true
                });
            });
        }
    });
</script>
