<form id="logistic-zone-form" action="{{ route('backend.logistic-zones.store') }}" method="POST">
    @csrf
    <input type="hidden" name="id" id="lz_id">
    <div class="offcanvas offcanvas-end custom-offcanvas-width" tabindex="-1" id="form-offcanvas"
        aria-labelledby="form-offcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="form-offcanvasLabel">
                <span
                    id="lz-form-title">{{ $createTitle ?? __('messages.new') . ' ' . __('logistic_zone.singular_title') }}</span>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body">
            <div class="row g-3">
                <div class="form-group col-md-12">
                    <label class="form-label" for="lz_name">{{ __('logistic_zone.lbl_name') }} <span
                            class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="lz_name" name="name"
                        placeholder="{{ __('logistic_zone.name') }}">
                    <div class="invalid-feedback" id="lz-name-error"></div>
                </div>

                <div class="form-group col-md-12">
                    <label class="form-label" for="lz_logistic_id">{{ __('logistic_zone.logistic') }} <span
                            class="text-danger">*</span></label>
                    <select class="form-control select2" id="lz_logistic_id" name="logistic_id"
                        data-placeholder="{{ __('logistic_zone.logistic') }}"></select>
                    <div class="invalid-feedback" id="lz-logistic_id-error"></div>
                </div>

                <div class="form-group col-md-12">
                    <label class="form-label" for="lz_country_id">{{ __('logistic_zone.country') }} <span
                            class="text-danger">*</span></label>
                    <select class="form-control select2" id="lz_country_id" name="country_id"
                        data-placeholder="{{ __('logistic_zone.select_country') }}"></select>
                    <div class="invalid-feedback" id="lz-country_id-error"></div>
                </div>

                <div class="form-group col-md-12">
                    <label class="form-label" for="lz_state_id">{{ __('logistic_zone.state') }} <span
                            class="text-danger">*</span></label>
                    <select class="form-control select2" id="lz_state_id" name="state_id"
                        data-placeholder="{{ __('logistic_zone.select_state') }}"></select>
                    <div class="invalid-feedback" id="lz-state_id-error"></div>
                </div>

                <div class="form-group col-md-12">
                    <label class="form-label" for="lz_city_id">{{ __('logistic_zone.cities') }} <span
                            class="text-danger">*</span></label>
                    <select class="form-control select2" id="lz_city_id" name="city_id[]" multiple
                        data-placeholder="{{ __('logistic_zone.select_city') }}"></select>
                    <div class="invalid-feedback" id="lz-city_id-error"></div>
                </div>

                <div class="form-group col-md-12">
                    <label class="form-label"
                        for="lz_standard_delivery_charge">{{ __('logistic_zone.standard_delivery_charge') }}</label>
                    <input type="number" min="0" step="0.01" class="form-control"
                        id="lz_standard_delivery_charge" name="standard_delivery_charge" placeholder="0.00">
                    <div class="invalid-feedback" id="lz-standard_delivery_charge-error"></div>
                </div>

                <div class="form-group col-md-12">
                    <label class="form-label"
                        for="lz_standard_delivery_time">{{ __('logistic_zone.standard_delivery_time') }}</label>
                    <input type="text" class="form-control" id="lz_standard_delivery_time"
                        name="standard_delivery_time" placeholder="{{ __('logistic_zone.delivery_time') }}">
                    <div class="invalid-feedback" id="lz-standard_delivery_time-error"></div>
                </div>
            </div>

        </div>

        <div class="offcanvas-footer p-3 border-top">
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="offcanvas">
                    {{ __('messages.cancel') }}
                </button>
                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2" id="lz-submit-btn" form="logistic-zone-form">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    <span id="lz-submit-text">{{ __('messages.save') }}</span>
                </button>
            </div>
        </div>
    </div>
</form>

