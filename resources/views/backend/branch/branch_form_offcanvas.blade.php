<form id="branch-form" method="POST"
    action="{{ isset($branch) ? route('backend.branch.update', $branch->id) : route('backend.branch.store') }}"
    enctype="multipart/form-data">
    @csrf
    @if (isset($branch))
        @method('PUT')
    @endif
    <input type="hidden" name="_method" id="method-override" value="{{ isset($branch) ? 'PUT' : '' }}" />
    <div class="offcanvas offcanvas-end" tabindex="-1" id="form-offcanvas" aria-labelledby="form-offcanvasLabel"
        style="width: 1200px !important; max-width: 95vw;">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="form-offcanvasLabel">
                @if (isset($branch) && $branch->id)
                    {{ $editTitle ?? __('Edit') }}
                @else
                    {{ $createTitle ?? __('Create') }}
                @endif
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="row">
                <div class="col-12 row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <!-- Branch Name -->
                        <div class="form-group">
                            <label for="name" class="form-label">{{ __('branch.lbl_branch_name') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                placeholder="{{ __('branch.branch_name') }}"
                                value="{{ old('name', $branch->name ?? '') }}" required>
                            @error('name')
                                <span class="text-danger text-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Branch For -->
                        <div class="form-group">
                            <label class="form-label">{{ __('branch.lbl_branch_for') }}</label>
                            <div class="btn-group w-100" role="group" aria-label="Basic example">
                                @foreach ($BRANCH_FOR_OPTIONS as $item)
                                    <input type="radio" class="btn-check" name="branch_for"
                                        id="{{ $item['id'] }}-for" value="{{ $item['id'] }}"
                                        {{ old('branch_for', $branch->branch_for ?? 'both') == $item['id'] ? 'checked' : '' }}
                                        autocomplete="off">
                                    <label class="btn btn-check-label"
                                        for="{{ $item['id'] }}-for">{{ $item['text'] }}</label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Image Upload -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="text-center upload-image-box">
                                <img src="{{ old('feature_image', $branch->feature_image ?? ($defaultImage ?? asset('images/default.png'))) }}"
                                    data-default="{{ $defaultImage ?? asset('images/default.png') }}"
                                    alt="feature-image" class="img-fluid mb-2 avatar-140 rounded" />
                                @if (session('validationMessage'))
                                    <div class="text-danger mb-2">{{ session('validationMessage') }}</div>
                                @endif
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <input type="file" class="form-control d-none" id="feature_image"
                                        name="feature_image" accept="image/*" />
                                    <label class="btn btn-sm btn-primary"
                                        for="feature_image">{{ __('messages.upload') }}</label>
                                    <input type="button" class="btn btn-sm btn-secondary" name="remove"
                                        id="remove-image-btn" value="{{ __('messages.remove') }}"
                                        onclick="removeLogo()"
                                        style="{{ old('feature_image', $branch->feature_image ?? false) ? '' : 'display:none;' }}" />
                                    <input type="hidden" name="remove_feature_image" id="remove_feature_image"
                                        value="0" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Manager -->
                    <div class="form-group col-md-12">
                        <div class="d-flex justify-content-between">
                            <label class="form-label" for="manager_id">{{ __('branch.lbl_select_manager') }} <span
                                    class="text-danger">*</span></label>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#exampleModal"
                                class="btn btn-sm text-primary"><i class="fa-solid fa-plus"></i>
                                {{ __('messages.create') }} {{ __('messages.new') }}</button>
                        </div>
                        <select name="manager_id" id="manager_id" class="form-select select2"
                            data-placeholder="{{ __('branch.assign_manager') }}">
                            <option value="">{{ __('branch.assign_manager') }}</option>
                            @foreach ($managers as $manager)
                                <option value="{{ $manager->id }}"
                                    {{ old('manager_id', $branch->manager_id ?? '') == $manager->id ? 'selected' : '' }}>
                                    {{ $manager->first_name }} {{ $manager->last_name }}</option>
                            @endforeach
                        </select>
                        @error('manager_id')
                            <span class="text-danger text-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Service -->
                    <div class="form-group col-md-12">
                        <label class="form-label" for="services">{{ __('branch.lbl_select_service') }}</label>
                        <select name="service_id[]" id="services" class="form-select select2" multiple
                            style="width:100%" data-placeholder="{{ __('branch.select_service') }}">
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}"
                                    {{ collect(old('service_id', $branch->service_id ?? []))->contains($service->id) ? 'selected' : '' }}>
                                    {{ $service->name }}</option>
                            @endforeach
                        </select>
                        @error('service_id')
                            <span class="text-danger text-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Contact Number -->
                    <div class="form-group col-md-6">
                        <label class="form-label">{{ __('branch.lbl_contact_number') }} <span
                                class="text-danger">*</span></label>
                        <div><input type="tel" name="contact_number" id="contact_number" class="form-control"
                                placeholder="{{ __('branch.enter_contact_number') }}"
                                value="{{ old('contact_number', $branch->contact_number ?? '') }}"
                                inputmode="numeric" pattern="[0-9]*" required></div>
                        <span class="text-danger phone-error text-error"></span>
                    </div>
                    <!-- Contact Email -->
                    <div class="form-group col-md-6">
                        <label class="form-label">{{ __('branch.lbl_contact_email') }} <span
                                class="text-danger">*</span></label>
                        <input type="email" name="contact_email" class="form-control"
                            placeholder="{{ __('branch.enter_email') }}"
                            value="{{ old('contact_email', $branch->contact_email ?? '') }}" required>
                        <span class="text-danger email-error text-error"></span>
                    </div>
                    <!-- Shop Number -->
                    <div class="form-group col-md-6">
                        <label class="form-label">{{ __('branch.lbl_shop_number') }} <span
                                class="text-danger">*</span></label>
                        <input type="text" name="address_line_1" class="form-control"
                            placeholder="{{ __('branch.enter_landmark') }}"
                            value="{{ old('address_line_1', $branch->address_line_1 ?? '') }}" required>
                        @error('address_line_1')
                            <span class="text-danger text-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Landmark -->
                    <div class="form-group col-md-6">
                        <label class="form-label">{{ __('branch.lbl_landmark') }}</label>
                        <input type="text" name="address_line_2" class="form-control"
                            placeholder="{{ __('branch.enter_nearby') }}"
                            value="{{ old('address_line_2', $branch->address_line_2 ?? '') }}">
                        @error('address_line_2')
                            <span class="text-danger text-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Country -->
                    <div class="col-md-3 form-group">
                        <label class="form-label">{{ __('branch.lbl_country') }} <span
                                class="text-danger">*</span></label>
                        <select name="country" id="country-list" class="form-select select2"
                            data-placeholder="{{ __('branch.select_country') }}" required>
                            <option value="">{{ __('branch.select_country') }}</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->id }}"
                                    {{ old('country', $branch->country ?? '') == $country->id ? 'selected' : '' }}>
                                    {{ $country->name }}</option>
                            @endforeach
                        </select>
                        @error('country')
                            <span class="text-danger text-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- State -->
                    <div class="col-md-3 form-group">
                        <label class="form-label">{{ __('branch.lbl_state') }} <span
                                class="text-danger">*</span></label>
                        <select name="state" id="state-list" class="form-select select2"
                            data-placeholder="{{ __('branch.select_state') }}" required>
                            <option value="">{{ __('branch.select_state') }}</option>
                            @if (isset($branch) && $branch->state)
                                @foreach ($states as $state)
                                    @if ($state->country_id == $branch->country)
                                        <option value="{{ $state->id }}"
                                            {{ old('state', $branch->state ?? '') == $state->id ? 'selected' : '' }}>
                                            {{ $state->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                        @error('state')
                            <span class="text-danger text-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- City -->
                    <div class="col-md-3 form-group">
                        <label class="form-label">{{ __('branch.lbl_city') }} <span
                                class="text-danger">*</span></label>
                        <select name="city" id="city-list" class="form-select select2"
                            data-placeholder="{{ __('branch.select_city') }}" required>
                            <option value="">{{ __('branch.select_city') }}</option>
                            @if (isset($branch) && $branch->city)
                                @foreach ($cities as $city)
                                    @if ($city->state_id == $branch->state)
                                        <option value="{{ $city->id }}"
                                            {{ old('city', $branch->city ?? '') == $city->id ? 'selected' : '' }}>
                                            {{ $city->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                        @error('city')
                            <span class="text-danger text-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Postal Code -->
                    <div class="form-group col-md-3">
                        <label class="form-label" for="postal_code">{{ __('branch.lbl_postal_code') }} <span
                                class="text-danger">*</span></label>
                        <input type="text" name="postal_code" class="form-control"
                            placeholder="{{ __('branch.select_code') }}"
                            value="{{ old('postal_code', $branch->postal_code ?? '') }}" required>
                        @error('postal_code')
                            <span class="text-danger text-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Latitude -->
                    <div class="form-group col-md-3">
                        <label class="form-label">{{ __('branch.lbl_lat') }} <span
                                class="text-danger">*</span></label>
                        <input type="number" name="latitude" id="latitude" class="form-control" step="any"
                            placeholder="{{ __('branch.enter_latitutude') }}"
                            value="{{ old('latitude', $branch->latitude ?? '') }}" required>
                        <div class="invalid-feedback latitude-error text-error"></div>
                        @error('latitude')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Longitude -->
                    <div class="form-group col-md-3">
                        <label class="form-label">{{ __('branch.lbl_long') }} <span
                                class="text-danger">*</span></label>
                        <input type="number" name="longitude" id="longitude" class="form-control" step="any"
                            placeholder="{{ __('branch.enter_logtitude') }}"
                            value="{{ old('longitude', $branch->longitude ?? '') }}" required>
                        <div class="invalid-feedback longitude-error"></div>
                        @error('longitude')
                            <span class="text-danger text-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Payment Methods -->
                    <div class="form-group col-md-6">
                        <label class="form-label" for="payment-method">{{ __('branch.lbl_payment_method') }} <span
                                class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-3 form-control p-2" role="group"
                            aria-label="Basic checkbox toggle button group" style="min-height: 50px;">
                            @foreach ($PAYMENT_METHODS_OPTIONS as $item)
                                <div class="form-check d-flex align-items-center mb-0">
                                    <input type="checkbox" class="form-check-input mt-0"
                                        id="{{ $item['id'] }}-payment-method" name="payment_method[]"
                                        value="{{ $item['id'] }}"
                                        {{ is_array(old('payment_method', $branch->payment_method ?? ['cash'])) && in_array($item['id'], old('payment_method', $branch->payment_method ?? ['cash'])) ? 'checked' : '' }}
                                        autocomplete="off">
                                    <label class="form-check-label mb-0 ms-1"
                                        for="{{ $item['id'] }}-payment-method"
                                        style="cursor: pointer;">{{ $item['text'] }}</label>
                                </div>
                            @endforeach
                        </div>
                        @error('payment_method')
                            <span class="text-danger text-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Description -->
                    <div class="form-group col-md-12">
                        <label class="form-label" for="description">{{ __('branch.lbl_description') }}</label>
                        <textarea class="form-control" name="description" placeholder="{{ __('branch.enter_decription') }}"
                            id="description" rows="3" maxlength="250">{{ old('description', $branch->description ?? '') }}</textarea>
                        <div class="d-flex justify-content-end">
                            <small class="text-muted">
                                <span id="description_count">0</span>/250 characters
                            </small>
                        </div>
                        @error('description')
                            <span class="text-danger text-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Custom Fields -->
                    @if (isset($customefield) && count($customefield))
                        @foreach ($customefield as $field)
                            @include('helpers.custom-field.form-element', [
                                'name' => $field['name'],
                                'label' => $field['label'],
                                'type' => $field['type'],
                                'required' => $field['required'],
                                'options' => $field['value'],
                                'field_id' => $field['id'],
                                'value' => old($field['name'], $branch->{$field['name']} ?? ''),
                            ])
                        @endforeach
                    @endif
                    <!-- Status -->
                    <div class="form-group col-md-3">
                        <div class="d-flex align-items-center gap-3 justify-content-between form-control">
                            <label class="form-label" for="category-status">{{ __('branch.lbl_status') }}</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" name="status" id="category-status" type="checkbox"
                                    value="1" {{ old('status', $branch->status ?? true) ? 'checked' : '' }} />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="offcanvas-footer p-3 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary"
                data-bs-dismiss="offcanvas">{{ __('messages.cancel') }}</button>
            <button type="submit" class="btn btn-primary">
                @if (isset($IS_SUBMITED) && $IS_SUBMITED)
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    {{ __('messages.submitting') }}
                @else
                    {{ __('messages.save') }}
                @endif
            </button>
        </div>
    </div>
</form>

<!-- Employee Create Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ __('Create Manager') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('employee.store') }}" id="create-manager-form">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="manager_first_name" class="form-label">{{ __('messages.first_name') }}
                                <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="manager_first_name" name="first_name"
                                placeholder="{{ __('messages.enter_first_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="manager_last_name" class="form-label">{{ __('messages.last_name') }}
                                <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="manager_last_name" name="last_name"
                                placeholder="{{ __('messages.enter_last_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="manager_email" class="form-label">{{ __('messages.email') }} <span
                                    class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="manager_email" name="email"
                                placeholder="{{ __('Enter Email') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="manager_contact_number"
                                class="form-label">{{ __('branch.lbl_contact_number') }} <span
                                    class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="manager_contact_number" name="mobile"
                                placeholder="{{ __('branch.enter_contact_number') }}" inputmode="numeric"
                                pattern="[0-9]*" required>
                        </div>
                        <div class="col-md-6">
                            <label for="manager_password" class="form-label">{{ __('messages.password') }} <span
                                    class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="manager_password" name="password"
                                placeholder="{{ __('messages.enter_password') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="manager_password_confirmation"
                                class="form-label">{{ __('messages.confirm_password') }} <span
                                    class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="manager_password_confirmation"
                                name="confirm_password" placeholder="{{ __('Enter Confirm Password') }}" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label d-block">{{ __('messages.gender') }}</label>
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="gender_male"
                                        value="male" checked>
                                    <label class="form-check-label"
                                        for="gender_male">{{ __('messages.male') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="gender_female"
                                        value="female">
                                    <label class="form-check-label"
                                        for="gender_female">{{ __('messages.female') }}</label>
                                </div>
                                {{-- <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="gender_other"
                                        value="other">
                                    <label class="form-check-label"
                                        for="gender_other">{{ __('messages.other') }}</label>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="submit"
                            class="btn btn-primary">{{ __('messages.add_manager') ?? 'Add Manager' }}</button>
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<script>
    // Add event listeners when the document is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM ready event fired');

        // Do not remove Select2 containers globally to avoid breaking instances

        // Initialize Select2 for services and location selects (ensure only ONE instance is visible)
        if (window.$ && $.fn.select2) {
            const initSingleSelect2 = function(selector, options) {
                const $el = $(selector);
                if (!$el.length) return;

                // If already initialized elsewhere, destroy then re-init to avoid duplicates
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
                // Remove any duplicate Select2 containers created by other scripts
                const $containers = $el.siblings('.select2.select2-container');
                if ($containers.length > 0) {
                    $containers.remove();
                }

                // Initialize only if not already initialized
                if (!$el.hasClass('select2-hidden-accessible')) {
                    $el.select2(options);
                }
                // Hide original select to avoid double boxes
                $el.css({
                    display: 'none'
                });
            };

            initSingleSelect2('#services', {
                width: '100%',
                placeholder: $('#services').data('placeholder'),
                allowClear: true
            });

            // Assign Manager select2
            initSingleSelect2('#manager_id', {
                width: '100%',
                placeholder: $('#manager_id').data('placeholder') || '',
                allowClear: true
            });

            // Function to initialize manager contact number prevention
            function initManagerContactNumberPrevention() {
                const managerPhone = document.getElementById('manager_contact_number');
                if (managerPhone && window.intlTelInput) {
                    try {
                        // Initialize intl-tel-input if not already initialized
                        let itiManager;
                        const alreadyInitialized = managerPhone.hasAttribute('data-intl-tel-input-id');
                        if (!alreadyInitialized) {
                            // Store original placeholder before initialization
                            const originalPlaceholder = managerPhone.getAttribute('placeholder') || managerPhone
                                .placeholder || '';

                            itiManager = intlTelInput(managerPhone, {
                                initialCountry: 'in',
                                separateDialCode: true,
                                utilsScript: '/js/utils.js',
                                autoHideDialCode: true, // Hide country code from inside input field
                                nationalMode: true, // Show only phone number in input (no country code)
                                autoPlaceholder: 'off' // Disable auto placeholder
                            });

                            // Restore original placeholder after initialization
                            setTimeout(() => {
                                if (managerPhone.placeholder !== originalPlaceholder) {
                                    managerPhone.placeholder = originalPlaceholder;
                                }
                            }, 100);
                        } else {
                            itiManager = (window.intlTelInput && window.intlTelInput.getInstance) ? window
                                .intlTelInput.getInstance(managerPhone) : null;
                        }

                        // Dedupe any duplicate dial code elements if they somehow exist
                        const container = managerPhone.closest('.iti');
                        if (container && container.querySelectorAll) {
                            const dials = container.querySelectorAll('.iti__selected-dial-code');
                            if (dials && dials.length > 1) {
                                for (let i = 1; i < dials.length; i++) {
                                    dials[i].remove();
                                }
                            }
                        }

                        // Function to remove country code from input value
                        const removeCountryCode = function() {
                            const currentValue = managerPhone.value || '';
                            if (!currentValue) return;

                            // Get dial code from intl-tel-input
                            let dialCode = '';
                            if (itiManager) {
                                const countryData = itiManager.getSelectedCountryData();
                                dialCode = countryData ? countryData.dialCode : '';
                            }

                            let cleanedValue = currentValue;

                            // Remove country code if present
                            if (dialCode) {
                                // Remove patterns like +91, 91, +91 123, etc.
                                const patterns = [
                                    new RegExp('^\\+?' + dialCode + '\\s*', 'g'),
                                    new RegExp('^\\+' + dialCode + '\\s*', 'g'),
                                    new RegExp('^' + dialCode + '\\s*', 'g')
                                ];
                                patterns.forEach(pattern => {
                                    cleanedValue = cleanedValue.replace(pattern, '');
                                });
                            }

                            // Also remove any generic +country code pattern
                            cleanedValue = cleanedValue.replace(/^\+\d{1,4}\s*/g, '');

                            if (currentValue !== cleanedValue) {
                                managerPhone.value = cleanedValue;
                            }
                        };

                        // Prevent non-numeric characters from being entered in manager contact number
                        const managerPhoneInput = managerPhone;

                        // Remove existing listeners to prevent duplicates
                        const newInputHandler = function(e) {
                            // First remove any country code
                            removeCountryCode();

                            // Then filter non-numeric characters
                            const currentValue = this.value || '';
                            const filteredValue = currentValue.replace(/[^0-9]/g, '');
                            if (currentValue !== filteredValue) {
                                this.value = filteredValue;
                            }

                            // Clear error messages immediately when user starts typing
                            if (filteredValue.length > 0) {
                                const $field = $(this);
                                const errorText = $field.siblings('.text-error').text() || '';
                                if (errorText.toLowerCase().indexOf('required') !== -1) {
                                    $field.siblings('.text-error').remove();
                                    $field.next('.text-error').remove();
                                    $field.nextAll('.text-error').remove();
                                }
                            }
                        };

                        const newPasteHandler = function(e) {
                            setTimeout(() => {
                                // First remove any country code
                                removeCountryCode();

                                // Then filter non-numeric characters
                                const currentValue = this.value || '';
                                const filteredValue = currentValue.replace(/[^0-9]/g, '');
                                if (currentValue !== filteredValue) {
                                    this.value = filteredValue;
                                }

                                // Clear error messages immediately when user pastes data
                                if (filteredValue.length > 0) {
                                    const $field = $(this);
                                    const errorText = $field.siblings('.text-error').text() || '';
                                    if (errorText.toLowerCase().indexOf('required') !== -1) {
                                        $field.siblings('.text-error').remove();
                                        $field.next('.text-error').remove();
                                        $field.nextAll('.text-error').remove();
                                    }
                                }
                            }, 10);
                        };

                        // Remove country code when field is clicked/focused
                        const focusHandler = function() {
                            setTimeout(() => {
                                removeCountryCode();
                            }, 10);
                        };

                        // Remove country code when country changes
                        const countryChangeHandler = function() {
                            setTimeout(() => {
                                removeCountryCode();
                            }, 10);
                        };

                        const newKeydownHandler = function(e) {
                            // Allow: backspace, delete, tab, escape, enter, and arrow keys
                            if ([8, 9, 27, 13, 46, 37, 38, 39, 40].indexOf(e.keyCode) !== -1 ||
                                // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                                (e.keyCode === 65 && e.ctrlKey === true) ||
                                (e.keyCode === 67 && e.ctrlKey === true) ||
                                (e.keyCode === 86 && e.ctrlKey === true) ||
                                (e.keyCode === 88 && e.ctrlKey === true) ||
                                // Allow home, end
                                (e.keyCode >= 35 && e.keyCode <= 39)) {
                                return;
                            }
                            // Prevent non-numeric keys from being entered (silently block them)
                            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e
                                    .keyCode > 105)) {
                                e.preventDefault();
                                // Just prevent entry, don't show error message
                                return false;
                            }
                        };

                        // Remove old listeners and add new ones
                        managerPhoneInput.removeEventListener('keydown', managerPhoneInput._keydownHandler);
                        managerPhoneInput.removeEventListener('input', managerPhoneInput._inputHandler);
                        managerPhoneInput.removeEventListener('paste', managerPhoneInput._pasteHandler);
                        managerPhoneInput.removeEventListener('focus', managerPhoneInput._focusHandler);
                        managerPhoneInput.removeEventListener('click', managerPhoneInput._focusHandler);
                        if (itiManager) {
                            managerPhoneInput.removeEventListener('countrychange', managerPhoneInput
                                ._countryChangeHandler);
                        }

                        managerPhoneInput._keydownHandler = newKeydownHandler;
                        managerPhoneInput._inputHandler = newInputHandler;
                        managerPhoneInput._pasteHandler = newPasteHandler;
                        managerPhoneInput._focusHandler = focusHandler;
                        managerPhoneInput._countryChangeHandler = countryChangeHandler;

                        managerPhoneInput.addEventListener('keydown', managerPhoneInput._keydownHandler);
                        managerPhoneInput.addEventListener('input', managerPhoneInput._inputHandler);
                        managerPhoneInput.addEventListener('paste', managerPhoneInput._pasteHandler);
                        managerPhoneInput.addEventListener('focus', managerPhoneInput._focusHandler);
                        managerPhoneInput.addEventListener('click', managerPhoneInput._focusHandler);
                        if (itiManager) {
                            managerPhoneInput.addEventListener('countrychange', managerPhoneInput
                                ._countryChangeHandler);
                        }

                        // Initial cleanup
                        setTimeout(() => {
                            removeCountryCode();
                        }, 100);
                    } catch (e) {
                        console.error('Manager contact number initialization error:', e);
                    }
                }
            }

            // Initialize on page load
            initManagerContactNumberPrevention();

            // Also initialize when modal is shown (in case modal is dynamically loaded)
            const exampleModal = document.getElementById('exampleModal');
            if (exampleModal) {
                exampleModal.addEventListener('shown.bs.modal', function() {
                    initManagerContactNumberPrevention();
                });
            }

            // Country, State, City
            initSingleSelect2('#country-list', {
                width: '100%',
                placeholder: $('#country-list').data('placeholder') || '',
                allowClear: true
            });
            initSingleSelect2('#state-list', {
                width: '100%',
                placeholder: $('#state-list').data('placeholder') || '',
                allowClear: true
            });
            initSingleSelect2('#city-list', {
                width: '100%',
                placeholder: $('#city-list').data('placeholder') || '',
                allowClear: true
            });
        }

        // Initialize intl-tel-input for contact number
        if (window.intlTelInput) {
            const contactInput = document.getElementById('contact_number');
            if (contactInput) {
                const iti = intlTelInput(contactInput, {
                    initialCountry: 'in',
                    preferredCountries: ['in', 'us', 'gb', 'au', 'ca'],
                    separateDialCode: true,
                    utilsScript: "/js/utils.js",
                    autoHideDialCode: false,
                    autoPlaceholder: 'aggressive',
                    formatOnDisplay: true,
                    nationalMode: false,
                    geoIpLookup: function(callback) {
                        // Default to India if geolocation fails
                        callback('in');
                    }
                });

                // Handle form submission to get the full international number
                const form = contactInput.closest('form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        const phoneErrorEl = document.querySelector('.phone-error');
                        const currentValue = contactInput.value || '';
                        const trimmedValue = currentValue.trim();

                        // If field is empty, don't show "Only numbers allowed" error - let required validation handle it
                        if (trimmedValue === '') {
                            // Clear any "Only numbers allowed" error, let required validation show its message
                            if (phoneErrorEl) {
                                const errorText = phoneErrorEl.textContent || '';
                                if (errorText.toLowerCase().indexOf('numbers') !== -1 || errorText
                                    .toLowerCase().indexOf('only') !== -1) {
                                    phoneErrorEl.textContent = '';
                                }
                            }
                            return; // Let jQuery Validate handle the required validation
                        }

                        // Field has value - check if it's numeric
                        contactInput.value = contactInput.value.replace(/[^0-9]/g, '');
                        const numericOk = /^[0-9]{6,}$/.test(contactInput.value); // at least 6 digits
                        if (!numericOk && contactInput.value.length > 0) {
                            // Field has value but not enough digits or contains non-numeric
                            e.preventDefault();
                            if (phoneErrorEl) {
                                phoneErrorEl.textContent =
                                    '{{ __('messages.only_numbers_allowed') ?? 'Only numbers are allowed' }}';
                            }
                            if (phoneErrorEl) {
                                phoneErrorEl.classList.add('text-danger');
                            }
                            return false;
                        } else if (numericOk) {
                            // Field is valid - clear any errors
                            if (phoneErrorEl) {
                                phoneErrorEl.textContent = '';
                                phoneErrorEl.classList.remove('text-danger');
                            }
                        }
                        if (iti.isValidNumber()) {
                            const fullNumber = iti.getNumber();
                            contactInput.value = fullNumber.replace(/[^0-9]/g, '');
                        }
                    });
                    // live filtering with real-time error display
                    const phoneError = document.querySelector('.phone-error');

                    if (phoneError) {
                        contactInput.addEventListener('input', function(e) {
                            const currentValue = this.value;
                            const hasNonNumeric = /[^0-9]/.test(currentValue);

                            // Filter out non-numeric characters
                            const filteredValue = currentValue.replace(/[^0-9]/g, '');
                            this.value = filteredValue;

                            // If field has any value, clear "required" error immediately
                            if (filteredValue.length > 0) {
                                // Clear required error immediately
                                const errorText = phoneError.textContent || '';
                                if (errorText.toLowerCase().indexOf('required') !== -1) {
                                    phoneError.textContent = '';
                                    phoneError.classList.remove('text-danger');
                                }
                            }

                            // Only show "Only numbers allowed" if field is NOT empty AND has non-numeric characters
                            if (hasNonNumeric && filteredValue.length > 0) {
                                phoneError.textContent =
                                    '{{ __('messages.only_numbers_allowed') ?? 'Only numbers are allowed' }}';
                                phoneError.classList.add('text-danger');
                                // Remove any jQuery Validate error messages to prevent duplicates
                                if (window.$ && $.fn.validate) {
                                    const $input = $(this);
                                    $input.siblings('.text-error').not('.phone-error').remove();
                                    $input.next('.text-error').not('.phone-error').remove();
                                }
                            } else if (filteredValue.length === 0) {
                                // Field is empty - clear "Only numbers allowed" error, let required validation handle it
                                phoneError.textContent = '';
                                phoneError.classList.remove('text-danger');
                            } else {
                                // Field has only valid numbers - clear all errors
                                phoneError.textContent = '';
                                phoneError.classList.remove('text-danger');
                            }
                        });

                        // Handle paste events to catch pasted non-numeric characters
                        contactInput.addEventListener('paste', function(e) {
                            setTimeout(() => {
                                const currentValue = this.value;
                                const hasNonNumeric = /[^0-9]/.test(currentValue);
                                const filteredValue = currentValue.replace(/[^0-9]/g, '');
                                this.value = filteredValue;

                                // If field has any value, clear "required" error immediately
                                if (filteredValue.length > 0) {
                                    // Clear required error immediately
                                    const errorText = phoneError.textContent || '';
                                    if (errorText.toLowerCase().indexOf('required') !== -1) {
                                        phoneError.textContent = '';
                                        phoneError.classList.remove('text-danger');
                                    }
                                }

                                // Only show "Only numbers allowed" if field is NOT empty AND has non-numeric characters
                                if (hasNonNumeric && filteredValue.length > 0) {
                                    phoneError.textContent =
                                        '{{ __('messages.only_numbers_allowed') ?? 'Only numbers are allowed' }}';
                                    phoneError.classList.add('text-danger');
                                    // Remove any jQuery Validate error messages to prevent duplicates
                                    if (window.$ && $.fn.validate) {
                                        const $input = $(this);
                                        $input.siblings('.text-danger').not('.phone-error')
                                            .remove();
                                        $input.next('.text-danger').not('.phone-error')
                                            .remove();
                                    }
                                } else if (filteredValue.length === 0) {
                                    // Field is empty - clear "Only numbers allowed" error
                                    phoneError.textContent = '';
                                    phoneError.classList.remove('text-danger');
                                } else {
                                    // Field has only valid numbers - clear all errors
                                    phoneError.textContent = '';
                                    phoneError.classList.remove('text-danger');
                                }
                            }, 10);
                        });

                        // Also check on keydown to prevent non-numeric keys and show error immediately
                        contactInput.addEventListener('keydown', function(e) {
                            // Allow: backspace, delete, tab, escape, enter, and arrow keys
                            if ([8, 9, 27, 13, 46, 37, 38, 39, 40].indexOf(e.keyCode) !== -1 ||
                                // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                                (e.keyCode === 65 && e.ctrlKey === true) ||
                                (e.keyCode === 67 && e.ctrlKey === true) ||
                                (e.keyCode === 86 && e.ctrlKey === true) ||
                                (e.keyCode === 88 && e.ctrlKey === true) ||
                                // Allow home, end
                                (e.keyCode >= 35 && e.keyCode <= 39)) {
                                return;
                            }
                            // Prevent non-numeric keys (but allow paste through)
                            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 ||
                                    e.keyCode > 105)) {
                                e.preventDefault();
                                // Only show "Only numbers allowed" if field will have content after this keypress
                                const currentValue = this.value || '';
                                if (currentValue.length > 0 || e.keyCode === 86) { // Ctrl+V paste case
                                    phoneError.textContent =
                                        '{{ __('messages.only_numbers_allowed') ?? 'Only numbers are allowed' }}';
                                    phoneError.classList.add('text-danger');
                                    // Remove any jQuery Validate error messages to prevent duplicates
                                    if (window.$ && $.fn.validate) {
                                        const $input = $(this);
                                        $input.siblings('.text-error').not('.phone-error').remove();
                                        $input.next('.text-error').not('.phone-error').remove();
                                    }
                                }
                            } else {
                                // User is typing a valid number - clear "Only numbers allowed" and "required" errors
                                setTimeout(() => {
                                    const currentValue = this.value || '';
                                    if (currentValue.length > 0) {
                                        // Clear required error if field has value
                                        const errorText = phoneError.textContent || '';
                                        if (errorText.toLowerCase().indexOf('required') !== -
                                            1) {
                                            phoneError.textContent = '';
                                            phoneError.classList.remove('text-danger');
                                        }
                                    } else {
                                        // Field will be empty, clear all errors
                                        phoneError.textContent = '';
                                        phoneError.classList.remove('text-danger');
                                    }
                                }, 10);
                            }
                        });
                    }
                }
            }
        }

        // Ensure _method=PUT is present when submitting in edit mode
        (function() {
            const $form = $('#branch-form');
            if (!$form.length) return;
            $form.on('submit', function() {
                const action = $form.attr('action') || '';
                const isUpdate = /\/branch\/[0-9]+(\?|$)/.test(action);
                // Keep hidden method override in sync
                const $method = $('#method-override');
                if (isUpdate) {
                    if ($method.length) {
                        $method.val('PUT');
                    } else {
                        $('<input>').attr({
                            type: 'hidden',
                            name: '_method',
                            value: 'PUT',
                            id: 'method-override'
                        }).prependTo($form);
                    }
                } else {
                    if ($method.length) {
                        $method.val('');
                    }
                }
            });
        })();

        // Feature image preview on file select
        (function() {
            const fileInput = document.getElementById('feature_image');
            const imgEl = document.querySelector('.upload-image-box img');
            const removeBtn = document.getElementById('remove-image-btn');

            if (fileInput && imgEl) {
                fileInput.addEventListener('change', function() {
                    const file = this.files && this.files[0];
                    if (!file) return;

                    const objUrl = URL.createObjectURL(file);
                    imgEl.onload = function() {
                        URL.revokeObjectURL(objUrl);
                    };
                    imgEl.src = objUrl;

                    // Show remove button when image is selected
                    if (removeBtn) {
                        removeBtn.style.display = 'inline-block';
                    }
                });
            }
        })();

        // Remove image function
        function removeLogo() {
            const fileInput = document.getElementById('feature_image');
            const imgEl = document.querySelector('.upload-image-box img');
            const removeBtn = document.getElementById('remove-image-btn');
            const removeFlag = document.getElementById('remove_feature_image');

            if (fileInput) {
                fileInput.value = '';
            }
            if (imgEl) {
                const defaultSrc = imgEl.getAttribute('data-default') || imgEl.src;
                imgEl.src = defaultSrc;
            }
            if (removeBtn) {
                removeBtn.style.display = 'none';
            }
            if (removeFlag) {
                removeFlag.value = '1';
            }
        }

        window.removeLogo = removeLogo;

        // Global dedupe: remove stray Select2 containers without a preceding select
        if (window.$) {
            $('.select2.select2-container').each(function() {
                const $c = $(this);
                const hasSelectSibling = $c.prev('select').length > 0;
                if (!hasSelectSibling) {
                    $c.remove();
                }
            });
            // For our specific selects, keep only one container
            ['#services', '#country-list', '#state-list', '#city-list'].forEach(function(sel) {
                const $s = $(sel);
                if ($s.length) {
                    $s.siblings('.select2.select2-container').not(':first').remove();
                }
            });
        }

        // Clear stale server-side error messages before submit and between opens
        (function() {
            if (!window.$) return;
            const $form = $('#branch-form');

            function clearErrors() {
                $form.find('.text-error.phone-error').text('').removeClass('text-danger');
                $form.find('.text-error.email-error').text('').removeClass('text-danger');
                // Clear all validation error messages when form opens
                $form.find('.text-error').not('.phone-error, .email-error').remove();
                // Don't clear latitude and longitude errors - they should persist until proper format is entered
            }
            document.getElementById('form-offcanvas')?.addEventListener('show.bs.offcanvas', clearErrors);
            // Don't clear errors on form submit - let validation handle it
        })();

        // Double validation for latitude and longitude
        (function() {
            if (!window.$) return;
            const $form = $('#branch-form');
            const $latitude = $('#latitude');
            const $longitude = $('#longitude');

            function validateDouble(value, fieldName) {
                if (!value || value.trim() === '') return true; // Allow empty for required field validation

                // Check if it's a valid number
                const num = parseFloat(value);
                if (isNaN(num) || !isFinite(num)) {
                    return false;
                }

                // Check if it's actually a double (must have decimal point)
                // Convert to string and check if it contains a decimal point
                const strValue = value.toString().trim();
                return strValue.includes('.') && !isNaN(num) && isFinite(num);
            }

            function showDoubleError($field, fieldName) {
                $field.siblings('.invalid-feedback').text('Please enter a decimal value (e.g., 121.32)')
                    .addClass('text-danger')
                    .css('color', '#dc3545');
            }

            function clearDoubleError($field) {
                $field.siblings('.invalid-feedback').text('').removeClass('text-danger');
            }

            // Let jQuery validation handle the real-time validation to avoid duplicates

            // jQuery validation will handle form submission validation

            // Override jQuery validation if it exists
            if ($.fn.validate) {
                // Mark form as validated so we can trigger validation later
                $form.data('validate-form', true);
                $('#city-list').data('validate-form', true);

                $form.validate({
                    ignore: '.d-none :input, :hidden:not(.select2-hidden-accessible)',
                    rules: {
                        name: {
                            required: true
                        },
                        manager_id: {
                            required: true
                        },
                        service_id: {
                            required: true
                        },
                        contact_number: {
                            required: true,
                            customDigits: true
                        },
                        contact_email: {
                            required: true,
                            email: true
                        },
                        address_line_1: {
                            required: true
                        },
                        country: {
                            required: true
                        },
                        state: {
                            required: true
                        },
                        city: {
                            required: true
                        },
                        postal_code: {
                            required: true
                        },
                        latitude: {
                            required: true,
                            customDecimal: true
                        },
                        longitude: {
                            required: true,
                            customDecimal: true
                        },
                        payment_method: {
                            required: true
                        }
                    },
                    messages: {
                        name: {
                            required: '{{ __('branch.lbl_branch_name') }} {{ __('messages.is_required') }}'
                        },
                        manager_id: {
                            required: '{{ __('branch.lbl_select_manager') }} {{ __('messages.is_required') }}'
                        },
                        service_id: {
                            required: '{{ __('branch.lbl_service') }} {{ __('messages.is_required') }}'
                        },
                        contact_number: {
                            required: '{{ __('branch.lbl_contact_number') }} {{ __('messages.is_required') }}',
                            customDigits: '{{ __('messages.only_numbers_allowed') ?? 'Only numbers are allowed' }}'
                        },
                        contact_email: {
                            required: '{{ __('branch.lbl_contact_email') }} {{ __('messages.is_required') }}',
                            email: '{{ __('branch.lbl_contact_email') }} {{ __('messages.must_be_valid_email') }}'
                        },
                        address_line_1: {
                            required: '{{ __('branch.lbl_shop_number') }} {{ __('messages.is_required') }}'
                        },
                        country: {
                            required: '{{ __('branch.lbl_country') }} {{ __('messages.is_required') }}'
                        },
                        state: {
                            required: '{{ __('branch.lbl_state') }} {{ __('messages.is_required') }}'
                        },
                        city: {
                            required: '{{ __('branch.lbl_city') }} {{ __('messages.is_required') }}'
                        },
                        postal_code: {
                            required: '{{ __('branch.lbl_postal_code') }} {{ __('messages.is_required') }}'
                        },
                        latitude: {
                            required: '{{ __('branch.lbl_lat') }} {{ __('messages.is_required') }}',
                            customDecimal: '{{ __('branch.lbl_lat') }} {{ __('messages.must_be_decimal') }}'
                        },
                        longitude: {
                            required: '{{ __('branch.lbl_long') }} {{ __('messages.is_required') }}',
                            customDecimal: '{{ __('branch.lbl_long') }} {{ __('messages.must_be_decimal') }}'
                        },
                        payment_method: {
                            required: '{{ __('branch.lbl_payment_method') }} {{ __('messages.is_required') }}'
                        }
                    },
                    errorElement: 'div',
                    errorClass: 'text-error mt-1',
                    highlight: function(el) {
                        // Don't add is-invalid class to remove red border
                    },
                    unhighlight: function(el) {
                        // Clear error messages when field becomes valid
                        $(el).siblings('.text-error').remove();
                        $(el).next('.text-error').remove();
                        $(el).nextAll('.text-error').remove();

                        // Clear Select2 errors if applicable
                        if ($(el).hasClass('select2-hidden-accessible')) {
                            $(el).next('.select2').next('.text-error').remove();
                        }
                    },
                    submitHandler: function(form) {
                        // Only submit if all validations pass
                        return true;
                    },
                    invalidHandler: function(event, validator) {
                        // When validation fails, ensure contact_number shows only one error
                        const $contactNumber = $('#contact_number');
                        const $phoneError = $contactNumber.siblings('.phone-error');
                        if ($phoneError.length && $contactNumber.length) {
                            const currentValue = $contactNumber.val() || '';
                            const trimmedValue = currentValue.trim();

                            // If field is empty, remove any "Only numbers allowed" messages
                            if (trimmedValue === '') {
                                const errorText = $phoneError.text();
                                if (errorText.indexOf('Only numbers') !== -1 || errorText
                                    .indexOf('numbers') !== -1) {
                                    // Clear digits error, let required error show
                                    $phoneError.text('');
                                }
                            }
                        }
                    },
                    errorPlacement: function(error, element) {
                        // Ensure error message has text-danger class
                        error.addClass('text-danger');

                        // Special handling for contact_number to prevent duplicate messages
                        if (element.attr('name') === 'contact_number') {
                            const $phoneError = element.siblings('.phone-error');
                            if ($phoneError.length) {
                                const currentValue = element.val() || '';
                                const trimmedValue = currentValue.trim();
                                const errorText = error.text().trim();

                                // CRITICAL: Always check if field is empty first - required takes priority
                                if (trimmedValue === '') {
                                    // Field is empty - only show "required" message, ignore ALL other errors
                                    if (errorText.toLowerCase().indexOf('required') !== -1) {
                                        // This is the required error - show it
                                        $phoneError.text(errorText).addClass('text-danger');
                                        error.remove();
                                        return;
                                    } else {
                                        // This is NOT the required error (probably digits) - ignore it completely
                                        error.remove();
                                        return;
                                    }
                                }

                                // Field has value - check what type of error
                                if (errorText.toLowerCase().indexOf('numbers') !== -1 ||
                                    errorText.toLowerCase().indexOf('only') !== -1) {
                                    // Show digits error (field has non-numeric characters)
                                    // First, remove any existing error text to avoid duplicates
                                    $phoneError.text('');
                                    // Then show the new error
                                    $phoneError.text(errorText).addClass('text-danger');
                                    error.remove();
                                    return;
                                }

                                // If field has value and only numbers, clear errors
                                if (trimmedValue !== '' && /^[0-9]+$/.test(trimmedValue)) {
                                    $phoneError.text('').removeClass('text-danger');
                                    error.remove();
                                    return;
                                }

                                // For any other case, replace existing error with new one
                                $phoneError.text(errorText).addClass('text-danger');
                                error.remove();
                                return;
                            }
                            // No custom error span, place error outside .iti container
                            const $itiContainer = element.closest('.iti');
                            if ($itiContainer.length) {
                                // Remove any existing error inside the .iti container
                                $itiContainer.find('#contact_number-error').remove();
                                // Place error after the .iti container (outside of it)
                                error.attr('id', 'contact_number-error');
                                error.insertAfter($itiContainer);
                            } else if (element.hasClass('select2-hidden-accessible')) {
                                error.insertAfter(element.next('.select2'));
                            } else {
                                error.insertAfter(element);
                            }
                        } else {
                            // Default behavior for other fields
                            element.siblings('.text-error').remove();
                            element.next('.text-error').remove();

                            // Ensure error message has text-danger class
                            error.addClass('text-danger');

                            if (element.hasClass('select2-hidden-accessible')) {
                                error.insertAfter(element.next('.select2'));
                            } else {
                                error.insertAfter(element);
                            }
                        }
                    }
                });
                // Ensure State shows an inline error even when left empty
                $form.on('submit.stateCheck', function() {
                    var $state = $('#state-list');
                    // If state has no value, temporarily enable and trigger validation to render message
                    if (!$state.val()) {
                        var wasDisabled = $state.prop('disabled');
                        if (wasDisabled) $state.prop('disabled', false);
                        $state.valid();
                        if (wasDisabled) $state.prop('disabled', true);
                    }
                });

                // Ensure City shows an inline error even when left empty (like Country and State)
                $form.on('submit.cityCheck', function() {
                    var $city = $('#city-list');
                    // If city has no value, temporarily enable and trigger validation to render message
                    if (!$city.val()) {
                        var wasDisabled = $city.prop('disabled');
                        if (wasDisabled) $city.prop('disabled', false);
                        $city.valid();
                        if (wasDisabled) $city.prop('disabled', true);
                    }
                });

                // Don't validate city when form is first shown - only validate on submit
                // Removed automatic validation on offcanvas shown to prevent showing errors before user submits
                // Clear errors on change for select2 fields (manager, country, state, city)
                $('#manager_id, #country-list, #state-list, #city-list').on('change', function() {
                    if ($(this).val()) {
                        // Remove validation error messages
                        const $sel2 = $(this).next('.select2');
                        if ($sel2.length) {
                            $sel2.next('.text-error').remove();
                            $(this).siblings('.text-error').filter(function() {
                                var text = $(this).text().toLowerCase();
                                return text.indexOf('required') !== -1;
                            }).remove();
                        }
                    }
                });

                // Clear errors when user starts entering data in input fields
                $('#branch-form input[type="text"], #branch-form input[type="email"], #branch-form input[type="number"], #branch-form input[type="tel"], #branch-form textarea')
                    .on('input keyup', function() {
                        const $field = $(this);
                        const fieldName = $field.attr('name');
                        const fieldId = $field.attr('id');

                        // Clear error messages for this field
                        $field.siblings('.text-error').remove();
                        $field.next('.text-error').remove();
                        $field.nextAll('.text-error').remove();

                        // Special handling for contact_number - clear phone-error span
                        if (fieldName === 'contact_number' || fieldId === 'contact_number') {
                            $field.siblings('.phone-error').text('').removeClass('text-danger');
                        }

                        // Clear Select2 errors if applicable
                        if ($field.hasClass('select2-hidden-accessible')) {
                            $field.next('.select2').next('.text-error').remove();
                        }

                        // Hide jQuery validation errors
                        if ($.fn.validate && $field.data('validator')) {
                            $field.valid();
                        }
                    });

                // Specific handler for contact_number to clear errors immediately
                $('#contact_number').on('input keyup', function() {
                    const $field = $(this);
                    const $phoneError = $field.siblings('.phone-error');
                    const fieldValue = $field.val() || '';

                    // If field has any value (even partial), clear "required" error immediately
                    if (fieldValue.length > 0) {
                        const errorText = $phoneError.text() || '';
                        if (errorText.toLowerCase().indexOf('required') !== -1) {
                            $phoneError.text('').removeClass('text-danger');
                        }
                        // Also clear any jQuery validation errors
                        $field.siblings('.text-error').not('.phone-error').remove();
                        $field.next('.text-error').not('.phone-error').remove();
                        $field.nextAll('.text-error').not('.phone-error').remove();
                    }
                });

                // Clear errors for payment method checkboxes
                $('input[name="payment_method[]"]').on('change', function() {
                    const $container = $(this).closest('.form-group');
                    $container.find('.text-error').remove();
                });

                // Clear errors for branch_for radio buttons
                $('input[name="branch_for"]').on('change', function() {
                    const $container = $(this).closest('.form-group');
                    $container.find('.text-error').remove();
                });

                // Add custom validation method for decimal
                $.validator.addMethod("customDecimal", function(value, element) {
                    return validateDouble(value, $(element).attr('name'));
                }, "Please enter a decimal value (e.g., 121.32)");

                // Add custom validation method for digits - only check if field has value
                $.validator.addMethod("customDigits", function(value, element) {
                    // If field is empty, let required validation handle it
                    if (!value || value.trim() === '') {
                        return true;
                    }
                    // Check if value contains only digits
                    return /^[0-9]+$/.test(value);
                }, "Only numbers are allowed");

                // Live validation: keep message until a city is chosen
                $('#city-list').on('change', function() {
                    if ($(this).val()) {
                        var $n = $(this).next('.select2');
                        if ($n.next('.text-error').length) {
                            $n.next('.text-error').remove();
                        }
                    } else {
                        // Trigger validation to show message
                        $(this).valid();
                        // Only try to open Select2 if it's initialized
                        const $el = $(this);
                        if ($el.hasClass('select2-hidden-accessible') && $.fn.select2) {
                            try {
                                $el.select2('open');
                            } catch (e) {
                                // Ignore errors if Select2 is not properly initialized
                            }
                        }
                    }
                });
            }
        })();

        // Use jQuery AJAX like the Add Address modal
        if (window.$) {
            $('#country-list').on('change', function() {
                var countryId = $(this).val();
                var $state = $('#state-list');
                var $city = $('#city-list');
                if (countryId) {
                    $state.prop('disabled', false);
                    $.ajax({
                        url: "{{ route('backend.orders.getStates') }}",
                        type: 'GET',
                        data: {
                            country_id: countryId
                        },
                        success: function(data) {
                            $state.empty().append(
                                '<option value="" selected>{{ __('branch.select_state') }}</option>'
                            );
                            $.each(data, function(key, value) {
                                $state.append('<option value="' + key + '">' +
                                    value + '</option>');
                            });
                            $city.prop('disabled', true).empty().append(
                                '<option value="" selected>{{ __('branch.select_city') }}</option>'
                            );
                            if ($state.hasClass('select2-hidden-accessible')) $state
                                .trigger('change');
                            if ($city.hasClass('select2-hidden-accessible')) $city.trigger(
                                'change');
                        }
                    });
                } else {
                    $state.prop('disabled', true).empty().append(
                        '<option value="" selected>{{ __('branch.select_state') }}</option>');
                    $city.prop('disabled', true).empty().append(
                        '<option value="" selected>{{ __('branch.select_city') }}</option>');
                    if ($state.hasClass('select2-hidden-accessible')) $state.trigger('change');
                    if ($city.hasClass('select2-hidden-accessible')) $city.trigger('change');
                }
            });

            $('#state-list').on('change', function() {
                var stateId = $(this).val();
                var $city = $('#city-list');
                if (stateId) {
                    $city.prop('disabled', false);
                    $.ajax({
                        url: "{{ route('backend.orders.getCities') }}",
                        type: 'GET',
                        data: {
                            state_id: stateId
                        },
                        success: function(data) {
                            $city.empty().append(
                                '<option value="" selected>{{ __('branch.select_city') }}</option>'
                            );
                            $.each(data, function(key, value) {
                                $city.append('<option value="' + key + '">' +
                                    value + '</option>');
                            });
                            if ($city.hasClass('select2-hidden-accessible')) $city.trigger(
                                'change');
                        }
                    });
                } else {
                    $city.prop('disabled', true).empty().append(
                        '<option value="" selected>{{ __('branch.select_city') }}</option>');
                    if ($city.hasClass('select2-hidden-accessible')) $city.trigger('change');
                }
            });

            // City validation is now handled in the main validation block above
        }

        // Function to reset form to create mode
        function resetFormToCreate() {
            // Reset all form fields
            $('#branch-form')[0].reset();

            // Reset Select2 dropdowns
            $('#manager_id').val('').trigger('change');
            $('#services').val('').trigger('change');
            $('#country-list').val('').trigger('change');
            $('#state-list').val('').trigger('change');
            $('#city-list').val('').trigger('change');

            // Reset radio buttons
            $('input[name="branch_for"]').prop('checked', false);
            $('input[name="branch_for"][value="both"]').prop('checked', true); // Default to 'both'

            // Reset checkboxes
            $('input[name="payment_method[]"]').prop('checked', false);
            $('input[name="payment_method[]"][value="cash"]').prop('checked', true); // Default to 'cash'

            // Reset status checkbox
            $('#category-status').prop('checked', true);

            // Reset feature image
            var imgEl = document.querySelector('.upload-image-box img');
            if (imgEl) {
                var fallback = imgEl.getAttribute('data-default') || '{{ asset('images/default.png') }}';
                imgEl.src = fallback;
            }
            // Hide remove button when form is reset
            var removeBtn = document.getElementById('remove-image-btn');
            if (removeBtn) {
                removeBtn.style.display = 'none';
            }
            document.getElementById('feature_image').value = '';
            document.getElementById('remove_feature_image').value = '0';

            // Reset form action to create
            var $form = $('#branch-form');
            $form.attr('action', @json(route('backend.branch.store')));
            $form.find('input[name="_method"]').remove();

            // Update title to Create
            var titleEl = document.getElementById('form-offcanvasLabel');
            if (titleEl) {
                titleEl.textContent = @json($createTitle ?? __('messages.new') . ' ' . __('branch.singular_title'));
            }

            // Clear any validation errors
            $('.text-error').text('').removeClass('text-danger');

            console.log('✅ Form reset to create mode');
        }

        // Listen for edit open events and populate the form with existing branch data
        (function() {
            if (!window.$) return;
            document.addEventListener('crud_change_id', function(e) {
                var id = Number(e.detail && e.detail.form_id ? e.detail.form_id : 0);

                // Reset form for create mode (id = 0)
                if (!id) {
                    resetFormToCreate();
                    return;
                }

                var url = @json(route('backend.branch.edit', ':id')).replace(':id', id);
                $.ajax({
                    url: url,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }).done(function(res) {
                    if (!res || !res.status || !res.data) return;
                    var b = res.data;

                    // Helper: select by id, else by visible text
                    function selectByIdOrText($sel, id, text) {
                        if (id && $sel.find('option[value="' + id + '"]').length) {
                            $sel.val(String(id));
                        } else if (text) {
                            var match = $sel.find('option').filter(function() {
                                return $(this).text().trim() === String(text)
                                    .trim();
                            }).first();
                            if (match.length) {
                                $sel.val(match.val());
                            }
                        }
                        if ($sel.hasClass('select2-hidden-accessible')) $sel.trigger(
                            'change.select2');
                        else $sel.trigger('change');
                    }

                    // Basic - Branch Name
                    var branchName = b.name || '';
                    var $nameField = $('[name="name"]');
                    if (branchName && $nameField.length) {
                        $nameField.val(branchName);
                        console.log('Branch name set to:', branchName);
                    } else if (!branchName) {
                        console.warn('Branch name not found in response:', b);
                    } else if (!$nameField.length) {
                        console.error('Branch name field not found in form');
                    }

                    // Manager (support nested manager)
                    var managerId = b.manager_id || (b.manager && b.manager.id) || null;
                    if (managerId) {
                        var $m = $('#manager_id');
                        if ($m.find('option[value="' + managerId + '"]').length === 0) {
                            // Use provided manager_full_name if present, else try to locate label
                            var label = (b.manager_full_name || '').trim();
                            if (!label) {
                                label = $m.find('option[value]')?.filter(function() {
                                    return $(this).val() == String(managerId)
                                }).text() || 'Selected';
                            }
                            $m.append(new Option(label, managerId, true, true));
                        }
                        $m.val(String(managerId)).trigger('change.select2');
                    }

                    // Services
                    if (Array.isArray(b.service_id)) {
                        var s = b.service_id.map(String);
                        $('#services').val(s).trigger('change');
                    }

                    // Contact/email
                    $('#contact_number').val(b.contact_number || '');
                    $('[name="contact_email"]').val(b.contact_email || '');

                    // Address (support nested address)
                    var addr = b.address || {};
                    $('[name="address_line_1"]').val(b.address_line_1 || addr
                        .address_line_1 || '');
                    $('[name="address_line_2"]').val(b.address_line_2 || addr
                        .address_line_2 || '');

                    // Location: load dependent options, then select
                    var countryId = b.country || addr.country || '';
                    var stateId = b.state || addr.state || '';
                    var cityId = b.city || addr.city || '';
                    var $country = $('#country-list');
                    var $state = $('#state-list');
                    var $city = $('#city-list');
                    if (countryId) {
                        selectByIdOrText($country, String(countryId), b.country_name || (
                            addr && addr.country_name));
                        $.ajax({
                                url: "{{ route('backend.orders.getStates') }}",
                                type: 'GET',
                                data: {
                                    country_id: countryId
                                }
                            })
                            .done(function(data) {
                                $state.empty().append(
                                    '<option value="" selected>{{ __('branch.select_state') }}</option>'
                                );
                                $.each(data, function(k, v) {
                                    $state.append('<option value="' + k + '">' +
                                        v + '</option>');
                                });
                                if (stateId) {
                                    selectByIdOrText($state, String(stateId), b
                                        .state_name || (addr && addr.state_name));
                                    $.ajax({
                                            url: "{{ route('backend.orders.getCities') }}",
                                            type: 'GET',
                                            data: {
                                                state_id: stateId
                                            }
                                        })
                                        .done(function(cities) {
                                            $city.empty().append(
                                                '<option value="" selected>{{ __('branch.select_city') }}</option>'
                                            );
                                            $.each(cities, function(k, v) {
                                                $city.append(
                                                    '<option value="' +
                                                    k + '">' + v +
                                                    '</option>');
                                            });
                                            if (cityId) {
                                                selectByIdOrText($city, String(
                                                        cityId), b.city_name ||
                                                    (addr && addr.city_name));
                                                // Clear any validation errors after city is set
                                                setTimeout(function() {
                                                    $city.siblings(
                                                            '.text-error')
                                                        .filter(function() {
                                                            var text =
                                                                $(this)
                                                                .text()
                                                                .toLowerCase();
                                                            return text
                                                                .indexOf(
                                                                    'city'
                                                                ) !==
                                                                -1 ||
                                                                text
                                                                .indexOf(
                                                                    'required'
                                                                ) !==
                                                                -1;
                                                        }).remove();
                                                    $city.next('.select2')
                                                        .siblings(
                                                            '.text-error')
                                                        .filter(function() {
                                                            var text =
                                                                $(this)
                                                                .text()
                                                                .toLowerCase();
                                                            return text
                                                                .indexOf(
                                                                    'city'
                                                                ) !==
                                                                -1 ||
                                                                text
                                                                .indexOf(
                                                                    'required'
                                                                ) !==
                                                                -1;
                                                        }).remove();
                                                }, 100);
                                            }
                                        });
                                }
                            });
                    }

                    // Postal/geo
                    $('[name="postal_code"]').val(b.postal_code || addr.postal_code || '');
                    var latVal = (typeof b.latitude !== 'undefined' ? b.latitude : (addr
                        .latitude || addr.lat || ''));
                    var lngVal = (typeof b.longitude !== 'undefined' ? b.longitude : (addr
                        .longitude || addr.lng || addr.long || ''));
                    $('[name="latitude"]').val(latVal);
                    $('[name="longitude"]').val(lngVal);

                    // Payment methods
                    if (Array.isArray(b.payment_method)) {
                        var pm = b.payment_method;
                        $('[name="payment_method[]"]').each(function() {
                            this.checked = pm.indexOf(this.value) !== -1;
                        });
                    }

                    // Description/status
                    $('#description').val(b.description || '');
                    $('#category-status').prop('checked', !!b.status);

                    // Branch for
                    if (b.branch_for) {
                        $('input[name="branch_for"][value="' + b.branch_for + '"]').prop(
                            'checked', true);
                    }

                    // Title -> Edit Branch
                    var titleEl = document.getElementById('form-offcanvasLabel');
                    if (titleEl) {
                        titleEl.textContent = @json($editTitle ?? __('messages.edit') . ' ' . __('branch.singular_title'));
                    }

                    // Feature image
                    if (b.feature_image) {
                        var imgEl = document.querySelector('.upload-image-box img');
                        if (imgEl) imgEl.src = b.feature_image;
                    }

                    // Switch form action to update
                    var $form = $('#branch-form');
                    $form.attr('action', @json(route('backend.branch.update', ':id')).replace(':id', b.id));
                    if ($form.find('input[name="_method"]').length === 0) {
                        $form.prepend('<input type="hidden" name="_method" value="PUT">');
                    } else {
                        $form.find('input[name="_method"]').val('PUT');
                    }
                });
            });
        })();

        // Handle Create Manager submit via AJAX, then update Assign Manager dropdown
        (function() {
            if (!window.$) return;
            const $form = $('#create-manager-form');
            if (!$form.length) return;
            $form.on('submit', function(e) {
                e.preventDefault();
                // If client-side validation exists and fails, do not send AJAX (avoid duplicate messages)
                if ($.fn && $.fn.validate && !$form.valid()) {
                    return false;
                }
                const $btns = $form.find('button[type="submit"]');
                $btns.prop('disabled', true);

                // Ensure status is included - if checkbox is unchecked, explicitly set status to 0
                let formData = $form.serialize();
                const $statusCheckbox = $('#category-status');
                if (!$statusCheckbox.is(':checked')) {
                    // Remove existing status if present and add status=0
                    formData = formData.replace(/&?status=\d+/, '');
                    formData += (formData ? '&' : '') + 'status=0';
                }

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    headers: {
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        // Extract manager object flexibly
                        const manager = (response && (response.data || response
                            .manager || response)) || {};
                        const managerId = manager.id || manager.manager_id;
                        const managerName = manager.full_name || [manager.first_name,
                            manager.last_name
                        ].filter(Boolean).join(' ');
                        if (managerId && managerName) {
                            const $select = $('#manager_id');
                            if ($select.length) {
                                // Add or update option and select it
                                let existing = $select.find('option[value="' +
                                    managerId + '"]');
                                if (!existing.length) {
                                    $select.append(new Option(managerName, managerId,
                                        true, true));
                                } else {
                                    existing.text(managerName);
                                    $select.val(managerId);
                                }
                                if ($select.hasClass('select2-hidden-accessible')) {
                                    $select.trigger('change');
                                }
                            }
                        }
                        // Hide only the modal, keep offcanvas open
                        const modalEl = document.getElementById('exampleModal');
                        if (modalEl) {
                            try {
                                if (window.bootstrap && bootstrap.Modal) {
                                    const inst = bootstrap.Modal.getInstance(modalEl) ||
                                        new bootstrap.Modal(modalEl);
                                    inst.hide();
                                } else if (window.$) {
                                    $('#exampleModal').modal('hide');
                                }
                            } catch (err) {}
                        }
                        // Reset form for next time
                        $form[0].reset();
                    },
                    error: function(xhr) {
                        // Show inline field errors instead of global toast
                        var errBag = (xhr.responseJSON && (xhr.responseJSON.errors ||
                            xhr.responseJSON.all_message)) || {};
                        var idMap = {
                            first_name: '#manager_first_name',
                            last_name: '#manager_last_name',
                            email: '#manager_email',
                            mobile: '#manager_contact_number',
                            password: '#manager_password',
                            confirm_password: '#manager_password_confirmation'
                        };
                        Object.keys(idMap).forEach(function(key) {
                            var $el = $(idMap[key]);
                            if ($el.length) {
                                $el.siblings('.text-error.server').remove();
                            }
                        });
                        Object.keys(errBag).forEach(function(field) {
                            var $el = $(idMap[field]);
                            if ($el && $el.length) {
                                var msg = Array.isArray(errBag[field]) ? errBag[
                                    field][0] : errBag[field];
                                $('<div class="text-error text-danger mt-1 server"></div>')
                                    .text(msg).insertAfter($el);
                            }
                        });
                    },
                    complete: function() {
                        $btns.prop('disabled', false);
                    }
                });
            });
        })();
    });

    // Country, State, City Functions
    // Removed fetch-based helpers in favor of jQuery AJAX for consistency

    // Remove logo function
    function removeLogo() {
        document.getElementById('feature_image').value = '';
        const imgElement = document.querySelector('.upload-image-box img');
        if (imgElement) {
            const fallback = imgElement.getAttribute('data-default') || '{{ asset('images/default.png') }}';
            imgElement.src = fallback;
        }
        var rem = document.getElementById('remove_feature_image');
        if (rem) rem.value = '1';
    }

    // Character count functionality for description field
    function setupDescriptionCharacterCount() {
        const descriptionField = document.getElementById('description');
        const descriptionCount = document.getElementById('description_count');

        if (descriptionField && descriptionCount) {
            // Set initial count based on current value
            const initialLength = descriptionField.value.length;
            descriptionCount.textContent = initialLength;

            // Set initial color based on current length
            if (initialLength >= 225) {
                descriptionCount.style.color = '#dc3545'; // Red when close to limit
            } else if (initialLength >= 200) {
                descriptionCount.style.color = '#ffc107'; // Yellow when approaching limit
            } else {
                descriptionCount.style.color = '#6c757d'; // Default gray
            }

            // Update count on input
            descriptionField.addEventListener('input', function() {
                const currentLength = this.value.length;
                descriptionCount.textContent = currentLength;

                // Change color when approaching limit
                if (currentLength >= 225) {
                    descriptionCount.style.color = '#dc3545'; // Red when close to limit
                } else if (currentLength >= 200) {
                    descriptionCount.style.color = '#ffc107'; // Yellow when approaching limit
                } else {
                    descriptionCount.style.color = '#6c757d'; // Default gray
                }
            });
        }
    }

    // Initialize character count when document is ready
    document.addEventListener('DOMContentLoaded', function() {
        setupDescriptionCharacterCount();
    });

    // Also initialize when offcanvas opens (for edit mode)
    document.getElementById('form-offcanvas').addEventListener('shown.bs.offcanvas', function() {
        setupDescriptionCharacterCount();
    });

    // Reset form when offcanvas is hidden to ensure clean state
    document.getElementById('form-offcanvas').addEventListener('hidden.bs.offcanvas', function() {
        // Small delay to ensure the offcanvas is fully closed
        setTimeout(function() {
            resetFormToCreate();
        }, 100);
    });
