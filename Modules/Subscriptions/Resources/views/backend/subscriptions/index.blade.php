@extends('backend.layouts.app')

@section('title')
    {{ __($module_action) }} 
@endsection


@push('after-styles')
    <link rel="stylesheet" href="{{ mix('modules/constant/style.css') }}">
@endpush
@section('content')

    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-6 col-sm-12">
                <h2>{{ __($module_action) }}
                    <small class="text-muted">{{ config('app.name') }}</small>
                </h2>
            </div>
        </div>
        </div>

        <div class="container-fluid">

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-lg-3 col-md-4">
                    <select id="plan-filter" class="form-select select2">
                        <option value="">{{__('messages.select_plan')}}</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ request()->get('plan_id') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-4 mt-md-0 mt-3">
                    <input type="text" name="date_range" id="date_range" value="" class="form-control dashboard-date-range" placeholder="{{ __('messages.select_date_range') }} " />
                </div>
                <div class="col-lg-3 col-md-4 mt-md-0 mt-3">
                    <button id="filter-btn" class="btn btn-primary">{{ __('messages.filter') }}</button>
                    <button id="reset-btn" class="btn btn-primary">{{ __('messages.reset') }}</button>
                </div>
                <div class="col-lg-3 col-md-4 mt-lg-0 mt-3">
                    <div class="input-group flex-nowrap top-input-search">
                        <span class="input-group-text" id="addon-wrapping"><i
                                class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control " placeholder="{{ __('messages.search') }}..." aria-label="Search"
                            aria-describedby="addon-wrapping">
                    </div>
                </div>
            </div>
            <table id="datatable" class="table table-bordered table-striped table-hover js-basic-example dataTable">
            </table>
        </div>
    </div>

    <div data-render="app">


<plan-offcanvas
    create-title="{{ __('messages.create') }} {{ __('messages.new') }} {{ is_array($module_title) ? implode(' ', $module_title) : $module_title }}"
    edit-title="{{ __('messages.edit') }} {{ is_array($module_title) ? implode(' ', $module_title) : $module_title }}">
</plan-offcanvas>



    </div>
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
                dom:
                '<"table-responsive" t>' +
                '<"row align-items-center mt-2"' +
                    '<"col-md-6"i>' +
                    '<"col-md-6 text-md-right"p>' +
                '>',
                ajax: {
                    url: '{{ route("backend.subscriptions.index_data") }}',
                    data: function(d) {
                        d.plan_id = $('#plan-filter').val();
                        d.date_range = $('input[name="date_range"]').val();
                        d.subscription_type = '{{ $subscription_type ?? null; }}';
                    }
                },
                buttons: [
                    {
                        extend: 'excelHtml5',
                        title: '{{ $module_title }} Data',
                        className: 'btn btn-sm btn-success',
                        text: "{{ __('messages.export_excel') }}",
                        exportOptions: {
                               columns: [1,2,3,4,5,6,7,8,9,11] // Ensure the same columns as PDF
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        title: '{{ $module_title }} Data',
                        className: 'btn btn-sm btn-danger',
                        text: "{{ __('messages.export_pdf') }}",
                        customize: function (doc) {
                            // Add space before the table (top margin)
                            doc.content[1].margin = [0, 20, 0, 20]; // [left, top, right, bottom] - Adjust as needed

                            // Center the text in all table cells
                            doc.content[1].table.body.forEach(function(row) {
                                row.forEach(function(cell) {
                                    cell.alignment = 'center'; // Center align text in each cell
                                });
                            });
                        }, exportOptions: {
                            columns: [1,2,3,4,5,6,7,8,9,11] // Specify only the columns you want to include
                        }
                    }
                ],
                drawCallback: function() {
                    
                },
                columns: [
                    // { data: 'start_date', name: 'start_date', title:"{{ __('frontend.date') }}" },
                    {
                        data: 'user.first_name',
                        name: 'user.first_name',
                        title: "{{ __('frontend.first_name') }}",
                        render: function(data, type, row) {
                            return row.user ? row.user.first_name : 'Deleted User'; // Check if user exists
                        }
                    },
                    {
                        data: 'user.last_name',
                        name: 'user.last_name',
                        title: "{{ __('frontend.last_name') }}",
                        render: function(data, type, row) {
                            return row.user ? row.user.last_name : 'Deleted User'; // Check if user exists
                        }
                    },
                    { data: 'plan_name', name: 'plan_name', title: "{{ __('frontend.plan') }}" },
                    { data: 'payment_method', name: 'payment_method', title: "{{ __('frontend.payment_method') }}" },
                    { data: 'plan_type', name: 'plan_type', title: "{{ __('frontend.plan_type') }}" },
                    { data: 'duration', name: 'duration', title: "{{ __('frontend.duration') }}" },
                    { data: 'amount', name: 'amount', title: "{{ __('frontend.amount') }}" },
                    { data: 'start_date', name: 'start_date', title: "{{ __('frontend.start_date') }}" },
                    { data: 'end_date', name: 'end_date', title: "{{ __('frontend.end_date') }}" },
                    { data: 'updated_at', name: 'updated_at', title: "{{ __('frontend.updated_at') }}",visible:false, },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false,
                        title:"{{ __('frontend.status') }}",
                        visible: false
                    }
                ],
                columnDefs: [
                    {
                        targets: 0, // Apply styles to the first column (index 0)
                        className: 'text-center' // Apply the 'text-center' class to the first column
                    }
                ],
                order: [[10, 'desc']],
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
                const subscription_type = '{{ $subscription_type ?? null; }}';

                $('#datatable').DataTable().settings()[0].ajax.data = {
                    plan_id: plan_id,
                    date_range: date_range,
                    subscription_type : subscription_type 

                };


                dataTable.ajax.reload();
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


        $('#reset-btn').on('click', function() {
            // Clear the filters
            $('#plan-filter').val('').trigger('change'); // Reset Select2 dropdown
            $('input[name="date_range"]').val(''); // Clear the date range input
            const subscription_type = '{{ $subscription_type ?? null; }}';

            $('#datatable').DataTable().settings()[0].ajax.data = {
                plan_id: '',
                date_range: '',
                serach: '',
                subscription_type : subscription_type 
            };

            // Reload the DataTable without filters
            $('#datatable').DataTable().ajax.reload();
        });
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('#date_range', {
            dateFormat: "Y-m-d",
            mode: "range",
            });

        });
    </script>
@endpush
