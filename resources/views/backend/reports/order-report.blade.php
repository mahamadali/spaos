@extends('backend.layouts.app')

@section('title')
    {{ __($module_title) }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <x-backend.section-header>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 order-report-row">
                    <!-- Left side - Filter controls -->
                    <div class="d-flex align-items-center flex-wrap gap-3">
                        <div>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="rounded-end-0">{{ setting('inv_prefix') }}</span>
                                </div>
                                <input type="text" class="form-control order-code" placeholder="{{ __('messages.order_code') }}"
                                    name="code" value="{{ isset($searchCode) ? $searchCode : '' }}">
                            </div>
                        </div>
                        <div>
                            <div class=" form-group gap-2 d-flex flex-nowrap">
                                <input type="text" name="order_date" id="order_date"
                                    placeholder="{{ __('messages.select_date_range') }}"
                                    class="order-report-date-range form-control" readonly />
                                <button id="reset" class="btn bg-primary rounded" data-bs-toggle="tooltip" title="Reset">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M21.4799 12.2424C21.7557 12.2326 21.9886 12.4482 21.9852 12.7241C21.9595 14.8075 21.2975 16.8392 20.0799 18.5506C18.7652 20.3986 16.8748 21.7718 14.6964 22.4612C12.518 23.1505 10.1711 23.1183 8.01299 22.3694C5.85488 21.6205 4.00382 20.196 2.74167 18.3126C1.47952 16.4293 0.875433 14.1905 1.02139 11.937C1.16734 9.68346 2.05534 7.53876 3.55018 5.82945C5.04501 4.12014 7.06478 2.93987 9.30193 2.46835C11.5391 1.99683 13.8711 2.2599 15.9428 3.2175L16.7558 1.91838C16.9822 1.55679 17.5282 1.62643 17.6565 2.03324L18.8635 5.85986C18.945 6.11851 18.8055 6.39505 18.549 6.48314L14.6564 7.82007C14.2314 7.96603 13.8445 7.52091 14.0483 7.12042L14.6828 5.87345C13.1977 5.18699 11.526 4.9984 9.92231 5.33642C8.31859 5.67443 6.8707 6.52052 5.79911 7.74586C4.72753 8.97119 4.09095 10.5086 3.98633 12.1241C3.8817 13.7395 4.31474 15.3445 5.21953 16.6945C6.12431 18.0446 7.45126 19.0658 8.99832 19.6027C10.5454 20.1395 12.2278 20.1626 13.7894 19.6684C15.351 19.1743 16.7062 18.1899 17.6486 16.8652C18.4937 15.6773 18.9654 14.2742 19.0113 12.8307C19.0201 12.5545 19.2341 12.3223 19.5103 12.3125L21.4799 12.2424Z"
                                            fill="#ffffff"></path>
                                        <path
                                            d="M20.0941 18.5594C21.3117 16.848 21.9736 14.8163 21.9993 12.7329C22.0027 12.4569 21.7699 12.2413 21.4941 12.2512L19.5244 12.3213C19.2482 12.3311 19.0342 12.5633 19.0254 12.8395C18.9796 14.283 18.5078 15.6861 17.6628 16.8739C16.7203 18.1986 15.3651 19.183 13.8035 19.6772C12.2419 20.1714 10.5595 20.1483 9.01246 19.6114C7.4654 19.0746 6.13845 18.0534 5.23367 16.7033C4.66562 15.8557 4.28352 14.9076 4.10367 13.9196C4.00935 18.0934 6.49194 21.37 10.008 22.6416C10.697 22.8908 11.4336 22.9852 12.1652 22.9465C13.075 22.8983 13.8508 22.742 14.7105 22.4699C16.8889 21.7805 18.7794 20.4073 20.0941 18.5594Z"
                                            fill="#ffffff"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <div class="datatable-filter">
                                <select name="payment_status" id="payment_status" class="select2 form-control"
                                    data-filter="select">
                                    <option value="">{{ __('order_report.payment_status') }}</option>
                                    <option value="paid">{{ __('order_report.paid') }}</option>
                                    <option value="unpaid">{{ __('order_report.unpaid') }}</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <div class="datatable-filter">
                                <select name="delivery_status" id="delivery_status" class="select2 form-control"
                                    data-filter="select">
                                    <option value="">{{ __('order_report.delivery_status') }}</option>
                                    <option value="order_placed">{{ __('order_report.order_placed') }}</option>
                                    <option value="pending">{{ __('order_report.pending') }}</option>
                                    <option value="processing">{{ __('order_report.processing') }}</option>
                                    <option value="delivered">{{ __('order_report.delivered') }}</option>
                                    <option value="cancelled">{{ __('order_report.cancelled') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right side - Export button and Total Amount -->
                    <div class="d-flex align-items-center gap-3 ms-5">
                        <button type="button" class="btn btn-secondary" data-modal="export"
                            data-bs-title="{{ __('messages.export') }}" data-bs-toggle="tooltip">
                            <i class="fa-solid fa-download me-2"></i>{{ __('messages.export') }}
                        </button>
                        <div class="total-amount">
                            <h5 class="mb-0 text-dark">{{ __('messages.total_amount') }}: <strong
                                    class="text-dark">{{ \Currency::format($totalAdminEarnings) }}</strong></h5>
                        </div>
                    </div>
                </div>

                <x-slot name="toolbar">
                </x-slot>
            </x-backend.section-header>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="datatable" class="table table-striped border table-responsive">
            </table>
        </div>
    </div>
    {{-- <x-backend.advance-filter>
        <x-slot name="title">
            <h4>{{ __('service.lbl_advanced_filter') }}</h4>
        </x-slot>
        <button type="reset" class="btn btn-danger" id="reset-filter">Reset</button>
    </x-backend.advance-filter> --}}
@endsection

@push('after-styles')
    <link rel="stylesheet" href='{{ mix('modules/product/style.css') }}'>
    <!-- DataTables Core and Extensions -->
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">

    <style>
        /* Override input-group-text rounded-end-0 styling */
        .input-group-text.rounded-end-0 {
            border-radius: 0.375rem 0 0 0.375rem !important;
            border-right: 0 !important;
        }

        /* Override form-control order-code styling */
        .form-control.order-code {
            border-radius: 0 0.375rem 0.375rem 0 !important;
            border-left: 0 !important;
        }

        /* Ensure proper border connection between input-group-text and form-control */
        .input-group .input-group-text.rounded-end-0+.form-control.order-code {
            border-left: 0 !important;
        }

        /* Focus states */
        .form-control.order-code:focus {
            border-color: #86b7fe !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
        }

        /* Consistent height for all elements */
        .order-report-row .form-control,
        .order-report-row .btn,
        .order-report-row .select2-container .select2-selection,
        .order-report-row .bg-light {
            height: 40px !important;
            min-height: 40px !important;
        }

        .order-report-row .total-amount {
            display: flex !important;
            align-items: center !important;
        }

        .order-report-row .btn {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .order-report-row .select2-container .select2-selection--single {
            height: 40px !important;
            display: flex !important;
            align-items: center !important;
        }

        .order-report-row .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 40px !important;
        }
    </style>
@endpush

@push('after-scripts')
    <script src='{{ mix('modules/product/script.js') }}'></script>
    <script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>
    <script src="{{ asset('js/form-modal/index.js') }}" defer></script>
    <!-- DataTables Core and Extensions -->
    <script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>

    <script type="text/javascript" defer>
        const range_flatpicker = document.querySelectorAll('.order-report-date-range')

        Array.from(range_flatpicker, (elem) => {
            if (typeof flatpickr !== typeof undefined) {
                flatpickr(elem, {
                    mode: "range",
                    dateFormat: "d-m-Y",
                })
            }
        })

        $('#reset').on('click', function(e) {
            $('#order_date').val('');
            window.renderedDataTable.ajax.reload(null, false);
            $('.tooltip').removeClass('show');
        });



        const columns = [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                title: "{{ __('report.lbl_no') }}",
                orderable: false,
                searchable: false,
                width: '5%',

            },
            {
                data: 'order_code',
                name: 'order_code',
                title: "{{ __('messages.order_code') }}",
                orderable: false,
                searchable: false,
            },
            {
                data: 'customer_name',
                name: 'customer_name',
                title: "{{ __('booking.lbl_customer_name') }}",
                orderable: false,
            },
            {
                data: 'phone',
                name: 'phone',
                title: "{{ __('branch.lbl_contact_number') }}",
                orderable: false,
            },
            {
                data: 'placed_on',
                name: 'placed_on',
                title: "{{ __('messages.placed_on') }}",
                orderable: false,
                searchable: false,
            },
            {
                data: 'items',
                name: 'items',
                title: "{{ __('messages.items') }}",
                orderable: false,
                searchable: false,
            },
            // {
            //     data: 'products',
            //     name: 'products',
            //     title: "{{ __('messages.products') }}",
            //     orderable: false,
            //     searchable: false,
            //     render: function(data) {
            //         return data;
            //     }
            // },
            {
                data: 'payment',
                name: 'payment',
                title: "{{ __('messages.payment') }}",
                orderable: false,
                searchable: false,
            },
            {
                data: 'status',
                name: 'status',
                title: "{{ __('messages.status') }}",
                orderable: false,
                searchable: false,
            },

            {
                data: 'total_admin_earnings',
                name: 'total_admin_earnings',
                title: "{{ __('messages.total_amount') }}",

            },
            {
                data: 'updated_at',
                name: 'updated_at',
                title: "{{ __('product.lbl_update_at') }}",
                orderable: true,
                visible: false,
            },

        ]


        let finalColumns = [
            ...columns

        ]

        document.addEventListener('DOMContentLoaded', (event) => {
            initDatatable({
                url: "{{ route('backend.reports.order-report.index_data') }}",
                finalColumns,
                orderColumn: [
                    [9, "desc"]
                ],
                advanceFilter: () => {
                    return {
                        search: $('[name="table_search"]').val(),
                        code: $('[name="code"]').val(),
                        delivery_status: $('[name="delivery_status"]').val(),
                        payment_status: $('[name="payment_status"]').val(),
                        order_date: $('[name="order_date"]').val().split(' to '),
                    }
                }
            });

            // Inject a reusable modal once and delegate click events
            if (!document.getElementById('order-report-products-modal')) {
                const modalHtml = `
                <div class="modal fade" id="order-report-products-modal" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-lg modal-dialog">
                    <div class="modal-content shadow rounded-3">
                      <div class="modal-header border-0 pb-0 px-4 pt-4">
                        <h5 class="modal-title fw-semibold">{{ __('messages.product_detail') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body px-4 pb-4">
                        <div class="table-responsive">
                          <table class="table table-striped align-middle mb-0">
                            <thead class="text-white" style="background-color: var(--bs-purple, #8e3b8a);">
                              <tr>
                                <th class="px-3" style="width: 80px;">{{ __('messages.no') }}</th>
                                <th class="px-3">{{ __('messages.products') }}</th>
                              </tr>
                            </thead>
                            <tbody id="order-report-products-tbody"></tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>`;
                $('body').append(modalHtml);
            }

            $(document).on('click', '.show-products', function(e) {
                e.preventDefault();
                try {
                    const list = JSON.parse($(this).attr('data-products') || '[]');
                    const escapeHtml = (s) => $('<div>').text(s ?? '').html();
                    const chunkAndEscape = (s, size = 500) => {
                        const raw = String(s ?? '');
                        const out = [];
                        for (let i = 0; i < raw.length; i += size) {
                            out.push(escapeHtml(raw.slice(i, i + size)));
                        }
                        return out.join('<br/>');
                    };
                    const rows = (list || []).map((name, idx) =>
                        `<tr><td>${idx+1}</td><td>${chunkAndEscape(name, 90)}</td></tr>`).join('');
                    $('#order-report-products-tbody').html(rows || '<tr><td colspan="2">-</td></tr>');
                    const modal = new bootstrap.Modal(document.getElementById(
                        'order-report-products-modal'));
                    modal.show();
                } catch (err) {}
            });
        })

        $(document).on('change', '#order_date', function() {
            window.renderedDataTable.ajax.reload(null, false)
        })


        $(document).on('input', '.order-code', function() {
            window.renderedDataTable.ajax.reload(null, false)
        })
    </script>
@endpush
