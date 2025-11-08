@extends('backend.layouts.app')

@section('title')
    {{ __($module_action) }}
@endsection

@push('after-styles')
    <link rel="stylesheet" href="{{ mix('modules/constant/style.css') }}">
@endpush

@section('content')
    <div class="card">
        <div class="card-body">

            <x-backend.section-header>
                <x-slot name="toolbar">
                    @if (userIsSuperAdmin())
                        @if ($module_name == 'payment')
                            <a href="{{ route('backend.payment.create') }}" class="btn btn-primary" title="Create Payment">
                                <i class="fas fa-plus-circle"></i>
                                {{ __('messages.new') }}
                            </a>
                        @endif
                    @endif

                </x-slot>
            </x-backend.section-header>
            <div class="mt-4">
                <div class="row mb-2">
                    <div class="col-lg-3 col-md-4">
                        <div class="d-flex gap-3 align-items-center">
                            <div class="flex-grow-1">
                                <select id="select_action" class="form-select select2"
                                    {{ $approve_payment_count == 0 ? 'disabled' : '' }}>
                                    <option value="">{{ __('messages.select_action') }}</option>
                                    <option value="approve">{{ __('messages.approve') }}</option>
                                    <option value="delete">{{ __('messages.delete') }}</option>
                                </select>
                            </div>
                            <button class="btn btn-primary" id="apply_acton" type="button"
                                {{ $approve_payment_count == 0 ? 'disabled' : '' }}>{{ __('messages.apply') }}</button>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-8 mt-md-0 mt-3">
                        <div class="d-flex gap-2 align-items-center flex-md-nowrap flex-wrap">
                            <div class="flex-grow-1">
                                <select id="plan-filter" class="form-select select2">
                                    <option value="">{{ __('messages.select_plan') }}</option>
                                    <!-- Populate plans dynamically -->
                                    @foreach ($plans as $plan)
                                        <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-grow-1">
                                <input type="text" name="date_range" id="date_range" value=""
                                    class="form-control dashboard-date-range"
                                    placeholder="{{ __('messages.select_date_range') }} " />
                            </div>
                            <div class="d-flex gap-1">
                                <button id="filter-btn" class="btn btn-primary">{{ __('messages.filter') }}</button>
                                <button id="reset-btn" class="btn btn-primary">{{ __('messages.reset') }}</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-12 mt-lg-0 mt-3">
                        <div class="input-group">
                            <span class="input-group-text" id="addon-wrapping">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" name="search" class="form-control dt-search"
                                placeholder="{{ __('messages.search') }}..." aria-label="Search"
                                aria-describedby="addon-wrapping">
                        </div>
                    </div>
                </div>
            </div>
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
        const module_name = '{{ $module_name }}';
        const columns = [{
                data: 'id',
                name: 'id',
                visible: false
            },
            {
                name: 'check',
                data: 'check',
                title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                width: '0%',
                exportable: false,
                orderable: false,
                searchable: false,
            },
            {
                data: 'user.first_name',
                name: 'user.first_name',
                title: "{{ __('messages.lbl_first_name') }}",
                render: function(data, type, row) {
                    return row.user ? row.user.first_name : 'Deleted User'; // Check if user exists
                }
            },
            {
                data: 'user.last_name',
                name: 'user.last_name',
                title: "{{ __('messages.lbl_last_name') }}",
                render: function(data, type, row) {
                    return row.user ? row.user.last_name : 'Deleted User'; // Check if user exists
                }
            },
            {
                data: 'plan_name',
                name: 'plan_name',
                title: "{{ __('messages.lbl_plan') }}"
            },
            {
                    data: 'duration',
                    name: 'duration',
                    title: "{{ __('frontend.duration') }}"
            },
            {
                data: 'amount',
                name: 'amount',
                title: "{{ __('messages.lbl_amount') }}"
            },
            {
                data: 'payment_date',
                name: 'payment_date',
                title: "{{ __('messages.lbl_date') }}"
            },
        ];

        const actionColumn = [{
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            title: "{{ __('messages.lbl_action') }}",
            render: function(data, type, row) {
                let buttons = ''; // Ensure buttons is defined
                console.log(row);
                if (row.status === 'Approved') {
                    buttons += `
                <span class="text-capitalize badge bg-success-subtle py-2 px-3">
                    <i class="fa-solid fa-circle mx-1" style="color: #22c55e"></i>Approved
                </span>
            `;
                } else {
                    buttons += `
                <button class="btn btn-primary btn-sm btn-edit" onclick="editPayment(${row.id})" title="{{ __('messages.edit') }}" data-bs-toggle="tooltip">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger btn-sm btn-delete me-1" onclick="deletePayment(${row.id})" title="{{ __('messages.delete') }}" data-bs-toggle="tooltip">
                    <i class="fas fa-trash"></i>
                </button>
                <button class="btn btn-success btn-sm btn-approve" onclick="approvePayment(${row.id})" title="{{ __('messages.approve') }}" data-bs-toggle="tooltip">
                    {{ __('messages.approve') }}
                </button>
            `;
                }
                console.log(buttons);
                return buttons;
            }
        }];
        let finalColumns = [...columns];
        if (module_name === 'payment') {
            finalColumns = [...columns, ...actionColumn]; // Include action column if module_name is 'payment'
        }

        document.addEventListener('DOMContentLoaded', (event) => {
            var url = (module_name == 'payment') ? '{{ route("backend.$module_name.index_data") }}' :
                '{{ route('backend.subscriptions.pending_subscription') }}'
            const plan_id = $('#plan-filter').val();
            const date_range = $('input[name="date_range"]').val();
            const search = $('input[name="search"]').val();

            initDatatable({
                url: url,
                finalColumns,
                order: [
                    [0, 'desc']
                ],
                ajax: {
                    url: url, // Use the same URL for the request
                    data: function(d) {
                        // Add custom parameters to the request
                        d.plan_id = plan_id;
                        d.date_range = date_range;
                        d.search = search;
                    }
                }
            });
        })

        // Function to check if any filter is applied
        function isAnyFilterApplied() {
            const planId = $('#plan-filter').val();
            const dateRange = $('input[name="date_range"]').val();
            const search = $('input[name="search"]').val();

            return planId || dateRange || search;
        }

        // Function to toggle the visibility of the Reset button
        function toggleResetButton() {
            const resetButton = $('#reset-btn');
            if (isAnyFilterApplied()) {
                resetButton.show();
            } else {
                resetButton.hide();
            }
        }

        // Initialize the Reset button visibility
        toggleResetButton();

        // Add event listeners to filter inputs to update the Reset button visibility
        $('#plan-filter').on('change', toggleResetButton);
        $('input[name="date_range"]').on('change', toggleResetButton);
        $('input[name="search"]').on('keyup', toggleResetButton);

        // When the filter button is clicked
        $('#filter-btn').on('click', function() {
            // Get the updated filter values
            const plan_id = $('#plan-filter').val();
            const date_range = $('input[name="date_range"]').val();
            const search = $('input[name="search"]').val();

            // Optionally, you can also pass the filter data if needed:
            $('#datatable').DataTable().settings()[0].ajax.data = {
                plan_id: plan_id,
                date_range: date_range,
                search: search
            };

            // Trigger reload again with updated filters
            $('#datatable').DataTable().ajax.reload();
        });

        // When the reset button is clicked
        $('#reset-btn').on('click', function() {
            // Clear the filters
            $('#plan-filter').val('').trigger('change'); // Reset Select2 dropdown
            $('input[name="date_range"]').val(''); // Clear the date range input
            $('input[name="search"]').val('');
            let fp = $('#date_range').get(0)._flatpickr; // Get Flatpickr instance

            if (fp) {
                fp.clear(); // Clear selection properly
            }

            // Optionally, you can also pass the filter data if needed:
            $('#datatable').DataTable().settings()[0].ajax.data = {
                plan_id: '',
                date_range: '',
                search: '',
            };

            // Reload the DataTable without filters
            $('#datatable').DataTable().ajax.reload();

            // Hide the Reset button after resetting
            toggleResetButton();
        });

        function selectedIds() {
            const selectedIds = [];
            $('input[name="select_payment"]:checked').each(function() {
                selectedIds.push($(this).data('id'));
            });
            return selectedIds;
        }

        $('#apply_acton').on('click', function() {
            // Clear the filters
            var action = $('#select_action').val();
            var payment_id = selectedIds();
            if (payment_id.length > 0) {
                // Send AJAX request
                if (action == 'approve') {
                    approvePayment(payment_id)
                } else if (action == 'delete') {
                    deletePayment(payment_id)
                }

            } else {
                alert('Please select at least one payment.');
            }
        });

        function editPayment(payment_id) {
            var route = "{{ route('backend.payment.edit', 'payment_id') }}".replace('payment_id', payment_id);
            window.location.href = route;
        }

        // function deletePayment(payment_id) {
        //     var route = "{{ route('backend.payment.delete') }}";

        function deletePayment(payment_id) {
            $('.btn-delete').tooltip('dispose');
            $('.tooltip').remove();
            var route = "{{ route('backend.payment.delete') }}";


            Swal.fire({
                title: "{{ __('messages.delete_payment_confirmation') }}",
                text: "You won't be able to revert this!",
                icon: 'question',
                showCancelButton: false, // Disable default buttons
                showConfirmButton: false,
                html: `
                    <div style="display: flex; justify-content: center; gap: 10px;">
                        <button id="cancelButton" class="swal2-cancel swal2-styled" style="background-color: #6c757d; color: #fff; padding: 10px 20px; border: none; border-radius: 5px;">{{ __('messages.cancel') }}</button>
                        <button id="confirmButton" class="swal2-confirm swal2-styled" style="background-color: #d33; color: #fff; padding: 10px 20px; border: none; border-radius: 5px;">{{ __('messages.yes_delete') }}</button>
                    </div>
                `
            });

            // Add event listeners for custom buttons
            document.getElementById('confirmButton').addEventListener('click', function() {
                $.ajax({
                    url: route,
                    type: 'GET',
                    data: {
                        ids: payment_id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        // Remove the deleted payment row from the table or update the UI
                        var table = $('#datatable').DataTable();
                        table.row(`#payment-row-${payment_id}`).remove()
                            .draw(); // Remove the row dynamically

                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted',
                            text: 'Payment has been deleted successfully',
                            confirmButtonColor: '#6f42c1',
                        });
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            'An error occurred while deleting the Payment.',
                            'error'
                        );
                        console.error(xhr.responseText);
                    }
                });
            });

            document.getElementById('cancelButton').addEventListener('click', function() {
                Swal.close(); // Close the Swal dialog
            });
        }

        function approvePayment(payment_id) {
            $.ajax({
                url: '{{ route('backend.payment.approve') }}', // Your route
                method: 'POST', // Use POST for modifying data
                data: {
                    ids: payment_id,
                    _token: '{{ csrf_token() }}' // CSRF token for Laravel
                },
                success: function(response) {
                    // Handle success (e.g., show a success message)
                    Swal.fire("Success", response.message, "success")
                        .then((result) => {
                            location.reload();
                        });

                },
                error: function(xhr) {
                    // Handle error (e.g., show an error message)
                    console.error('Error approving payments:', xhr.responseText);
                }
            });
        }

        // Function to handle "Select All" checkbox behavior
        function selectAllTable(checkbox) {
            // Check if the "Select All" checkbox is checked
            const isChecked = checkbox.checked;

            // Select or unselect all checkboxes
            $('input[name="select_payment"]').each(function() {
                $(this).prop('checked', isChecked); // Set each checkbox based on "Select All"
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('#date_range', {
                dateFormat: "Y-m-d",
                mode: "range",
            });

        });

        document.addEventListener("DOMContentLoaded", function() {
            const selectAction = document.getElementById("select_action");
            const applyButton = document.getElementById("apply_acton");

            function toggleActionState() {
                // Check if any checkbox is checked
                const isAnyChecked = document.querySelectorAll("input[name='select_payment']:checked").length > 0;

                // Enable or disable select and button based on checkbox selection
                if (isAnyChecked) {
                    selectAction.removeAttribute("disabled");
                    applyButton.removeAttribute("disabled");
                } else {
                    selectAction.setAttribute("disabled", "disabled");
                    applyButton.setAttribute("disabled", "disabled");
                }
            }

            // **Use event delegation for dynamically loaded checkboxes**
            document.addEventListener("change", function(event) {
                if (event.target.matches("input[name='select_payment']")) {
                    toggleActionState();
                }
            });

            // Also apply when "Select All" checkbox is clicked
            document.getElementById("select-all-table")?.addEventListener("change", function() {
                toggleActionState();
            });

            // Run on page load in case of pre-checked items
            toggleActionState();
        });
    </script>
@endpush