</script>

<script>
    // Create Manager modal - jQuery Validate
    $(function() {
        if ($.fn && $.fn.validate) {
            $('#create-manager-form').validate({
                ignore: [],
                onkeyup: function(el) {
                    // Don't validate mobile and password fields on keyup to prevent error messages while typing
                    const fieldName = $(el).attr('name');
                    const fieldId = $(el).attr('id');
                    if (fieldName === 'mobile' || fieldId === 'manager_contact_number' ||
                        fieldName === 'password' || fieldId === 'manager_password' ||
                        fieldName === 'confirm_password' || fieldId ===
                        'manager_password_confirmation') {
                        return false;
                    }
                    $(el).valid();
                },
                onfocusout: function(el) {
                    // Don't validate mobile and password fields on focusout to prevent error messages while typing
                    const fieldName = $(el).attr('name');
                    const fieldId = $(el).attr('id');
                    if (fieldName === 'mobile' || fieldId === 'manager_contact_number' ||
                        fieldName === 'password' || fieldId === 'manager_password' ||
                        fieldName === 'confirm_password' || fieldId ===
                        'manager_password_confirmation') {
                        return false;
                    }
                    $(el).valid();
                },
                rules: {
                    first_name: {
                        required: true
                    },
                    last_name: {
                        required: true
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    mobile: {
                        required: true,
                        digits: true
                    },
                    password: {
                        required: true,
                        minlength: 8
                    },
                    confirm_password: {
                        required: true,
                        equalTo: '#manager_password'
                    }
                },
                messages: {
                    first_name: {
                        required: 'First name is required'
                    },
                    last_name: {
                        required: 'Last name is required'
                    },
                    email: {
                        required: 'Email is required',
                        email: 'Enter a valid email'
                    },
                    mobile: {
                        required: 'Contact number is required',
                        digits: 'Only numbers are allowed'
                    },
                    password: {
                        required: 'Password is required',
                        minlength: 'The password must be at least 8 characters'
                    },
                    confirm_password: {
                        required: 'Confirm your password',
                        equalTo: 'Passwords must match'
                    }
                },
                errorElement: 'div',
                errorClass: 'text-error text-danger mt-1',
                highlight: function(el) {
                    // Don't add is-invalid class to remove red border
                },
                unhighlight: function(el) {
                    // Clear error messages when field becomes valid
                    $(el).siblings('.text-error').remove();
                    $(el).next('.text-error').remove();
                    $(el).nextAll('.text-error').remove();
                },
                errorPlacement: function(error, element) {
                    // Ensure error message has text-danger class
                    error.addClass('text-danger');

                    // Remove any previous server-side error to avoid duplicates
                    element.siblings('.text-error.server').remove();
                    // Remove any duplicate client error inserted earlier
                    var $next = element.next('.text-error.mt-1');
                    if ($next.length) {
                        $next.remove();
                    }
                    // For manager mobile field, place error outside intl-tel-input container
                    if (element.attr('id') === 'manager_contact_number' || element.attr('name') ===
                        'mobile') {
                        const $itiContainer = element.closest('.iti');
                        if ($itiContainer.length) {
                            // Remove any existing inner error and place outside
                            $itiContainer.find('#manager_contact_number-error').remove();
                            error.attr('id', 'manager_contact_number-error');
                            error.insertAfter($itiContainer);
                            return;
                        }
                    }
                    error.insertAfter(element);
                }
            });

            // Clear errors when user starts entering data in manager form fields
            $('#create-manager-form input[type="text"], #create-manager-form input[type="email"], #create-manager-form input[type="tel"], #create-manager-form input[type="password"]')
                .on('input keyup', function() {
                    const $field = $(this);
                    const fieldName = $field.attr('name');
                    const fieldId = $field.attr('id');

                    // Clear error messages for this field
                    $field.siblings('.text-error').remove();
                    $field.next('.text-error').remove();
                    $field.nextAll('.text-error').remove();

                    // Hide jQuery validation errors
                    if ($.fn.validate && $field.data('validator')) {
                        $field.valid();
                    }
                });

            // Specific handler for manager contact_number to clear errors immediately
            $('#manager_contact_number').on('input keyup', function() {
                const $field = $(this);
                const fieldValue = $field.val() || '';

                // If field has any value (even partial), clear "required" error immediately
                if (fieldValue.length > 0) {
                    const errorText = $field.siblings('.text-error').text() || '';
                    if (errorText.toLowerCase().indexOf('required') !== -1) {
                        $field.siblings('.text-error').remove();
                        $field.next('.text-error').remove();
                        $field.nextAll('.text-error').remove();
                    }

                    // Hide jQuery validation errors
                    if ($.fn.validate && $field.data('validator')) {
                        $field.valid();
                    }
                }
            });

            // Clear errors for gender radio buttons
            $('#create-manager-form input[name="gender"]').on('change', function() {
                const $container = $(this).closest('.form-group');
                $container.find('.text-error').remove();
            });

            // Prevent submit + global snackbar if invalid
            $('#create-manager-form').on('submit', function(e) {
                var $form = $(this);
                // Clear server messages before validating to prevent duplicates
                $form.find('.text-error.server').remove();
                if (!$form.valid()) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }
            });
        }
    });
</script>
