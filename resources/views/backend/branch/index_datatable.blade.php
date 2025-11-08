@extends('backend.layouts.app')

@section('title')
 {{ __($module_title) }}
@endsection


@section('content')
<div class="card">
    <div class="card-body">
        <x-backend.section-header>
            <div class="d-flex flex-wrap gap-3">
                @if(auth()->user()->can('edit_branch') || auth()->user()->can('delete_branch'))
                <x-backend.quick-action url="{{route('backend.branch.bulk_action')}}">
                    <div class="">
                        <select name="action_type" class="form-control select2 col-12" id="quick-action-type"
                            style="width:100%">
                            <option selected disabled value="">{{ __('messages.no_action') }}</option>
                            @can('edit_branch')
                            <option value="change-status">{{ __('messages.status') }}</option>
                            @endcan
                            @can('delete_branch')
                            <option value="delete">{{ __('messages.delete') }}</option>
                            @endcan
                        </select>
                    </div>
                    <div class="select-status d-none quick-action-field" id="change-status-action">
                            <select name="status" class="form-control select2" id="status" style="width:100%">
                                <option value="1" selected>{{ __('messages.active') }}</option>
                                <option value="0">{{ __('messages.inactive') }}</option>
                            </select>
                        </div>
                </x-backend.quick-action>
                @endif
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#exportModal">
                            <i class="fa-solid fa-download"></i> {{ __('messages.export') }}
                        </button>
                    </div>
            </div>
            <x-slot name="toolbar">
                <div>
                    <div class="datatable-filter">
                        <select name="column_status" id="column_status" class="select2 form-select"
                            data-filter="select" style="width: 100%">
                            <option value="">{{__('messages.all')}}</option>
                            <option value="0" {{$filter['status'] == '0' ? "selected" : ''}}>
                                {{ __('messages.inactive') }}</option>
                            <option value="1" {{$filter['status'] == '1' ? "selected" : ''}}>{{ __('messages.active') }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="input-group flex-nowrap top-input-search">
                    <span class="input-group-text" id="addon-wrapping"><i
                            class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}..." aria-label="Search"
                        aria-describedby="addon-wrapping">
                </div>

                @hasPermission('add_branch')
                <button type="button" class="btn btn-primary customer-create-btn" data-bs-toggle="offcanvas" data-bs-target="#form-offcanvas">
                    <i class="fas fa-plus-circle"></i> {{ __('messages.new') }}
                </button>
                @endhasPermission
            </x-slot>
        </x-backend.section-header>
        <table id="datatable" class="table table-striped border table-responsive">
        </table>
            <div data-render="app">
                @include('backend.branch.branch_form_offcanvas', [
                    'defaultImage' => default_feature_image(),
                    'createTitle' => __('messages.new') . ' ' . __('branch.singular_title'),
                    'editTitle' => __('messages.edit') . ' ' . __('branch.singular_title'),
                    'BRANCH_FOR_OPTIONS' => $select_data['BRANCH_FOR'] ?? [],
                    'PAYMENT_METHODS_OPTIONS' => $select_data['PAYMENT_METHODS'] ?? [],
                    'managers' => $managers ?? [],
                    'services' => $services ?? [],
                    'countries' => $countries ?? [],
                    'states' => $states ?? [],
                    'cities' => $cities ?? [],
                    'customefield' => $customefield ?? [],
                    'IS_SUBMITED' => false,
                    'branch' => $branch ?? null
                ])
                @include('backend.branch.branch_gallery_offcanvas')
                @include('backend.branch.assign_branch_employee_offcanvas')
            </div>

            <!-- Export Modal -->
            <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exportModalLabel">Export Data</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="exportForm" action="{{ $export_url }}" method="GET">
                            <div class="modal-body">
                                <!-- Date Range Section -->
                                <div class="form-group mb-4 w-100">
                                    <label class="form-label" for="date_range">Date</label>
                                    <input type="text" class="form-control w-100" id="date_range" name="date_range" 
                                           placeholder="Select date range">
                                </div>

                                <!-- File Type Section -->
                                <div class="form-group mb-4">
                                    <label class="form-label mb-3">Select File Type</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <input type="radio" class="btn-check" name="file_type" id="xlsx" value="xlsx">
                                        <label class="btn btn-outline-secondary" for="xlsx">XLSX</label>
                                        
                                        <input type="radio" class="btn-check" name="file_type" id="xls" value="xls">
                                        <label class="btn btn-outline-secondary" for="xls">XLS</label>
                                        
                                        <input type="radio" class="btn-check" name="file_type" id="ods" value="ods">
                                        <label class="btn btn-outline-secondary" for="ods">ODS</label>
                                        
                                        <input type="radio" class="btn-check" name="file_type" id="csv" value="csv" checked>
                                        <label class="btn btn-outline-secondary" for="csv">CSV</label>
                                        
                                        <input type="radio" class="btn-check" name="file_type" id="pdf" value="pdf">
                                        <label class="btn btn-outline-secondary" for="pdf">PDF</label>
                                        
                                        <input type="radio" class="btn-check" name="file_type" id="html" value="html">
                                        <label class="btn btn-outline-secondary" for="html">HTML</label>
                                    </div>
                                </div>
                                
                                <!-- Columns Selection Section -->
                                <div class="form-group">
                                    <label class="form-label mb-3">Select Columns</label>
                                    <div class="d-flex flex-column">
                                        @foreach($export_columns as $column)
                                        <div class="mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="columns[]" 
                                                       value="{{ $column['value'] }}" id="column_{{ $column['value'] }}" checked>
                                                <label class="form-check-label" for="column_{{ $column['value'] }}">
                                                    {{ $column['text'] }}
                                                </label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Modal Footer -->
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" id="exportSubmitBtn">
                                    <i class="fa-solid fa-download me-2"></i>Download
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
    </div>
