<div class="offcanvas offcanvas-end" tabindex="-1" id="form-offcanvas" aria-labelledby="form-offcanvasLabel">
	<div class="offcanvas-header">
		<h5 class="offcanvas-title" id="form-offcanvasLabel">
			<span id="variation-form-title">{{ __('messages.new') }} {{ __('variations.singular_title') }}</span>
		</h5>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>

    <form id="variation-form" action="{{ route('backend.variations.store') }}" method="POST" novalidate class="d-flex flex-column h-100">
		@csrf
		<input type="hidden" name="id" id="variation_id">
		<div class="offcanvas-body flex-grow-1">
			<div class="form-group">
				<label class="form-label" for="variation_name">{{ __('service.lbl_name') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="variation_name" name="name" placeholder="{{ __('messages.enter_variation_name') }}">
				<div class="invalid-feedback" id="variation-name-error"></div>
			</div>
			<div class="form-group">
				<label class="form-label" for="variation_type">{{ __('service.lbl_type') }} <span class="text-danger">*</span></label>
                <select id="variation_type" name="type" class="form-control select2" style="width:100%" data-placeholder="{{ __('product.select_type') }}">
					<option value="">{{ __('product.select_type') }}</option>
					<option value="text">Text</option>
					<option value="color">Color</option>
				</select>
				<div class="invalid-feedback" id="variation-type-error"></div>
			</div>
			<div id="values-container" class="mb-3"></div>
			<button type="button" class="btn btn-secondary w-100" id="add-value-btn"><i class="fa fa-plus-circle"></i> {{ __('service.add_values') }}</button>
			<div class="form-group mt-3">
				<div class="d-flex justify-content-between align-items-center form-control">
					<label class="form-label mb-0" for="variation-status">{{ __('service.lbl_status') }}</label>
					<div class="form-check form-switch">
						<input type="hidden" name="status" value="0" />
						<input class="form-check-input" value="1" name="status" id="variation-status" type="checkbox" checked />
					</div>
				</div>
			</div>
		</div>
		
		<div class="offcanvas-footer p-3 border-top">
			<div class="d-flex justify-content-end gap-2">
				<button type="button" class="btn btn-outline-primary" data-bs-dismiss="offcanvas">
					{{ __('messages.cancel') }}
				</button>
				<button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2" id="variation-submit-btn">
					<span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
					<span id="variation-submit-text">{{ __('messages.save') }}</span>
				</button>
			</div>
		</div>
	</form>
</div>

@push('after-scripts')
<script>
function initVariationForm(){
  const form = document.getElementById('variation-form');
  const submitBtn = document.getElementById('variation-submit-btn');
  const submitText = document.getElementById('variation-submit-text');
  const spinner = submitBtn.querySelector('.spinner-border');
  const valuesContainer = document.getElementById('values-container');
  const addValueBtn = document.getElementById('add-value-btn');
  const typeSelect = document.getElementById('variation_type');
  console.debug('[VariationForm] init');

  function setLoading(loading){
    submitBtn.disabled = loading;
    spinner.classList.toggle('d-none', !loading);
    submitText.textContent = loading ? '{{ __("messages.saving") }}' : '{{ __("messages.save") }}';
  }

  function clearErrors(){
    document.querySelectorAll('#variation-form .is-invalid').forEach(n => n.classList.remove('is-invalid'));
    ['variation-name-error','variation-type-error'].forEach(id => { const el = document.getElementById(id); if(el) el.textContent=''; });
    document.querySelectorAll('#values-container .invalid-feedback').forEach(el => el.remove());
  }

  function rowTemplate(isColor, existingId = ''){
    const wrap = document.createElement('div');
    wrap.className = 'variation-value-row d-flex gap-3 align-items-end mb-3';
    wrap.innerHTML = `
      <input type="hidden" name="values[][id]" value="${existingId || ''}" />
      <div class="flex-grow-1">
        <label class="form-label">${isColor ? '{{ __('service.lbl_colour') }}' : '{{ __('service.lbl_value') }}'}</label>
        ${isColor ? '<input type="color" class="form-control form-control-color w-100" name="values[][value]" value="#000000" />' : '<input type="text" class="form-control" name="values[][value]" placeholder="{{ __('messages.enter_value') }}" />'}
      </div>
      <div class="flex-grow-1">
        <label class="form-label">{{ __('service.lbl_name') }}</label>
        <input type="text" class="form-control" name="values[][name]" placeholder="{{ __('messages.enter_name') }}" />
      </div>
      <div class="d-flex align-items-end">
        <button type="button" class="btn btn-danger btn-remove-value"><i class="fas fa-trash"></i></button>
      </div>
    `;
    wrap.querySelector('.btn-remove-value').addEventListener('click', () => {
      if (valuesContainer.children.length > 1) {
        wrap.remove();
        refreshRemoveButtons();
      }
    });
    return wrap;
  }

  function ensureAtLeastOneRow(){
    if (!typeSelect.value) return; // only after type is selected
    if (valuesContainer.children.length === 0) {
      valuesContainer.appendChild(rowTemplate(typeSelect.value === 'color'));
    }
  }

  function refreshRemoveButtons(){
    const rows = Array.from(valuesContainer.children);
    const showRemove = rows.length > 1;
    rows.forEach(r => {
      const btn = r.querySelector('.btn-remove-value');
      if (btn) {
        btn.classList.toggle('d-none', !showRemove);
        btn.style.display = showRemove ? '' : 'none';
      }
    });
  }

  function updateValuesUI(){
    const hasType = !!typeSelect.value;
    valuesContainer.classList.toggle('d-none', !hasType);
    addValueBtn.disabled = !hasType;
    if (hasType) {
      ensureAtLeastOneRow();
      refreshRemoveButtons();
    } else {
      valuesContainer.innerHTML = '';
    }
  }

  function reindexRows(){
    const rows = valuesContainer.querySelectorAll('.variation-value-row');
    rows.forEach((row, idx) => {
      const hid = row.querySelector('input[name="values[][id]"]');
      const val = row.querySelector('input[name="values[][value]"]');
      const nam = row.querySelector('input[name="values[][name]"]');
      if (hid) hid.setAttribute('name', `values[${idx}][id]`);
      if (val) val.setAttribute('name', `values[${idx}][value]`);
      if (nam) nam.setAttribute('name', `values[${idx}][name]`);
    });
  }

  addValueBtn.addEventListener('click', () => {
    console.debug('[VariationForm] add value row');
    valuesContainer.appendChild(rowTemplate(typeSelect.value === 'color'));
    reindexRows();
    refreshRemoveButtons();
  });

  // Clear errors when user enters data
  const nameInput = document.getElementById('variation_name');
  if (nameInput) {
    nameInput.addEventListener('input', function() {
      if (this.value.trim()) {
        this.classList.remove('is-invalid');
        const errorEl = document.getElementById('variation-name-error');
        if (errorEl) {
          errorEl.textContent = '';
          errorEl.classList.remove('d-block', 'text-danger');
        }
      }
    });
  }

  // Clear errors when type is selected
  function clearTypeError() {
    typeSelect.classList.remove('is-invalid');
    const errorEl = document.getElementById('variation-type-error');
    if (errorEl) {
      errorEl.textContent = '';
      errorEl.classList.remove('d-block', 'text-danger');
    }
  }

  typeSelect.addEventListener('change', () => {
    if (typeSelect.value) {
      clearTypeError();
    }
    // reset rows and update UI on type change
    valuesContainer.innerHTML = '';
    updateValuesUI();
    reindexRows();
  });

  // init select2
  if (window.$ && $.fn.select2) {
    $('#variation_type').select2({ width: '100%', placeholder: $('#variation_type').data('placeholder') || '{{ __('product.select_type') }}', allowClear: true });
    // also handle select2 events
    $('#variation_type').on('change select2:select select2:clear', function(){
      const val = $(this).val();
      console.debug('[VariationForm] type changed (select2) ->', val);
      if (val) {
        clearTypeError();
      }
      valuesContainer.innerHTML = '';
      updateValuesUI();
      reindexRows();
    });
  }

  // Clear errors when values are entered (using event delegation)
  valuesContainer.addEventListener('input', function(e) {
    const input = e.target;
    if (input.matches('input[name$="[value]"], input[name$="[name]"]')) {
      if (input.value.trim()) {
        input.classList.remove('is-invalid');
        const errorFb = input.nextElementSibling;
        if (errorFb && errorFb.classList.contains('invalid-feedback')) {
          errorFb.remove();
        }
      }
    }
  });

  function validate(){
    clearErrors();
    let ok = true;
    // Run jQuery Validate for basic fields if available
    if (window.$ && $.fn && $.fn.validate) {
      const $form = $('#variation-form');
      if (!$form.data('validator')) {
        $form.validate({
          ignore: [],
          onkeyup: function(el){ $(el).valid(); },
          onfocusout: function(el){ $(el).valid(); },
          rules: {
            name: { required: true, normalizer: function(v){ return $.trim(v); } },
            type: { required: true }
          },
          messages: {
            name: { required: 'Name is a required field' },
            type: { required: 'Type is a required field' }
          },
          errorPlacement: function(error, element){
            const map = { name: 'variation-name-error', type: 'variation-type-error' };
            const id = map[element.attr('name')];
            if (id) { $('#' + id).text(error.text()).addClass('d-block text-danger'); }
            else { error.insertAfter(element); }
          },
          highlight: function(el){ $(el).addClass('is-invalid'); },
          unhighlight: function(el){ $(el).removeClass('is-invalid'); const map={ name:'variation-name-error', type:'variation-type-error' }; const id=map[$(el).attr('name')]; if(id){ $('#'+id).text('').removeClass('d-block text-danger'); } }
        });
      }
      if (!$form.valid()) { return false; }
    } else {
      const name = document.getElementById('variation_name').value.trim();
      const type = document.getElementById('variation_type').value;
      if (!name) { ok = false; document.getElementById('variation_name').classList.add('is-invalid'); document.getElementById('variation-name-error').textContent = 'Name is required'; }
      if (!type) { ok = false; document.getElementById('variation_type').classList.add('is-invalid'); document.getElementById('variation-type-error').textContent = 'Type is required'; }
    }
    const rows = valuesContainer.querySelectorAll('.variation-value-row');
    if (rows.length === 0) { ok = false; valuesContainer.insertAdjacentHTML('beforeend', '<div class="text-danger">{{ __('service.add_values') }}</div>'); }
    rows.forEach(r => {
      const valueInput = r.querySelector('input[name$="[value]"]');
      const nameInput = r.querySelector('input[name$="[name]"]');
      if (!valueInput || !valueInput.value) {
        ok = false; if (valueInput) { valueInput.classList.add('is-invalid'); const fb=document.createElement('div'); fb.className='invalid-feedback d-block'; fb.textContent='Value is required'; valueInput.after(fb); }
      }
      if (!nameInput || !nameInput.value) {
        ok = false; if (nameInput) { nameInput.classList.add('is-invalid'); const fb2=document.createElement('div'); fb2.className='invalid-feedback d-block'; fb2.textContent='Name is required'; nameInput.after(fb2); }
      }
    });
    console.debug('[VariationForm] validate -> result:', ok);
    return ok;
  }

  form.addEventListener('submit', function(e){
    e.preventDefault();
    console.debug('[VariationForm] submit clicked');
    if (submitBtn.disabled) return;
    if (!validate()) { if (window.$ && $.fn && $.fn.validate) $('#variation-form').validate().focusInvalid(); return; }
    setLoading(true);

    const id = document.getElementById('variation_id').value;
    const isEdit = !!id;
    const url = isEdit ? '{{ route('backend.variations.update', ':id') }}'.replace(':id', id) : '{{ route('backend.variations.store') }}';
    const fd = new FormData(form);
    if (isEdit) fd.append('_method', 'PUT');
    console.debug('[VariationForm] submit -> isEdit:', isEdit, 'url:', url);
    for (const [k,v] of fd.entries()) { console.debug('[VariationForm] formData', k, v); }
    fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]')||{}).content }, body: fd })
      .then(async r => { const t = await r.text(); console.debug('[VariationForm] raw response text:', t); try { return t ? JSON.parse(t) : {}; } catch (e) { console.error('[VariationForm] JSON parse error:', e); return { status:false, message: t||'Invalid response' }; } })
      .then(json => {
        console.debug('[VariationForm] parsed response:', json);
        if (json.status) {
          if (window.successSnackbar) window.successSnackbar(json.message);
          if (window.renderedDataTable) window.renderedDataTable.ajax.reload(null, false);
          const oc = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('form-offcanvas')); oc.hide();
          form.reset(); valuesContainer.innerHTML=''; ensureAtLeastOneRow(); document.getElementById('variation_id').value=''; document.getElementById('variation-form-title').textContent='{{ __('messages.new') }} {{ __('variations.singular_title') }}';
        } else {
          if (window.errorSnackbar) window.errorSnackbar(json.message || 'Error');
        }
      })
      .catch((err) => { console.error('[VariationForm] fetch error:', err); if (window.errorSnackbar) window.errorSnackbar('An error occurred'); })
      .finally(() => setLoading(false));
  });

  // listen for edit trigger
  document.addEventListener('crud_change_id', function(e){
    const id = Number(e.detail.form_id || 0);
    if (id > 0) {
      fetch('{{ route('backend.variations.edit', ':id') }}'.replace(':id', id), { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
        .then(r => r.json())
        .then(res => {
          if (res.status) {
            const d = res.data || {};
            document.getElementById('variation_id').value = d.id;
            document.getElementById('variation_name').value = d.name || '';
            $('#variation_type').val(d.type || '').trigger('change');
            valuesContainer.innerHTML='';
            const isColor = (d.type === 'color');
            (Array.isArray(d.values) ? d.values : []).forEach((v, idx) => {
              const row = rowTemplate(isColor, v.id || '');
              valuesContainer.appendChild(row);
              reindexRows();
              const valueInput = valuesContainer.querySelector(`input[name="values[${idx}][value]"]`);
              const nameInput = valuesContainer.querySelector(`input[name="values[${idx}][name]"]`);
              if (valueInput) valueInput.value = v.value || '';
              if (nameInput) nameInput.value = v.name || '';
            });
            reindexRows();
            // show container if type present; add at least one row if none
            updateValuesUI();
            document.getElementById('variation-form-title').textContent='{{ __('messages.edit') }} {{ __('variations.singular_title') }}';
            bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('form-offcanvas')).show();
          }
        });
    } else {
      // reset create mode
      form.reset(); valuesContainer.innerHTML=''; $('#variation_type').val('').trigger('change'); document.getElementById('variation_id').value=''; document.getElementById('variation-form-title').textContent='{{ __('messages.new') }} {{ __('variations.singular_title') }}';
    }
  });

  // initial state: hide values until type is selected
  addValueBtn.disabled = true;
  valuesContainer.classList.add('d-none');
}

// Ensure init runs whether or not DOMContentLoaded has already fired
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initVariationForm);
} else {
  initVariationForm();
}
</script>
@endpush

