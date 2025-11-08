<div class="offcanvas offcanvas-end" tabindex="-1" id="package-service-form" aria-labelledby="package-service-form-label">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title" id="package-service-form-label">{{ __('service.title') ?? 'Services' }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <div class="d-flex flex-column">
      <h4 class="mb-3">{{ __('service.title') ?? 'Services' }}</h4>
      <div class="row g-2 align-items-end mb-3">
        <div class="col-8">
          <label class="form-label">{{ __('employee.lbl_select_service') ?? 'Select Service' }}</label>
          <select id="employee-service-select" class="form-control select2" style="width:100%" multiple></select>
          <!-- <small class="text-muted">{{ __('messages.select_multiple') ?? 'You can select multiple services' }}</small> -->
        </div>
        <div class="col-4">
          <button type="button" class="btn btn-primary w-100" id="employee-add-service"><i class="fa-solid fa-plus me-1"></i>{{ __('service.add') ?? 'Add' }}</button>
        </div>
      </div>
      <div class="table-responsive">
      <table class="table table-striped border dataTable no-footer mb-0" id="employee-services-table" style="display:none;">
        <thead>
          <tr>
            <th>{{ __('messages.name') ?? 'Name' }}</th>
            <th>{{ __('product.price') ?? 'Price' }}</th>
            <th style="width:120px;">{{ __('service.lbl_action') ?? 'Action' }}</th>
          </tr>
        </thead>
        <tbody id="employee-services-tbody"></tbody>
      </table>
      </div>
      <p class="text-muted" id="employee-services-empty">{{ __('messages.No_data_available') ?? 'No data available' }}</p>
    </div>
  </div>
</div>

@push('after-scripts')
<script type="text/javascript">
(function($){
  const offcanvasEl = document.getElementById('package-service-form');
  if(!offcanvasEl) return;
  const $tbody = $('#employee-services-tbody');
  const $table = $('#employee-services-table');
  const $empty = $('#employee-services-empty');
  var hasServiceChanged = false;
  var currentEmployeeId = 0;
  var $serviceSelect;

  function formatCurrency(value){
    if(window.currencyFormat !== undefined){ return window.currencyFormat(value); }
    return value;
  }

  function renderRows(items){
    $tbody.empty();
    if(Array.isArray(items) && items.length){
      items.forEach(it => {
        const name = it.service_name || it.name || '-';
        const price = formatCurrency(it.service_price ?? it.price ?? 0);
        const serviceId = it.service_id || it.id;
        $tbody.append(`<tr>
          <td><h6 class="m-0">${name}</h6></td>
          <td><h6 class="m-0 text-danger">${price}</h6></td>
          <td>
            <button type="button" class="btn btn-sm text-danger js-delete-employee-service" data-service-id="${serviceId}" data-service-name="${name}">
              <i class="fa-regular fa-trash-can"></i>
            </button>
          </td>
        </tr>`);
      })
      $table.show();
      $empty.hide();
    } else {
      $table.hide();
      $empty.show();
    }
  }

  function initServiceSelect(){
    $serviceSelect = $('#employee-service-select');
    $serviceSelect.select2({
      placeholder: '{{ __('messages.search') ?? 'Search...' }}',
      multiple: true,
      closeOnSelect: false,
      ajax: {
        url: '{{ route('backend.services.index_list') }}',
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return { q: params.term, employee_id: currentEmployeeId, exclude_assigned: 1 };
        },
        processResults: function (data) {
          const results = (data || []).map(function(s){ return { id: s.id, text: s.name } });
          return { results: results };
        },
        cache: true
      }
    });
  }

  async function loadServices(employeeId){
    try {
      const url = `{{ url('app/employees/empolye-services') }}/${employeeId}`;
      const res = await $.get(url);
      if(res && res.status){
        renderRows(res.data || []);
      } else {
        renderRows([]);
      }
    } catch (e){
      renderRows([]);
    }
  }

  document.addEventListener('package_service_form', function(ev){
    const employeeId = ev?.detail?.form_id ? parseInt(ev.detail.form_id) : 0;
    currentEmployeeId = employeeId > 0 ? employeeId : 0;
    if(!$serviceSelect){ initServiceSelect(); }
    if(currentEmployeeId > 0){ loadServices(currentEmployeeId); }
  });

  offcanvasEl.addEventListener('hidden.bs.offcanvas', function(){
    renderRows([]);
    if (hasServiceChanged && window.renderedDataTable) {
      try { window.renderedDataTable.ajax.reload(null, false) } catch(e) {}
      hasServiceChanged = false;
    }
  })
  // delete service assignment
  $(document).on('click', '.js-delete-employee-service', function(){
    const serviceId = $(this).data('service-id');
    const serviceName = $(this).data('service-name');
    if(!currentEmployeeId || !serviceId) return;
    const proceedDelete = () => {
      $.ajax({
        url: `{{ url('app/employees/employee-services') }}/${currentEmployeeId}/${serviceId}`,
        method: 'POST',
        data: { _method: 'DELETE', _token: '{{ csrf_token() }}' }
      }).done(function(res){
        if(res?.status){
          hasServiceChanged = true;
          window.successSnackbar && window.successSnackbar(res.message || '{{ __('messages.delete_form', ['form' => __('service.singular_title')]) }}');
          loadServices(currentEmployeeId);
        } else {
          window.errorSnackbar && window.errorSnackbar(res.message || 'Failed to delete');
        }
      }).fail(function(){
        window.errorSnackbar && window.errorSnackbar('Server error');
      })
    };

    const deleteMessage = `{{ __('messages.are_you_sure?', ['name' => ':name', 'module' => __('service.singular_title')]) }}`.replace(':name', serviceName || 'this service');

    if (window.Swal && typeof window.Swal.fire === 'function') {
      Swal.fire({
        title: deleteMessage,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: `{{ __('messages.delete') ?? 'Delete' }}`,
        cancelButtonText: `{{ __('messages.cancel') ?? 'Cancel' }}`
      }).then((result) => { if (result.isConfirmed) proceedDelete(); })
    } else {
      if(confirm(deleteMessage)) proceedDelete();
    }
  })

  // add service assignment
  $('#employee-add-service').on('click', function(){
    const selected = $serviceSelect && $serviceSelect.val ? $serviceSelect.val() : [];
    if(!currentEmployeeId || !selected || selected.length === 0){ return; }
    $.post(`{{ url('app/employees/employee-services') }}/${currentEmployeeId}`, { service_ids: selected, _token: '{{ csrf_token() }}' })
      .done(function(res){
        if(res?.status){
          hasServiceChanged = true;
          window.successSnackbar && window.successSnackbar(res.message || '{{ __('messages.create_form', ['form' => __('service.singular_title')]) }}');
          // clear selections
          $serviceSelect.val(null).trigger('change');
          loadServices(currentEmployeeId);
        } else {
          window.errorSnackbar && window.errorSnackbar(res.message || 'Failed to add');
        }
      }).fail(function(){
        window.errorSnackbar && window.errorSnackbar('Server error');
      });
  })
})(window.$)
</script>
@endpush


