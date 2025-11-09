@extends('backend.layouts.app')

@section('title') {{ __($module_title) }}
@endsection


@push('after-styles')
    <link rel="stylesheet" href="{{ mix('modules/constant/style.css') }}">
@endpush
@section('content')

<div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-6 col-sm-12">
                <h2>{{ __($module_title) }}
                    <small class="text-muted">{{ config('app.name') }}</small>
                </h2>
            </div>
        </div>
        </div>

        <div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <x-backend.section-header>

                <x-slot name="toolbar">
                    <div class="input-group flex-nowrap top-input-search">
                        <span class="input-group-text" id="addon-wrapping"><i
                                class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}..."
                            aria-label="Search" aria-describedby="addon-wrapping">
                    </div>
                 
                        <a href="{{ route('backend.subscription.plans.create') }}" class="btn btn-primary" title="Create Plans">
                            <i class="fas fa-plus-circle"></i>
                          {{ __('messages.new')}}
                        </a>
                 

                </x-slot>
            </x-backend.section-header>
            <table id="datatable" class="table border table-responsive rounded">
            </table>
        </div>
    </div>

    <div data-render="app">

        <plan-offcanvas create-title="{{ __('messages.create') }} {{ __('messages.new') }} {{ __($module_title) }}"
            edit-title="{{ __('messages.edit') }} {{ __($module_title) }}"
            :customefield="{{ json_encode($customefield) }}">
        </plan-offcanvas>


    </div>
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
        const userRole = "{{ auth()->user()->getRoleNames()->first() }}"; // Example role check
        const paymentMethod = '{{ $payment_method }}';
        const columns = [
    { data: 'id', name: 'id', visible: false},
    { data: 'name', name: 'name', title: "{{ __('frontend.name') }}" },
    // { data: 'type', name: 'type', title: "{{ __('frontend.interval') }}" },
    {
        data: null,
        name: 'duration',
        title: "{{ __('frontend.duration') }}",
        render: function(data, type, row) {
        return `${row.duration} ${row.type}`; // Combines duration with interval
        }
    },
    { data: 'price', name: 'price', title: "{{ __('frontend.price') }}" },
    { data: 'discount_value', name: 'discount_value', title: '{{__("messages.discount")}}'},
    { data: 'tax', name: 'tax', title: "{{ __('frontend.tax') }}" },
    { data: 'total_price', name: 'total_price', title: "{{ __('frontend.total_price') }}" },
    { 
        data: 'subscription_count', 
        name: 'subscription_count', 
        title: "{{ __('frontend.total_subscriptions') }}",
        render: function(data, type, row) {
            if (type === 'display') {
                return `<span data-bs-toggle="tooltip" 
                             data-bs-placement="top" 
                             title="{{ __('frontend.total_subscriptions_tooltip') }}">${data}</span>`;
            }
            return data;
        }
    },
    { data: 'status', name: 'status', orderable: false, searchable: true, title: "{{ __('frontend.status') }}" , visible: userRole=='super admin'},
    
        ];


        const actionColumn = [{
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            title: "{{ __('plan.action') }}",
            render: function(data, type, row) {
                let buttons = '';

                // Assuming you have a way to check the user's role, like a global variable

                if (userRole === 'super admin') {
                    buttons += `
                        <button class="btn btn-primary btn-sm btn-edit" onclick="editPlan(${row.id})" title="Edit" data-bs-toggle="tooltip">
                            <i class="fas fa-edit"></i>
                        </button>
                      <a href="{{ route('backend.subscription.plans.destroy', '') }}/${row.id}"
           id="delete-subscription-${row.id}"
           class="btn btn-danger btn-sm"
           data-type="ajax"
           data-method="DELETE"
           data-token="{{ csrf_token() }}"
           data-bs-toggle="tooltip"
           title="{{ __('messages.delete') }}"
            data-confirm="{{ __('messages.are_you_sure?', [ 'name' => '${row.name}', 'module' =>'Plan' ]) }} ">
            <i class="fa-solid fa-trash"></i>
        </a>
    `;

                } else if (userRole === 'admin') {
                    buttons += `
                        <button class="btn btn-primary btn-sm btn-purchase" onclick="checkExistingPlan(${row.id})" title="Purchase" data-bs-toggle="tooltip">
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                    `;
                }
                return buttons;
            }
        }];



        const customFieldColumns = JSON.parse(@json($columns))

        let finalColumns = [
            ...columns,
            ...customFieldColumns,
            ...actionColumn
        ]

        document.addEventListener('DOMContentLoaded', (event) => {
            initDatatable({
                url: '{{ route("backend.subscription.$module_name.index_data") }}',
                finalColumns,
                order: [[0, 'desc']]
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

        function editPlan(plan_id)
        {
            var route = "{{ route('backend.subscription.plans.edit', 'plan_id') }}".replace('plan_id', plan_id);
            window.location.href = route;
        }

        function deletePlan(plan_id) {
            var route = "{{ route('backend.subscription.plans.delete', 'plan_id') }}".replace('plan_id', plan_id);
            confirmDelete(route, plan_id);
        }

    </script>

    <script>
        function checkExistingPlan(planId) {
            $.ajax({
                url: "{{ route('backend.check.plan.start_date') }}",
                type: 'GET',
                success: function(response) {
                    if (response.status) {
                        let new_plan_start_date = response.new_plan_start_date;
                        let current_plan_end_date = response.current_plan_end_date;

                        let message = 'Your Current Plan Expired At '+current_plan_end_date+ '. So Your Plan Will be Start From '+new_plan_start_date;
                        Swal.fire({
                            title: message,
                            text: "Are you sure? Do you want to purchase plan now?",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes!',
                            cancelButtonText: 'No'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                purchasePlan(planId)

                            } else {
                                return;
                            }
                        });
                    } else {
                        purchasePlan(planId)
                    }
                },
                error: function(xhr) {
                    // Handle error response
                    alert('An error occurred while deleting the plan.');
                    console.error(xhr.responseText);
                }
            });
        }

        function purchasePlan(planId) {
            switch (paymentMethod) {
                case 'str_payment_method':
                    openStripePopup(planId);
                    break;
                case 'razor_payment_method':
                    openRazorpayPopup(planId);
                    break;
                case 'paypal_payment_method':
                    openPaypalPopup(planId);
                    break;
                default:
                    alert('Invalid payment method.');
                    break;
            }
        }

        // Example of handling Stripe payment
        function openStripePopup(planId) {
            window.location.href = "{{ route('stripe.pay', ':planId') }}".replace(':planId', planId);
        }

        // Example of handling Razorpay payment
        function openRazorpayPopup(planId) {
            window.location.href = "{{ route('razorpay.pay', ':planId') }}".replace(':planId', planId);
        }

        // Example of handling PayPal payment
        function openPaypalPopup(planId) {
            window.location.href = "{{ route('paypal.pay', ':planId') }}".replace(':planId', planId);
        }
    </script>



@endpush
