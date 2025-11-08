<form method="POST" action="{{ isset($branch) ? route('branch.update', $branch->id) : route('branch.store') }}"
    enctype="multipart/form-data">
    @csrf
    @if (isset($branch))
        @method('PUT')
    @endif
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
                    <!-- Branch Name -->
                    <div class="form-group col-md-12">
                        <label for="name" class="form-label">{{ __('branch.lbl_branch_name') }}</label>
                        <input type="text" name="name" class="form-control"
                            placeholder="{{ __('branch.branch_name') }}" value="{{ old('name', $branch->name ?? '') }}"
                            required>
                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Branch For -->
                    <div class="form-group col-md-12">
                        <label class="form-label">{{ __('branch.lbl_branch_for') }}</label>
                        <div class="btn-group w-100" role="group">
                            @foreach ($BRANCH_FOR_OPTIONS as $item)
                                <input type="radio" class="btn-check" name="branch_for" id="{{ $item['id'] }}-for"
                                    value="{{ $item['id'] }}"
                                    {{ old('branch_for', $branch->branch_for ?? 'both') == $item['id'] ? 'checked' : '' }}
                                    autocomplete="off">
                                <label class="btn btn-check-label"
                                    for="{{ $item['id'] }}-for">{{ $item['text'] }}</label>
                            @endforeach
                        </div>
                    </div>
                    <!-- Feature Image -->
                    <div class="form-group col-md-6">
                        <div class="text-center upload-image-box">
                            <img src="{{ old('feature_image', $branch->feature_image ?? ($defaultImage ?? asset('images/default.png'))) }}"
                                alt="feature-image" class="img-fluid mb-2 avatar-140 rounded" />
                            @if (session('validationMessage'))
                                <div class="text-danger mb-2">{{ session('validationMessage') }}</div>
                            @endif
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <input type="file" class="form-control d-none" id="feature_image"
                                    name="feature_image" />
                                <label class="btn btn-sm btn-primary"
                                    for="feature_image">{{ __('messages.upload') }}</label>
                                @if (old('feature_image', $branch->feature_image ?? false))
                                    <input type="button" class="btn btn-sm btn-secondary" name="remove"
                                        value="{{ __('messages.remove') }}"
                                        onclick="document.getElementById('feature_image').value = ''" />
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- Manager -->
                    <div class="form-group col-md-12">
                        <div class="d-flex justify-content-between">
                            <label for="manager_id">{{ __('branch.lbl_select_manager') }} <span
                                    class="text-danger">*</span></label>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#exampleModal"
                                class="btn btn-sm text-primary"><i class="fa-solid fa-plus"></i>
                                {{ __('messages.create') }} {{ __('messages.new') }}</button>
                        </div>
                        <select name="manager_id" id="manager_id" class="form-control">
                            <option value="">{{ __('branch.assign_manager') }}</option>
                            @foreach ($managers as $manager)
                                <option value="{{ $manager->id }}"
                                    {{ old('manager_id', $branch->manager_id ?? '') == $manager->id ? 'selected' : '' }}>
                                    {{ $manager->name }}</option>
                            @endforeach
                        </select>
                        @error('manager_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Service -->
                    <div class="form-group col-md-12">
                        <label class="form-label" for="services">{{ __('branch.lbl_select_service') }}</label>
                        <select name="service_id[]" id="services" class="form-control" multiple>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}"
                                    {{ collect(old('service_id', $branch->service_id ?? []))->contains($service->id) ? 'selected' : '' }}>
                                    {{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Contact Number -->
                    <div class="form-group col-md-6">
                        <label class="form-label">{{ __('branch.lbl_contact_number') }} <span
                                class="text-danger">*</span></label>
                        <input type="text" name="contact_number" class="form-control"
                            value="{{ old('contact_number', $branch->contact_number ?? '') }}" maxlength="15" required>

                    </div>
                    @error('contact_number')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                    <!-- Contact Email -->
                    <div class="form-group col-md-6">
                        <label class="form-label">{{ __('branch.lbl_contact_email') }}</label>
                        <input type="email" name="contact_email" class="form-control"
                            placeholder="{{ __('branch.enter_email') }}"
                            value="{{ old('contact_email', $branch->contact_email ?? '') }}" required>
                        @error('contact_email')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Shop Number -->
                    <div class="form-group col-md-6">
                        <label class="form-label">{{ __('branch.lbl_shop_number') }}</label>
                        <input type="text" name="address_line_1" class="form-control"
                            placeholder="{{ __('branch.enter_landmark') }}"
                            value="{{ old('address_line_1', $branch->address_line_1 ?? '') }}" required>
                        @error('address_line_1')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Landmark -->
                    <div class="form-group col-md-6">
                        <label class="form-label">{{ __('branch.lbl_landmark') }}</label>
                        <input type="text" name="address_line_2" class="form-control"
                            placeholder="{{ __('branch.enter_nearby') }}"
                            value="{{ old('address_line_2', $branch->address_line_2 ?? '') }}">
                        @error('address_line_2')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Country -->
                    <div class="col-md-3 form-group">
                        <label class="form-label">{{ __('branch.lbl_country') }} <span
                                class="text-danger">*</span></label>
                        <select name="country" id="country-list" class="form-control" required>
                            <option value="">{{ __('branch.select_country') }}</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->id }}"
                                    {{ old('country', $branch->country ?? '') == $country->id ? 'selected' : '' }}>
                                    {{ $country->name }}</option>
                            @endforeach
                        </select>
                        @error('country')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- State -->
                    <div class="col-md-3 form-group">
                        <label class="form-label">{{ __('branch.lbl_state') }} <span
                                class="text-danger">*</span></label>
                        <select name="state" id="state-list" class="form-control" required>
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
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- City -->
                    <div class="col-md-3 form-group">
                        <label class="form-label">{{ __('branch.lbl_city') }} <span
                                class="text-danger">*</span></label>
                        <select name="city" id="city-list" class="form-control" required>
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
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Postal Code -->
                    <div class="form-group col-md-3">
                        <label class="form-label" for="postal_code">{{ __('branch.lbl_postal_code') }}</label>
                        <input type="text" name="postal_code" class="form-control"
                            placeholder="{{ __('branch.select_code') }}"
                            value="{{ old('postal_code', $branch->postal_code ?? '') }}" required>
                        @error('postal_code')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Latitude -->
                    <div class="form-group col-md-3">
                        <label class="form-label">{{ __('branch.lbl_lat') }}</label>
                        <input type="text" name="latitude" class="form-control"
                            placeholder="{{ __('branch.enter_latitutude') }}"
                            value="{{ old('latitude', $branch->latitude ?? '') }}" required>
                        @error('latitude')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Longitude -->
                    <div class="form-group col-md-3">
                        <label class="form-label">{{ __('branch.lbl_long') }}</label>
                        <input type="text" name="longitude" class="form-control"
                            placeholder="{{ __('branch.enter_logtitude') }}"
                            value="{{ old('longitude', $branch->longitude ?? '') }}" required>
                        @error('longitude')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Payment Methods -->
                    <div class="form-group col-md-6">
                        <label class="form-label" for="payment-method">{{ __('branch.lbl_payment_method') }} <span
                                class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-3 form-control">
                            @foreach ($PAYMENT_METHODS_OPTIONS as $item)
                                <div class="d-flex gap-1 form-check">
                                    <input type="checkbox" class="form-check-input mt-0"
                                        id="{{ $item['id'] }}-payment-method" name="payment_method[]"
                                        value="{{ $item['id'] }}"
                                        {{ is_array(old('payment_method', $branch->payment_method ?? ['cash'])) && in_array($item['id'], old('payment_method', $branch->payment_method ?? ['cash'])) ? 'checked' : '' }}
                                        autocomplete="off">
                                    <label class="form-label mb-0"
                                        for="{{ $item['id'] }}-payment-method">{{ $item['text'] }}</label>
                                </div>
                            @endforeach
                        </div>
                        @error('payment_method')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Description -->
                    <div class="form-group col-md-12">
                        <label class="form-label" for="description">{{ __('branch.lbl_description') }}</label>
                        <textarea class="form-control" name="description" placeholder="{{ __('branch.enter_decription') }}"
                            id="description">{{ old('description', $branch->description ?? '') }}</textarea>
                        @error('description')
                            <span class="text-danger">{{ $message }}</span>
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
                                <input class="form-check-input mt-0" name="status" id="category-status"
                                    type="checkbox" value="1"
                                    {{ old('status', $branch->status ?? true) ? 'checked' : '' }} />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="offcanvas-footer p-3">
            <button type="submit" class="btn btn-primary">
                @if (isset($IS_SUBMITED) && $IS_SUBMITED)
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    {{ __('messages.submitting') }}
                @else
                    {{ __('messages.save') }}
                @endif
            </button>
            <button type="button" class="btn btn-secondary"
                data-bs-dismiss="offcanvas">{{ __('messages.cancel') }}</button>
        </div>
    </div>
