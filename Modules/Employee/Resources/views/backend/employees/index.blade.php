@extends('backend.layouts.app')

@section('title')
{{ __($module_title) }}
@endsection


@push('after-styles')
    <link rel="stylesheet" href="{{ mix('modules/constant/style.css') }}">
@endpush
@section('content')
    <div class="card">
        <div class="card-body">
            <x-backend.section-header>
                <div class="d-flex flex-wrap gap-3">
                    @if (auth()->user()->can('edit_staff') || auth()->user()->can('delete_staff'))
                        <x-backend.quick-action url='{{ route("backend.$module_name.bulk_action") }}'>
                            <div class="">
                                <select name="action_type" class="form-control select2 col-12" id="quick-action-type"
                                    style="width:100%">
                                    <option selected disabled value="">{{ __('messages.no_action') }}</option>
                                    @can('edit_staff')
                                        <option value="change-status">{{ __('messages.status') }}</option>
                                    @endcan
                                    @can('delete_staff')
                                        <option value="delete">{{ __('messages.delete') }}</option>
                                    @endcan
                                </select>
                            </div>
                            <div class="select-status d-none quick-action-field" id="change-status-action">
                                <select name="status" class="form-control select2" id="status" style="width:100%">
                                    <option value="1">{{ __('messages.active') }}</option>
                                    <option value="0">{{ __('messages.inactive') }}</option>
                                </select>
                            </div>
                        </x-backend.quick-action>
                    @endif
                    <div>
                        <button type="button" class="btn btn-secondary" data-modal="export">
                            <i class="fa-solid fa-download"></i> {{ __('messages.export') }}
                        </button>

                    </div>
                </div>
                <x-slot name="toolbar">
                    <div class="input-group flex-nowrap top-input-search">
                        <span class="input-group-text" id="addon-wrapping"><i
                                class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}..."
                            aria-label="Search" aria-describedby="addon-wrapping">
                    </div>
                    @hasPermission('add_staff')
                        <x-buttons.offcanvas target='#form-offcanvas' class="customer-create-btn"
                            title="">{{ __('messages.new') }}
                        </x-buttons.offcanvas>
                    @endhasPermission
                </x-slot>
            </x-backend.section-header>
            <table id="datatable" class="table table-striped border table-responsive">
            </table>
        </div>
    </div>
     <div data-render="app">
     </div>
@include('employee::backend.employees.form-offcanvas', [
            'selected_session_branch_id' => $selected_branch_id !== '' ? $selected_branch_id : null,
            'default_image' => default_user_avatar(),
            'create_title' => __('messages.new') . ' ' . __('employee.singular_title_manager'),
            'edit_title' => __('messages.edit') . ' ' . __('employee.singular_title_manager'),
            'customefield' => $customefield,
        ])
@include('employee::backend.employees.change_password', ['createTitle' => __('messages.change_password')])
@include('employee::backend.employees.service_view_offcanvas')

@endsection

@push('after-styles')
    <!-- DataTables Core and Extensions -->
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push('after-scripts')
    <script src="{{ mix('modules/employee/script.js') }}"></script>
    <script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>
    <script src="{{ asset('js/form-modal/index.js') }}" defer></script>

    <!-- DataTables Core and Extensions -->
    <script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
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
                    data: { type: 'staff' },
                    dataType: "json",
                    success: function (response) {
                        if (!response.status) {
                            event.preventDefault(); // Prevent default action
                            window.errorSnackbar(response.message);
                            button.removeAttr("data-crud-id"); // Remove attribute if status is false
                            offcanvasInstance.hide();
                        } else {
                            button.attr("data-crud-id", 0); // Set a valid value if required
                            offcanvasInstance.show(); // Show the offcanvas only if allowed
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", error);
                    }
                });
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
            data: 'employee_id',
            name: 'employee_id',
             title: "{{ __('booking.lbl_staff_name') }}"
            },

            {
                data: 'service',
                name: 'service',
                title: "{{ __('package.lbl_no_name') }}",
                orderable: false,
                searchable: false
            },
            {
                data: 'branch_id',
                name: 'branch_id',
                title: "{{ __('branch.title') }}",
                orderable: false,
                searchable: false
            },
            {
                data: 'is_manager',
                name: 'is_manager',
                title: "{{ __('employee.lbl_role') }}"
            },
            {
                data: 'email_verified_at',
                name: 'email_verified_at',
                orderable: true,
                searchable: false,
                title: "{{ __('employee.lbl_verification_status') }}"
            },

            {
                data: 'status',
                name: 'status',
                orderable: true,
                searchable: true,
                title: "{{ __('employee.lbl_status') }}"
            },
            {
                data: 'updated_at',
                name: 'updated_at',
                title: "{{ __('customer.lbl_update_at') }}",
                orderable: true,
                visible: false,
            },
        ]

        const actionColumn = [{
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
             title: "{{ __('employee.lbl_action') }}"
       }]

        const customFieldColumns = JSON.parse(@json($columns))

        let finalColumns = [
            ...columns,
            ...customFieldColumns,
            ...actionColumn
        ]

        document.addEventListener('DOMContentLoaded', (event) => {
            initDatatable({
                url: '{{ route("backend.$module_name.index_data") }}',
                finalColumns,
                orderColumn: [
                    [7, "desc"]
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