</div>

@endsection

@push('after-styles')
<!-- DataTables Core and Extensions -->
<link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">

<!-- Branch Export Modal Styles -->
    <link rel="stylesheet" href="{{ asset('css/branch-export-modal.css') }}?v={{ time() }}">
@endpush

@push('after-scripts')
<script src="{{ mix('js/vue.min.js') }}"></script>
<script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>

<!-- DataTables Core and Extensions -->
<script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>

<!-- Flatpickr Date Range Picker -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    $(document).ready(function () {
        // Hide offcanvas initially
        const formOffcanvas = document.getElementById("form-offcanvas");
        const offcanvasInstance = bootstrap.Offcanvas.getInstance(formOffcanvas) || new bootstrap.Offcanvas(formOffcanvas);
        offcanvasInstance.hide();
        
        $(document).on("click", ".customer-create-btn", function (event) {
            let button = $(this); // Store reference to button
            $.ajax({
                url: "{{ route('backend.customers.verify') }}", // Ensure this route exists
                type: "GET",
                data: { type: 'branch' },
                dataType: "json",
                success: function (response) {
                    if (!response.status) {
                        event.preventDefault(); // Prevent default action
                        window.errorSnackbar(response.message);
                        button.removeAttr("data-crud-id"); // Remove attribute if status is false
                        offcanvasInstance.hide();
                    } else {
                        button.attr("data-crud-id", 0); // Set a valid value if required
                        
                        // Trigger crud_change_id event to reset form to create mode
                        const crudEvent = new CustomEvent('crud_change_id', {
                            detail: { form_id: 0 }
                        });
                        document.dispatchEvent(crudEvent);
                        
                        // Small delay to ensure form reset completes before showing offcanvas
                        setTimeout(function() {
                            offcanvasInstance.show();
                        }, 50);
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });
        });
        
        // Initialize Flatpickr date range picker with month/year dropdowns
        if (typeof flatpickr !== 'undefined') {
            flatpickr("#date_range", {
                dateFormat: "Y/m/d",
                static: true,
                mode: "range",
                monthSelectorType: "dropdown",
                yearSelectorType: "dropdown",
                showMonths: 1,
                enableTime: false,
                time_24hr: true,
                allowInput: true,
                clickOpens: true,
                noCalendar: false,
                inline: false,
                disable: [],
                locale: {
                    firstDayOfWeek: 0,
                    weekdays: {
                        shorthand: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                        longhand: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
                    },
                    months: {
                        shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        longhand: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
                    }
                }
            });
        }
        
        // Handle export form submission
        $('#exportForm').on('submit', function(e) {
            const submitBtn = $('#exportSubmitBtn');
            const columns = $('input[name="columns[]"]:checked');
            
            // Validate columns selection
            if (columns.length === 0) {
                e.preventDefault();
                alert('Please select at least one column to export.');
                return false;
            }
            
            // Show loading state
            submitBtn.addClass('loading').prop('disabled', true);
            
            // Reset loading state after 3 seconds (in case of error)
            setTimeout(function() {
                submitBtn.removeClass('loading').prop('disabled', false);
            }, 3000);
        });
        
        // Handle modal show/hide events
        $('#exportModal').on('show.bs.modal', function() {
            // Reset form when modal opens
            $('#exportForm')[0].reset();
            $('input[name="columns[]"]').prop('checked', true);
            $('#csv').prop('checked', true);
        });
        
        $('#exportModal').on('hidden.bs.modal', function() {
            // Reset loading state when modal closes
            $('#exportSubmitBtn').removeClass('loading').prop('disabled', false);
        });
    });
</script>

<script type="text/javascript" defer>
const columns = [{
        name: 'check',
        data: 'check',
        title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
        width: '0%',
        exportable: false,
        orderable: false,
        searchable: false,
    },
    {
        data: 'name',
        name: 'name',
        title: "{{ __('branch.lbl_name') }}",
        width: '15%',
    },
    {
        data: 'contact_number',
        name: 'contact_number',
        title: "{{ __('branch.lbl_contact_number') }}",
        width: '15%',
    },
    {
        data: 'manager_id',
        name: 'manager_id',
        title: "{{ __('branch.lbl_manager_name') }}",
        width: '15%',
    },
    {
        data: 'address.city',
        name: 'address.city',
        title: "{{ __('branch.lbl_city') }}",
        width: '15%',
    },
    {
        data: 'address.postal_code',
        name: 'address.postal_code',
        title: "{{ __('branch.lbl_postal_code') }}",
        width: '10%',
    },
    {
        data: 'assign',
        name: 'assign',
        title: "{{ __('messages.assign_staff') }}",
        orderable: false,
        searchable: false
    },
    {
        data: 'branch_for',
        name: 'branch_for',
        title: "{{ __('branch.lbl_branch_for') }}",
        width: '12%'
    },
    {
        data: 'status',
        name: 'status',
        orderable: true,
        searchable: true,
        title: "{{ __('branch.lbl_status') }}",
        width: '5%',
    },
    {
        data: 'updated_at',
        name: 'updated_at',
        title: "{{ __('branch.lbl_update_at') }}",
        orderable: true,
        visible: false,
    },

]

const actionColumn = [{
        data: 'action',
        name: 'action',
        orderable: false,
        searchable: false,
        title: "{{ __('branch.lbl_action') }}",
        width: '5%'
    }]

    // Check permissions
    const hasEditPermission = @json(auth()->user()->can('edit_branch'));
    const hasDeletePermission = @json(auth()->user()->can('delete_branch'));

    // Add the action column only if the user has edit or delete permission
    let finalColumns = [...columns];
    if (hasEditPermission || hasDeletePermission) {
        finalColumns = [...finalColumns, ...actionColumn];
    }

    const customFieldColumns = JSON.parse(@json($columns))

    finalColumns = [
        ...finalColumns,
        ...customFieldColumns
    ]

    document.addEventListener('DOMContentLoaded', (event) => {
        initDatatable({
            url: '{{ route("backend.$module_name.index_data") }}',
            finalColumns,
            orderColumn: [
                [9, "desc"]
            ],
        })
    })

    function resetQuickAction() {
        const actionValue = $('#quick-action-type').val();
        if (actionValue != '') {
            $('#quick-action-apply').removeAttr('disabled');

            if (actionValue == 'change-status') {
                $('.quick-action-field').addClass('d-none');
                $('#change-status-action').removeClass('d-none');
            } else {
                $('.quick-action-field').addClass('d-none');
            }

        } else {
            $('#quick-action-apply').attr('disabled', true);
            $('.quick-action-field').addClass('d-none');
        }
    }

    $('#quick-action-type').change(function() {
        resetQuickAction()
    });
    </script>
@endpush
