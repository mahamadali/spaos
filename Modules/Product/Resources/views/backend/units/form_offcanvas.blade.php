<div class="offcanvas offcanvas-end" tabindex="-1" id="form-offcanvas" aria-labelledby="form-offcanvasLabel">
	<div class="offcanvas-header">
		<h5 class="offcanvas-title" id="form-offcanvasLabel">
			<span id="unit-form-title">{{ __('messages.new') }} {{ __('units.singular_title') }}</span>
		</h5>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>

    <form id="unit-form" action="{{ route('backend.units.store') }}" novalidate class="d-flex flex-column h-100">
		@csrf
		<input type="hidden" name="id" id="unit_id">
		<div class="offcanvas-body flex-grow-1">
			<div class="form-group">
                <label class="form-label" for="unit_name">{{ __('service.lbl_name') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="unit_name" name="name" placeholder="{{ __('messages.enter_unit_name') }}" required>
				<div class="invalid-feedback" id="unit-name-error"></div>
			</div>

			<div class="form-group">
				<div class="d-flex justify-content-between align-items-center form-control">
					<label class="form-label mb-0" for="unit-status">{{ __('service.lbl_status') }}</label>
					<div class="form-check form-switch">
						<input type="hidden" name="status" value="0" />
						<input class="form-check-input" value="1" name="status" id="unit-status" type="checkbox" checked />
					</div>
				</div>
			</div>
		</div>
		
		<div class="offcanvas-footer p-3 border-top">
			<div class="d-flex justify-content-end gap-2">
				<button type="button" class="btn btn-outline-primary" data-bs-dismiss="offcanvas">
					{{ __('messages.cancel') }}
				</button>
				<button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2" id="unit-submit-btn">
					<span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
					<span id="unit-submit-text">{{ __('messages.save') }}</span>
				</button>
			</div>
		</div>
	</form>
</div>

@push('after-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
	const form = document.getElementById('unit-form');
	const submitBtn = document.getElementById('unit-submit-btn');
	const submitText = document.getElementById('unit-submit-text');
	const spinner = submitBtn.querySelector('.spinner-border');

	let isEdit = false;

	function setLoadingState(loading) {
		submitBtn.disabled = loading;
		if (loading) {
			spinner.classList.remove('d-none');
			submitText.textContent = '{{ __("messages.saving") }}';
		} else {
			spinner.classList.add('d-none');
			submitText.textContent = '{{ __("messages.save") }}';
		}
	}

	function clearErrors() {
		document.querySelectorAll('.is-invalid').forEach(f => f.classList.remove('is-invalid'));
		document.querySelectorAll('.invalid-feedback').forEach(e => e.textContent = '');
	}

	function showFieldError(fieldId, message) {
		const field = document.getElementById(fieldId);
		const errorDiv = document.getElementById(fieldId.replace('unit_', '').replace('unit-', '') + '-error');
		if (field && errorDiv) {
			field.classList.add('is-invalid');
			errorDiv.textContent = message;
		}
	}

    // jQuery Validate inline messages
    if (window.$ && $.fn && $.fn.validate) {
        $('#unit-form').validate({
            ignore: [],
            onkeyup: function(el){ $(el).valid(); },
            onfocusout: function(el){ $(el).valid(); },
            rules: { name: { required: true, normalizer: function(v){ return $.trim(v); } } },
            messages: { name: { required: 'Name is a required field' } },
            errorPlacement: function(error, element){
                const id = 'unit-name-error';
                $('#' + id).text(error.text()).addClass('d-block text-danger');
            },
            highlight: function(el){ $(el).addClass('is-invalid'); },
            unhighlight: function(el){ $(el).removeClass('is-invalid'); $('#unit-name-error').text('').removeClass('d-block text-danger'); }
        });
    }

    function validateForm() {
		let ok = true;
		clearErrors();
		const name = document.getElementById('unit_name').value.trim();
		if (!name) { showFieldError('unit_name', 'Name is a required field'); ok = false; }
		return ok;
	}

	function resetForm() {
		form.reset();
		clearErrors();
		isEdit = false;
		document.getElementById('unit_id').value = '';
		document.getElementById('unit-form-title')?.remove();
		const titleEl = document.getElementById('form-offcanvasLabel');
		if (titleEl) titleEl.textContent = '{{ __("messages.new") }} {{ __("units.singular_title") }}';
		form.action = '{{ route('backend.units.store') }}';
	}

	form.addEventListener('submit', function(e) {
		e.preventDefault();
		if (submitBtn.disabled) return;
        if (window.$ && $.fn && $.fn.validate) {
            const $form = $('#unit-form');
            if (!$form.valid()) { $form.validate().focusInvalid(); return; }
        } else {
            if (!validateForm()) return;
        }
		setLoadingState(true);

		const formData = new FormData(form);
		const id = document.getElementById('unit_id').value;
		let url;
		if (isEdit) {
			url = '{{ route('backend.units.update', ':id') }}'.replace(':id', id);
			formData.append('_method', 'PUT');
		} else {
			url = '{{ route('backend.units.store') }}';
		}

		fetch(url, {
			method: 'POST',
			headers: {
				'X-Requested-With': 'XMLHttpRequest',
				'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
			},
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
						const fieldId = field === 'name' ? 'unit_name' : field;
						showFieldError(fieldId, data.all_message[field][0]);
					});
				}
			}
		}).catch(() => {
			if (window.errorSnackbar) window.errorSnackbar('An error occurred while processing your request.');
		}).finally(() => setLoadingState(false));
	});

	window.editUnit = function(unitData) {
		isEdit = true;
		document.getElementById('unit_id').value = unitData.id;
		document.getElementById('unit_name').value = unitData.name || '';
		document.getElementById('unit-status').checked = unitData.status == 1;
		const titleEl = document.getElementById('form-offcanvasLabel');
		if (titleEl) titleEl.textContent = '{{ __("messages.edit") }} {{ __("units.singular_title") }}';
		const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('form-offcanvas'));
		offcanvas.show();
	}

	document.getElementById('form-offcanvas').addEventListener('hidden.bs.offcanvas', function() { resetForm(); });

	document.addEventListener('crud_change_id', function(e) {
		const id = Number(e.detail.form_id || 0);
		if (id > 0) {
			const url = '{{ route('backend.units.edit', ':id') }}'.replace(':id', id);
			fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
				.then(r => r.json())
				.then(res => { if (res.status) { window.editUnit(res.data); } else { if (window.errorSnackbar) window.errorSnackbar(res.message || 'Error'); } })
				.catch(() => { if (window.errorSnackbar) window.errorSnackbar('An error occurred while loading unit data'); });
		} else {
			resetForm();
		}
	});

	// Initialize defaults
	resetForm();
});
</script>
@endpush

