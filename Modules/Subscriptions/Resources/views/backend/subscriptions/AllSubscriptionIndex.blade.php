@extends('backend.layouts.app')

@section('title')
{{ __($module_action) }}
@endsection


@push('after-styles')
<link rel="stylesheet" href="{{ mix('modules/constant/style.css') }}">
@endpush
@section('content')
<div class="row mb-4">
    <!-- Total Subscriptions Card -->
    <div class="col-md-3">
        <div class="dashboard-cards services p-5 bg-primary-subtle rounded">
            <div class="d-flex align-items-center justify-content-between title">
                <h2 class="text-primary fw-semibold mb-0">
                    {{ $activeSubscriptions + $expiredSubscriptions + $pendingSubscriptions }}
                </h2>
                <div class="dashboard-icon fs-4" data-bs-toggle="tooltip"
                    data-bs-title="{{ __('dashboard.total_subscriptions') }}">
                    <i class="fa-solid fa-bars"></i>
                </div>
            </div>
            <h5 class="mb-0">{{ __('dashboard.total_subscriptions') }}</h5>
        </div>
    </div>

    <!-- Active Subscriptions Card -->
    <div class="col-md-3">
        <a href="{{ route('backend.subscriptions.index') }}" class="text-decoration-none">
            <div class="dashboard-cards services p-5 bg-primary-subtle rounded">
                <div class="d-flex align-items-center justify-content-between title">
                    <h2 class="text-primary fw-semibold mb-0">{{ $activeSubscriptions ?? 0 }}</h2>
                    <div class="dashboard-icon fs-4" data-bs-toggle="tooltip"
                        data-bs-title="{{ __('dashboard.total_active_subscriptions') }}">
                        <i class="fa-solid fa-web-awesome"></i>
                    </div>
                </div>
                <h5 class="mb-0">{{ __('dashboard.total_active_subscriptions') }}</h5>
            </div>
        </a>
    </div>

    <!-- Expired Subscriptions Card -->
    <div class="col-md-3">
        <a href="{{ route('backend.subscriptions.expired') }}" class="text-decoration-none">
            <div class="dashboard-cards services p-5 bg-primary-subtle rounded">
                <div class="d-flex align-items-center justify-content-between title">
                    <h2 class="text-primary fw-semibold mb-0">{{ $expiredSubscriptions ?? 0 }}</h2>
                    <div class="dashboard-icon fs-4" data-bs-toggle="tooltip"
                        data-bs-title="{{ __('dashboard.total_expired_subscriptions') }}">
                        <i class="fa-solid fa-calendar-xmark"></i>

                    </div>
                </div>
                <h5 class="mb-0">{{ __('dashboard.total_expired_subscriptions') }}</h5>
            </div>
        </a>
    </div>

    <!-- Pending Subscriptions Card -->
    <div class="col-md-3">
        <a href="{{ route('backend.subscriptions.pending') }}" class="text-decoration-none">
            <div class="dashboard-cards services p-5 bg-primary-subtle rounded">
                <div class="d-flex align-items-center justify-content-between title">
                    <h2 class="text-primary fw-semibold mb-0">{{ $pendingSubscriptions ?? 0 }}</h2>
                    <div class="dashboard-icon fs-4" data-bs-toggle="tooltip"
                        data-bs-title="{{ __('dashboard.total_pending_subscriptions') }}">
                        <i class="fa-solid fa-exclamation"></i>

                    </div>
                </div>
                <h5 class="mb-0">{{ __('dashboard.total_pending_subscriptions') }}</h5>
            </div>
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">


    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-2">
                <select id="plan-filter" class="form-select select2">
                    <option value="">{{__('messages.select_plan')}}</option>
                    @foreach($plans as $plan)
                    <option value="{{ $plan->id }}" {{ request()->get('plan_id') == $plan->id ? 'selected' : '' }}>
                        {{ $plan->name }}
                        ({{ $plan->duration . ' ' . str_replace('ly', '', $plan->type) }})

                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mt-md-0 mt-3">
                <input type="text" name="date_range" id="date_range" value="" class="form-control dashboard-date-range"
                    placeholder="{{ __('messages.select_date_range') }} " />
            </div>



            <div class="col-md-3 mt-md-0 mt-3">
                <button id="filter-btn" class="btn btn-primary me-2">{{ __('messages.filter') }}</button>
                <button id="reset-btn" class="btn btn-primary">{{ __('messages.reset') }}</button>
            </div>

            <div class="col-md-2">
                <div class="form-group">
                    <select class="form-select" id="status_filter" name="status">
                        <label class="form-label">{{ __('frontend.status') }}</label>
                        <option value="">{{ __('frontend.status') }}</option>
                        <option value="active">{{ __('frontend.active') }}</option>
                        <option value="inactive">{{ __('frontend.inactive') }}</option>
                        <option value="pending">{{ __('frontend.pending') }}</option>
                        <option value="cancel">{{ __('frontend.cancelled') }}</option>
                    </select>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 mt-lg-0 mt-3">
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
<!-- DataTables Core and Extensions -->
<script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>

