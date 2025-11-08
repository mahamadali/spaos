<form id="logistic-form" action="{{ route('backend.logistics.store') }}" method="POST" enctype="multipart/form-data" novalidate>
    @csrf
    <input type="hidden" name="id" id="logistic_id">
    <div class="offcanvas offcanvas-end custom-offcanvas-width" tabindex="-1" id="form-offcanvas" aria-labelledby="form-offcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="form-offcanvasLabel">
                <span id="logistic-form-title">{{ $createTitle ?? __('messages.new') . ' ' . __('logistics.singular_title') }}</span>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body">
            <div class="row g-3">
                <div class="form-group col-md-12">
                    <div class="text-center upload-image-box">
                        <img src="{{ $defaultImage ?? default_feature_image() }}" alt="feature-image" class="img-fluid mb-2 avatar-140 rounded" id="feature-image-preview" />
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <input type="file" class="form-control d-none" id="feature_image" name="feature_image" accept=".jpeg, .jpg, .png, .gif" />
                            <label class="btn btn-sm btn-primary" for="feature_image">{{ __('messages.upload') }}</label>
                            <button type="button" class="btn btn-sm btn-secondary d-none" id="remove-image-btn">{{ __('messages.remove') }}</button>
                        </div>
                        <div class="text-danger mt-2 d-none" id="image-validation"></div>
                        <input type="hidden" name="remove_feature_image" id="remove_feature_image" value="0" />
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="form-label" for="logistic_name">{{ __('service.lbl_name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="logistic_name" name="name" placeholder="{{ __('logistic_zone.name') }}" required>
                    <div class="invalid-feedback" id="name-error"></div>
                </div>

                @if(isset($customefield) && count($customefield))
                    @foreach($customefield as $field)
                        @include('helpers.custom-field.form-element', [
                            'name' => $field['name'],
                            'label' => $field['label'],
                            'type' => $field['type'],
                            'required' => $field['required'],
                            'options' => $field['value'],
                            'field_id' => $field['id'],
                            'value' => old($field['name'])
                        ])
                    @endforeach
                @endif

                <div class="form-group col-md-12">
                    <div class="d-flex justify-content-between align-items-center form-control">
                        <label class="form-label mb-0" for="logistic-status">{{ __('service.lbl_status') }}</label>
                        <div class="form-check form-switch">
                            <input type="hidden" name="status" value="0" />
                            <input class="form-check-input" type="checkbox" name="status" id="logistic-status" value="1" checked />
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
                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2" id="logistic-submit-btn" form="logistic-form">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    <span id="logistic-submit-text">{{ __('messages.save') }}</span>
                </button>
            </div>
        </div>
    </div>
</form>

@push('after-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('logistic-form');
    const submitBtn = document.getElementById('logistic-submit-btn');
    const submitText = document.getElementById('logistic-submit-text');
    const spinner = submitBtn.querySelector('.spinner-border');
    const titleEl = document.getElementById('logistic-form-title');

    const idField = document.getElementById('logistic_id');
    const nameField = document.getElementById('logistic_name');
    const statusField = document.getElementById('logistic-status');

    const imageInput = document.getElementById('feature_image');
    const imagePreview = document.getElementById('feature-image-preview');
    const removeImageBtn = document.getElementById('remove-image-btn');
    const imageValidation = document.getElementById('image-validation');
    const removeImageFlag = document.getElementById('remove_feature_image');

    function setLoading(loading) {
        submitBtn.disabled = loading;
        spinner.classList.toggle('d-none', !loading);
        submitText.textContent = loading ? '{{ __('messages.saving') }}' : '{{ __('messages.save') }}';
    }

    function clearErrors() {
        document.querySelectorAll('#logistic-form .is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.getElementById('name-error').textContent = '';
    }

    function showFieldError(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (field) field.classList.add('is-invalid');
        const err = document.getElementById(fieldId === 'logistic_name' ? 'name-error' : fieldId + '-error');
        if (err) err.textContent = message || '';
    }

    function resetForm() {
        form.reset();
        clearErrors();
        idField.value = '';
        if (titleEl) titleEl.textContent = '{{ $createTitle ?? __('messages.new') . ' ' . __('logistics.singular_title') }}';
        imageValidation.classList.add('d-none');
        removeImageBtn.classList.add('d-none');
        imagePreview.src = @json($defaultImage ?? default_feature_image());
        form.action = @json(route('backend.logistics.store'));
        removeImageFlag.value = '0';
    }

    function setEdit(data) {
        idField.value = data.id;
        nameField.value = data.name || '';
        statusField.checked = !!data.status;
        if (titleEl) titleEl.textContent = '{{ __('messages.edit') }} {{ __('logistics.singular_title') }}';
        if (data.feature_image) {
            imagePreview.src = data.feature_image;
            removeImageBtn.classList.remove('d-none');
        } else {
            removeImageBtn.classList.add('d-none');
        }
        removeImageFlag.value = '0';
        form.action = @json(route('backend.logistics.update', ':id')).replace(':id', data.id);
    }

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        imageValidation.classList.add('d-none');
        if (!file) { return; }
        const maxSizeBytes = 2 * 1024 * 1024; // 2MB
        if (file.size > maxSizeBytes) {
            imageValidation.textContent = 'File size exceeds 2 MB. Please upload a smaller file.';
            imageValidation.classList.remove('d-none');
            imageInput.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(evt) {
            imagePreview.src = evt.target.result;
            removeImageBtn.classList.remove('d-none');
        }
        reader.readAsDataURL(file);
    });

    removeImageBtn.addEventListener('click', function() {
        imagePreview.src = @json($defaultImage ?? default_feature_image());
        imageInput.value = '';
        removeImageFlag.value = '1';
        removeImageBtn.classList.add('d-none');
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (submitBtn.disabled) return;
        clearErrors();
        // jQuery Validate for inline error under name
        if (window.$ && $.fn && $.fn.validate) {
            const $form = $('#logistic-form');
            if (!$form.data('validator')) {
                $form.validate({
                    ignore: [],
                    onkeyup: function(el){ $(el).valid(); },
                    onfocusout: function(el){ $(el).valid(); },
                    rules: { name: { required: true, normalizer: function(v){ return $.trim(v); } } },
                    messages: { name: { required: 'Name is a required field' } },
                    errorPlacement: function(error, element){ $('#name-error').text(error.text()).addClass('d-block text-danger'); },
                    highlight: function(el){ $(el).addClass('is-invalid'); },
                    unhighlight: function(el){ $(el).removeClass('is-invalid'); $('#name-error').text('').removeClass('d-block text-danger'); }
                });
            }
            if (!$form.valid()) { $form.validate().focusInvalid(); return; }
        } else {
            if (!document.getElementById('logistic_name').value.trim()) { showFieldError('logistic_name', 'Name is a required field'); return; }
        }
        setLoading(true);

        const formData = new FormData(form);
        const isEdit = !!idField.value;
        if (isEdit) { formData.append('_method', 'PUT'); }

        fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: formData
        }).then(async res => {
            const text = await res.text();
            let data;
            try { data = text ? JSON.parse(text) : {}; } catch (e) { data = { status: false, message: 'Invalid response' }; }
            if (data.status) {
                if (window.successSnackbar) window.successSnackbar(data.message);
                if (typeof renderedDataTable !== 'undefined') { renderedDataTable.ajax.reload(null, false); }
                const ocEl = document.getElementById('form-offcanvas');
                const instance = bootstrap.Offcanvas.getOrCreateInstance(ocEl);
                instance.hide();
                resetForm();
            } else {
                if (window.errorSnackbar) window.errorSnackbar(data.message || 'Error');
                if (data.all_message) {
                    Object.keys(data.all_message).forEach(field => {
                        if (field === 'name') showFieldError('logistic_name', data.all_message[field][0]);
                    });
                }
            }
        }).catch(() => {
            if (window.errorSnackbar) window.errorSnackbar('An error occurred while processing your request.');
        }).finally(() => setLoading(false));
    });

    document.addEventListener('crud_change_id', function(e) {
        const id = Number(e.detail.form_id || 0);
        if (id > 0) {
            const url = @json(route('backend.logistics.edit', ':id')).replace(':id', id);
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(res => { if (res.status) { setEdit(res.data); const oc = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('form-offcanvas')); oc.show(); } else { if (window.errorSnackbar) window.errorSnackbar(res.message || 'Error'); } })
                .catch(() => { if (window.errorSnackbar) window.errorSnackbar('An error occurred while loading data'); });
        } else {
            resetForm();
        }
    });

    document.getElementById('form-offcanvas').addEventListener('hidden.bs.offcanvas', function() { resetForm(); });

    // Initialize defaults
    resetForm();
});
</script>
@endpush


