@extends('backend.layouts.app')

@section('title')
    {{ $module_title }}
@endsection


@push('after-styles')
    <link rel="stylesheet" href="{{ mix('modules/constant/style.css') }}">
@endpush
@section('content')
    <div class="card">
        <div class="card-body">
            <x-backend.section-header>
                <x-slot name="toolbar">
                    <div class="input-group flex-nowrap top-input-search">
                        <span class="input-group-text" id="addon-wrapping"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}..."
                            aria-label="Search" aria-describedby="addon-wrapping">
                    </div>
                    @if (userIsSuperAdmin())
                        <a href="{{ route('backend.plan.tax.create') }}" class="btn btn-primary" title="Create Tax">
                            <i class="fas fa-plus-circle"></i>
                            {{ __('messages.new') }}
                        </a>
                    @endif

                </x-slot>
            </x-backend.section-header>
            <table id="datatable" class="table border table-responsive rounded">
            </table>
        </div>
    </div>

    <div data-render="app">


    </div>
@endsection

@push('after-styles')
    <!-- DataTables Core and Extensions -->
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push('after-scripts')
    <script src="{{ mix('modules/subscriptions/script.js') }}"></script>
    <script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>

    <!-- DataTables Core and Extensions -->
    <script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
    <script type="text/javascript" defer>
        const columns = [{
                data: 'id',
                name: 'id',
                visible: false
            },
            {
                data: 'title',
                name: 'title',
                title: "{{ __('frontend.title') }}"
            },
            {
                data: 'value',
                name: 'value',
                title: "{{ __('frontend.value') }}"
            },
            // {
            //     data: 'type',
            //     name: 'type',
            //     title: "{{ __('frontend.type') }}"
            // },
            {
                data: 'plans',
                name: 'plans',
                title: "{{ __('frontend.plans') }}",
                orderable: false
            },
            {
                data: 'status',
                name: 'status',
                orderable: false,
                searchable: true,
                title: "{{ __('frontend.status') }}"
            },
        ];


        const actionColumn = [{
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            title: "{{ __('frontend.action') }}",
            render: function(data, type, row) {
    let buttons = `
        <button class="btn btn-primary btn-sm btn-edit" onclick="editTax(${row.id})" title="Edit" data-bs-toggle="tooltip">
            <i class="fas fa-edit"></i>
        </button>
        <button class="btn btn-danger btn-sm btn-delete me-1" 
            onclick="confirmDelete(${row.id}, '${row.title.replace(/'/g, "\\'")}', '{{ $module_title }}')" 
            title="{{ __('messages.delete') }}" data-bs-toggle="tooltip">
            <i class="fas fa-trash"></i>
        </button>
    `;
    return buttons;
}
        }];
        function confirmDelete(id, name, moduleTitle) {
    Swal.fire({
        title: `Are you sure you want to delete this ${name} ${moduleTitle}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '{{ __("messages.yes_delete") }}',
        cancelButtonText: '{{ __("messages.cancel") }}'
    }).then((result) => {
        if (result.isConfirmed) {
            const route = "{{ route('backend.plan.tax.delete', '__ID__') }}".replace('__ID__', id);

            $.ajax({
                url: route,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    $('#datatable').DataTable().ajax.reload(null, false);
                    Swal.fire('{{ __("messages.deleted") }}', response.message, 'success');
                },
                error: function() {
                    Swal.fire('{{ __("messages.error") }}', '{{ __("messages.delete_failed") }}', 'error');
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
            initDatatable({
                url: '{{ route("backend.$module_name.index_data") }}',
                finalColumns,
                order: [
                    [0, 'desc']
                ]
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

        $(document).on('update_quick_action', function() {
            // resetActionButtons()
        })

        function editTax(tax_id) {
            var route = "{{ route('backend.plan.tax.edit', 'tax_id') }}".replace('tax_id', tax_id);
            window.location.href = route;
        }

        // function deleteTax(tax_id) {
        //     var route = "{{ route('backend.plan.tax.delete', 'tax_id') }}".replace('tax_id', tax_id);
        //     confirmDelete(route, tax_id);
        // }
        function deleteTax(tax_id) {
            $('.btn-danger').tooltip('dispose');  
            $('.tooltip').remove();

            var route = "{{ route('backend.plan.tax.delete', 'tax_id') }}".replace('tax_id', tax_id);
            confirmDelete(route, tax_id);
        }

        $('#approve-button').on('click', function() {
            const selectedIds = [];
            $('input[name="select_payment"]:checked').each(function() {
                selectedIds.push($(this).data('id'));
            });

            if (selectedIds.length > 0) {
                // Send AJAX request
                $.ajax({
                    url: '{{ route('backend.plan.tax.approve') }}', // Your route
                    method: 'POST', // Use POST for modifying data
                    data: {
                        ids: selectedIds,
                        _token: '{{ csrf_token() }}' // CSRF token for Laravel
                    },
                    success: function(response) {
                        // Handle success (e.g., show a success message)
                        Swal.fire("{{ __('messages.success') }}", response.message, "success")
                            .then((result) => {
                                location.reload();
                            });

                    },
                    error: function(xhr) {
                        // Handle error (e.g., show an error message)
                        console.error("{{ __('messages.error_approving_payments') }}", xhr
                            .responseText);
                    }
                });
            } else {
                alert('Please select at least one payment to approve.');
            }
        });
        $(document).on('click', '[data-bs-toggle="tooltip"]', function () {
            $(this).tooltip('dispose');
            $('.tooltip').remove();
        });

    </script>
@endpush
