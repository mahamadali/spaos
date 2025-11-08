@extends('backend.layouts.app')

@section('title')
    {{ $module_title }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <x-backend.section-header>
                <x-slot name="toolbar">
                    <div class="flex-grow-1">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent">
                                {{ setting('inv_prefix') }}
                                <i class="fa-solid fa-hashtag"></i>
                            </span>
                            <input type="text" class="form-control order-code" placeholder="code" name="code"
                                value="{{ isset($searchCode) ? $searchCode : '' }}">
                        </div>
                    </div>
                    <div>
                        <div class="datatable-filter" style="width: 100%; display: inline-block;">
                            <select name="payment_status" id="payment_status" class="select2 form-control"
                                data-filter="select">
                                <option value="">{{ __('messages.payment_status') }}</option>
                                <option value="paid">{{ __('messages.paid') }}</option>
                                <option value="unpaid">{{ __('messages.unpaid') }}</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <div class="datatable-filter" style="width: 100%; display: inline-block;">
                            <select name="delivery_status" id="delivery_status" class="select2 form-control"
                                data-filter="select">
                                <option value="">{{ __('messages.delivery_status') }}</option>
                                <option value="order_placed">{{ __('messages.order_palce') }}</option>
                                <option value="pending">{{ __('messages.pending') }}</option>
                                <option value="processing">{{ __('messages.processing_status') }}</option>
                                <option value="delivered">{{ __('messages.delivered') }}</option>
                                <option value="cancelled">{{ __('messages.cancelled') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-group flex-nowrap top-input-search">
                        <span class="input-group-text" id="addon-wrapping"><i
                                class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="table_search" class="form-control dt-search"
                            placeholder="{{ __('messages.search') }}...">
                    </div>
                    @hasPermission('add_order')
                        <x-buttons.offcanvas href="{{ route('backend.orders.create') }}" title="">
                            {{ __('messages.new') }}</x-buttons.offcanvas>
                    @endhasPermission
                </x-slot>
            </x-backend.section-header>
            <table id="datatable" class="table table-striped border table-responsive">
            </table>
        </div>
    </div>
@endsection

@push('after-styles')
    <link rel="stylesheet" href='{{ mix('modules/product/style.css') }}'>
    <!-- DataTables Core and Extensions -->
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push('after-scripts')
    <script src='{{ mix('modules/product/script.js') }}'></script>
    <script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>
    <script src="{{ asset('js/form-modal/index.js') }}" defer></script>
    <!-- DataTables Core and Extensions -->
    <script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>

    <script type="text/javascript" defer>
        const columns = [
            // {
            //     name: 'check',
            //     data: 'check',
            //     title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
            //     width: '0%',
            //     exportable: false,
            //     orderable: false,
            //     searchable: false,
            // },
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
            //     render: function(data){ return data; }
            // },
            {
                data: 'payment',
                name: 'payment',
                title: "{{ __('messages.payment') }}",
                orderable: false,
                searchable: false,
            },
            {
                data: 'type',
                name: 'type',
                title: "{{ __('messages.type') }}",
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
                data: 'updated_at',
                name: 'updated_at',
                title: "{{ __('product.lbl_update_at') }}",
                orderable: true,
                visible: false,
            },

        ]

        const actionColumn = [{
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            title: "{{ __('service.lbl_action') }}",
            width: '5%'
        }]

        let finalColumns = [
            ...columns,
            ...actionColumn
        ]

        document.addEventListener('DOMContentLoaded', (event) => {
            initDatatable({
                url: '{{ route("backend.$module_name.index_data") }}',
                finalColumns,
                orderColumn: [
                    [8, "desc"]
                ],
                advanceFilter: () => {
                    return {
                        search: $('[name="table_search"]').val(),
                        code: $('[name="code"]').val(),
                        delivery_status: $('[name="delivery_status"]').val(),
                        payment_status: $('[name="payment_status"]').val(),
                        location_id: $('[name="location_id"]').val()
                    }
                }
            });

            // Global reusable products modal (reused from order-report behavior)
            if (!document.getElementById('order-products-modal')) {
                const modalHtml = `
                <div class="modal fade" id="order-products-modal" tabindex="-1" aria-hidden="true">
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
                            <tbody id="order-products-tbody"></tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>`;
                $('body').append(modalHtml);
            }

            $(document).on('click', '.show-products', function(e){
                e.preventDefault();
                try {
                    const list = JSON.parse($(this).attr('data-products') || '[]');
                    const escapeHtml = (s) => $('<div>').text(s ?? '').html();
                    const chunkAndEscape = (s, size=500) => {
                        const raw = String(s ?? '');
                        const out = [];
                        for (let i = 0; i < raw.length; i += size) {
                            out.push(escapeHtml(raw.slice(i, i + size)));
                        }
                        return out.join('<br/>');
                    };
                    const rows = (list || []).map((name, idx) => `<tr><td>${idx+1}</td><td>${chunkAndEscape(name, 90)}</td></tr>`).join('');
                    $('#order-products-tbody').html(rows || '<tr><td colspan="2">-</td></tr>');
                    const modal = new bootstrap.Modal(document.getElementById('order-products-modal'));
                    modal.show();
                } catch(err) {}
            });
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

        $(document).on('input', '.order-code', function() {
            window.renderedDataTable.ajax.reload(null, false)
        })
    </script>
@endpush
