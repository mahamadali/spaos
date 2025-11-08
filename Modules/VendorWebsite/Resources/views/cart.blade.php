@extends('vendorwebsite::layouts.master')
@section('title')
    {{ __('vendorwebsite.cart') }}
@endsection


@section('content')
    <x-breadcrumb title="Cart" />


    <div class="section-spacing-inner-pages">
        <div class="container">
            <div class="row gy-4">
                <!-- Empty Cart Message -->
                <div id="empty-cart-container" style="display: none;">
                    <div class="container">
                        <div class="row gy-4">
                            <div class="cart-page">

                                <div
                                    class="empty-cart-page d-flex flex-column justify-content-center align-items-center text-center=">
                                    <img src="{{ asset('img/vendorwebsite/empty-cart.jpg') }}" alt="Empty Cart"
                                        class="img-fluid mb-4 avatar-200">
                                    <h5 class="mb-2">{{ __('vendorwebsite.your_cart_is_empty') }}</h5>
                                    <p class="text-body mb-3">
                                        {{ __('vendorwebsite.add_items_to_your_cart_to_proceed_with_checkout') }}</p>
                                    <a href="{{ route('shop') }}"
                                        class="btn btn-primary">{{ __('vendorwebsite.continue_shopping') }}</a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-7 col-lg-9">
                    <!-- Cart Table Container -->
                    <div id="cart-table-container">
                        <div class="cart-table table-responsive">
                            <table id="cart-table" class="table table-borderless rounded custom-table-bg">
                                <thead>
                                    <tr>
                                        <th>{{ __('vendorwebsite.product') }}</th>
                                        <th>{{ __('vendorwebsite.price') }}</th>
                                        <th>{{ __('vendorwebsite.discount') }}</th>
                                        <!-- <th>Discount Amount</th> -->
                                        <th>{{ __('vendorwebsite.quantity') }}</th>
                                        <th>{{ __('vendorwebsite.subtotal') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>


                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="cart-summary">
                        <h5 class="mb-3">{{ __('vendorwebsite.payment_details') }}</h5>
                        <div class="payment-details bg-gray-800 p-4 rounded">
                            <div id="cart-summary">
                                <!-- Cart summary will be loaded dynamically -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Debug: Show a test SweetAlert2 popup on page load to confirm it's loaded


        $(document).ready(function() {
            $('#cart-table').DataTable({
                dom: '<"table-container position-relative"t>',
                processing: true,
                serverSide: true,
                ajax: "{{ route('cart.data') }}",
                columns: [{
                        data: 'product_name',
                        name: 'product_name',
                        width: '25%',
                        render: function(data, type, row) {
                            return `
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-gray-900 avatar avatar-50 rounded">
                                ${row.product_image ?
                                    `<img src="${row.product_image}" alt="${row.product_name}" class="img-fluid avatar avatar-50">` :
                                    `<img src="{{ asset('img/vendorwebsite/product.png') }}" alt="${row.product_name}" class="img-fluid avatar avatar-50">`
                                }
                            </div>
                            <div>
                                <h6 class="mb-0 text-body small">${row.product_name}</h6>
                                ${row.product_short_description ? `<p class="mb-0 text-muted small" style="font-size: 0.75rem; line-height: 1.2;">${row.product_short_description}</p>` : ''}
                            </div>
                        </div>
                    `;
                        }
                    },
                    {
                        data: 'price',
                        name: 'price',
                        width: '15%',
                        render: function(data, type, row) {
                            if (row.discount_value > 0) {
                                return `
                            <div class="small">
                                 <span class="text-primary">${row.discounted_price}</span>
                                <del class="text-body">${row.original_price}</del>

                            </div>
                        `;
                            }
                            return `<div class="small">${row.price}</div>`;
                        }
                    },
                    {
                        data: 'discount_percentage',
                        name: 'discount_percentage',
                        width: '10%',
                        render: function(data, type, row) {
                            if (row.discount_value > 0) {
                                return `<div class="small text-success">${row.discount_value}%</div>`;
                            }
                            return `<div class="small">-</div>`;
                        }
                    },
                    // {
                    //     data: 'discount_amount',
                    //     name: 'discount_amount',
                    //     width: '10%',
                    //     render: function(data, type, row) {
                    //         if (row.discount_value > 0) {
                    //             const discountAmount = (row.original_price * row.discount_value / 100).toFixed(2);
                    //             return `<div class="small text-success">-$${discountAmount}</div>`;
                    //         }
                    //         return `<div class="small">-</div>`;
                    //     }
                    // },
                    {
                        data: 'quantity',
                        name: 'quantity',
                        width: '15%',
                        render: function(data, type, row) {
                            const stockQty = row.product ? row.product.stock_qty : 0;
                            return `
                        <div class="btn-group iq-qty-btn" data-qty="btn" role="group">
                            <button type="button" class="btn btn-link border-0 iq-quantity-minus heading-color p-0" onclick="updateCartQuantity(${row.id}, 'decrease')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="6" height="3" viewBox="0 0 6 3" fill="none">
                                    <path d="M5.22727 0.886364H0.136364V2.13636H5.22727V0.886364Z" fill="currentColor"></path>
                                </svg>
                            </button>
                            <input type="text" class="btn btn-link border-0 input-display" data-qty="input" pattern="^(0|[1-9][0-9]*)$" minlength="1" maxlength="2" value="${row.quantity}" title="Qty" onchange="updateCartQuantity(${row.id}, 'set', this.value)" max="${stockQty}" readonly>
                            <button type="button" class="btn btn-link border-0 iq-quantity-plus heading-color p-0" onclick="updateCartQuantity(${row.id}, 'increase')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="9" height="8" viewBox="0 0 9 8" fill="none">
                                    <path d="M3.63636 7.70455H4.90909V4.59091H8.02273V3.31818H4.90909V0.204545H3.63636V3.31818H0.522727V4.59091H3.63636V7.70455Z" fill="currentColor"></path>
                                </svg>
                            </button>
                        </div>
                        <small class="text-success d-block mt-1 small">Available: ${stockQty}</small>
                    `;
                        }
                    },
                    {
                        data: 'subtotal',
                        name: 'subtotal',
                        width: '15%',
                        render: function(data, type, row) {
                            return `<div class="small">${row.subtotal}</div>`;
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        width: '10%',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
         <button class="btn btn-link border-0 text-body p-0 remove-cart-btn" data-id="${row.product_id}">
             <i class="ph ph-trash-simple font-size-20 text-danger"></i>
         </button>
     `;
                        }
                    }
                ],
                order: [
                    [0, 'asc']
                ],
                paging: false,
                searching: false,
                lengthChange: false,
                autoWidth: false,
                responsive: true,
                info: false,
                language: {
                    emptyTable: "<div class='text-center p-4'>{{ __('vendorwebsite.no_products_available_at_the_moment') }}</div>",
                },
                drawCallback: function() {
                    updateCartSummary();
                    checkIfCartIsEmpty();
                }
            });

            function formatCurrencyvalue(value) {
                value = parseFloat(value);
                if (window.currencyFormat !== undefined) {
                    return window.currencyFormat(value);
                }
                return value.toFixed(2);
            }

            function updateCartSummary() {
                $.get("{{ route('cart.summary') }}", function(response) {
                    if (response.status) {
                        let summaryHtml = '';
                        if (response.cart_items_count > 0) {
                            summaryHtml = `
                        <div class="payment-details-item border-bottom d-flex flex-wrap align-items-center justify-content-between mb-3 pb-3">
                            <div class="font-size-14">{{ __('vendorwebsite.subtotal') }}</div>
                            <h6 class="font-size-14 mb-0">${formatCurrencyvalue(response.subtotal)}</h6>
                        </div>
                        ${response.discount > 0 ? `
                                                                                    <div class="payment-details-item border-bottom d-flex flex-wrap align-items-center justify-content-between mb-3 pb-3">
                                                                                        <div class="font-size-14">{{ __('vendorwebsite.discount') }}</div>
                                                                                        <h6 class="font-size-14 mb-0 text-success">-${formatCurrencyvalue(response.discount)}</h6>
                                                                                    </div>
                                                                                ` : ''}
                        ${response.tax > 0 ? `
                                                                                    <div class="payment-details-item border-bottom d-flex flex-wrap align-items-center justify-content-between mb-3 pb-3">
                                                                                        <div class="font-size-14">{{ __('vendorwebsite.tax') }}</div>
                                                                                        <div class="d-flex flex-wrap align-items-center justify-content-between text-decoration-none cursor-pointer taxDetails"
                                                                                            data-bs-toggle="collapse" href="#taxDetailsCart" role="button"
                                                                                            aria-expanded="false" aria-controls="taxDetailsCart">
                                                                                            <i class="ph ph-caret-down rotate-icon tax2"></i>
                                                                                            <h6 class="font-size-14 mb-0 text-danger">${formatCurrencyvalue(response.tax)}</h6>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="collapse mt-2 mb-2" id="taxDetailsCart">
                                                                                        <div class="text-calculate card py-2 px-3">
                                                                                            ${(response.tax_breakdown && response.tax_breakdown.length > 0) ? response.tax_breakdown.map(tax => `
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="font-size-12">${tax.title}${tax.type === 'percent' ? ' (' + tax.value + '%)' : ''}</span>
                                            <span class="font-size-12 text-danger fw-medium">${formatCurrencyvalue(tax.amount)}</span>
                                        </div>
                                    `).join('') : `
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="font-size-12">{{ __('vendorwebsite.tax') }}</span>
                                            <span class="font-size-12 text-danger fw-medium">0</span>
                                        </div>
                                    `}
                                                                                        </div>
                                                                                    </div>
                                                                                ` : ''}
                        <div class="payment-details-item d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div class="font-size-14 fw-bold">{{ __('vendorwebsite.total_amount') }}</div>
                            <h6 class="font-size-14 mb-0 text-primary fw-bold">${formatCurrencyvalue(response.total_without_delivery)}</h6>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('check-out') }}" class="btn btn-primary w-100 d-flex align-items-center gap-lg-2 gap-1">
                                <i class="ph ph-shopping-cart"></i>{{ __('vendorwebsite.proceed_to_checkout') }}
                            </a>
                        </div>
                    `;
                        }
                        $('#cart-summary').html(summaryHtml);

                        // Handle tax collapse toggle for dynamic content
                        $('[href="#taxDetailsCart"]').off('click').on('click', function(e) {
                            e.preventDefault();
                            const taxIcon = $(this).find('.tax2');
                            const isExpanded = $('#taxDetailsCart').hasClass('show');
                            if (isExpanded) {
                                taxIcon.css('transform', 'rotate(0deg)');
                            } else {
                                taxIcon.css('transform', 'rotate(180deg)');
                            }
                        });
                    }
                });
            }

            // Delegated event for remove from cart with SweetAlert2 confirmation
            $(document).on('click', '.remove-cart-btn', function(e) {
                e.preventDefault();
                var productId = $(this).data('id');
                Swal.fire({
                    title: '{{ __('vendorwebsite.are_you_sure') }}',
                    text: '{{ __('vendorwebsite.do_you_want_to_remove_this_item_from_your_cart') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '{{ __('vendorwebsite.yes_delete_it') }}',
                    cancelButtonText: '{{ __('vendorwebsite.cancel') }}',
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-secondary'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        removeFromFullCart(productId); // Only run if confirmed
                    }
                });
            });
        });

        function updateCartQuantity(cartItemId, action, value = null) {


            let qty = value;
            if (!value) {
                const input = $(`input[data-qty="input"]`).filter(function() {
                    return $(this).closest('tr').find('button[onclick*="' + cartItemId + '"]').length > 0;
                });
                qty = parseInt(input.val());
                const maxQty = parseInt(input.attr('max'));



                if (action === 'increase') {
                    if (qty >= maxQty) {

                        toastr.warning(`Only ${maxQty} items available in stock`);
                        return;
                    }
                    qty++;
                } else if (action === 'decrease') {
                    qty = Math.max(1, qty - 1);
                }
            }


            $.ajax({
                url: "{{ route('cart.update') }}",
                type: 'POST',
                data: {
                    cart_item_id: cartItemId,
                    qty: qty,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {

                    if (response.status) {
                        $('#cart-table').DataTable().ajax.reload();
                        // toastr.success('Cart updated successfully');
                        toastr.success('{{ __('messages.cart_updated_successfully') }}');

                    } else {
                        console.error('Update failed:', response.message);
                        toastr.error(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Update error:', {
                        status: status,
                        error: error,
                        response: xhr.responseText,
                        statusCode: xhr.status
                    });

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Failed to update quantity. Please try again.');
                    }
                }
            });
        }

        // Place removeFromFullCart in the global scope
        function removeFromFullCart(productId) {
            $.ajax({
                url: "{{ route('cart.remove') }}",
                type: 'POST',
                data: {
                    product_id: productId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#cart-table').DataTable().ajax.reload();
                    toastr.success('Product removed from cart successfully');

                    // Update cart count if provided in response
                    if (typeof response.cart_count !== 'undefined') {
                        $('#cartCount').text(response.cart_count);
                        $('#cartItemCount').text(response.cart_count);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Remove error:', {
                        status: status,
                        error: error,
                        response: xhr.responseText,
                        statusCode: xhr.status
                    });
                    toastr.error('Failed to remove item from cart. Please try again.');
                }
            });
        }

        function checkIfCartIsEmpty() {
            // Check if the table has any data rows (excluding the empty table message)
            const table = $('#cart-table').DataTable();
            const rowCount = table.data().count();

            if (rowCount === 0) {
                // Hide cart table and show empty cart message
                $('#cart-table-container').hide();
                $('#empty-cart-container').show();
                $('.cart-summary').hide();
            } else {
                // Show cart table and hide empty cart message
                $('#cart-table-container').show();
                $('#empty-cart-container').hide();
                $('.cart-summary').show();
            }
        }
    </script>
@endpush
