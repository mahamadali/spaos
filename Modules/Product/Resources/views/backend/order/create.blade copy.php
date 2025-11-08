@extends('backend.layouts.app')

@section('title')
    {{ $module_title }}
@endsection

@section('content')
    <div class="card shadow rounded-2 mb-4 p-4">
        <h5 class="mb-3">{{ __('messages.place_order') }}</h5>
        <div class="row gy-4">
            <div class="col-lg-4 col-md-6">
                <label for="branch-id" class="form-label">{{ __('messages.branches') }}</label>
                <select id="branch-id" class="form-select select2">
                    <option value="" selected disabled>{{ __('messages.select_branch') }}</option>
                    @foreach ($activeBranches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4 col-md-6">
                <label for="user-id" class="form-label">{{ __('messages.users') }}</label>
                <select id="user-id" class="form-select select2">
                    <option value="" selected disabled>{{ __('messages.select_user') }}</option>
                    @foreach ($activeUsers as $user)
                        <option value="{{ $user->id }}" data-user='@json($user)'>{{ $user->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4 col-md-6">
                <label for="product-id" class="form-label">{{ __('messages.products') }}</label>
                <select id="product-id" class="form-select select2">
                    <option value="" selected disabled>{{ __('messages.select_product') }}</option>
                    @foreach ($activeProducts as $product)
                        <option value="{{ $product->id }}" data-product='@json($product)'>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4 col-md-6">
                <label for="variant-id" class="form-label">{{ __('messages.varients') }}</label>
                <select id="variant-id" class="form-select select2">
                    <option value="" selected disabled>{{ __('messages.select_varient') }}</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="payment-status" class="form-label">{{ __('messages.payment_status') }}</label>
                <div style="width: 100%; display: inline-block;">
                    <select name="payment_status" id="payment-status" class="select2 form-control" data-filter="select"
                        style="width: 375px">
                        <option value="" selected disabled>{{ __('messages.select_payment_status') }}</option>
                        <option value="paid">{{ __('messages.paid') }}</option>
                        <option value="unpaid">{{ __('messages.unpaid') }}</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <label for="delivery-status" class="form-label">{{ __('messages.delivery_status') }}</label>
                <div style="width: 100%; display: inline-block;">
                    <select name="delivery_status" id="delivery-status" class="select2 form-control" data-filter="select"
                        style="width: 375px">
                        <option value="" selected disabled>{{ __('messages.select_delivery_status') }}</option>
                        <option value="order_placed">{{ __('messages.order_palce') }}</option>
                        <option value="pending">{{ __('messages.pending') }}</option>
                        <option value="processing">{{ __('messages.processing_status') }}</option>
                        <option value="delivered">{{ __('messages.delivered') }}</option>
                        <option value="cancelled">{{ __('messages.cancelled') }}</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-8 col-md-6" id="address-section" style="display:none">
                <div class="row align-items-center">
                    <div class="col-8">
                        <label for="address-id" class="form-label"></label>
                        <div class="input-group flex-nowrap">
                            <select id="address-id" class="form-select" aria-label="User addresses">
                                <option value="" selected disabled>{{ __('messages.select_address') }}</option>
                                <!-- Dynamic options will appear here -->
                            </select>
                            @hasPermission('add_address')
                                <button class="btn btn-primary flex-shrink-0" type="button" data-bs-toggle="modal"
                                    data-bs-target="#modalAddAddress" id="btn-add-address" aria-label="Add new address">
                                    <span class="d-flex align-items-center gap-1">
                                        <i class="fas fa-plus-circle"></i>
                                        <span>{{ __('messages.new') }}</span>
                                    </span>
                                </button>
                            @endhasPermission
                        </div>
                    </div>

                    <!-- Place Order Button -->
                    <div class="col-4">

                    </div>
                </div>
            </div>
            <div class="col-md-12 d-flex align-items-center justify-content-end">
                <button type="button" class="btn btn-primary" id="add-to-cart-btn">
                    <div class="d-flex align-items-center gap-1">
                        <i class="fas fa-plus"></i>
                        {{ __('messages.add_to_cart') }}</span>
                    </div>
                </button>
            </div>

            <div class="col-md-12 d-flex align-items-center justify-content-end">
                <div class="d-flex justify-content-end d-none" id="place-order-container">
                    <button class="btn btn-outline-secondary" id="place-order-btn">
                        <div class="d-flex align-items-center gap-1">
                            <i class="fas fa-check-circle"></i>
                            <span>{{ __('messages.save') }}</span>
                        </div>
                    </button>
                </div>

            </div>
        </div>
    </div>
    <!-- Centered Modal for Adding Address -->
    <div class="modal fade" id="modalAddAddress" tabindex="-1" aria-labelledby="modalAddAddressLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 800px; min-width: 600px;">
            <div class="modal-content" style="min-height: 550px;">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAddAddressLabel">{{ __('Add Address') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-5">
                    <!-- Add your address form or content here -->
                    <form id="add-address-form" action="{{ route('backend.orders.store_address') }}" method="POST">
                        @csrf
                        <input type="hidden" name="user_id" id="user_id" value="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="firstName" class="form-label"
                                    style="font-size: 1.2rem;">{{ __('messages.first_name') }}</label>
                                <input type="text" class="form-control" id="firstName" name="first_name"
                                    placeholder='eg. "Michael"' required>
                            </div>
                            <div class="col-md-6">
                                <label for="lastName" class="form-label"
                                    style="font-size: 1.2rem;">{{ __('messages.last_name') }}</label>
                                <input type="text" class="form-control" id="lastName" name="last_name"
                                    placeholder='eg. "Thompson"' required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <!-- Country -->
                            <div class="col-md-6">
                                <label for="country" class="form-label"
                                    style="font-size: 1.2rem;">{{ __('messages.countries') }}</label>
                                <select class="form-select select2" id="country" name="country" required>
                                    <option value="" selected disabled>{{ __('messages.select_country') }}</option>
                                    @foreach ($activeCountries as $country)
                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- State -->
                            <div class="col-md-6">
                                <label for="state" class="form-label"
                                    style="font-size: 1.2rem;">{{ __('messages.states') }}</label>
                                <select class="form-select select2" id="state" name="state" disabled required>
                                    <option value="" selected disabled>{{ __('messages.select_state') }}</< /option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <!-- City -->
                            <div class="col-md-6">
                                <label for="city" class="form-label"
                                    style="font-size: 1.2rem;">{{ __('messages.cities') }}</label>
                                <select class="form-select select2" id="city" name="city" disabled required>
                                    <option value="" selected disabled>{{ __('messages.select_city') }}</option>
                                </select>
                            </div>
                            <!-- Pin Code -->
                            <div class="col-md-6">
                                <label for="pinCode" class="form-label"
                                    style="font-size: 1.2rem;">{{ __('messages.postal_code') }}</label>
                                <input type="text" class="form-control" id="pinCode" name="postal_code" required>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="mb-3">
                            <label for="address" class="form-label"
                                style="font-size: 1.2rem;">{{ __('messages.address') }}</label>
                            <textarea class="form-control" id="address" name="address_line_1" rows="3" required></textarea>
                        </div>
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="setAsPrimary" name="is_primary"
                                    value="1">
                                <label class="form-check-label" for="setAsPrimary"
                                    style="font-size: 1rem;">{{ __('messages.set_as_primary') }}</label>
                            </div>
                        </div>
                        <!-- Buttons -->
                        <div class="row">
                            <div class="col d-flex justify-content-end">
                                <button type="button" class="btn btn-outline-secondary btn-cancel me-2"
                                    data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                                <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Cart container -->
    <div class="card shadow rounded-2 p-4">
        <div id="cart-container">
            <div class="d-flex align-items-center justify-content-center" style="width: 100%; height: 500px;">
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">{{ __('messages.your_cart_is_empty') }}</h4>
                    <p class="text-muted"> {{ __('messages.select_products_to_add_to_your_order') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow rounded-2 mb-4 p-4" id="total-section" style="display: none;">
        <div class="total-summary">
        </div>
    </div>
@endsection

@push('after-scripts')
    <script>
        // --- Globals ---
        let cart = [];
        // Tax definitions from backend
        const productTax = @json($productTax);

        // --- Helpers ---
        // Calculate and render tax breakdown
        function calculateAndRenderTaxes(totalBeforeTax) {
            let totalTaxAmount = 0;
            let taxBreakdownHtml = '';
            productTax.forEach(tax => {
                let taxAmount = 0;
                if (tax.type === 'percent') {
                    taxAmount = totalBeforeTax * (tax.value / 100);
                } else {
                    taxAmount = parseFloat(tax.value);
                }
                totalTaxAmount += taxAmount;
                taxBreakdownHtml += `
                    <div class="d-flex justify-content-between align-items-center mb-2 ms-3">
                        <span class="fs-7">${tax.title} (${tax.type === 'percent' ? tax.value + '%' : window.currencyFormat(parseFloat(tax.value))}):</span>
                        <span class="fw-medium fs-6">${window.currencyFormat(taxAmount)}</span>
                    </div>
                `;
            });
            return {
                totalTaxAmount,
                taxBreakdownHtml
            };
        }

        // --- Main summary update ---
        function updateTotalSummary() {
            const hasCartItems = cart.length > 0;
            if (!hasCartItems) {
                $('#total-section').hide();
                return;
            }

            // Calculate subtotal and total discount
            let subtotal = 0;
            let totalDiscount = 0;
            cart.forEach(item => {
                const price = parseFloat(item.variant.price);
                const itemSubtotal = price * item.quantity;
                const discount_start_date = item.product.discount_start_date;
                const discount_end_date = item.product.discount_end_date;
                const current_date = new Date();
                const is_discount_applicable =
                    discount_start_date == null ||
                    discount_end_date == null ||
                    (
                        current_date >= new Date(discount_start_date * 1000) &&
                        current_date <= new Date(discount_end_date * 1000)
                    );
                let discountValue = 0;
                let discountedPrice = 0;
                if (
                    item.product.discount_type &&
                    item.product.discount_value > 0 &&
                    is_discount_applicable
                ) {
                    discountValue = item.product.discount_value;
                    if (item.product.discount_type === 'fixed') {
                        discountedPrice = discountValue * item.quantity;
                    } else if (item.product.discount_type === 'percent') {
                        discountedPrice = itemSubtotal * discountValue / 100;
                    }
                }
                subtotal += itemSubtotal;
                totalDiscount += discountedPrice;
            });
            const totalBeforeTax = subtotal - totalDiscount;
            // Calculate taxes
            const {
                totalTaxAmount,
                taxBreakdownHtml
            } = calculateAndRenderTaxes(totalBeforeTax);
            const total = totalBeforeTax + totalTaxAmount;
            // Render summary
            $('.total-summary').html(`
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fs-6">Subtotal (${cart.length} items):</span>
                    <span class="fw-medium fs-5">${window.currencyFormat(totalBeforeTax)}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fs-6">Tax Total:</span>
                    <span class="fw-medium fs-5">${window.currencyFormat(totalTaxAmount)}</span>
                </div>
                ${taxBreakdownHtml}
                <div class="d-flex justify-content-between align-items-center pt-3 mt-3 border-top">
                    <span class="fw-bold h5 m-0">Order Total:</span>
                    <span class="fs-4 text-primary fw-bold">${window.currencyFormat(total)}</span>
                </div>
            `);
            $('#total-section').show();
        }

        $(document).ready(function() {
            // Initialize select2 elements
            $('#branch-id, #user-id, #product-id, #variant-id, #address-id, #country, #state, #city').select2();

            // Load variants when product changes
            $('#product-id').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const productJson = selectedOption.attr('data-product');
                $('#variant-id').empty().append(
                    '<option value="" selected disabled>{{ __('messages.select_varient') }}</option>');

                if (productJson) {
                    const product = JSON.parse(productJson);
                    if (product.product_variations && product.product_variations.length > 0) {
                        product.product_variations.forEach(variant => {
                            // Check if variant has stock information and stock > 0
                            if (variant.product_variation_stock && variant.product_variation_stock
                                .stock_qty > 0) {
                                const option = new Option(variant.code, variant.id);
                                $(option).attr('data-variant', JSON.stringify(variant));
                                $('#variant-id').append(option);
                            }
                        });

                        // If no in-stock variants, show message
                        if ($('#variant-id option').length === 1) {
                            $('#variant-id').append(
                                '<option value="" disabled>{{ __('messages.no_in-stock_variants_available') }}</option>'
                            );
                        }
                    }
                }
            });

            // Country change event
            $('#country').change(function() {
                var countryId = $(this).val();
                if (countryId) {
                    $('#state').prop('disabled', false);
                    $.ajax({
                        url: "{{ route('backend.orders.getStates') }}",
                        type: "GET",
                        data: {
                            country_id: countryId
                        },
                        success: function(data) {
                            $('#state').empty().append(
                                '<option value="" selected disabled>{{ __('messages.select_state') }}</option>'
                            );
                            $.each(data, function(key, value) {
                                $('#state').append('<option value="' + key + '">' +
                                    value + '</option>');
                            });
                            $('#city').prop('disabled', true).empty().append(
                                '<option value="" selected disabled>{{ __('messages.select_city') }}</option>'
                            );
                        }
                    });
                } else {
                    $('#state').prop('disabled', true).empty().append(
                        '<option value="" selected disabled>{{ __('messages.select_state') }}</option>'
                    );
                    $('#city').prop('disabled', true).empty().append(
                        '<option value="" selected disabled>{{ __('messages.select_city') }}</option>');
                }
            });

            // State change event
            $('#state').change(function() {
                var stateId = $(this).val();
                if (stateId) {
                    $('#city').prop('disabled', false);
                    $.ajax({
                        url: "{{ route('backend.orders.getCities') }}",
                        type: "GET",
                        data: {
                            state_id: stateId
                        },
                        success: function(data) {
                            $('#city').empty().append(
                                '<option value="" selected disabled>{{ __('messages.select_city') }}</option>'
                            );
                            $.each(data, function(key, value) {
                                $('#city').append('<option value="' + key + '">' +
                                    value + '</option>');
                            });
                        }
                    });
                } else {
                    $('#city').prop('disabled', true).empty().append(
                        '<option value="" selected disabled>{{ __('messages.select_city') }}</option>');
                }
            });

            // Re-initialize select2 when modal is shown
            $('#modalAddAddress').on('shown.bs.modal', function() {
                $('#country, #state, #city').select2({
                    dropdownParent: $('#modalAddAddress')
                });
            });

            // User change event
            $('#user-id').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var userData = selectedOption.data('user');
                var userId = $(this).val();

                // Fill the address form fields if user data exists
                if (userData) {
                    $('#firstName').val(userData.first_name || '');
                    $('#lastName').val(userData.last_name || '');
                    $('#user_id').val(userId);
                }

                // Load addresses for the selected user
                $('#address-id').empty().append(
                    '<option value="">{{ __('messages.select_address') }}</option>');
                if (!userId) return;

                $.ajax({
                    url: "{{ route('backend.orders.get_addresses') }}",
                    type: "GET",
                    data: {
                        user_id: userId
                    },
                    success: function(response) {
                        $('#address-id').empty().append(
                            '<option value=""  selected disabled>{{ __('messages.select_address') }}</option>'
                        );
                        const addresses = Array.isArray(response) ? response : (response.data ||
                            []);

                        addresses.forEach(address => {
                            const addressText = [
                                address.address_line_1,
                                address.city,
                                address.state,
                                address.country
                            ].filter(Boolean).join(', ');

                            $('#address-id').append(new Option(addressText, address
                                .id));
                        });

                        $('#address-id').select2();
                    },
                    error: function(xhr) {
                        console.error('Error fetching addresses:', xhr);
                        toastr.error('Failed to fetch addresses.');
                    }
                });
            });

            // Handle address form submission
            $('#add-address-form').on('submit', function(e) {
                e.preventDefault();
                const $form = $(this);

                // Ensure user is selected
                const userId = $('#user-id').val();
                if (!userId) {
                    toastr.error('Please Select a User First');
                    return;
                }

                // Set user ID in form
                $('#user_id').val(userId);

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: $form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]', $form).val()
                    },
                    success: function(response) {
                        $('#modalAddAddress').modal('hide');
                        toastr.success('Address Added Successfully!');

                        // Preserve current selections
                        const currentProductId = $('#product-id').val();
                        const currentVariantId = $('#variant-id').val();

                        // Reload addresses
                        loadAddresses(userId);

                        // Restore selections
                        $('#product-id').val(currentProductId).trigger('change');
                        setTimeout(() => {
                            if (currentVariantId) {
                                $('#variant-id').val(currentVariantId).trigger(
                                    'change');
                            }
                        }, 300);

                        // Clear only address-related fields
                        $form.find('#country, #state, #city').val('').trigger('change');
                        $('#pinCode, #address').val('');
                        $('#setAsPrimary').prop('checked', false);
                    },
                    error: function(xhr) {
                        // Error handling remains the same
                    }
                });
            });

            // Enhanced loadAddresses function
            function loadAddresses(userId) {
                if (!userId) {
                    $('#address-id').empty().append(
                        '<option value="" selected disabled>{{ __('messages.select_address') }}</option>');
                    return;
                }

                $.ajax({
                    url: "{{ route('backend.orders.get_addresses') }}",
                    type: "GET",
                    data: {
                        user_id: userId
                    },
                    success: function(response) {
                        $('#address-id').empty().append(
                            '<option value="" selected disabled>{{ __('messages.select_address') }}</option>'
                        );
                        const addresses = Array.isArray(response) ? response : (response.data || []);

                        addresses.forEach(address => {
                            const addressText = [
                                address.address_line_1,
                                address.postal_code,
                                address.city,
                                address.state,
                                address.country
                            ].filter(Boolean).join(', ');

                            $('#address-id').append(new Option(addressText, address.id));
                        });

                        $('#address-id').select2();
                    }
                });

                $('#address-id').on('change', function() {
                    togglePlaceOrderButton();
                });
            }

            // Update user change event
            $('#user-id').on('change', function() {
                const userId = $(this).val();
                const selectedOption = $(this).find('option:selected');
                const userData = selectedOption.data('user');

                // Reset cart and selections
                cart = [];
                updateCartDisplay();
                $('#product-id, #variant-id').val('').trigger('change');

                // Load addresses for new user
                loadAddresses(userId);

                // Fill address form with user's name
                if (userData) {
                    $('#firstName').val(userData.first_name || '');
                    $('#lastName').val(userData.last_name || '');
                    $('#user_id').val(userId);
                }

                toggleAddressSection();
                togglePlaceOrderButton();
            });

            // Add to cart button click handler
            $('#add-to-cart-btn').on('click', function() {
                const branchId = $('#branch-id').val();
                const userId = $('#user-id').val();
                const productId = $('#product-id').val();
                const variantId = $('#variant-id').val();
                const quantity = 1;

                if (!branchId) {
                    toastr.error('Please select a branch');
                    return;
                }

                if (!userId) {
                    toastr.error('Please select a user first');
                    return;
                }

                if (!productId) {
                    toastr.error('Please select a product');
                    return;
                }

                if (!variantId) {
                    toastr.error('Please select a variant');
                    return;
                }

                const productSelect = $('#product-id').find('option:selected');
                const variantSelect = $('#variant-id').find('option:selected');

                if (productSelect.length && variantSelect.length) {
                    try {
                        const product = JSON.parse(productSelect.attr('data-product'));
                        const variant = JSON.parse(variantSelect.attr('data-variant'));

                        const existingItemIndex = cart.findIndex(item =>
                            item.variant.id === variant.id && item.product.id === product.id
                        );

                        if (existingItemIndex >= 0) {
                            toastr.error('This variant is already in your cart');
                        } else {
                            cart.push({
                                product,
                                variant,
                                quantity
                            });
                            toastr.success('Product added to cart');
                            updateCartDisplay();
                            $('#product-id, #variant-id').val('').trigger('change');
                        }
                    } catch (e) {
                        console.error('Error parsing product data:', e);
                        toastr.error('Error adding product to cart');
                    }
                }
            });

            // Function to update the cart display
            function updateCartDisplay() {
                const cartContainer = $('#cart-container');

                if (cart.length === 0) {
                    cartContainer.html(`
                    <div class="d-flex align-items-center justify-content-center" style="width: 100%; height: 500px;">
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">Your cart is empty</h4>
                            <p class="text-muted">Select products to add to your order</p>
                        </div>
                    </div>
                `);
                    $('#total-section').hide();
                    return;
                }

                let cartHtml = '<div class="row">';
                cart.forEach((item, index) => {
                    const price = parseFloat(item.variant.price);
                    const subtotal = price * item.quantity;

                    const discount_start_date = item.product.discount_start_date;
                    const discount_end_date = item.product.discount_end_date;
                    const current_date = new Date();

                    // Use this corrected version
                    const is_discount_applicable =
                        discount_start_date == null ||
                        discount_end_date == null ||
                        (
                            current_date >= new Date(discount_start_date * 1000) &&
                            current_date <= new Date(discount_end_date * 1000)
                        );

                    let discountValue = 0;
                    let discountedPrice = 0;
                    let hasDiscount = false; // Add this line

                    if (
                        item.product.discount_type &&
                        item.product.discount_value > 0 &&
                        is_discount_applicable
                    ) {
                        discountValue = item.product.discount_value;
                        hasDiscount = true;
                        if (item.product.discount_type === 'fixed') {
                            discountedPrice = discountValue * item.quantity;
                        } else if (item.product.discount_type === 'percent') {
                            discountedPrice = subtotal * discountValue / 100;
                        }
                    }

                    cartHtml += `
                        <div class="col-md-12 order-deatils-card">
                            <div class="card shadow-sm bg-body border-1 border-primary product-card-order position-relative m-0">
                                <button class="btn remove-item btn-link bg-danger border-0 rounded-circle p-0 close-icon" data-index="${index}" aria-label="Remove item" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove from cart"><i class="fas fa-times text-white fs-6"></i></button>
                                <div class="card-body">
                                    <div class="row gy-3">
                                        <div class="col-lg-1 col-md-2">
                                            <img src="${item.product.feature_image}" alt="${item.product.feature_image}" class="order-img object-fit-cover">
                                        </div>
                                        <div class="col-lg-8 col-md-6">
                                            <div class="d-flex align-items-center gap-2 flex-wrap mb-4 ">
                                                <h4 class="mb-0 text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="${item.product.name}">${item.product.name}</h4>
                                                <span class="text-${item.variant.product_variation_stock.stock_qty > 0 ? 'success' : 'danger'} fw-medium">${item.variant.product_variation_stock.stock_qty > 0 ? `${item.variant.product_variation_stock.stock_qty} in stock` : 'Out of stock'}</span>
                                            </div>
                                            <div class="input-group p-2 border rounded bg-gray-900" style="width: fit-content;">
                                                <button class="btn btn-sm btn-link update-qty border-0 p-0" data-index="${index}" data-change="-1" ${item.quantity === 1 ? 'disabled' : ''}>
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" class="form-control text-center qty-input p-1 bg-transparent border-0 text-heading fw-medium" value="${item.quantity}" min="1" max="${item.variant.product_variation_stock.stock_qty}" data-index="${index}" readonly>
                                                <button class="btn btn-sm btn-link update-qty  border-0 p-0" data-index="${index}" data-change="1">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-4">
                                            <div class="d-flex align-items-center justify-content-between gap-1 flex-wrap order-price-box">
                                                <span class="fs-6">Price:</span>
                                                <span class="fw-medium fs-5">${window.currencyFormat(price)}</span>
                                            </div>
                                            ${item.variant.code !== null ? `<div class="d-flex align-items-center justify-content-between gap-1 flex-wrap order-price-box"><span class="fs-6">SKU:</span><span class="fw-medium fs-5">${item.variant.code}</span></div>` : ''}
                                            <div class="d-flex align-items-center justify-content-between gap-1 flex-wrap order-price-box">
                                                <span class="fs-6">Subtotal:</span>
                                                <span class="fw-medium fs-5">${window.currencyFormat(subtotal)}</span>
                                            </div>
                                            ${hasDiscount ? `<div class="d-flex align-items-center justify-content-between gap-1 flex-wrap order-price-box"><span class="fs-6">Discount:</span><span class="d-flex align-items-center gap-1"><span class="fw-medium fs-5">-${window.currencyFormat(discountedPrice)}</span> ${item.product.discount_type === 'percent' ? `<span class="fs-6">(${discountValue}%)</span>` : `<span class="fs-6">(${window.currencyFormat(discountValue)})</span>`}</span></div>` : ''}
                                            <div class="d-flex align-items-center justify-content-between gap-1 flex-wrap order-price-box mt-3 pt-3 border-top">
                                                <span class="fw-bold h5 m-0">Total:</span>
                                                <span class="fw-bold fs-4 text-primary fw-bold">${window.currencyFormat(subtotal - discountedPrice)}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                cartContainer.html(cartHtml);
                updateTotalSummary();
                togglePlaceOrderButton();
            }

            // Event delegation for dynamic elements
            $(document)
                .on('click', '.remove-item', function() {
                    const index = $(this).data('index');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Are you sure?',
                            text: 'Do you want to remove this item from the cart?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, remove it',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                cart.splice(index, 1);
                                updateCartDisplay();
                                toastr.success('Product removed from cart');
                            }
                        });
                    } else {
                        if (confirm('Do you want to remove this item from the cart?')) {
                            cart.splice(index, 1);
                            updateCartDisplay();
                            toastr.success('Product removed from cart');
                        }
                    }
                })
                .on('click', '.update-qty', function() {
                    const index = $(this).data('index');
                    const change = $(this).data('change');
                    const newQty = cart[index].quantity + change;
                    if (newQty > 0 && newQty <= cart[index].variant.product_variation_stock.stock_qty) {
                        cart[index].quantity = newQty;
                        updateCartDisplay();
                    }
                    togglePlaceOrderButton();
                });

            function toggleAddressSection() {
                const userSelected = $('#user-id').val();
                if (userSelected) {
                    $('#address-section').show();
                    $('#place-order-container').show().removeClass('d-none');
                    $('#btn-add-address').prop('disabled', false);
                } else {
                    $('#address-section').hide();
                    $('#place-order-container').show().addClass('d-none');
                    $('#btn-add-address').prop('disabled', true);
                }
            }

            // Initialize sections
            toggleAddressSection();
            updateTotalSummary();

            // Clear cart when user changes
            $('#user-id').on('change', function() {
                cart = [];
                updateCartDisplay();
                toggleAddressSection();
                $('#product-id, #variant-id').val('').trigger('change');
            });

            // Add this inside your $(document).ready() function
            $(document).on('click', '#place-order-btn', function() {
                placeOrder();
            });

            function placeOrder() {
                const branchId = $('#branch-id').val();
                const userId = $('#user-id').val();
                const addressId = $('#address-id').val();
                const paymentstatus = $('#payment-status').val();
                const deliverystatus = $('#delivery-status').val();

                if (!branchId) {
                    toastr.error('Please select a branch');
                    return;
                }

                if (!userId) {
                    toastr.error('Please select a user');
                    return;
                }

                if (!cart.length) {
                    toastr.error('Please add items to the cart');
                    return;
                }

                if (!addressId) {
                    toastr.error('Please select an address');
                    return;
                }

                if (!paymentstatus) {
                    toastr.error('Please select a payment status');
                    return;
                }

                if (!deliverystatus) {
                    toastr.error('Please select a delivery status');
                    return;
                }

                const orderData = {
                    branch_id: branchId,
                    user_id: userId,
                    location_id: addressId,
                    paymentstatus: paymentstatus,
                    deliverystatus: deliverystatus,
                    items: cart.map(item => ({
                        product_id: item.product.id,
                        product_variation_id: item.variant.id,
                        quantity: item.quantity,
                    }))
                };

                const $btn = $('#place-order-btn');
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

                $.ajax({
                    url: "{{ route('backend.orders.add_to_cart') }}",
                    method: 'POST',
                    data: JSON.stringify(orderData),
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (deliverystatus === 'order_placed') {
                            toastr.success('Order placed successfully!');
                        } else if (deliverystatus === 'pending') {
                            toastr.success('Order is currently pending!');
                        } else if (deliverystatus === 'processing') {
                            toastr.success('Order is being processed!');
                        } else if (deliverystatus === 'delivered') {
                            toastr.success('Order has been delivered!');
                        } else {
                            toastr.success('Order has been cancelled!');
                        }
                        cart = [];
                        updateCartDisplay();
                        $('#branch-id, #user-id, #address-id, #product-id, #variant-id').val('')
                            .trigger('change');
                        window.location.href = "{{ route('backend.orders.index') }}";
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to place order';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        toastr.error(errorMsg);
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('Place Order');
                    }
                });
            }

            function togglePlaceOrderButton() {
                const branchSelected = $('#branch-id').val();
                const userSelected = $('#user-id').val();
                const addressSelected = $('#address-id').val();
                const hasCartItems = cart.length > 0;

                if (branchSelected && userSelected && addressSelected && hasCartItems) {
                    $('#place-order-section').show();
                } else {
                    $('#place-order-section').hide();
                }
            }
            togglePlaceOrderButton();
        });
    </script>
@endpush