</form>
<!-- Employee Create Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ __('messages.create') }} {{ __('messages.new') }}
                    {{ __('branch.lbl_select_manager') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('employee.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="employeeName" class="form-label">{{ __('messages.name') }}</label>
                        <input type="text" class="form-control" id="employeeName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="employeeEmail" class="form-label">{{ __('messages.email') }}</label>
                        <input type="email" class="form-control" id="employeeEmail" name="email" required>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    // Add event listeners when the document is ready
    document.addEventListener('DOMContentLoaded', function() {
        const countrySelect = document.getElementById('country-list');
        const stateSelect = document.getElementById('state-list');
        const citySelect = document.getElementById('city-list');

        if (countrySelect) {
            countrySelect.addEventListener('change', function() {
                const countryId = this.value;
                if (countryId) {
                    getStates(countryId);
                } else {
                    // Reset state and city dropdowns
                    stateSelect.innerHTML =
                        '<option value="">{{ __('branch.select_state') }}</option>';
                    citySelect.innerHTML = '<option value="">{{ __('branch.select_city') }}</option>';
                }
            });
        }

        if (stateSelect) {
            stateSelect.addEventListener('change', function() {
                const stateId = this.value;
                if (stateId) {
                    getCities(stateId);
                } else {
                    // Reset city dropdown
                    citySelect.innerHTML = '<option value="">{{ __('branch.select_city') }}</option>';
                }
            });
        }
    });

    function getStates(countryId) {
        fetch('/api/states?country_id=' + countryId)
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    let stateList = document.getElementById('state-list');
                    stateList.innerHTML = '<option value="">{{ __('branch.select_state') }}</option>';
                    data.data.forEach(state => {
                        stateList.innerHTML += `<option value="${state.id}">${state.name}</option>`;
                    });
                    // Reset city dropdown
                    document.getElementById('city-list').innerHTML =
                        '<option value="">{{ __('branch.select_city') }}</option>';
                } else {
                    console.error('Error fetching states:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function getCities(stateId) {
        fetch('/api/cities?state_id=' + stateId)
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    let cityList = document.getElementById('city-list');
                    cityList.innerHTML = '<option value="">{{ __('branch.select_city') }}</option>';
                    data.data.forEach(city => {
                        cityList.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                    });
                } else {
                    console.error('Error fetching cities:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
</script>