@push('after-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('logistic-zone-form');
            const submitBtn = document.getElementById('lz-submit-btn');
            const submitText = document.getElementById('lz-submit-text');
            const spinner = submitBtn.querySelector('.spinner-border');

            const idField = document.getElementById('lz_id');
            const titleEl = document.getElementById('lz-form-title');

            const nameField = document.getElementById('lz_name');
            const logisticField = document.getElementById('lz_logistic_id');
            const countryField = document.getElementById('lz_country_id');
            const stateField = document.getElementById('lz_state_id');
            const cityField = document.getElementById('lz_city_id');
            const chargeField = document.getElementById('lz_standard_delivery_charge');
            const timeField = document.getElementById('lz_standard_delivery_time');

            function addLog(message) {
                const logEl = document.getElementById('lz-event-log');
                if (!logEl) return;
                const stamp = new Date().toLocaleTimeString();
                const item = document.createElement('div');
                item.textContent = `[${stamp}] ${message}`;
                logEl.prepend(item);
            }
            (function bindClearLog() {
                const btn = document.getElementById('lz-clear-log');
                if (btn) btn.addEventListener('click', () => {
                    const logEl = document.getElementById('lz-event-log');
                    if (logEl) logEl.innerHTML = '';
                });
            })();

            function setLoadingState(loading) {
                submitBtn.disabled = loading;
                if (loading) {
                    spinner.classList.remove('d-none');
                    submitText.textContent = '{{ __('messages.saving') }}';
                } else {
                    spinner.classList.add('d-none');
                    submitText.textContent = '{{ __('messages.save') }}';
                }
            }

            function clearErrors() {
                document.querySelectorAll('#logistic-zone-form .is-invalid').forEach(f => f.classList.remove(
                    'is-invalid'));
                document.querySelectorAll('#logistic-zone-form .invalid-feedback').forEach(e => e.textContent = '');
            }

            // jQuery Validate - inline errors right after inputs (like Categories)
            if (window.$ && $.fn && $.fn.validate) {
                const fieldErrorIds = {
                    name: 'lz-name-error',
                    logistic_id: 'lz-logistic_id-error',
                    country_id: 'lz-country_id-error',
                    state_id: 'lz-state_id-error',
                    'city_id[]': 'lz-city_id-error',
                    standard_delivery_charge: 'lz-standard_delivery_charge-error',
                    standard_delivery_time: 'lz-standard_delivery_time-error'
                };

                $('#logistic-zone-form').validate({
                    ignore: [],
                    onkeyup: function(element){ $(element).valid(); },
                    onfocusout: function(element){ $(element).valid(); },
                    errorElement: 'div',
                    errorClass: 'text-danger',
                    rules: {
                        name: { required: true },
                        logistic_id: { required: true },
                        country_id: { required: true },
                        state_id: { required: true },
                        'city_id[]': { required: true },
                        standard_delivery_charge: { number: true, min: 0 }
                    },
                    messages: {
                        name: { required: 'Name is a required field' },
                        logistic_id: { required: 'Logistic is a required field' },
                        country_id: { required: 'Country is a required field' },
                        state_id: { required: 'State is a required field' },
                        'city_id[]': { required: 'At least one city must be selected' }
                    },
                    errorPlacement: function(error, element) {
                        const name = element.attr('name');
                        const errorId = fieldErrorIds[name];
                        if (errorId) {
                            const $target = $('#' + errorId);
                            $target.text(error.text());
                            $target.addClass('d-block');
                            $target.css('color', '#dc3545');
                        } else {
                            error.insertAfter(element);
                        }
                    },
                    highlight: function(element) {
                        $(element).addClass('is-invalid');
                        if ($(element).hasClass('select2-hidden-accessible')) {
                            $(element).next('.select2-container').find('.select2-selection').addClass('is-invalid');
                        }
                    },
                    unhighlight: function(element) {
                        $(element).removeClass('is-invalid');
                        if ($(element).hasClass('select2-hidden-accessible')) {
                            $(element).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
                        }
                        const name = $(element).attr('name');
                        const errorId = fieldErrorIds[name];
                        if (errorId) {
                            const $target = $('#' + errorId);
                            $target.text('');
                            $target.removeClass('d-block');
                        }
                    }
                });

                // Revalidate on select2 changes to show inline messages instantly
                [logisticField, countryField, stateField, cityField].forEach(function(el) {
                    if (window.$) {
                        $(el).on('change.select2 change', function(){
                            $(this).valid();
                        });
                    }
                });
            }

            function showFieldError(fieldId, message) {
                const field = document.getElementById(fieldId);
                const errorDiv = document.getElementById(fieldId.replace('lz_', 'lz-') + '-error');
                if (field && errorDiv) {
                    field.classList.add('is-invalid');
                    errorDiv.textContent = message;
                    errorDiv.style.color = '#dc3545';
                }
            }

            // Helpers to init select2 if available
            function initSelect2(el) {
                if (!(window.$ && $.fn && $.fn.select2)) return;
                const $el = $(el);
                try {
                    if ($el.data('select2')) {
                        $el.select2('destroy');
                    }
                } catch (_) {}
                $el.select2({
                    width: '100%'
                });
                $el.addClass('d-none');
                // Remove any duplicate containers left behind
                const $siblings = $el.nextAll('.select2');
                if ($siblings.length > 1) {
                    $siblings.slice(1).remove();
                }
            }
            [logisticField, countryField, stateField, cityField].forEach(initSelect2);
            document.addEventListener('shown.bs.offcanvas', function(e) {
                if (e.target && e.target.id === 'form-offcanvas') {
                    [logisticField, countryField, stateField, cityField].forEach(initSelect2);
                }
            });

            // Fetch helpers that are resilient to different shapes
            async function fetchOptions(url) {
                try {
                    const res = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const json = await res.json();
                    let arr = [];
                    if (Array.isArray(json)) arr = json;
                    else if (Array.isArray(json?.data)) arr = json.data;
                    else if (json && typeof json === 'object') {
                        const src = json.data && typeof json.data === 'object' && !Array.isArray(json.data) ?
                            json.data : json;
                        arr = Object.entries(src).map(([id, name]) => ({
                            id,
                            text: name
                        }));
                    }
                    return arr.map(o => ({
                        id: o.id,
                        text: o.text || o.name
                    }));
                } catch (e) {
                    addLog('Fetch failed: ' + url);
                    return [];
                }
            }

            function populateSelect(selectEl, options, selected = null, isMultiple = false) {
                selectEl.innerHTML = '';
                if (!isMultiple) {
                    const opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = selectEl.getAttribute('data-placeholder') || '{{ __('messages.select') }}';
                    selectEl.appendChild(opt);
                }
                const selectedSet = new Set(
                    Array.isArray(selected) ?
                    selected.map(v => String(v)) :
                    (selected !== null && selected !== undefined ? [String(selected)] : [])
                );
                options.forEach(o => {
                    const opt = document.createElement('option');
                    opt.value = o.id;
                    opt.textContent = o.text;
                    if (selectedSet.size > 0 && selectedSet.has(String(o.id))) {
                        opt.selected = true;
                    }
                    selectEl.appendChild(opt);
                });
                if (window.$ && $(selectEl).data && $(selectEl).data('select2')) {
                    const $sel = $(selectEl);
                    try {
                        $sel.select2('destroy');
                    } catch (_) {}
                    $sel.select2({
                        width: '100%'
                    });
                    $sel.addClass('d-none');
                    const $siblings = $sel.nextAll('.select2');
                    if ($siblings.length > 1) {
                        $siblings.slice(1).remove();
                    }
                    if (selectedSet.size > 0) {
                        $sel.val(Array.from(selectedSet));
                        $sel.trigger('change.select2');
                    }
                }
            }

            async function loadLogistics(selected) {
                const url = '{{ route('backend.logistics.index_list') }}';
                const options = await fetchOptions(url);
                populateSelect(logisticField, options, selected);
                addLog('Logistics loaded');
            }
            async function loadCountries(selected) {
                const url = '{{ route('backend.country.index_list') }}';
                const options = await fetchOptions(url);
                populateSelect(countryField, options, selected);
                addLog('Countries loaded');
            }
            async function loadStates(countryId, selected) {
                if (!countryId) {
                    populateSelect(stateField, [], null);
                    return;
                }
                stateField.setAttribute('data-placeholder', 'Loading states...');
                populateSelect(stateField, [], null);
                const url = '{{ route('backend.orders.getStates') }}' + '?country_id=' + countryId;
                addLog('Fetching states → ' + url);
                const options = await fetchOptions(url);
                addLog('States options: ' + options.length);
                stateField.setAttribute('data-placeholder', '{{ __('logistic_zone.select_state') }}');
                populateSelect(stateField, options, selected);
                addLog('States loaded for country ' + countryId);
            }
            async function loadCities(stateId, selectedArr) {
                if (!stateId) {
                    populateSelect(cityField, [], null, true);
                    return;
                }
                cityField.setAttribute('data-placeholder', 'Loading cities...');
                populateSelect(cityField, [], null, true);
                const url = '{{ route('backend.orders.getCities') }}' + '?state_id=' + stateId;
                addLog('Fetching cities → ' + url);
                const options = await fetchOptions(url);
                addLog('Cities options: ' + options.length);
                cityField.setAttribute('data-placeholder', '{{ __('logistic_zone.select_city') }}');
                populateSelect(cityField, options, Array.isArray(selectedArr) ? selectedArr : [], true);
                addLog('Cities loaded for state ' + stateId);
            }

            countryField.addEventListener('change', function() {
                loadStates(this.value, null).then(() => {
                    populateSelect(cityField, [], [], true);
                });
                const txt = this.options[this.selectedIndex]?.text || this.value;
                addLog('Country changed to ' + (txt || '-'));
            });
            stateField.addEventListener('change', function() {
                loadCities(this.value, []);
                const txt = this.options[this.selectedIndex]?.text || this.value;
                addLog('State changed to ' + (txt || '-'));
            });
            // Also react to Select2 change events
            if (window.$) {
                $(stateField).on('select2:select', function(e) {
                    const id = e.params.data.id;
                    loadCities(id, []);
                    addLog('State changed (select2) to ' + (e.params.data.text || id));
                });
                $(countryField).on('select2:select', function(e) {
                    const id = e.params.data.id;
                    loadStates(id, null).then(() => populateSelect(cityField, [], [], true));
                    addLog('Country changed (select2) to ' + (e.params.data.text || id));
                });
            }
            logisticField.addEventListener('change', function() {
                const txt = this.options[this.selectedIndex]?.text || this.value;
                addLog('Logistic changed to ' + (txt || '-'));
            });
            cityField.addEventListener('change', function() {
                const selected = Array.from(this.selectedOptions || []).map(o => o.text).join(', ');
                addLog('Cities changed: ' + (selected || '-'));
            });

            function resetForm() {
                form.reset();
                clearErrors();
                idField.value = '';
                nameField.value = '';
                chargeField.value = '';
                timeField.value = '1 Day';
                if (titleEl) titleEl.textContent =
                    '{{ $createTitle ?? __('messages.new') . ' ' . __('logistic_zone.singular_title') }}';
                // Clear selects
                populateSelect(logisticField, []);
                populateSelect(countryField, []);
                populateSelect(stateField, []);
                populateSelect(cityField, [], [], true);
                // Reload base options
                loadLogistics();
                loadCountries();
            }

            async function fillFormForEdit(data) {
                idField.value = data.id;
                nameField.value = data.name || '';
                // Ensure standard_delivery_charge shows 0 if null/empty
                chargeField.value = data.standard_delivery_charge !== null && data.standard_delivery_charge !== undefined && data.standard_delivery_charge !== '' 
                    ? data.standard_delivery_charge 
                    : '0';
                timeField.value = data.standard_delivery_time || '1 Day';
                if (titleEl) titleEl.textContent =
                    '{{ $editTitle ?? __('messages.edit') . ' ' . __('logistic_zone.singular_title') }}';

                await loadLogistics(data.logistic_id || '');
                await loadCountries(data.country_id || '');
                await loadStates(data.country_id || '', data.state_id || '');
                const selectedCities = Array.isArray(data.city_id) ? data.city_id.map(String) : [];
                await loadCities(data.state_id || '', selectedCities);
                if (window.$) {
                    $(cityField).val(selectedCities).trigger('change.select2');
                }
            }

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (submitBtn.disabled) return;
                clearErrors();
                if (window.$ && $.fn && $.fn.validate) {
                    const $form = $('#logistic-zone-form');
                    if (!$form.valid()) {
                        return; // keep inline errors visible; no toast
                    }
                }
                setLoadingState(true);

                const formData = new FormData(form);
                
                // Ensure standard_delivery_charge is not null/empty - convert to 0 if empty
                const deliveryCharge = chargeField.value.trim();
                if (!deliveryCharge || deliveryCharge === '') {
                    formData.set('standard_delivery_charge', '0');
                } else {
                    formData.set('standard_delivery_charge', deliveryCharge);
                }
                
                // Ensure standard_delivery_time has a default value if empty
                const deliveryTime = timeField.value.trim();
                if (!deliveryTime || deliveryTime === '') {
                    formData.set('standard_delivery_time', '1 Day');
                } else {
                    formData.set('standard_delivery_time', deliveryTime);
                }
                
                const id = idField.value;
                let url = '';
                if (id) {
                    url = '{{ route('backend.logistic-zones.update', ':id') }}'.replace(':id', id);
                    formData.append('_method', 'PUT');
                } else {
                    url = '{{ route('backend.logistic-zones.store') }}';
                }

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                    },
                    body: formData
                }).then(async (res) => {
                    const text = await res.text();
                    let data;
                    try {
                        data = text ? JSON.parse(text) : {};
                    } catch (e) {
                        data = {
                            status: false,
                            message: 'Invalid response'
                        };
                    }
                    if (data.status) {
                        if (window.successSnackbar) window.successSnackbar(data.message);
                        if (typeof renderedDataTable !== 'undefined') {
                            renderedDataTable.ajax.reload(null, false);
                        }
                        const ocEl = document.getElementById('form-offcanvas');
                        const instance = bootstrap.Offcanvas.getOrCreateInstance(ocEl);
                        instance.hide();
                        resetForm();
                        addLog('Saved successfully');
                    } else {
                        if (window.errorSnackbar) window.errorSnackbar(data.message || 'Error');
                        if (data.all_message) {
                            Object.keys(data.all_message).forEach(field => {
                                const fieldIdMap = {
                                    name: 'lz_name',
                                    logistic_id: 'lz_logistic_id',
                                    country_id: 'lz_country_id',
                                    state_id: 'lz_state_id',
                                    city_id: 'lz_city_id',
                                    standard_delivery_charge: 'lz_standard_delivery_charge',
                                    standard_delivery_time: 'lz_standard_delivery_time'
                                };
                                const fieldId = fieldIdMap[field] || field;
                                showFieldError(fieldId, data.all_message[field][0]);
                            });
                        }
                    }
                }).catch(() => {
                    if (window.errorSnackbar) window.errorSnackbar(
                        'An error occurred while processing your request.');
                }).finally(() => setLoadingState(false));
            });

            // Listen to crud_change_id to open in edit mode
            document.addEventListener('crud_change_id', function(e) {
                const id = Number(e.detail.form_id || 0);
                if (id > 0) {
                    const url = '{{ route('backend.logistic-zones.edit', ':id') }}'.replace(':id', id);
                    fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.status && res.data) {
                                fillFormForEdit(res.data);
                            } else {
                                if (window.errorSnackbar) window.errorSnackbar(res.message || 'Error');
                            }
                        }).catch(() => {
                            if (window.errorSnackbar) window.errorSnackbar(
                                'An error occurred while loading data');
                        });
                    addLog('Opening for edit #' + id);
                } else {
                    resetForm();
                }
            });

            // Initialize base options on load
            resetForm();
            addLog('Form initialized');

            // Reset on offcanvas hide
            const oc = document.getElementById('form-offcanvas');
            oc.addEventListener('hidden.bs.offcanvas', function() {
                resetForm();
                addLog('Offcanvas closed');
            });
        });
    </script>
@endpush
