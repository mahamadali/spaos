<form id="assign-employee-form" method="POST" action="{{ $service ? route('backend.services.assign_employee_update', $service->id) : '#' }}">
    @csrf
    <div class="offcanvas offcanvas-end" tabindex="-1" id="service-employee-assign-form" aria-labelledby="form-offcanvasLabel">
        <div class="offcanvas-header border-bottom">
            <h6 class="m-0 h5">
                {{ __('service.singular_title') }}: <span>{{ $service ? $service->name : '' }}</span>
            </h6>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="form-group">
                <div class="d-grid">
                    <div class="d-flex flex-column">
                        <div class="mb-4">
                            <label for="employees_ids">{{ __('messages.select_staff') }}</label>
                            <select id="employees_ids" name="employees[]" class="form-control" multiple="multiple" style="width: 100%;">
                                @foreach($employees as $employee)
                                    <option value="{{ $employee['id'] }}"
                                        @if(collect($assignedEmployees)->pluck('employee_id')->contains($employee['id'])) selected @endif
                                        data-avatar="{{ $employee['avatar'] }}"
                                    >{{ $employee['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="list-group list-group-flush" id="selected-employee-list" style="max-height: 50vh; overflow-y: auto;">
                        @foreach($assignedEmployees as $item)
                            <div class="list-group-item d-flex justify-content-between align-items-center" data-employee-id="{{ $item['employee_id'] }}">
                                <div class="d-flex justify-between flex-grow-1 gap-2 my-2">
                                    <img src="{{ $item['avatar'] }}" class="avatar avatar-40 img-fluid rounded-pill" alt="user" />
                                    <div class="flex-grow-1 mt-2">{{ $item['name'] }}</div>
                                </div>
                                <button type="button" class="btn btn-sm text-danger remove-employee-btn"><i class="fa-regular fa-trash-can"></i></button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="offcanvas-footer">
            <div class="d-grid d-md-flex gap-3 p-3">
                <button type="submit" class="btn btn-primary d-block" form="assign-employee-form">
                    <i class="fa-solid fa-floppy-disk"></i>
                    {{ __('messages.update') }}
                </button>
                <button type="button" class="btn btn-outline-primary d-block" data-bs-dismiss="offcanvas">
                    <i class="fa-solid fa-angles-left"></i>
                    {{ __('messages.close') }}
                </button>
            </div>
        </div>
    </div>
</form>

<script>
$(document).ready(function() {
    // Destroy any previous Select2 instance to avoid conflicts
    if ($.fn.select2 && $('#employees_ids').data('select2')) {
        $('#employees_ids').select2('destroy');
    }

    // Enhanced Select2 with avatars and search
    $('#employees_ids').select2({
        placeholder: "{{ __('messages.select_staff') }}",
        width: '100%',
        allowClear: true,
        closeOnSelect: false, // Important for multi-select
        templateResult: function (data) {
            if (!data.id) return data.text;
            var avatar = $(data.element).data('avatar');
            return $(
                '<span><img src="' + avatar + '" class="avatar avatar-30 img-fluid rounded-pill me-2" style="width:24px;height:24px;object-fit:cover;">' + data.text + '</span>'
            );
        },
        templateSelection: function (data) {
            if (!data.id) return data.text;
            var avatar = $(data.element).data('avatar');
            return $(
                '<span><img src="' + avatar + '" class="avatar avatar-30 img-fluid rounded-pill me-2" style="width:24px;height:24px;object-fit:cover;">' + data.text + '</span>'
            );
        }
    });

    // Update selected employee list on change
    $('#employees_ids').on('change', function() {
        var selectedIds = $(this).val() || [];
        var employees = @json($employees);
        var assigned = employees.filter(emp => selectedIds.includes(emp.id.toString()));
        var $list = $('#selected-employee-list');
        $list.empty();
        assigned.forEach(function(emp) {
            $list.append(`
                <div class="list-group-item d-flex justify-content-between align-items-center" data-employee-id="${emp.id}">
                    <div class="d-flex justify-between flex-grow-1 gap-2 my-2">
                        <img src="${emp.avatar}" class="avatar avatar-40 img-fluid rounded-pill" alt="user" />
                        <div class="flex-grow-1 mt-2">${emp.name}</div>
                    </div>
                    <button type="button" class="btn btn-sm text-danger remove-employee-btn"><i class="fa-regular fa-trash-can"></i></button>
                </div>
            `);
        });
    });

    // Remove employee from selection
    $(document).on('click', '.remove-employee-btn', function() {
        var $item = $(this).closest('.list-group-item');
        var empId = $item.data('employee-id').toString();
        var $select = $('#employees_ids');
        var selected = $select.val() || [];
        $select.val(selected.filter(id => id !== empId)).trigger('change');
    });

    // AJAX form submit
    $('#assign-employee-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var action = $form.attr('action');
        // Don't submit if no service is selected
        if (action === '#') {
            return false;
        }
        
        var $submitBtn = $form.find('button[type="submit"]');
        var originalHTML = $submitBtn.html();
        
        // Show loading state
        $submitBtn.prop('disabled', true);
        $submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __("messages.updating") }}');
        
        $.ajax({
            url: action,
            method: 'POST',
            data: $form.serialize(),
            success: function(res) {
                if(res.status) {
                    window.successSnackbar(res.message);
                    bootstrap.Offcanvas.getInstance(document.getElementById('service-employee-assign-form')).hide();
                    if(window.renderedDataTable) {
                        window.renderedDataTable.ajax.reload(null, false);
                    }
                } else {
                    window.errorSnackbar(res.message);
                }
            },
            error: function(xhr) {
                window.errorSnackbar("{{ __('messages.something_went_wrong') }}");
            },
            complete: function() {
                // Restore button state
                $submitBtn.prop('disabled', false);
                $submitBtn.html(originalHTML);
            }
        });
    });
});
</script>