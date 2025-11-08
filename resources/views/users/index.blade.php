@extends('backend.layouts.app')

@section('title')
    {{ $module_title }}
@endsection

@section('content')

    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-6 col-sm-12">
                <h2>{{ __('messages.vendors') }}
                    <small class="text-muted">{{ config('app.name') }}</small>
                </h2>
            </div>
        </div>
    </div>

    <div class="container-fluid">

    <div class="card">
        <div class="card-body">

            <div class="d-flex justify-content-between flex-wrap gap-3 align-items-center">
                <x-backend.quick-action url="{{ route('backend.users.bulk_action') }}">
                    <div class="">
                        <select name="action_type" class="form-control show-tick" id="quick-action-type">
                            <option selected value="">{{ __('messages.no_action') }}</option>
                            <option value="change-status">{{ __('messages.status') }}</option>
                            <option value="delete">{{ __('messages.delete') }}</option>
                        </select>
                    </div>
                    <div class="select-status d-none quick-action-field" id="change-status-action">
                        <select name="status" class="form-select " id="status">
                            <option value="1" selected>{{ __('messages.active') }}</option>
                            <option value="0">{{ __('messages.inactive') }}</option>
                        </select>
                    </div>
                </x-backend.quick-action>
                <x-backend.section-header>
                    <x-slot name="toolbar">
                        <div>
                            <div class="datatable-filter">
                                <select name="column_status" id="column_status" class="" data-filter="select">
                                    <option value="">{{ __('messages.all') }}</option>
                                    <option value="0">{{ __('messages.inactive') }}</option>
                                    <option value="1">{{ __('messages.active') }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group flex-nowrap top-input-search">
                            <span class="input-group-text" id="addon-wrapping"><i
                                    class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" class="form-control dt-search"
                                placeholder="{{ __('messages.search') }}..." aria-label="Search"
                                aria-describedby="addon-wrapping">
                        </div>
                        @if (userIsSuperAdmin())
                            <a href="{{ route('backend.users.create') }}" class="btn btn-primary" title="Create Vendor">
                                <i class="fas fa-plus-circle"></i>
                                {{ __('frontend.new') }}
                            </a>
                        @endif

                    </x-slot>
                </x-backend.section-header>
            </div>

            <table id="datatable" class="table table-bordered table-striped table-hover js-basic-example dataTable">
            </table>
        </div>
    </div>

    <div data-render="app">

    </div>
    </div>
@endsection

@push('after-styles')
    <!-- DataTables Core and Extensions -->
@endpush

@push('after-scripts')
    <script src="{{ mix('js/vue.min.js') }}"></script>
    <script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>

    <script type="text/javascript" defer>
        const columns = [{
                data: 'id',
                name: 'id',
                visible: false
            },
            {
                name: 'check',
                data: 'check',
                title: '<div class="checkbox"><input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)"><label for="select-all-table"></label></div>',
                className: 'text-center pt-0',
                exportable: false,
                orderable: false,
                searchable: false,
            },
            {
                data: 'name',
                name: 'name',
                title: '{{ __('users.name') }}'
            },
            {
                data: 'email',
                name: 'email',
                title: '{{ __('users.email') }}'
            },
            {
                data: 'slug',
                name: 'slug',
                title: '{{ __('users.website_identifier') }}'
            },
            {
                data: 'gender',
                name: 'gender',
                title: '{{ __('users.gender') }}'
            },
            {
                data: 'mobile',
                name: 'mobile',
                title: '{{ __('users.mobile') }}'
            },
            {
                data: 'created_at',
                name: 'created_at',
                title: '{{ __('users.created_at') }}'
            },
            {
                data: 'status',
                name: 'status',
                orderable: false,
                searchable: true,
                title: '{{ __('users.status') }}'
            },
        ];


        const actionColumn = [{
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            title: "{{ __('messages.lbl_action') }}",
            render: function(data, type, row) {
                let buttons = `
            <button class="btn btn-primary btn-sm btn-edit" onclick="editUser(${row.id})" title="Edit" data-bs-toggle="tooltip">
                <i class="fas fa-edit"></i>
            </button>

            <button class="btn btn-danger btn-sm"
                onclick="confirmDelete(${row.id}, '${row.name.replace(/'/g, "\\'")}')"
                data-bs-toggle="tooltip"
                title="{{ __('messages.delete') }}">
                <i class="fa-solid fa-trash"></i>
            </button>
        `;
                return buttons;
            }
        }];

        function confirmDelete(id, name, moduleTitle = 'Vendor') {
            const route = "{{ route('backend.users.delete', '__ID__') }}".replace('__ID__', id);

            Swal.fire({
                title: `Are you sure you want to delete this ${name} ${moduleTitle}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '{{ __('messages.yes_delete') }}',
                cancelButtonText: '{{ __('messages.cancel') }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: route,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            $('#datatable').DataTable().ajax.reload(null, false);
                            Swal.fire('{{ __('messages.deleted') }}', response.message, 'success');
                        },
                        error: function() {
                            Swal.fire('{{ __('messages.error') }}',
                                '{{ __('messages.delete_failed') }}', 'error');
                        }
                    });
                }
            });
        }

        let finalColumns = [
            ...columns,
            ...actionColumn
        ]

        document.addEventListener('DOMContentLoaded', (event) => {
            const table = initDatatable({
                url: '{{ route("backend.$module_name.index_data") }}',
                finalColumns,
                order: [
                    [0, 'desc']
                ],
                data: {
                    status: $('#filter_status').val(), // Initial value for status filter
                },
            })

            $('#filter_status').change(function() {
                $('#datatable').DataTable().ajax.reload(); // Reload DataTable with new filter value
            });
            $(document).on('click', '.delete-user-btn', function() {
                const $btn = $(this);
                $btn.tooltip('dispose');
                $('.tooltip').remove(); // optional cleanup
            });
        })

        function editUser(user_id) {
            var route = "{{ route('backend.users.edit', 'user_id') }}".replace('user_id', user_id);
            window.location.href = route;
        }

        function deleteUser(user_id) {
            var route = "{{ route('backend.users.delete', 'user_id') }}".replace('user_id', user_id);
            confirmDelete(route, user_id);
        }

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
