<form method="POST" action="{{ isset($product) ? route('backend.products.update', $product->id) : route('backend.products.store') }}" enctype="multipart/form-data" id="product-form">
    @csrf
    @if(isset($product))
        @method('PUT')
    @endif

    
     <div class="offcanvas offcanvas-end" tabindex="-1" id="form-offcanvas" aria-labelledby="form-offcanvasLabel" style="width: 1200px !important; max-width: 95vw;">

        <!-- Form Header -->
        <div class="offcanvas-header">

            <h5 class="offcanvas-title" id="form-offcanvasLabel">
                {{ isset($product) ? $editTitle ?? 'Edit Product' : $createTitle ?? 'Create Product' }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        

        <div class="offcanvas-body">
            <!-- Product Information Section -->
            <fieldset>
                <legend>{{ __('product.product_information') }}</legend>
                <div class="row">

                    <!-- Image Upload Section -->
                    <div class="form-group col-md-4">
                        <div class="text-center">
                            <label class="form-label d-block mb-2">
                                {{ __('messages.image') }} <span class="text-danger">*</span>
                            </label>
                            <img src="{{ old('feature_image', $product->feature_image ?? $defaultImage ?? asset('images/default.png')) }}" 
                                 alt="feature-image" 
                                 class="img-fluid mb-2 product-image-thumbnail" 
                                 id="image-preview" />
                            <div id="validation-message" class="text-danger mb-2" style="display: none;"></div>
                            <div id="image-error" class="text-danger small mt-1 mb-2" style="display: none;"></div>
                            <div class="d-flex align-items-center justify-content-center gap-2">

                                <input type="file" class="form-control d-none" id="feature_image" name="feature_image" 
                                       accept=".jpeg, .jpg, .png, .gif" />
                                <label class="btn btn-secondary" for="feature_image">{{ __('messages.upload') }}</label>

                                <input type="button" class="btn btn-danger" name="remove" value="{{ __('messages.remove') }}"
                                       onclick="removeImage()" id="remove-image-btn" 
                                       style="display: {{ old('feature_image', $product->feature_image ?? false) ? 'block' : 'none' }};" />
                                <input type="hidden" name="remove_feature_image" id="remove_feature_image" value="0" />
                                <input type="hidden" name="existing_image" id="existing_image" value="{{ $product->feature_image ?? '' }}" />
                            </div>
                        </div>
                    </div>


                    <!-- Basic Product Details -->
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="name">{{ __('product.name') }} <span class="text-danger">*</span></label>

                            <input type="text" name="name" id="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" 
                                   value="{{ old('name', $product->name ?? '') }}" placeholder="{{ __('messages.enter_product_name') }}" required>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="short_description">{{ __('product.description') }}</label>

                            <textarea name="short_description" id="short_description" class="form-control" rows="5" maxlength="100" placeholder="{{ __('messages.enter_short_description') }}">{{ old('short_description', $product->short_description ?? '') }}</textarea>
                            <div class="d-flex justify-content-end">
                                <small class="text-muted">
                                    <span id="short_description_count">0</span>/100 characters
                                </small>
                            </div>
                        </div>
                    </div>


                                         <!-- Long Description Editor -->
                     <div class="col-md-12 form-group editor-container">
                         <label class="form-label" for="description">{{ __('product.long_description') }}</label>
                         
                                                   <!-- Quill Editor Container -->
                          <div id="quill-editor" style="height: 600px;">{{ old('description', $product->description ?? '') }}</div>
                          <textarea name="description" id="description" class="form-control d-none" rows="5" maxlength="250">{{ old('description', $product->description ?? '') }}</textarea>
                          <div class="d-flex justify-content-end">
                              <small class="text-muted">
                                  <span id="description_count">0</span>/250 characters
                              </small>
                          </div>
                         
                         @error('description')
                             <span class="text-danger">{{ $message }}</span>
                         @enderror
                     </div>


                    <!-- Brand Selection -->
                    <div class="form-group col-md-6">
                        <label class="form-label">{{ __('product.brand') }} <span class="text-danger">*</span></label>

                        <select name="brand_id" id="brand_id" class="form-control select2 {{ $errors->has('brand_id') ? 'is-invalid' : '' }}" style="width:100%" data-placeholder="{{ __('product.select_brand') }}" required>
                            <option value="">{{ __('product.select_brand') }}</option>
                            @foreach($brands as $brand)

                                <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id ?? '') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('brand_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>


                    <!-- Categories Selection -->
                    <div class="form-group col-md-6">

                        <label class="form-label" for="category_ids">{{ __('product.categories') }} <span class="text-danger">*</span></label>
                        <select name="category_ids[]" id="category_ids" class="form-control select2 {{ $errors->has('category_ids') ? 'is-invalid' : '' }}" multiple style="width:100%" data-placeholder="{{ __('messages.select_categories') }}" required>
                            @foreach($categories as $category)

                                <option value="{{ $category->id }}" 
                                        {{ collect(old('category_ids', $product->category_ids ?? []))->contains($category->id) ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('category_ids')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Subcategories Selection (dependent on Categories) -->
                    <div class="form-group col-md-6">
                        <label class="form-label" for="subcategory_ids">{{ __('messages.subcategory') }}</label>
                        <select name="subcategory_ids[]" id="subcategory_ids" class="form-control select2" multiple style="width:100%" data-placeholder="{{ __('messages.select_subcategories') }}">
                            <!-- Options populated dynamically based on selected categories -->
                        </select>
                    </div>


                    <!-- Tags Selection -->
                    <div class="form-group col-md-6">
                        <label class="form-label">{{ __('product.tags') }}</label>

                        <select name="tags[]" id="tags" class="form-control select2" multiple style="width:100%" data-placeholder="{{ __('messages.select_tags') }}">
                            @foreach($tagsList as $tag)

                                <option value="{{ $tag->name }}" 
                                        {{ collect(old('tags', $product->tags ?? []))->contains($tag->name) ? 'selected' : '' }}>
                                    {{ $tag->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    <!-- Unit Selection -->
                    <div class="col-md-6 form-group">
                        <label class="form-label">{{ __('product.unit') }}</label>

                        <select name="unit_id" id="unit_id" class="form-control select2" style="width:100%" data-placeholder="{{ __('product.select_unit') }}">
                            <option value="">{{ __('product.select_unit') }}</option>
                            @foreach($units as $unit)

                                <option value="{{ $unit->id }}" {{ old('unit_id', $product->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </fieldset>


            <!-- Product Price Section -->
            <fieldset>
                <legend>{{ __('product.product_price') }}</legend>
                <div class="form-group">
                    <div class="d-flex justify-content-end">

                        <label class="form-label me-2" for="has_variation">{{ __('product.has_variation') }}</label>
                        <div class="form-check form-switch">
                            <input type="hidden" name="has_variation" value="0" />
                            <input class="form-check-input" type="checkbox" name="has_variation" id="has_variation" 
                                   value="1" {{ old('has_variation', $product->has_variation ?? 0) ? 'checked' : '' }} />
                        </div>
                    </div>
                </div>


                <!-- Variations Section -->
                <div class="row" id="variation-section" style="display: {{ old('has_variation', $product->has_variation ?? 0) ? 'block' : 'none' }};">
                    <div id="variations-container">
                        <!-- Dynamic variation fields will be added here -->
                    </div>
                    
                    <div class="col-md-6" id="add-variation-btn" style="display: none;">
                        <div class="form-group">
                            <button class="btn btn-secondary" type="button" onclick="addVariation()">
                                + {{ __('product.add_more_variation') }}
                            </button>
                        </div>
                    </div>

                    <!-- Combinations Table -->
                    <div class="row" id="combinations-table" style="display: none;">
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('product.variation') }}</th>
                                        <th>{{ __('product.price_tax') }}</th>
                                        <th>{{ __('product.stock') }}</th>
                                        <th>{{ __('product.sku') }}</th>
                                        <th>{{ __('product.code') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="combinations-tbody">
                                    <!-- Dynamic combinations will be added here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <!-- No Variation Section -->
                <div class="d-flex flex-nowrap gap-3 align-items-end" id="no-variation-section" style="display: {{ old('has_variation', $product->has_variation ?? 0) ? 'none' : 'flex' }}; overflow-x: auto;">
                    <div class="w-25" style="min-width: 220px;">
                        <div class="form-group">
                        <label>{{ __('product.price_tax') }}</label><span class="text-danger">*</span></label>

                            <input type="number" name="price" id="price" class="form-control {{ $errors->has('price') ? 'is-invalid' : '' }}" 
                                   step="0.01" min="0.01" value="{{ old('price', $product->price ?? '') }}" placeholder="{{ __('messages.enter_price') }}">
                            @error('price')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="w-25" style="min-width: 220px;">
                        <div class="form-group">
                        <label>{{ __('product.stock') }}</label><span class="text-danger">*</span></label>

                            <input type="number" name="stock" id="stock" class="form-control {{ $errors->has('stock') ? 'is-invalid' : '' }}" 
                                   step="1" min="1" value="{{ old('stock', $product->stock ?? '') }}" placeholder="{{ __('messages.enter_stock') }}">
                            @error('stock')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="w-25" style="min-width: 220px;">
                        <div class="form-group">
                        <label>{{ __('product.sku') }}</label><span class="text-danger">*</span></label>

                            <input type="text" name="sku" id="sku" class="form-control {{ $errors->has('sku') ? 'is-invalid' : '' }}" 
                                   value="{{ old('sku', $product->sku ?? '') }}" placeholder="{{ __('messages.enter_sku') }}">
                            @error('sku')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="w-25" style="min-width: 220px;">
                        <div class="form-group">
                        <label>{{ __('product.code') }}</label><span class="text-danger">*</span></label>

                            <input type="text" name="code" id="code" class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}" 
                                   value="{{ old('code', $product->code ?? '') }}" placeholder="{{ __('messages.enter_code') }}">
                            @error('code')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </fieldset>


            <!-- Product Discount Section -->
            @php $hasDiscount = isset($product) ? ((float)($product->discount_value ?? 0) > 0) : false; @endphp
            <fieldset>
                <legend>{{ __('product.product_discount') }}</legend>
                <div class="form-group">
                    <div class="d-flex justify-content-end">
                        <label class="form-label me-2" for="discount_enabled">{{ __('messages.enable') }}</label>
                        <div class="form-check form-switch">
                            <input type="hidden" name="discount_enabled" value="0" />
                            <input class="form-check-input" type="checkbox" name="discount_enabled" id="discount_enabled" value="1" {{ old('discount_enabled', $hasDiscount ? 1 : 0) ? 'checked' : '' }} />
                        </div>
                    </div>
                </div>
                <div class="row" id="discount-section" style="display: {{ old('discount_enabled', $hasDiscount ? 1 : 0) ? 'flex' : 'none' }};">
                    <div class="col-md-4">
                        <div class="form-group">

                            <label class="form-label" for="date_range">{{ __('product.date') }}</label>
                            <div class="w-100">
                                <input type="text" name="date_range" id="date_range" class="form-control" 
                                       value="{{ old('date_range', $product->date_range ?? '') }}" placeholder="{{ __('messages.enter_date_range') }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('product.amount') }}</label>

                        <input type="number" name="discount_value" id="discount_value" class="form-control {{ $errors->has('discount_value') ? 'is-invalid' : '' }}" 
                               step="1" min="0" value="{{ old('discount_value', $product->discount_value ?? 0) }}" 
                               oninput="formatDiscountValue(this)" onkeypress="return isIntegerKey(event)" placeholder="0">
                        @error('discount_value')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('product.percent_or_fixed') }}</label>

                        <select name="discount_type" id="discount_type" class="form-control select2" style="width:100%">
                            <option value="percent" {{ old('discount_type', $product->discount_type ?? 'percent') == 'percent' ? 'selected' : '' }}>
                                Percent(%)
                            </option>
                            <option value="fixed" {{ old('discount_type', $product->discount_type ?? 'percent') == 'fixed' ? 'selected' : '' }}>
                                Fixed
                            </option>
                        </select>
                    </div>
                </div>
            </fieldset>


            <!-- Status and Featured Section -->
            <div class="row">
                <div class="col-md-12 px-5">
                    <div class="form-group">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <input class="form-check-input m-0" type="checkbox" name="is_featured" id="is_featured" 
                                       value="1" {{ old('is_featured', $product->is_featured ?? 0) ? 'checked' : '' }} />
                                <label class="form-label m-0" for="is_featured">{{ __('product.lbl_is_featured') }}</label>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <label class="form-label" for="status">{{ __('product.lbl_status') }}</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="status" id="status" 
                                           value="1" {{ old('status', isset($product) ? $product->status : 1) ? 'checked' : '' }} />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
       

        <!-- Form Footer -->
        <div class="offcanvas-footer p-3 border-top">
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="offcanvas">
                    {{ __('messages.cancel') }}
                </button>
                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2" id="submit-btn">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save
                </button>
            </div>
        </div>
        
    
    </div>
    <!-- Hidden inputs for variations and combinations (must be inside the form) -->
    <input type="hidden" name="variations" id="variations-input">
    <input type="hidden" name="combinations" id="combinations-input">
</form>

 <!-- Include Flatpickr CSS and JS -->
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
 <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
 
 <!-- Include Quill CSS and JS -->
 <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
 <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
 
<script>
// Global variables
let variations = [];
let combinations = [];
let variationCounter = 0;
let variationsData = @json($variations ?? []);
let brands = @json($brands ?? []);
let categories = @json($categories ?? []);
let units = @json($units ?? []);
let tagsList = @json($tagsList ?? []);
let __productFormIsEditing = false;
let quillEditor = null;

// Global function to update discount UI
function updateDiscountUI() {
    const discToggle = document.getElementById('discount_enabled');
    const discSection = document.getElementById('discount-section');
    const discAmount = document.getElementById('discount_value');
    const discType = document.getElementById('discount_type');
    const discDate = document.getElementById('date_range');
    
    if (discToggle && discSection) {
        const on = !!discToggle.checked;
        discSection.style.display = on ? 'flex' : 'none';
        [discAmount, discType, discDate].forEach(el => {
            if (el) {
                el.disabled = !on;
            }
        });
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeForm();
    initializeFlatpickr();
    setupEventListeners();
    // If variationsData is empty, fetch from API as a fallback
    if (!Array.isArray(variationsData) || variationsData.length === 0) {
        fetch(`{{ url('app/variations/index_list') }}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(list => { variationsData = Array.isArray(list) ? list : []; })
            .catch(() => {});
    }
    // Initialize Select2 for categories and tags (ensure only ONE instance is visible)
    if (window.$ && $.fn.select2) {
        const initSingleSelect2 = function(selector, options) {
            const $el = $(selector);
            if (!$el.length) return;
            // Remove any duplicate Select2 containers created by other scripts
            const $containers = $el.siblings('.select2');
            if ($containers.length > 1) {
                $containers.not(':first').remove();
            }
            // Initialize only if not already initialized
            if (!$el.hasClass('select2-hidden-accessible')) {
                $el.select2(options);
            }
            // Hide original select to avoid double boxes
            $el.css({ position: 'absolute', width: '1px', height: '1px', opacity: 0, pointerEvents: 'none' });
        };

        initSingleSelect2('#brand_id', { width: '100%', placeholder: $('#brand_id').data('placeholder') || '{{ __('product.select_brand') }}', allowClear: true });
        initSingleSelect2('#category_ids', { width: '100%', placeholder: $('#category_ids').data('placeholder') || '{{ __('messages.select') }}', allowClear: true });
        initSingleSelect2('#subcategory_ids', { width: '100%', placeholder: $('#subcategory_ids').data('placeholder') || '{{ __('messages.select') }}', allowClear: true });
        initSingleSelect2('#tags', { width: '100%', tags: true, tokenSeparators: [','], placeholder: $('#tags').data('placeholder') || '{{ __('messages.select') }}', allowClear: true });
        initSingleSelect2('#unit_id', { width: '100%', placeholder: $('#unit_id').data('placeholder') || '{{ __('product.select_unit') }}', allowClear: true });
        initSingleSelect2('#discount_type', { width: '100%' });
    }
    
    // jQuery Validate - custom rules when no variations
    if (window.$ && $.fn.validate) {
        const $form = $('#product-form');
        const baseRules = {
            name: { required: true },
            brand_id: { required: true },
            'category_ids[]': { required: true },
            feature_image: { 
                required: function() {
                    // Image is required for new products or if removing existing image
                    const existingImage = document.getElementById('existing_image')?.value;
                    const removeFlag = document.getElementById('remove_feature_image')?.value;
                    const isEditing = !!existingImage && existingImage !== '';
                    const isRemoving = removeFlag === '1';
                    
                    // If editing and has existing image and not removing, not required
                    if (isEditing && !isRemoving) {
                        return false;
                    }
                    // Otherwise, check if file input has a value
                    const fileInput = document.getElementById('feature_image');
                    return !fileInput || !fileInput.files || fileInput.files.length === 0;
                }
            }
        };
        function clearFieldErrors(selectors){
            selectors.forEach(sel => {
                const $el = $(sel);
                $el.removeClass('is-invalid');
                // remove next error message if present
                const $next = $el.next();
                if ($next && $next.hasClass && $next.hasClass('text-danger')) { $next.remove(); }
            });
        }
        function applyRules(hasVar){
            // remove previous dynamic rules
            $('#price').rules && $('#price').rules('remove');
            $('#stock').rules && $('#stock').rules('remove');
            $('#sku').rules && $('#sku').rules('remove');
            $('#code').rules && $('#code').rules('remove');
            if (!hasVar) {
                $('#price').rules('add', { required: true, number: true, min: 0.01, messages: { required: 'Price is a required field' } });
                $('#stock').rules('add', { required: true, digits: true, min: 1, messages: { required: 'Stock is a required field' } });
                $('#sku').rules('add', { required: true, messages: { required: 'SKU is a required field' } });
                $('#code').rules('add', { required: true, messages: { required: 'Code is a required field' } });
            } else {
                // when variations are on, clear existing error states for hidden fields
                clearFieldErrors(['#price', '#stock', '#sku', '#code']);
            }
        }
        $form.validate({
            // ignore hidden inputs but NOT select2 replacement containers and NOT feature_image
            ignore: '.d-none :input:not(#feature_image), :hidden:not(.select2-hidden-accessible):not(#feature_image)',
            rules: baseRules,
            errorElement: 'div',
            errorClass: 'text-danger small mt-1',
            highlight: function(element){ 
                const $element = $(element);
                $element.addClass('is-invalid');
                // For image field, show error in custom error div
                if (element.id === 'feature_image') {
                    $('#image-error').text('Image is required').show();
                }
            },
            unhighlight: function(element){ 
                const $element = $(element);
                $element.removeClass('is-invalid');
                // For image field, hide error in custom error div
                if (element.id === 'feature_image') {
                    $('#image-error').hide();
                }
            },
            errorPlacement: function(error, element){
                const $element = $(element);
                // Place errors correctly for Select2 and normal inputs
                if ($element.hasClass('select2-hidden-accessible')) {
                    error.insertAfter($element.next('.select2'));
                } else if (element.id === 'feature_image') {
                    // Place image error in custom error div
                    $('#image-error').text(error.text()).show();
                } else {
                    error.insertAfter($element);
                }
            },
            messages: {
                name: { required: 'Product Name is a required field' },
                brand_id: { required: 'Brand is a required field' },
                'category_ids[]': { required: 'Categories is a required field' },
                feature_image: { required: 'Image is required' }
            }
        });
        applyRules($('#has_variation').is(':checked'));
        $('#has_variation').on('change', function(){ applyRules($(this).is(':checked')); });
    }

    // On load: normalize categories list to brand-specific current names only
    try {
        const brandEl = document.getElementById('brand_id');
        let currentBrand = '';
        if (brandEl) {
            if (window.$ && $.fn.select2 && $(brandEl).hasClass('select2-hidden-accessible')) {
                currentBrand = ($(brandEl).val() || '');
            } else {
                currentBrand = brandEl.value || '';
            }
        }
        if (currentBrand) {
            // Replace any server-rendered options with fresh API data to avoid stale/renamed entries
            reloadCategoriesByBrand(currentBrand);
        } else {
            // If no brand selected yet, keep categories empty to avoid mixing parent/sub entries
            const catSel = document.getElementById('category_ids');
            if (catSel) { while (catSel.firstChild) catSel.removeChild(catSel.firstChild); }
        }
    } catch (e) {}

    // Enable submit button by default
    const submitBtn = document.getElementById('submit-btn');
    if (submitBtn) {
        submitBtn.disabled = false;
    }

    // Default form to create mode when offcanvas opens unless an explicit edit load just occurred
    const offcanvasEl = document.getElementById('form-offcanvas');
    if (offcanvasEl) {
        offcanvasEl.addEventListener('show.bs.offcanvas', function(){
            if (!__productFormIsEditing) {
                resetFormForCreate();
            }
            
            // Status will be set by edit function when needed
        });
        offcanvasEl.addEventListener('hidden.bs.offcanvas', function(){
            // Clear edit flag when closed so next open defaults to create
            __productFormIsEditing = false;
        });
    }
});

function initializeForm() {
    // Set initial form state
    updateFormValidation();
    
    // Initialize variations if editing
    @if(isset($product) && $product->has_variation)
        initializeExistingVariations();
    @endif

    // Ensure correct visibility based on current toggle state
    const hasVarEl = document.getElementById('has_variation');
    if (hasVarEl) {
        toggleVariationSection(hasVarEl.checked);
    }
    
    // Initialize discount UI based on current state
    updateDiscountUI();
    
    // Initialize character counts after form is ready
    setupCharacterCounts();

    // Ensure subcategories reflect initial categories (edit mode)
    const initialCats = (window.$ && $.fn.select2) ? ($('#category_ids').val() || []) : Array.from(document.getElementById('category_ids')?.selectedOptions || []).map(o => o.value);
    populateSubcategories(initialCats);
}

function initializeFlatpickr() {
    flatpickr("#date_range", {
        dateFormat: "Y/m/d",
        minDate: "today",
        static: true,
        mode: "range"
    });
}

function setupEventListeners() {
    // Has variation toggle
    document.getElementById('has_variation').addEventListener('change', function() {
        toggleVariationSection(this.checked);
    });

    // Brand change event -> reload categories by brand
    const brandEl = document.getElementById('brand_id');
    if (brandEl) {
        brandEl.addEventListener('change', async function() {
            await reloadCategoriesByBrand(this.value);
        });
        if (window.$ && $.fn.select2) {
            $('#brand_id').on('select2:select', async function(){
                await reloadCategoriesByBrand($(this).val());
            });
        }
    }

    // Populate subcategories when categories change
    const categorySelect = document.getElementById('category_ids');
    if (categorySelect) {
        // Listen to native and Select2 change
        const onCatChange = function(){
            const vals = (window.$ && $.fn.select2) ? ($('#category_ids').val() || []) : Array.from(categorySelect.selectedOptions).map(o => o.value);
            populateSubcategories(vals);
        };
        categorySelect.addEventListener('change', onCatChange);
        if (window.$ && $.fn.select2) { $('#category_ids').on('change', onCatChange); }
        // initial load
        const initVals = (window.$ && $.fn.select2) ? ($('#category_ids').val() || []) : Array.from(categorySelect.selectedOptions || []).map(o => o.value);
        populateSubcategories(initVals);
    }

    // File upload
    document.getElementById('feature_image').addEventListener('change', handleFileUpload);
    
    // Setup form validation
    setupFormValidation();

    // Initialize discount toggle behavior
    const discToggle = document.getElementById('discount_enabled');
    if (discToggle) {
        discToggle.addEventListener('change', updateDiscountUI);
    }
    
    // Add real-time validation for discount value
    const discAmount = document.getElementById('discount_value');
    if (discAmount) {
        discAmount.addEventListener('input', function() {
            const value = parseFloat(this.value) || 0;
            const discountTypeEl = document.getElementById('discount_type');
            const discountType = discountTypeEl ? discountTypeEl.value : 'percent';
            
            // Remove previous error styling
            this.classList.remove('is-invalid');
            const errorElement = this.nextElementSibling;
            if (errorElement && errorElement.classList.contains('text-danger')) {
                errorElement.remove();
            }
            
            // Validate discount value
            if (value === 0) {
                this.classList.add('is-invalid');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'text-danger small mt-1';
                errorDiv.textContent = 'Discount value cannot be 0. Please enter a value greater than 0.';
                this.parentNode.appendChild(errorDiv);
            } else if (value < 0) {
                this.classList.add('is-invalid');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'text-danger small mt-1';
                errorDiv.textContent = 'Discount value cannot be negative.';
                this.parentNode.appendChild(errorDiv);
            }
        });
    }
    
    // Add character count functionality for description fields
    setupCharacterCounts();
    
    // Initialize Quill editor
    initializeQuillEditor();
}

// Function to setup character count functionality
function setupCharacterCounts() {
    // Short description character count
    const shortDesc = document.getElementById('short_description');
    const shortDescCount = document.getElementById('short_description_count');
    
    if (shortDesc && shortDescCount) {
        // Set initial count based on current value
        const initialShortLength = shortDesc.value.length;
        shortDescCount.textContent = initialShortLength;
        
        // Set initial color based on current length
        if (initialShortLength >= 90) {
            shortDescCount.style.color = '#dc3545'; // Red when close to limit
        } else if (initialShortLength >= 80) {
            shortDescCount.style.color = '#ffc107'; // Yellow when approaching limit
        } else {
            shortDescCount.style.color = '#6c757d'; // Default gray
        }
        
        // Update count on input
        shortDesc.addEventListener('input', function() {
            const currentLength = this.value.length;
            shortDescCount.textContent = currentLength;
            
            // Change color when approaching limit
            if (currentLength >= 90) {
                shortDescCount.style.color = '#dc3545'; // Red when close to limit
            } else if (currentLength >= 80) {
                shortDescCount.style.color = '#ffc107'; // Yellow when approaching limit
            } else {
                shortDescCount.style.color = '#6c757d'; // Default gray
            }
        });
    }
    
    // Long description character count
    const longDesc = document.getElementById('description');
    const longDescCount = document.getElementById('description_count');
    
    if (longDesc && longDescCount) {
        // Set initial count based on current value
        const initialLongLength = longDesc.value.length;
        longDescCount.textContent = initialLongLength;
        
        // Set initial color based on current length
        if (initialLongLength >= 225) {
            longDescCount.style.color = '#dc3545'; // Red when close to limit
        } else if (initialLongLength >= 200) {
            longDescCount.style.color = '#ffc107'; // Yellow when approaching limit
        } else {
            longDescCount.style.color = '#6c757d'; // Default gray
        }
        
        // Update count on input
        longDesc.addEventListener('input', function() {
            const currentLength = this.value.length;
            longDescCount.textContent = currentLength;
            
            // Change color when approaching limit
            if (currentLength >= 225) {
                longDescCount.style.color = '#dc3545'; // Red when close to limit
            } else if (currentLength >= 200) {
                longDescCount.style.color = '#ffc107'; // Yellow when approaching limit
            } else {
                longDescCount.style.color = '#6c757d'; // Default gray
            }
        });
    }
}

// Populate subcategories based on selected categories
async function fetchSubcategoriesByCategories(categoryIds) {
    try {
        if (!Array.isArray(categoryIds) || categoryIds.length === 0) return [];
        // Use Product module endpoint: GET /app/products-categories/index_list?parent_id={id}
        const base = `{{ url('app/products-categories/index_list') }}`;
        const responses = await Promise.all(categoryIds.map(async (cid) => {
            const res = await fetch(`${base}?parent_id=${encodeURIComponent(cid)}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return [];
            const json = await res.json();
            // Endpoint returns array of {id, name}
            return Array.isArray(json) ? json.map(it => ({ id: it.id, name: it.name, parent_id: cid })) : [];
        }));
        const flat = responses.flat();
        // Normalize to {id, name, category_id}
        return flat.map(it => ({ id: it.id, name: it.name, category_id: it.parent_id ?? it.category_id }));
    } catch (e) {
        return [];
    }
}

async function populateSubcategories(categoryIds) {
    const subSelect = document.getElementById('subcategory_ids');
    if (!subSelect) return;
    const $sub = window.$ ? $(subSelect) : null;

    // Clear existing options completely
    while (subSelect.firstChild) subSelect.removeChild(subSelect.firstChild);
    // Clear any previous selection
    if ($sub && $.fn.select2) {
        $sub.val(null).trigger('change.select2');
    } else {
        subSelect.value = '';
    }

    let list = @json($subcategories ?? []);
    const selectedSet = new Set((categoryIds || []).map(String));
    if (!list || list.length === 0) {
        // Fallback to AJAX fetch when server didn't pass $subcategories
        list = await fetchSubcategoriesByCategories(Array.from(selectedSet));
    }
    const options = (list || []).filter(sc => selectedSet.size === 0 ? false : selectedSet.has(String(sc.category_id)) || (sc.category_ids && sc.category_ids.some && sc.category_ids.some(id => selectedSet.has(String(id)))));
    if (selectedSet.size === 0 || options.length === 0) {
        // Keep dropdown empty with no extra rows; Select2 will show its own placeholder
        if ($sub && $.fn.select2) {
            $sub.trigger('change.select2');
        }
    } else {
        options.forEach(sc => {
            const opt = document.createElement('option');
            opt.value = sc.id;
            opt.textContent = sc.name;
            subSelect.appendChild(opt);
        });
        if ($sub && $.fn.select2) {
            $sub.trigger('change.select2');
        }
    }

    if ($sub && $.fn.select2) {
        $sub.trigger('change.select2');
    }
    // Return the currently available subcategory options for callers that need to preselect
    return Array.from(subSelect.options).map(o => ({ id: String(o.value), name: o.textContent }));
}

// Initialize Quill editor
function initializeQuillEditor() {
    const quillContainer = document.getElementById('quill-editor');
    const hiddenTextarea = document.getElementById('description');
    const charCount = document.getElementById('description_count');
    
    if (!quillContainer) return;
    
    // Quill editor configuration
    const toolbarOptions = [
        [{ 'header': [1, 2, 3, false] }],
        ['bold', 'italic', 'underline'],
        ['link'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        ['clean']
    ];
    
    // Initialize Quill
    quillEditor = new Quill(quillContainer, {
        theme: 'snow',
        modules: {
            toolbar: toolbarOptions
        },
        placeholder: '{{ __('messages.enter_long_description') }}'
    });
    
    // Set initial content
    if (hiddenTextarea && hiddenTextarea.value) {
        quillEditor.root.innerHTML = hiddenTextarea.value;
    }
    
    // Function to get text content length (without HTML tags)
    function getTextLength() {
        const text = quillEditor.getText().trim();
        return text.length;
    }
    
    // Function to update character count
    function updateCharCount() {
        if (charCount) {
            const length = getTextLength();
            charCount.textContent = length;
            
            // Change color when approaching limit
            if (length >= 225) {
                charCount.style.color = '#dc3545'; // Red when close to limit
            } else if (length >= 200) {
                charCount.style.color = '#ffc107'; // Yellow when approaching limit
            } else {
                charCount.style.color = '#6c757d'; // Default gray
            }
        }
    }
    
    // Set initial character count
    updateCharCount();
    
    // Sync content with hidden textarea and update count on change
    quillEditor.on('text-change', function() {
        const textLength = getTextLength();
        
        // Check if text exceeds limit
        if (textLength > 250) {
            // Get the text content and truncate it
            const text = quillEditor.getText().trim();
            const truncatedText = text.substring(0, 250);
            
            // Set the truncated text back to the editor
            quillEditor.setText(truncatedText);
            
            // Update character count
            updateCharCount();
        } else {
            // Update character count
            updateCharCount();
        }
        
        // Sync with hidden textarea
        if (hiddenTextarea) {
            hiddenTextarea.value = quillEditor.root.innerHTML;
        }
    });
}

// Initialize select2 on a given select element safely
function initSelect2Element(el, options = { width: '100%' }) {
    if (!el || !(window.$ && $.fn.select2)) return;
    const $el = $(el);
    const $containers = $el.siblings('.select2');
    if ($containers.length > 1) {
        $containers.not(':first').remove();
    }
    if (!$el.hasClass('select2-hidden-accessible')) {
        $el.select2(options);
    } else {
        $el.trigger('change.select2');
    }
}

// Keep hidden inputs in sync so any submit handler or AJAX picks them up
function syncHiddenVariationInputs() {
    const hasVarCheckbox = document.getElementById('has_variation');
    const hasVar = !!(hasVarCheckbox && hasVarCheckbox.checked);
    const form = document.getElementById('product-form');
    if (!form) return;
    let variationsInput = document.getElementById('variations-input');
    let combinationsInput = document.getElementById('combinations-input');
    let combinationsJsonAlt = document.getElementById('combinations-json');
    if (!variationsInput) {
        variationsInput = document.createElement('input');
        variationsInput.type = 'hidden';
        variationsInput.name = 'variations';
        variationsInput.id = 'variations-input';
        form.appendChild(variationsInput);
    }
    if (!combinationsInput) {
        combinationsInput = document.createElement('input');
        combinationsInput.type = 'hidden';
        combinationsInput.name = 'combinations';
        combinationsInput.id = 'combinations-input';
        form.appendChild(combinationsInput);
    }
    if (!combinationsJsonAlt) {
        combinationsJsonAlt = document.createElement('input');
        combinationsJsonAlt.type = 'hidden';
        combinationsJsonAlt.name = 'combinations_json';
        combinationsJsonAlt.id = 'combinations-json';
        form.appendChild(combinationsJsonAlt);
    }
    if (hasVar) {
        const validCombinations = (combinations || []).map(c => ({
            variation: c.variation,
            variation_key: c.variation_key,
            price: c.price,
            stock: c.stock,
            sku: c.sku,
            code: c.code,
        }));
        const combJson = JSON.stringify(validCombinations || []);
        combinationsInput.value = combJson;
        combinationsJsonAlt.value = combJson;
        variationsInput.value = JSON.stringify(variations || []);
    } else {
        combinationsInput.value = '[]';
        combinationsJsonAlt.value = '[]';
        variationsInput.value = '[]';
    }
}

// ------- Edit Mode Wiring (listen to offcanvas edit events) -------
const ROUTE_EDIT = `{{ route('backend.products.edit', ':id') }}`;
const ROUTE_UPDATE = `{{ route('backend.products.update', ':id') }}`;
const ROUTE_STORE = `{{ route('backend.products.store') }}`;
const DEFAULT_IMAGE = `{{ $defaultImage ?? asset('images/default.png') }}`;
const TITLE_CREATE = `{{ $createTitle ?? __('messages.new').' '.__('product.singular_title') }}`;
const TITLE_EDIT = `{{ $editTitle ?? __('messages.edit').' '.__('product.singular_title') }}`;
const BTN_CREATE = 'Save';
const BTN_UPDATE = 'Save';

function setFormActionToUpdate(productId) {
    const form = document.getElementById('product-form');
    if (!form) return;
    form.action = ROUTE_UPDATE.replace(':id', productId);
    // ensure _method=PUT exists
    let method = form.querySelector('input[name="_method"]');
    if (!method) {
        method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        form.appendChild(method);
    }
    method.value = 'PUT';
}

function setFormActionToCreate() {
    const form = document.getElementById('product-form');
    if (!form) return;
    form.action = ROUTE_STORE;
    const method = form.querySelector('input[name="_method"]');
    if (method) method.remove();
    // Reset image to default
    resetPreviewImage();
}

function resetFormForCreate() {
    setFormActionToCreate();
    updateHeader(false);
    // Clear basic fields
    const fields = ['name','short_description','description','price','stock','sku','code','discount_value','date_range'];
    fields.forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    // Reset selects
    fillSelectByValues(document.getElementById('brand_id'), '');
    fillSelectByValues(document.getElementById('unit_id'), '');
    fillSelectByValues(document.getElementById('category_ids'), []);
    fillSelectByValues(document.getElementById('tags'), []);
    // Variation off
    const hv = document.getElementById('has_variation');
    if (hv) hv.checked = false;
    toggleVariationSection(false);
    // Set status to 'on' by default for new products
    const statusCheckbox = document.getElementById('status');
    if (statusCheckbox) {
        statusCheckbox.checked = true;
    }
    // Reset image fields
    const fileInput = document.getElementById('feature_image');
    if (fileInput) fileInput.value = '';
    const existingImageInput = document.getElementById('existing_image');
    if (existingImageInput) existingImageInput.value = '';
    const remFlag = document.getElementById('remove_feature_image');
    if (remFlag) remFlag.value = '0';
    const imageError = document.getElementById('image-error');
    if (imageError) imageError.style.display = 'none';
    resetPreviewImage();
}

function updateHeader(isEdit) {
    const titleEl = document.getElementById('form-offcanvasLabel');
    const submitBtn = document.getElementById('submit-btn');
    if (titleEl) titleEl.textContent = isEdit ? TITLE_EDIT : TITLE_CREATE;
    if (submitBtn) {
        submitBtn.classList.add('d-inline-flex','align-items-center','gap-2');
        submitBtn.innerHTML = `<i class="fa-solid fa-floppy-disk"></i> ${isEdit ? BTN_UPDATE : BTN_CREATE}`;
    }
}

function fillSelectByValues(selectEl, values) {
    if (!selectEl) return;
    // Normalize incoming values which can be primitives or objects from API
    const normalizeValue = (val) => {
        if (val === undefined || val === null) return '';
        if (typeof val === 'object') {
            // For tags, option value is the tag name. For others prefer id/value.
            if (selectEl.id === 'tags') {
                return String(val.name ?? val.value ?? val.id ?? '');
            }
            return String(val.id ?? val.value ?? val.name ?? '');
        }
        return String(val);
    };
    const setVals = Array.isArray(values)
        ? values.map(normalizeValue).filter(v => v !== '')
        : [normalizeValue(values)].filter(v => v !== '');

    // Ensure options exist for all values (especially for Select2 with tags=true)
    const currentValues = new Set(Array.from(selectEl.options).map(o => String(o.value)));
    setVals.forEach(v => {
        if (!currentValues.has(v)) {
            const opt = new Option(v, v, false, false);
            selectEl.appendChild(opt);
        }
    });

    if (window.$ && $.fn.select2) {
        $(selectEl).val(setVals).trigger('change');
    } else {
        Array.from(selectEl.options).forEach(opt => {
            opt.selected = setVals.includes(String(opt.value));
        });
    }
}

async function loadProductAndPopulate(productId) {
    try {
        const url = ROUTE_EDIT.replace(':id', productId);
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) throw new Error('Failed to fetch');
        const payload = await res.json();
        if (!payload.status) return;
        const p = payload.data || {};
        __productFormIsEditing = true;

        // Switch form to update and header
        setFormActionToUpdate(productId);
        updateHeader(true);

        // Basic fields
        document.getElementById('name').value = p.name || '';
        document.getElementById('short_description').value = p.short_description || '';
        
        // Handle Quill editor content
        const hiddenTextarea = document.getElementById('description');
        if (hiddenTextarea) {
            hiddenTextarea.value = p.description || '';
        }
        if (quillEditor) {
            quillEditor.root.innerHTML = p.description || '';
        }

        // Brand / Unit
        fillSelectByValues(document.getElementById('brand_id'), p.brand_id);
        fillSelectByValues(document.getElementById('unit_id'), p.unit_id);

        // Categories (works for single or multiple)
        fillSelectByValues(document.getElementById('category_ids'), p.category_ids || []);

        // Subcategories – preselect on edit
        (async () => {
            const subSel = document.getElementById('subcategory_ids');
            if (!subSel) return;
            // Build candidate subcategory ids from payload
            let savedSubIds = Array.isArray(p.subcategory_ids) ? p.subcategory_ids.map(String) : [];
            if (savedSubIds.length === 0 && Array.isArray(p.categories)) {
                // Fallback: derive children from categories array if it contains parent_id
                savedSubIds = p.categories
                    .filter(c => c && (c.parent_id || (c.pivot && c.pivot.parent_id)))
                    .map(c => String(c.id));
            }
            // Ensure options are populated for current parent categories
            const parentIds = Array.isArray(p.category_ids) ? p.category_ids.map(String) : [];
            const available = await populateSubcategories(parentIds);
            if (savedSubIds.length) {
                // Ensure all saved sub ids exist as options
                const existing = new Set(Array.from(subSel.options).map(o => String(o.value)));
                savedSubIds.forEach(id => {
                    if (!existing.has(String(id))) {
                        const opt = new Option(String(id), String(id), false, false);
                        subSel.appendChild(opt);
                    }
                });
                if (window.$ && $.fn.select2) {
                    $(subSel).val(savedSubIds).trigger('change');
                } else {
                    Array.from(subSel.options).forEach(opt => { opt.selected = savedSubIds.includes(String(opt.value)); });
                }
            }
        })();

        // Tags (names array)
        fillSelectByValues(document.getElementById('tags'), p.tags || []);

        // Image (if present)
        const img = document.getElementById('image-preview');
        const rem = document.getElementById('remove-image-btn');
        const existingImageInput = document.getElementById('existing_image');
        if (p.feature_image && img) {
            img.src = p.feature_image;
            if (rem) rem.style.display = 'block';
            if (existingImageInput) existingImageInput.value = p.feature_image;
        } else {
            // No image -> show default placeholder without breaking layout
            resetPreviewImage();
            if (existingImageInput) existingImageInput.value = '';
        }

        // Discount
        document.getElementById('discount_value').value = p.discount_value ?? 0;
        document.getElementById('discount_type').value = p.discount_type || 'percent';
        document.getElementById('date_range').value = p.date_range || '';
        
        // Set discount enabled state based on actual discount value
        const discountEnabled = document.getElementById('discount_enabled');
        if (discountEnabled) {
            discountEnabled.checked = !!(p.discount_value && parseFloat(p.discount_value) > 0);
            // Update UI immediately after setting the checkbox
            updateDiscountUI();
        }

        // Set status field
        // Find the status checkbox in the form-offcanvas specifically
        let statusCheckbox = document.querySelector('#form-offcanvas #status');
        if (!statusCheckbox) {
            // Fallback: try to find any status checkbox in the form
            statusCheckbox = document.querySelector('form #status');
        }
        if (!statusCheckbox) {
            // Last resort: get by ID
            statusCheckbox = document.getElementById('status');
        }
        
        if (statusCheckbox) {
            // Use same logic as index table: simple truthy check
            const statusValue = !!p.status;
            statusCheckbox.checked = statusValue;
            
            // Force UI update - try multiple approaches for Bootstrap form-switch
            statusCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
            statusCheckbox.dispatchEvent(new Event('input', { bubbles: true }));
            statusCheckbox.dispatchEvent(new Event('click', { bubbles: true }));
            
            // Force Bootstrap form-switch update
            if (statusCheckbox.closest('.form-switch')) {
                const formSwitch = statusCheckbox.closest('.form-switch');
                if (statusValue) {
                    formSwitch.classList.add('active');
                } else {
                    formSwitch.classList.remove('active');
                }
            }
            
            // Check if something is overriding our setting
            setTimeout(() => {
                // If something overrode our setting, force it back
                if (statusCheckbox.checked !== statusValue) {
                    statusCheckbox.checked = statusValue;
                    statusCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
                    
                    // Try multiple times with different approaches
                    setTimeout(() => {
                        statusCheckbox.checked = statusValue;
                        statusCheckbox.setAttribute('checked', statusValue ? 'checked' : '');
                        statusCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
                        statusCheckbox.dispatchEvent(new Event('input', { bubbles: true }));
                        statusCheckbox.dispatchEvent(new Event('click', { bubbles: true }));
                        
                        // Force Bootstrap form-switch visual update
                        const formSwitch = statusCheckbox.closest('.form-switch');
                        if (formSwitch) {
                            if (statusValue) {
                                formSwitch.classList.add('active');
                            } else {
                                formSwitch.classList.remove('active');
                            }
                        }
                    }, 100);
                }
            }, 500);
        }

        // Set is_featured field
        const isFeaturedCheckbox = document.getElementById('is_featured');
        if (isFeaturedCheckbox) {
            // Ensure we're working with actual boolean/truthy values
            const featuredValue = p.is_featured === 1 || p.is_featured === '1' || p.is_featured === true;
            isFeaturedCheckbox.checked = featuredValue;
        }

        // Toggle variation section
        const hasVar = !!p.has_variation;
        document.getElementById('has_variation').checked = hasVar;
        toggleVariationSection(hasVar);

        if (hasVar) {
            // Load variations if present
            if (p.variations && Array.isArray(p.variations) && p.variations.length > 0) {
                // Clear existing variations
                variations = [];
                variationCounter = 0;
                const container = document.getElementById('variations-container');
                if (container) container.innerHTML = '';
                
                // Add and populate each variation
                p.variations.forEach(variation => {
                    addVariation();
                    const variationId = variationCounter - 1;
                    const variationRow = document.getElementById(`variation-${variationId}`);
                    
                    if (variationRow) {
                        const typeSelect = variationRow.querySelector('.variation-type');
                        const valueSelect = variationRow.querySelector('.variation-value');
                        
                        // Get variation data
                        const variationTypeId = variation.variation || variation.variation_type_id;
                        const variationValueIds = variation.variationValue || variation.variation_value_ids || [];
                        
                        // Set values in the JavaScript object
                        const lastVariation = variations[variations.length - 1];
                        lastVariation.type = variationTypeId;
                        lastVariation.values = Array.isArray(variationValueIds) ? variationValueIds : [];
                        
                        // Set the variation type in Select2
                        if (typeSelect && variationTypeId) {
                            $(typeSelect).val(variationTypeId).trigger('change');
                            
                            // Wait for Select2 to update, then populate values
                            setTimeout(() => {
                                onVariationTypeChange(variationId);
                                
                                // Then set the selected values
                                setTimeout(() => {
                                    if (valueSelect && lastVariation.values.length > 0) {
                                        $(valueSelect).val(lastVariation.values).trigger('change');
                                    }
                                }, 100);
                            }, 100);
                        }
                    }
                });
            }
            
            // Load combinations prepared by controller
            combinations = p.combinations || [];
            updateCombinationsTable();
        } else {
            // Simple fields
            document.getElementById('price').value = p.price ?? '';
            document.getElementById('stock').value = p.stock ?? '';
            document.getElementById('sku').value = p.sku ?? '';
            document.getElementById('code').value = p.code ?? '';
        }
        
        // Update character counts after populating form data
        setupCharacterCounts();
        
        // Status is already set correctly above
        
    } catch (err) {
    }
}

// Listen for edit event from global handler
document.addEventListener('crud_change_id', (e) => {
    const id = e?.detail?.form_id;
    if (id && String(id) !== '0') {
        loadProductAndPopulate(id);
    } else {
        // Switch to create mode and lightly reset
        setFormActionToCreate();
        updateHeader(false);
    }
});

function toggleVariationSection(hasVariation) {
    const variationSection = document.getElementById('variation-section');
    const noVariationSection = document.getElementById('no-variation-section');
    const container = document.getElementById('variations-container');

    if (hasVariation) {
        // Show variation section and ensure a clean slate to avoid duplicate rows
        variationSection.classList.remove('d-none');
        variationSection.style.display = 'block';
        noVariationSection.classList.add('d-none');
        noVariationSection.style.display = 'none';

        // Clear any previously rendered rows in the DOM to prevent duplicates
        if (container) container.innerHTML = '';
        // When variations are enabled, simple price/stock are not required
        const priceInput = document.getElementById('price');
        const stockInput = document.getElementById('stock');
        if (priceInput) priceInput.required = false;
        if (stockInput) stockInput.required = false;

        // If there are no variations in memory, start with one fresh row
        if (variations.length === 0) {
            variationCounter = 0;
            addVariation();
        } else {
            // Re-render existing variations from state
            const existing = [...variations];
            variations = [];
            variationCounter = 0;
            existing.forEach(() => addVariation());
        }

        syncHiddenVariationInputs();
    } else {
        // Hide variation section and reset state + DOM
        variationSection.classList.add('d-none');
        variationSection.style.display = 'none';
        noVariationSection.classList.remove('d-none');
        noVariationSection.style.display = 'flex';

        if (container) container.innerHTML = '';
        // When variations are disabled, simple price/stock must be provided
        const priceInput2 = document.getElementById('price');
        const stockInput2 = document.getElementById('stock');
        if (priceInput2) priceInput2.required = true;
        if (stockInput2) stockInput2.required = true;
        variations = [];
        variationCounter = 0;
        combinations = [];
        updateCombinationsTable();
        syncHiddenVariationInputs();
    }
}

function addVariation() {
    const container = document.getElementById('variations-container');
    const variationDiv = document.createElement('div');
    variationDiv.className = 'col-md-12 mb-3';
    variationDiv.id = `variation-${variationCounter}`;
    
    variationDiv.innerHTML = `
        <div class="d-flex gap-3 align-items-center">
            <div class="d-flex flex-grow-1 gap-3">
                <div class="form-group w-50">
                    <label>{{ __('product.variation_type') }}</label>
                    <select class="form-control select2 variation-type" style="width:100%" data-placeholder="{{ __('product.select_type') }}" onchange="onVariationTypeChange(${variationCounter})">
                        ${getVariationOptions()}
                    </select>
                </div>
                <div class="form-group w-50">
                    <label>{{ __('product.variation_value') }}</label>
                    <select class="form-control select2 variation-value" style="width:100%" multiple data-placeholder="{{ __('product.select_value') }}" onchange="generateCombinations()"></select>
                </div>
            </div>
            ${variations.length > 0 ? `
                <button class="btn btn-danger btn-icon" onclick="removeVariation(${variationCounter})">
                    <i class="fa-solid fa-trash"></i>
                </button>
            ` : ''}
        </div>
    `;
    
    container.appendChild(variationDiv);
    variations.push({
        id: variationCounter,
        type: '',
        values: []
    });
    
    // Initialize select2 for the newly added selects
    const typeSelectEl = variationDiv.querySelector('.variation-type');
    const valueSelectEl = variationDiv.querySelector('.variation-value');
    
    // Ensure no value is selected initially to show placeholder
    if (typeSelectEl) {
        typeSelectEl.value = '';
    }
    
    initSelect2Element(typeSelectEl, { width: '100%', placeholder: '{{ __('product.select_type') }}', allowClear: true });
    initSelect2Element(valueSelectEl, { width: '100%', placeholder: '{{ __('product.select_value') }}' });
    
    // Add Select2 change event listener for variation type dropdown
    $(typeSelectEl).on('select2:select select2:clear', function() {
        onVariationTypeChange(variationCounter);
    });
    
    // Add event listener for value selection changes
    valueSelectEl.addEventListener('change', function() {
        // Update the variation object with selected values
        const selectedValues = Array.from(this.selectedOptions).map(opt => opt.value);
        const currentVariation = variations.find(v => v.id === variationCounter);
        if (currentVariation) {
            currentVariation.values = selectedValues;
        }
        
        generateCombinations();
        syncHiddenVariationInputs();
        validateVariationValues();
    });
    
    // Add Select2 specific event listener
    $(valueSelectEl).on('select2:select select2:unselect', function() {
        // Update the variation object with selected values
        const selectedValues = Array.from(this.selectedOptions).map(opt => opt.value);
        const currentVariation = variations.find(v => v.id === variationCounter);
        if (currentVariation) {
            currentVariation.values = selectedValues;
        }
        
        generateCombinations();
        syncHiddenVariationInputs();
        
        // Add a small delay to ensure Select2 DOM is fully updated
        setTimeout(() => {
            validateVariationValues();
        }, 100);
    });

    variationCounter++;
    updateAddVariationButton();
    syncHiddenVariationInputs();
}

function removeVariation(id) {
    const element = document.getElementById(`variation-${id}`);
    if (element) {
        element.remove();
        variations = variations.filter(v => v.id !== id);
        generateCombinations();
        updateAddVariationButton();
        
        // Clear any validation errors related to variations
        clearVariationValidationErrors();
        
        // Update hidden inputs to reflect the removal
        syncHiddenVariationInputs();
        
        // If no variations left, consider switching to no-variation mode
        if (variations.length === 0) {
            // Optionally show a message or automatically switch to no-variation mode
        }
    }
}

// Function to clear variation-related validation errors
function clearVariationValidationErrors() {
    // Clear any existing validation messages
    const errorElements = document.querySelectorAll('.text-danger');
    errorElements.forEach(el => {
        if (el.textContent.includes('variation') || el.textContent.includes('combination')) {
            el.remove();
        }
    });
    
    // Clear any jQuery validation errors
    if (window.$ && $.fn.validate) {
        const $form = $('#product-form');
        if ($form.length && $form.data('validator')) {
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.error').removeClass('error');
        }
    }
}

// Function to validate variation values
function validateVariationValues() {
    const rows = Array.from(document.querySelectorAll('#variations-container > div[id^="variation-"]'));
    let hasErrors = false;
    
    // Clear previous validation errors
    clearVariationValidationErrors();
    
    rows.forEach((row, index) => {
        const typeSelect = row.querySelector('.variation-type');
        const valueSelect = row.querySelector('.variation-value');
        
        if (!typeSelect || !valueSelect) return;
        
        const typeValue = typeSelect.value;
        
        // Check if values are selected using multiple methods
        let hasSelectedValues = false;
        
        // Method 1: Check selected options in the select element
        const selectedOptions = Array.from(valueSelect.selectedOptions);
        if (selectedOptions.length > 0) {
            hasSelectedValues = true;
        }
        
        // Method 2: Check variations array
        const variation = variations.find(v => v.id === parseInt(row.id.replace('variation-', '')));
        if (variation && variation.values && variation.values.length > 0) {
            hasSelectedValues = true;
        }
        
        // Method 3: Check Select2 DOM elements (for visual selections)
        const select2Container = valueSelect.parentNode.querySelector('.select2-container');
        if (select2Container) {
            const select2Choices = select2Container.querySelectorAll('.select2-selection__choice');
            if (select2Choices.length > 0) {
                hasSelectedValues = true;
            }
        }
        
        // Method 4: Check if Select2 has any selected values by looking at the selection text
        if (select2Container) {
            const selectionText = select2Container.querySelector('.select2-selection__rendered');
            if (selectionText && selectionText.textContent.trim() !== '' && !selectionText.textContent.includes('Select')) {
                hasSelectedValues = true;
            }
        }
        
        // If a variation type is selected but no values are chosen
        if (typeValue && !hasSelectedValues) {
            hasErrors = true;
            
            // Add error styling to the value select
            valueSelect.classList.add('is-invalid');
            
            // Add error message
            let errorMsg = valueSelect.parentNode.querySelector('.variation-error-msg');
            if (!errorMsg) {
                errorMsg = document.createElement('div');
                errorMsg.className = 'text-danger variation-error-msg mt-1';
                valueSelect.parentNode.appendChild(errorMsg);
            }
            errorMsg.textContent = 'Please select at least one value for this variation type.';
        } else {
            // Remove error styling if values are selected
            valueSelect.classList.remove('is-invalid');
            const errorMsg = valueSelect.parentNode.querySelector('.variation-error-msg');
            if (errorMsg) {
                errorMsg.remove();
            }
        }
    });
    
    return !hasErrors; // Return true if no errors, false if there are errors
}

function updateAddVariationButton() {
    const addBtn = document.getElementById('add-variation-btn');
    if (variations.length < variationsData.length) {
        addBtn.style.display = 'block';
    } else {
        addBtn.style.display = 'none';
    }
}

function getVariationOptions() {
    // Add empty placeholder option first, then variation options
    return '<option value=""></option>' + variationsData.map(v => `<option value="${v.id}">${v.name}</option>`).join('');
}

function onVariationTypeChange(variationId) {
    const variation = variations.find(v => v.id === variationId);
    if (variation) {
        const typeSelect = document.querySelector(`#variation-${variationId} .variation-type`);
        const valueSelect = document.querySelector(`#variation-${variationId} .variation-value`);
        
        variation.type = typeSelect.value;
        variation.values = [];
        
        // Clear and populate values
        valueSelect.innerHTML = '';
        if (variation.type) {
            const variationData = variationsData.find(v => v.id == variation.type);
            if (variationData && variationData.values) {
                variationData.values.forEach(val => {
                    const option = document.createElement('option');
                    option.value = val.id;
                    option.textContent = val.name;
                    valueSelect.appendChild(option);
                });
            }
        }
        
        // Re-initialize or refresh select2 on value select after options update
        initSelect2Element(valueSelect, { width: '100%', placeholder: '{{ __('product.select_value') }}' });
        
        // Add event listener for value selection changes
        valueSelect.addEventListener('change', function() {
            // Update the variation object with selected values
            const selectedValues = Array.from(this.selectedOptions).map(opt => opt.value);
            variation.values = selectedValues;
            
            generateCombinations();
            syncHiddenVariationInputs();
            validateVariationValues();
        });
        
        // Add Select2 specific event listener
        $(valueSelect).on('select2:select select2:unselect', function() {
            // Update the variation object with selected values
            const selectedValues = Array.from(this.selectedOptions).map(opt => opt.value);
            variation.values = selectedValues;
            
            generateCombinations();
            syncHiddenVariationInputs();
            
            // Add a small delay to ensure Select2 DOM is fully updated
            setTimeout(() => {
                validateVariationValues();
            }, 100);
        });

        generateCombinations();
        syncHiddenVariationInputs();
        
        // Clear any previous validation errors and validate
        clearVariationValidationErrors();
        validateVariationValues();
    }
}

function generateCombinations() {
    // Build groups with selected values including names and type mapping
    const rows = Array.from(document.querySelectorAll('#variations-container > div[id^="variation-"]'));
    const groups = [];
    rows.forEach(row => {
        const typeSelect = row.querySelector('.variation-type');
        const valueSelect = row.querySelector('.variation-value');
        if (!typeSelect || !valueSelect) return;
        const typeId = String(typeSelect.value || '');
        if (!typeId) return; // skip rows without chosen type
        const typeName = typeSelect.options[typeSelect.selectedIndex]?.text?.trim() || '';
        const selectedOptions = Array.from(valueSelect.selectedOptions || []);
        if (selectedOptions.length === 0) return;
        const values = selectedOptions.map(opt => ({
            typeId,
            typeName,
            valueId: String(opt.value || ''),
            valueName: (opt.text || '').trim()
        }));
        groups.push(values);
    });

    if (groups.length === 0) {
        combinations = [];
        updateCombinationsTable();
        return;
    }

    // Cartesian product across groups
    const out = [];
    const dfs = (idx, acc) => {
        if (idx === groups.length) {
            const variation_key = acc.map(x => `${x.typeId}:${x.valueId}`).join('/');
            const nameParts = acc.map(x => x.valueName);
            const display = nameParts.join('-');
            out.push({
                variation: display,
                variation_key,
                price: '',
                stock: '',
                sku: display,
                code: display.toLowerCase()
            });
            return;
        }
        for (const v of groups[idx]) dfs(idx + 1, acc.concat(v));
    };
    dfs(0, []);
    combinations = out;
    updateCombinationsTable();
    syncHiddenVariationInputs();
}

function updateCombinationsTable() {
    const tbody = document.getElementById('combinations-tbody');
    const table = document.getElementById('combinations-table');
    
    if (combinations.length === 0) {
        table.style.display = 'none';
        return;
    }
    
    table.style.display = 'block';
    tbody.innerHTML = '';
    
    combinations.forEach((comb, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <input class="form-control" value="${comb.variation}" readonly disabled required />
            </td>
            <td>
                <input class="form-control" type="number" min="0.01" step="0.01" 
                       value="${comb.price}" required onchange="updateCombination(${index}, 'price', this.value)" />
            </td>
            <td>
                <input class="form-control" type="number" min="1" step="1" 
                       value="${comb.stock}" required onchange="updateCombination(${index}, 'stock', this.value)" />
            </td>
            <td>
                <input class="form-control" value="${comb.sku}" required onchange="updateCombination(${index}, 'sku', this.value)" />
            </td>
            <td>
                <input class="form-control" value="${comb.code}" required onchange="updateCombination(${index}, 'code', this.value)" />
            </td>
        `;
        tbody.appendChild(row);
    });
    syncHiddenVariationInputs();
}

function updateCombination(index, field, value) {
    if (combinations[index]) {
        combinations[index][field] = value;
    }
    syncHiddenVariationInputs();
}

async function reloadCategoriesByBrand(brandId) {
    try {
        const select = document.getElementById('category_ids');
        if (!select) return;
        // fetch categories filtered by brand from backend
        const url = `{{ url('app/products-categories/index_list') }}?brand_id=${encodeURIComponent(brandId || '')}`;
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const text = await res.text();
        let list = [];
        try { list = text ? JSON.parse(text) : []; } catch(e) { list = []; }
        // reset select options
        if (window.$ && $.fn.select2 && $(select).hasClass('select2-hidden-accessible')) {
            $(select).val(null).trigger('change');
            $(select).select2('destroy');
        }
        while (select.firstChild) select.removeChild(select.firstChild);
        list.forEach(item => {
            const opt = document.createElement('option');
            opt.value = String(item.id ?? item.value ?? '');
            opt.textContent = item.name ?? item.label ?? '';
            select.appendChild(opt);
        });
        // re-init select2
        if (window.$ && $.fn.select2) {
            $(select).select2({ width: '100%' });
        }
        // also clear subcategory list
        const subSel = document.getElementById('subcategory_ids');
        if (subSel) { while (subSel.firstChild) subSel.removeChild(subSel.firstChild); if (window.$ && $.fn.select2) $(subSel).val(null).trigger('change'); }
    } catch (e) { }
}

function handleFileUpload(event) {
    const file = event.target.files[0];
    const maxSizeInMB = 2;
    const maxSizeInBytes = maxSizeInMB * 1024 * 1024;
    
    if (file) {
        if (file.size > maxSizeInBytes) {
            showValidationMessage(`File size exceeds ${maxSizeInMB} MB. Please upload a smaller file.`);
            event.target.value = '';
            return;
        }
        
        // Preview image
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('image-preview').src = e.target.result;
            document.getElementById('remove-image-btn').style.display = 'block';
            hideValidationMessage();
            
            // Clear validation error when image is uploaded
            const imageError = document.getElementById('image-error');
            if (imageError) {
                imageError.style.display = 'none';
            }
            const fileInput = document.getElementById('feature_image');
            if (fileInput && window.$ && $.fn.validate) {
                $(fileInput).removeClass('is-invalid');
                const $form = $('#product-form');
                if ($form.length && $form.data('validator')) {
                    $form.validate().element(fileInput);
                }
            }
            
            // Reset remove flag if image is uploaded
            const removeFlag = document.getElementById('remove_feature_image');
            if (removeFlag) {
                removeFlag.value = '0';
            }
        };
        reader.readAsDataURL(file);
    }
}

function removeImage() {
    resetPreviewImage();
    const input = document.getElementById('feature_image');
    if (input) input.value = '';
    const rem = document.getElementById('remove-image-btn');
    if (rem) rem.style.display = 'none';
    const flag = document.getElementById('remove_feature_image');
    if (flag) flag.value = '1';
    hideValidationMessage();
    
    // Trigger validation check when image is removed
    if (window.$ && $.fn.validate) {
        const $form = $('#product-form');
        const fileInput = document.getElementById('feature_image');
        if ($form.length && $form.data('validator') && fileInput) {
            $form.validate().element(fileInput);
        }
    }
}

function resetPreviewImage() {
    const img = document.getElementById('image-preview');
    if (img) img.src = DEFAULT_IMAGE;
}

function showValidationMessage(message) {
    const validationDiv = document.getElementById('validation-message');
    validationDiv.textContent = message;
    validationDiv.style.display = 'block';
}

function hideValidationMessage() {
    const validationDiv = document.getElementById('validation-message');
    validationDiv.style.display = 'none';
}

function updateFormValidation() {
    // Don't disable submit button - let backend handle validation
    // This function is kept for future use if needed
}

// Function to show validation error above save button
function showValidationError(message) {
    // Remove any existing error messages
    const existingError = document.querySelector('.form-validation-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Create error message element
    const errorDiv = document.createElement('div');
    errorDiv.className = 'form-validation-error text-danger';
    errorDiv.textContent = message;
    
    // Find the save button and insert error above it
    const saveButton = document.querySelector('button[type="submit"]');
    if (saveButton && saveButton.parentNode) {
        saveButton.parentNode.insertBefore(errorDiv, saveButton);
        
        // Scroll to error message
        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Remove error after 5 seconds
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, 5000);
    }
}

// Form validation setup - currently disabled to allow normal form submission
function setupFormValidation() {
    // Validation is handled by backend
    
    // Add click event listener to save button
    const saveButton = document.querySelector('button[type="submit"]');
    if (saveButton) {
        saveButton.addEventListener('click', function(e) {
            
            // Check if there are any error messages displaying
            const errorMessages = document.querySelectorAll('.variation-error-msg');
            const invalidFields = document.querySelectorAll('.is-invalid');
            
            // Also check for any text that contains "Please select"
            const allTextElements = document.querySelectorAll('*');
            let hasErrorText = false;
            allTextElements.forEach(function(element) {
                if (element.textContent && element.textContent.includes('Please select at least one value')) {
                    hasErrorText = true;
                }
            });
            
            
            if (errorMessages.length > 0 || invalidFields.length > 0 || hasErrorText) {
                e.preventDefault();
                e.stopPropagation();
                showValidationError('Please fix all errors before saving.');
                return false;
            }
            
        });
    }
    
    // Add global form submission prevention
    document.addEventListener('submit', function(e) {
        if (e.target.id === 'product-form') {
            
            // Check for validation errors
            const errorMessages = document.querySelectorAll('.variation-error-msg');
            const invalidFields = document.querySelectorAll('.is-invalid');
            
            // Also check for any text that contains "Please select"
            const allTextElements = document.querySelectorAll('*');
            let hasErrorText = false;
            allTextElements.forEach(function(element) {
                if (element.textContent && element.textContent.includes('Please select at least one value')) {
                    hasErrorText = true;
                }
            });
            
            
            if (errorMessages.length > 0 || invalidFields.length > 0 || hasErrorText) {
                e.preventDefault();
                e.stopPropagation();
                showValidationError('Please fix all errors before saving.');
                return false;
            }
        }
    });
}

// Form submission - Simple validation and submit
document.getElementById('product-form').addEventListener('submit', function(e) {
    
    // Trigger jQuery validation first
    if (window.$ && $.fn.validate) {
        const $form = $('#product-form');
        if ($form.length && $form.data('validator')) {
            // Validate all fields including image
            const fileInput = document.getElementById('feature_image');
            if (fileInput) {
                $form.validate().element(fileInput);
            }
            
            // Check if form is valid
            if (!$form.valid()) {
                e.preventDefault();
                e.stopPropagation();
                // Scroll to first error
                const firstError = $form.find('.is-invalid').first();
                if (firstError.length) {
                    firstError[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
        }
    }
    
    // Check if there are any error messages displaying
    const errorMessages = document.querySelectorAll('.variation-error-msg');
    const invalidFields = document.querySelectorAll('.is-invalid');
    
    // Also check for any text that contains "Please select"
    const allTextElements = document.querySelectorAll('*');
    let hasErrorText = false;
    allTextElements.forEach(function(element) {
        if (element.textContent && element.textContent.includes('Please select at least one value')) {
            hasErrorText = true;
        }
    });
    
    
    if (errorMessages.length > 0 || invalidFields.length > 0 || hasErrorText) {
        e.preventDefault();
        e.stopPropagation();
        showValidationError('Please fix all errors before saving.');
        return false;
    }
    
    
    // Handle checkbox values before submission - ALWAYS ensure 0 or 1 values
    const statusCheckbox = document.getElementById('status');
    const isFeaturedCheckbox = document.getElementById('is_featured');
    
    
    // Remove any existing hidden inputs first
    const existingStatusHidden = document.querySelector('input[name="status"][type="hidden"]');
    const existingFeaturedHidden = document.querySelector('input[name="is_featured"][type="hidden"]');
    if (existingStatusHidden) existingStatusHidden.remove();
    if (existingFeaturedHidden) existingFeaturedHidden.remove();
    
    // ALWAYS add hidden inputs to ensure 0 or 1 values are sent
    const statusValue = (statusCheckbox && statusCheckbox.checked) ? '1' : '0';
    const featuredValue = (isFeaturedCheckbox && isFeaturedCheckbox.checked) ? '1' : '0';
    
    // Add hidden input for status
    const hiddenStatus = document.createElement('input');
    hiddenStatus.type = 'hidden';
    hiddenStatus.name = 'status';
    hiddenStatus.value = statusValue;
    this.appendChild(hiddenStatus);
    
    // Add hidden input for is_featured
    const hiddenFeatured = document.createElement('input');
    hiddenFeatured.type = 'hidden';
    hiddenFeatured.name = 'is_featured';
    hiddenFeatured.value = featuredValue;
    this.appendChild(hiddenFeatured);
    
    
    // Additional backup: Ensure form data is properly formatted
    const formData = new FormData(this);
    
    // Basic validation
    const name = document.getElementById('name').value.trim();
    const brandId = document.getElementById('brand_id').value;
    const categoryIds = Array.from(document.getElementById('category_ids').selectedOptions).map(opt => opt.value);
    
    if (!name || !brandId || categoryIds.length === 0) {
        e.preventDefault();
        alert('Please fill in all required fields (Name, Brand, and Categories).');
        return;
    }
    
    // Validate image field
    const fileInput = document.getElementById('feature_image');
    const existingImage = document.getElementById('existing_image')?.value;
    const removeFlag = document.getElementById('remove_feature_image')?.value;
    const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
    const hasExistingImage = existingImage && existingImage !== '';
    const isRemoving = removeFlag === '1';
    
    // Image is required if:
    // 1. Creating new product (no existing image)
    // 2. Editing product but removing existing image (isRemoving = true)
    if ((!hasExistingImage || isRemoving) && !hasFile) {
        e.preventDefault();
        e.stopPropagation();
        const imageError = document.getElementById('image-error');
        if (imageError) {
            imageError.textContent = 'Image is required';
            imageError.style.display = 'block';
            imageError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        showValidationError('Image is required');
        return false;
    }
    
    // Validate discount value
    const discountType = document.getElementById('discount_type').value;
    const discountValue = parseFloat(document.getElementById('discount_value').value) || 0;
    const discountEnabled = document.getElementById('discount_enabled').checked;
    
    if (discountEnabled && discountValue <= 0) {
        e.preventDefault();
        alert('Discount is enabled but value must be greater than 0. Please enter a valid discount value.');
        return;
    }
    
    const hasVariation = document.getElementById('has_variation').checked;
    if (!hasVariation) {
        const priceEl = document.getElementById('price');
        const stockEl = document.getElementById('stock');
        const priceVal = parseFloat(priceEl?.value || '');
        const stockVal = parseInt(stockEl?.value || '');
        if (!priceEl || isNaN(priceVal) || priceVal <= 0) {
            e.preventDefault();
            alert('Please enter a valid price (> 0).');
            return;
        }
        if (!stockEl || isNaN(stockVal) || stockVal < 1) {
            e.preventDefault();
            alert('Please enter a valid stock (>= 1).');
            return;
        }
    } else {
        // Validate variation values when variations are enabled
        if (!validateVariationValues()) {
            e.preventDefault();
            alert('Please select values for all variation types before saving.');
            return;
        }
    }
    
    if (discountType === 'fixed' && discountValue > 0) {
        if (hasVariation) {
            // Check if there are any valid variations with values
            const hasValidVariations = variations.some(v => v.type && v.values && v.values.length > 0);
            if (!hasValidVariations || combinations.length === 0) {
                e.preventDefault();
                alert('Please add at least one variation with values and fill in the combinations.');
                return;
            }
            const prices = combinations.map(c => parseFloat(c.price)).filter(p => !isNaN(p) && p > 0);
            if (prices.length === 0) {
                e.preventDefault();
                alert('Please fill in prices for variations.');
                return;
            }
            const minPrice = Math.min(...prices);
            if (discountValue > minPrice) {
                e.preventDefault();
                alert('Discount must not exceed minimum variation price.');
                return;
            }
        } else {
            const price = parseFloat(document.getElementById('price').value);
            if (!price || price <= 0) {
                e.preventDefault();
                alert('Please enter a valid price.');
                return;
            }
            if (discountValue > price) {
                e.preventDefault();
                alert('Discount must not exceed price.');
                return;
            }
        }
    }
    
    // Set hidden inputs (only if has variations)
    const hasVar = hasVariation;
    const form = document.getElementById('product-form');
    let variationsInput = document.getElementById('variations-input');
    let combinationsInput = document.getElementById('combinations-input');
    if (!variationsInput) {
        variationsInput = document.createElement('input');
        variationsInput.type = 'hidden';
        variationsInput.name = 'variations';
        variationsInput.id = 'variations-input';
        form.appendChild(variationsInput);
    }
    if (!combinationsInput) {
        combinationsInput = document.createElement('input');
        combinationsInput.type = 'hidden';
        combinationsInput.name = 'combinations';
        combinationsInput.id = 'combinations-input';
        form.appendChild(combinationsInput);
    }
    // Provide a secondary field for servers that drop large text fields under the same name
    let combinationsJsonAlt = document.getElementById('combinations-json');
    if (!combinationsJsonAlt) {
        combinationsJsonAlt = document.createElement('input');
        combinationsJsonAlt.type = 'hidden';
        combinationsJsonAlt.name = 'combinations_json';
        combinationsJsonAlt.id = 'combinations-json';
        form.appendChild(combinationsJsonAlt);
    }
    if (hasVar) {
        // Ensure variation_key present for each combination
        const validCombinations = (combinations || []).map(c => ({
            variation: c.variation,
            variation_key: c.variation_key,
            price: c.price,
            stock: c.stock,
            sku: c.sku,
            code: c.code,
        }));
        const combJson = JSON.stringify(validCombinations || []);
        variationsInput.value = JSON.stringify(variations || []);
        combinationsInput.value = combJson;
        combinationsJsonAlt.value = combJson;
    } else {
        variationsInput.value = '[]';
        combinationsInput.value = '[]';
        const cj = document.getElementById('combinations-json');
        if (cj) cj.value = '[]';
    }
    
    // Final safety check: Ensure status and is_featured are never null
    const finalStatusValue = (statusCheckbox && statusCheckbox.checked) ? '1' : '0';
    const finalFeaturedValue = (isFeaturedCheckbox && isFeaturedCheckbox.checked) ? '1' : '0';
    
    // Update hidden inputs with final values
    const finalStatusHidden = document.querySelector('input[name="status"][type="hidden"]');
    const finalFeaturedHidden = document.querySelector('input[name="is_featured"][type="hidden"]');
    
    if (finalStatusHidden) finalStatusHidden.value = finalStatusValue;
    if (finalFeaturedHidden) finalFeaturedHidden.value = finalFeaturedValue;
    
    
    // Show loading state
    const submitBtn = document.getElementById('submit-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
    
    // Form will submit normally and backend will handle redirect
});

@if(isset($product) && $product->has_variation)
function initializeExistingVariations() {
    // Initialize variations from existing product data
    const existingVariations = @json($product->variations ?? []);
    const existingCombinations = @json($product->combinations ?? []);
    
    if (existingVariations.length > 0) {
        existingVariations.forEach(variation => {
            addVariation();
            // Get the variation row that was just created
            const variationId = variationCounter - 1; // variationCounter was incremented in addVariation
            const variationRow = document.getElementById(`variation-${variationId}`);
            
            if (variationRow) {
                const typeSelect = variationRow.querySelector('.variation-type');
                const valueSelect = variationRow.querySelector('.variation-value');
                
                // Set values for existing variation in the JavaScript object
                const lastVariation = variations[variations.length - 1];
                // Support both field name formats: variation_type_id or variation, variation_value_ids or variationValue
                const variationTypeId = variation.variation_type_id || variation.variation;
                const variationValueIds = variation.variation_value_ids || variation.variationValue || [];
                
                lastVariation.type = variationTypeId;
                lastVariation.values = Array.isArray(variationValueIds) ? variationValueIds : [];
                
                // Set the variation type in Select2
                if (typeSelect && variationTypeId) {
                    $(typeSelect).val(variationTypeId).trigger('change');
                    
                    // Wait for Select2 to update, then populate values
                    setTimeout(() => {
                        // Call onVariationTypeChange to populate values dropdown
                        onVariationTypeChange(variationId);
                        
                        // Then set the selected values
                        setTimeout(() => {
                            if (valueSelect && lastVariation.values.length > 0) {
                                $(valueSelect).val(lastVariation.values).trigger('change');
                            }
                        }, 100);
                    }, 100);
                }
            }
        });
        
        if (existingCombinations.length > 0) {
            combinations = existingCombinations;
            updateCombinationsTable();
        }
    }
}
@endif

// Allow only integer keys (0-9) and backspace/delete/arrow keys
function isIntegerKey(event) {
    const charCode = event.which ? event.which : event.keyCode;
    
    // Allow backspace, delete, tab, escape, enter, and arrow keys
    if (charCode === 8 || charCode === 9 || charCode === 27 || charCode === 13 || 
        (charCode >= 35 && charCode <= 40)) {
        return true;
    }
    
    // Allow only digits (0-9)
    if (charCode >= 48 && charCode <= 57) {
        return true;
    }
    
    // Block all other characters including decimal points
    return false;
}

// Format discount value to remove leading zeroes and ensure integer-only input
function formatDiscountValue(input) {
    let value = input.value;
    
    // Remove any decimal points and non-numeric characters except digits
    value = value.replace(/[^0-9]/g, '');
    
    // Remove leading zeroes but keep single zero
    if (value.length > 1 && value.startsWith('0')) {
        value = value.replace(/^0+/, '') || '0';
    }
    
    // If empty, set to 0
    if (value === '') {
        value = '0';
    }
    
    // Update the input value
    input.value = value;
    
    // Validate based on discount type
    const discountType = document.getElementById('discount_type').value;
    const numValue = parseInt(value);
    
    if (discountType === 'percent' && numValue > 100) {
        input.value = '100';
    }
}

// Handle discount type change
document.getElementById('discount_type').addEventListener('change', function() {
    const discountValue = document.getElementById('discount_value');
    const numValue = parseInt(discountValue.value);
    
    if (this.value === 'percent' && numValue > 100) {
        discountValue.value = '100';
    }
});

// Add validation on form submit
document.getElementById('product-form').addEventListener('submit', function(e) {
    // Check if there are any error messages displaying
    const errorMessages = document.querySelectorAll('.variation-error-msg');
    const invalidFields = document.querySelectorAll('.is-invalid');
    
    // Also check for any text that contains "Please select"
    const allTextElements = document.querySelectorAll('*');
    let hasErrorText = false;
    allTextElements.forEach(function(element) {
        if (element.textContent && element.textContent.includes('Please select at least one value')) {
            hasErrorText = true;
        }
    });
    
    if (errorMessages.length > 0 || invalidFields.length > 0 || hasErrorText) {
        e.preventDefault();
        alert('Please fix all errors before saving.');
        return false;
    }
    
    const discountValue = document.getElementById('discount_value');
    const discountType = document.getElementById('discount_type');
    
    if (discountValue.value && discountValue.value !== '') {
        const numValue = parseInt(discountValue.value);
        
        // Validate based on type
        if (discountType.value === 'percent' && (numValue < 0 || numValue > 100)) {
            e.preventDefault();
            alert('{{ __("messages.discount_value_max_percent") }}');
            return false;
        } else if (discountType.value === 'fixed' && numValue < 0) {
            e.preventDefault();
            alert('{{ __("messages.discount_value_min") }}');
            return false;
        }
    }
});
</script>


