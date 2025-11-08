@php
    $isEdit = isset($tax) && $tax->id;
@endphp

<div class="offcanvas offcanvas-end" tabindex="-1" id="tax-form-offcanvas" aria-labelledby="taxFormOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="taxFormOffcanvasLabel">
            {{ $isEdit ? ($editTitle ?? __('messages.edit') . ' ' . __('tax.tax')) : ($createTitle ?? __('messages.new') . ' ' . __('tax.tax')) }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <form id="tax-form" method="POST" novalidate
              action="{{ $isEdit ? route('backend.tax.update', $tax->id) : route('backend.tax.store') }}">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="title" class="form-label">{{ __('tax.lbl_title') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="title" name="title"
                       value="{{ old('title', $tax->title ?? '') }}"
                       placeholder="{{ __('tax.enter_title') }}">
                <span class="text-danger title_error"></span>
            </div>

            <div class="mb-3">
                <label for="type" class="form-label">{{ __('tax.lbl_select_type') }} <span class="text-danger">*</span></label>
                <select class="form-select select2" id="type" name="type">
                    <option value="">{{ __('tax.select_type') }}</option>
                    <option value="percent" {{ old('type', $tax->type ?? '') == 'percent' ? 'selected' : '' }}>Percent</option>
                    <option value="fixed" {{ old('type', $tax->type ?? '') == 'fixed' ? 'selected' : '' }}>Fixed</option>
                </select>
                <span class="text-danger type_error"></span>
            </div>

            <div class="mb-3">
                <label for="value" class="form-label">{{ __('tax.lbl_value') }} <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="value" name="value"
                       value="{{ old('value', $tax->value ?? '') }}"
                       placeholder="{{ __('tax.enter_value') }}">
                <span class="text-danger value_error"></span>
            </div>

            <div class="mb-3">
                <label for="module_type" class="form-label">{{ __('tax.lbl_module_type') }} <span class="text-danger">*</span></label>
                <select class="form-select select2" id="module_type" name="module_type" required>
                    <option value="">{{ __('tax.module_type') }}</option>
                    <option value="products" {{ old('module_type', $tax->module_type ?? '') == 'products' ? 'selected' : '' }}>Products</option>
                    <option value="services" {{ old('module_type', $tax->module_type ?? '') == 'services' ? 'selected' : '' }}>Services</option>
                </select>
                <span class="text-danger module_type_error"></span>
            </div>

            <div class="form-check form-switch mb-3">
                <input type="hidden" name="status" value="0">
                <input class="form-check-input" type="checkbox" id="status" name="status" value="1"
                    {{ old('status', $tax->status ?? 1) ? 'checked' : '' }}>
                <label class="form-check-label" for="status">{{ __('tax.lbl_status') }}</label>
            </div>

        </form>
    </div>

    <div class="offcanvas-footer border-top p-3 d-flex justify-content-end gap-2">
        <button type="submit" form="tax-form" class="btn btn-primary" id="saveBtn">
            <i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save') }}
        </button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">
            <i class="fa-solid fa-angles-left"></i> {{ __('messages.close') }}
        </button>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('tax-form');

    // Initialize Select2
    $('.select2').select2({
        width: '100%',
        placeholder: "Select an option",
        allowClear: true
    });

    // Helper: show error in <span class="text-danger ..._error">
    function showError(field, message) {
        const span = form.querySelector('.' + field + '_error');
        if (span) span.textContent = message;
        const input = form.querySelector('#' + field);
        if (input) input.classList.add('is-invalid');
    }

    // Clear all errors
    function clearErrors() {
        form.querySelectorAll('.text-danger').forEach(span => span.textContent = '');
        form.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));
    }

    const newBtn = document.getElementById('newTaxBtn');
    if (newBtn) {
        newBtn.addEventListener('click', function () {
            form.reset();       // Reset input values
            clearErrors();      // Clear error messages
            $('.select2').val(null).trigger('change'); // Reset Select2
            // Ensure status checkbox is checked by default (optional)
            form.querySelector('#status').checked = true;

            // Reset form action & method to create
            form.action = "{{ route('backend.tax.store') }}";
            form.querySelector('[name="_method"]')?.remove();
        });
    }

    // Submit form
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();

        let valid = true;
        const title = form.querySelector('#title').value.trim();
        const type = form.querySelector('#type').value;
        const value = form.querySelector('#value').value.trim();
        const module_type = form.querySelector('#module_type').value;
        if (!title) { showError('title', 'Title is required field'); valid = false; }
        else if (/^\d+$/.test(title)) { showError('title', 'Only strings are allowed'); valid = false; }
        else if (/^\s+$/.test(title)) { showError('title', 'Title cannot contain only spaces'); valid = false; }

        if (!type) { showError('type', 'Type is required field'); valid = false; }
        if (!value) { showError('value', 'Value is required field'); valid = false; }
        else if (!/^\d+(\.\d+)?$/.test(value)) { showError('value', 'Only numbers are allowed'); valid = false; }
        else if (type === 'percent' && parseFloat(value) > 100) { showError('value', 'Percent value must be less than or equal to 100'); valid = false; }
        if (!module_type) { showError('module_type', 'Module Type is required field'); valid = false; }

        if (!valid) return;

        // AJAX submit
        const formData = new FormData(form);
        fetch(form.action, {
            method: form.method,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            clearErrors();
            if (data.status) {
                window.successSnackbar(data.message);
                bootstrap.Offcanvas.getInstance(document.getElementById('tax-form-offcanvas')).hide();
                if (typeof renderedDataTable !== 'undefined') renderedDataTable.ajax.reload(null, false);
            } else {
                window.errorSnackbar(data.message);
                for (const field in data.all_message) {
                    showError(field, data.all_message[field][0]);
                }
            }
        })
        .catch(err => console.error(err));
    });

    // Delegate click for edit buttons (works for dynamic buttons too)
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-tax-id]');
        if (!btn) return;

        const taxId = btn.getAttribute('data-tax-id');
        if (!taxId) return;

        clearErrors();
        form.reset();

        fetch(`{{ url('app/tax') }}/${taxId}/edit`, { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(data => {
            if (data.status) {
                form.querySelector('#title').value = data.data.title;
                $('#type').val(data.data.type).trigger('change');
                form.querySelector('#value').value = data.data.value;
                $('#module_type').val(data.data.module_type).trigger('change');
                form.querySelector('#status').checked = data.data.status == 1;
                form.action = `{{ url('app/tax') }}/${taxId}`;
                form.querySelector('[name="_method"]')?.remove();
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                form.appendChild(methodInput);

                new bootstrap.Offcanvas(document.getElementById('tax-form-offcanvas')).show();
            }
        })
        .catch(err => console.error(err));
    });
});
</script>