<script src="{{ mix('js/jszip.min.js') }}"></script>
<script src="{{ mix('js/pdfmake.min.js') }}"></script>
<script src="{{ mix('js/vfs_fonts.js') }}"></script>
<script src="{{ mix('js/dataTables.buttons.min.js') }}"></script>
<script src="{{ mix('js/buttons.html5.min.js') }}"></script>
<script src="{{ mix('js/buttons.print.min.js') }}"></script>



<script type="text/javascript" defer>
    const search = $('input[name="search"]').val();
        const data_table_limit = $('meta[name="data_table_limit"]').attr('content')

        document.addEventListener('DOMContentLoaded', (event) => {
            const isExpiredSubscriptions = "{{ $module_action }}" === 'Expired Subscriptions';
            const dataTable = $('#datatable').DataTable({
                processing: true,
                autoWidth: false,
                responsive: true,
                pageLength: data_table_limit,
                lengthMenu: [
        [5, 10, 15, 20, 25, 100, -1],
        [5, 10, 15, 20, 25, 100, 'All']
      ],  language: {
        processing: window.translations.processing,
        search: window.translations.search,
        lengthMenu: window.translations.lengthMenu,
        info: window.translations.info,
        infoEmpty: window.translations.infoEmpty,
        infoFiltered: window.translations.infoFiltered,
        loadingRecords: window.translations.loadingRecords,
        zeroRecords: window.translations.zeroRecords,
        paginate: {
          first: window.translations.paginate.first,
          last: window.translations.paginate.last,
          next: window.translations.paginate.next,
          previous: window.translations.paginate.previous
        }
      },
                dom: '<"row"<"col-12" B>>' +  // Move export buttons to the top
                    '<"table-responsive my-3" rt>' +  // Keep the table in the middle
                    '<"row align-items-center data_table_widgets"<"col-md-6" <"d-flex align-items-center flex-wrap gap-3" l i>><"col-md-6" p>>', // Keep the length menu and search box below the table
                ajax: {
                    url: '{{ route("backend.subscriptions.all_subscription_data") }}',
                    data: function(d) {
                        d.plan_id = $('#plan-filter').val() || '';
                        d.date_range = $('input[name="date_range"]').val() || '';
                        d.status = $('#status_filter').val() || '';
                        d.subscription_type = '{{ $subscription_type ?? null; }}';
                    }
                },
                buttons: [
                    {
                        text: ' {{ __("messages.export_excel") }}',
                        className: 'btn btn-sm p-3 py-2 btn-success',
                        action: function(e, dt, node, config) {
                            const plan_id = $('#plan-filter').val();
                            const date_range = $('input[name="date_range"]').val();
                            const status = $('#status_filter').val();
                            
                            let url = '{{ route("backend.subscription.subscriptions.export") }}?format=excel';
                            
                            if (plan_id) url += `&plan_id=${plan_id}`;
                            if (date_range) url += `&date_range=${date_range}`;
                            if (status) url += `&status=${status}`;
                            
                            window.location.href = url;
                        }
                    },
                    {
                        text: ' {{ __("messages.export_pdf") }}',
                        className: 'btn btn-sm p-3 py-2 btn-danger',
                        action: function(e, dt, node, config) {
                            const plan_id = $('#plan-filter').val();
                            const date_range = $('input[name="date_range"]').val();
                            const status = $('#status_filter').val();
                            
                            let url = '{{ route("backend.subscription.subscriptions.export") }}?format=pdf';
                            
                            if (plan_id) url += `&plan_id=${plan_id}`;
                            if (date_range) url += `&date_range=${date_range}`;
                            if (status) url += `&status=${status}`;
                            
                            window.location.href = url;
                        }
                    }
                ],
                drawCallback: function() {
                    if (laravel !== undefined) {
                        window.laravel.initialize();
                    }
                },
                columns: [
                    //                     {
                    //     data: 'user.first_name',
                    //     name: 'user.first_name',
                    //     title: "{{ __('frontend.first_name') }}",
                    //     render: function(data, type, row) {
                    //         return row.user ? row.user.first_name : 'Deleted User'; // Check if user exists
                    //     }
                    // },
                    // {
                    //     data: 'user.last_name',
                    //     name: 'user.last_name',
                    //     title: "{{ __('frontend.last_name') }}",
                    //     render: function(data, type, row) {
                    //         return row.user ? row.user.last_name : 'Deleted User'; // Check if user exists
                    //     }
                    // },
                    {
                        data: 'full_name',
                        name: 'full_name',
                        title: '{{ __("messages.vendor_name") }}'
                    },
                    { data: 'plan_name', name: 'plan_name', title: "{{ __('frontend.plan') }}" },
                    { 
                        data: 'payment_method', 
                        name: 'payment_method', 
                        title: "{{ __('frontend.payment_method') }}" 
                    },
                    { data: 'amount', name: 'amount', title: "{{ __('frontend.amount') }}" },
                    { 
                        data: 'duration', 
                        name: 'duration', 
                        title: "{{ __('frontend.duration') }}" 
                    },
                    { 
                        data: 'start_date', 
                        name: 'start_date', 
                        title: "{{ __('frontend.start_date') }}" 
                    },
                    { data: 'end_date', name: 'end_date', title: "{{ __('frontend.expired_date') }}" },
                    { data: 'updated_at', name: 'updated_at', title: "{{ __('frontend.updated_at') }}",visible:false, },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: true,
                        searchable: false,
                        title:"{{ __('frontend.status') }}",
            
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        title: "{{ __('frontend.action') }}",
                        render: function(data, type, row) {
                            return `
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" 
                                            class="btn btn-sm btn-danger delete-subscription" 
                                            data-id="${row.id}"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('messages.deleted') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                columnDefs: [
                    {
                        targets: 0, // Apply styles to the first column (index 0)
                        className: 'text-center' // Apply the 'text-center' class to the first column
                    }
                ],
                
                language: {
                    emptyTable: "No matching records found", // Custom message when there is no data
                    zeroRecords: "No records to display",   // Custom message when no records match the filters
                    info: "Showing _START_ to _END_ of _TOTAL_ entries", // Info text
                    infoEmpty: "Showing 0 to 0 of 0 entries", // Info text when there are no records
                    infoFiltered: "(filtered from _MAX_ total entries)" // Filtered info
                }
            });

            $('#filter-btn').on('click', function() {
                const plan_id = $('#plan-filter').val();
                const date_range = $('input[name="date_range"]').val();
                const status = $('#status_filter').val();
                
                $('#datatable').DataTable().settings()[0].ajax.data = {
                    plan_id: plan_id,
                    date_range: date_range,
                    status: status,
                    subscription_type: '{{ $subscription_type ?? null; }}'
                };

                dataTable.ajax.reload();
            });

            $('#status_filter').on('change', function() {
                $('#filter-btn').click(); // Trigger filter button click
            });

            $('#reset-btn').on('click', function() {
                // Clear the filters
                $('#plan-filter').val('').trigger('change'); // Reset Select2 dropdown
                $('input[name="date_range"]').val(''); // Clear the date range input
                $('#status_filter').val('').trigger('change'); // Reset status filter
                const subscription_type = '{{ $subscription_type ?? null; }}';
                let fp = $('#date_range').get(0)._flatpickr; // Get Flatpickr instance

                if (fp) {
                    fp.clear(); // Clear selection properly
                }
                $('#datatable').DataTable().settings()[0].ajax.data = {
                    plan_id: '',
                    date_range: '',
                    status: '',
                    search: '',
                    subscription_type : subscription_type 
                };

                // Reload the DataTable without filters
                $('#datatable').DataTable().ajax.reload();
            });
        });

 $('input[name="search"]').on('keyup', function () {

const plan_id = $('#plan-filter').val();
const date_range = $('input[name="date_range"]').val();
const search = $('input[name="search"]').val();
const subscription_type = '{{ $subscription_type ?? null; }}';

$('#datatable').DataTable().settings()[0].ajax.data = {
    plan_id: plan_id,
    date_range: date_range,
    search: search,
    subscription_type : subscription_type 

};

// Trigger reload again with updated filters
$('#datatable').DataTable().ajax.reload();

});


        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('#date_range', {
            dateFormat: "Y-m-d",
            mode: "range",
            });

        });

// Add this after DataTable initialization
$(document).on('click', '.delete-subscription', function() {
    const id = $(this).data('id');
    const fullName = $(this).closest('tr').find('td:eq(0)').text(); 

    Swal.fire({
        title: `Are you sure you want to delete ${fullName} from All Subscriptions?`,
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: "{{ __('messages.yes_delete_it') }}",
        cancelButtonText: "{{ __('messages.cancel') }}"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('backend.subscriptions.destroy', '') }}/" + id,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire(
                            "{{ __('messages.deleted') }}",
                            response.message,
                            'success'
                        );
                        $('#datatable').DataTable().ajax.reload();
                    } else {
                        Swal.fire(
                            "{{ __('messages.error') }}",
                            response.message,
                            'error'
                        );
                    }
                },
                error: function(xhr) {
                    Swal.fire(
                        "{{ __('messages.error') }}",
                        "{{ __('messages.something_went_wrong') }}",
                        'error'
                    );
                }
            });
        }
    });
});

</script>
@endpush