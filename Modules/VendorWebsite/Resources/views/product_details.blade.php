@extends('vendorwebsite::layouts.master')
@section('title') {{$product->name}} @endsection

@section('content')


    <div class="section-spacing-inner-pages">
        <div class="container">

            <div class="section-spacing-bottom">
                <div class="row gy-4">
                    <div class="col-lg-6">
                        <div class="product-details-slider">
                            <div class="bg-gray-800 rounded img-thmbnail-product position-relative">
                                @if ($product->discount_value > 0)
                                    <span class="product-meta badge bg-primary">{{ __('vendorwebsite.sale') }}</span>
                                @endif
                                <span class="product-wishlist cursor-pointer" data-product-id="{{ $product->id }}"
                                    onclick="handleWishlist({{ $product->id }})"
                                    style="position: absolute; top: 1rem; right: 1rem; color: var(--bs-primary); font-size: 1.25rem; z-index: 2;">
                                    <i class="ph ph-heart {{ $product->in_wishlist ? 'ph-fill' : '' }}" style="display: inline-block;"></i>
                                </span>
                                <div class="slider slider-for">
                                    @php
                                        $mainImage = $product->feature_image ?? asset('img/vendorwebsite/product.png');
                                    @endphp
                                    <div class="slick-item">
                                        <img src="{{ $mainImage }}" alt="{{ $product->name }}"
                                             class="w-100 object-cover image-for-slide">
                                    </div>

                                    @if (isset($product->gallery) && $product->gallery->count() > 0)
                                        @foreach ($product->gallery as $image)
                                            @if ($image->full_url !== $mainImage)
                                                <div class="slick-item">
                                                    <img src="{{ $image->full_url }}" alt="{{ $product->name }}"
                                                         class="w-100 object-cover image-for-slide">
                                                </div>
                                            @endif
                                        @endforeach
                                    @elseif (isset($product->media) && $product->media->count() > 0)
                                        @foreach ($product->media as $media)
                                            @php $url = $media->getFullUrl(); @endphp
                                            @if ($url !== $mainImage)
                                                <div class="slick-item">
                                                    <img src="{{ $url }}" alt="{{ $product->name }}"
                                                         class="w-100 object-cover image-for-slide">
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <div class="slick-item">
                                            <img src="{{ asset('dummy-images/dummy.png') }}" alt="{{ $product->name }}"
                                                class="w-100 object-cover image-for-slide">
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="slider slider-nav mt-4" data-spacing="10">
                                @php
                                    $mainImage = $product->feature_image ?? asset('dummy-images/dummy.png');
                                @endphp
                                <div class="slick-item">
                                    <div class="bg-gray-800 thumb-image">
                                        <img src="{{ $mainImage }}" alt="{{ $product->name }}"
                                             class="avatat avatar-70 object-cover">
                                    </div>
                                </div>

                                @if (isset($product->gallery) && $product->gallery->count() > 0)
                                    @foreach ($product->gallery as $image)
                                        @if ($image->full_url !== $mainImage)
                                            <div class="slick-item">
                                                <div class="bg-gray-800 thumb-image">
                                                    <img src="{{ $image->full_url }}" alt="{{ $product->name }}"
                                                         class="avatat avatar-70 object-cover">
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @elseif (isset($product->media) && $product->media->count() > 0)
                                    @foreach ($product->media as $media)
                                        @php $url = $media->getFullUrl(); @endphp
                                        @if ($url !== $mainImage)
                                            <div class="slick-item">
                                                <div class="bg-gray-800 thumb-image">
                                                    <img src="{{ $url }}" alt="{{ $product->name }}"
                                                         class="avatat avatar-70 object-cover">
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @else
                                    <div class="slick-item">
                                        <div class="bg-gray-800 thumb-image">
                                            <img src="{{ asset('dummy-images/dummy.png') }}" alt="{{ $product->name }}"
                                                class="avatat avatar-70 object-cover">
                                        </div>
                                        
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <h4>{{ $product->name }}</h4>
                        <span>{!! $product->description ?? 'No description available' !!}</span>

                        @php
                            $selectedPrice = $product->price ?? 0; // Fallback to product's main price if no variations
$selectedStock = $product->stock_qty ?? 0; // Fallback to product's main stock

                            $firstVariation = $product->product_variations->first();
                            if ($firstVariation) {
                                $selectedPrice = $firstVariation->price;
                                $selectedStock = $firstVariation->stock_qty;
                            }

                            // For the original price if a discount is applied on the main product
                            $originalProductPrice = $product->max_price ?? ($product->price ?? 0);
                            $discountValue = $product->discount_value ?? 0;
                            $discountType = $product->discount_type ?? '';
                            $discountedProductPrice =
                                $discountType === 'percent'
                                    ? $originalProductPrice - ($originalProductPrice * $discountValue) / 100
                                    : $originalProductPrice - $discountValue;
                        @endphp

                        <div class="d-flex align-items-center gap-lg-3 gap-2 mb-2 pb-2 mt-3">
                            @if ($discountValue > 0)
                                 <span class="text-primary font-size-21-3"
                                    id="display-price">{{ \Currency::vendorCurrencyFormate($discountedProductPrice) }}</span>
                                <del class="text-body font-size-21-3"
                                    id="original-price">{{ \Currency::vendorCurrencyFormate($originalProductPrice) }}</del>

                            @else
                                <span class="text-primary font-size-21-3"
                                    id="display-price">{{ \Currency::vendorCurrencyFormate($selectedPrice) }}</span>
                            @endif
                        </div>

                        @php
                            $avgRating = $product->product_review->avg('rating') ?? 0;
                            $fullStars = floor($avgRating);
                            $hasHalfStar = $avgRating - $fullStars >= 0.5;
                        @endphp

                        <div class="d-flex align-items-center gap-2 mb-4">
                            <div class="d-flex align-items-center gap-1">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= $fullStars)
                                        <i class="ph-fill ph-star text-warning"></i>
                                    @elseif($i == $fullStars + 1 && $hasHalfStar)
                                        <i class="ph-fill ph-star-half text-warning"></i>
                                    @else
                                        <i class="ph ph-star text-warning"></i>
                                    @endif
                                @endfor
                            </div>
                            <span class="fw-medium font-size-14">{{ number_format($avgRating, 1) }}</span>
                        </div>

                        <div class="d-flex align-items-center gap-2 mb-4">
                            <span class="fw-medium font-size-14">{{ __('vendorwebsite.Availability') }}</span>
                            <span class="fw-medium font-size-14" id="stock-status">
                                @if ($product->stock_qty > 0)
                                    <span class="text-success">{{ __('vendorwebsite.In_Stock') }}</span>
                                @else
                                    <span class="text-danger">{{ __('vendorwebsite.Out_of_Stock') }}</span>
                                @endif
                            </span>
                            <span class="fw-medium font-size-14" id="stock-qty-display">

                            </span>
                        </div>

                        <div class="mb-4">
                            <!-- <span class="fw-medium font-size-14 heading-color">{{ __('vendorwebsite.size') }}</span> -->
                            <div class="select-size d-flex align-items-center flex-wrap gap-3 mt-2">
                                @php
                                    $availableSizes = ['small', 'medium', 'large'];
                                    $productSizes = $product->product_variations
                                        ->pluck('variation_key')
                                        ->map(function ($size) {
                                            return strtolower($size);
                                        })
                                        ->toArray();
                                @endphp

                                @foreach ($availableSizes as $size)
                                    @if (in_array($size, $productSizes))
                                        <div class="form-check">
                                            <label class="form-check-label" for="size_{{ $size }}">
                                                <input class="form-check-input variation-radio" type="radio"
                                                    value="{{ $size }}" name="size"
                                                    id="size_{{ $size }}" data-product-id="{{ $product->id }}"
                                                    data-variation-key="{{ $size }}"
                                                    data-price="{{ $product->product_variations->where('variation_key', $size)->first()->price ?? $product->price }}"
                                                    {{ $loop->first ? 'checked' : '' }}>
                                                {{ ucfirst($size) }}
                                            </label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <div class="btn-group iq-qty-btn" data-qty="btn" role="group">
                                <button type="button" class="btn btn-link border-0 iq-quantity-minus heading-color"
                                    onclick="decrementQuantity()">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="6" height="3" viewBox="0 0 6 3"
                                        fill="none">
                                        <path d="M5.22727 0.886364H0.136364V2.13636H5.22727V0.886364Z" fill="currentColor">
                                        </path>
                                    </svg>
                                </button>
                                <input type="number" class="btn btn-link border-0 input-display" data-qty="input"
                                    min="1" max="{{ $product->stock_qty }}" value="1" title="Qty"
                                    id="quantity-input" autocomplete="off">
                                <button type="button" class="btn btn-link border-0 iq-quantity-plus heading-color"
                                    onclick="incrementQuantity()">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="9" height="8" viewBox="0 0 9 8"
                                        fill="none">
                                        <path
                                            d="M3.63636 7.70455H4.90909V4.59091H8.02273V3.31818H4.90909V0.204545H3.63636V3.31818H0.522727V4.59091H3.63636V7.70455Z"
                                            fill="currentColor"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-md-nowrap flex-wrap">
                                <button class="btn btn-secondary" id="add-to-cart-btn"
                                    data-product-id="{{ $product->id }}"
                                    data-product-variation-id="{{ $product->product_variations->first()->id }}"
                                    style="display: {{ $product->in_cart ? 'none' : 'inline-block' }};"
                                    {{ $product->stock_qty <= 0 ? 'disabled' : '' }}>{{ __('vendorwebsite.add_to_cart') }}</button>
                                <button class="btn btn-danger" id="remove-from-cart-btn"
                                    data-product-id="{{ $product->id }}"
                                    data-product-variation-id="{{ $product->product_variations->first()->id }}"
                                    style="display: {{ $product->in_cart ? 'inline-block' : 'none' }};">{{ __('vendorwebsite.remove_from_cart') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if (isset($relatedProducts) && count($relatedProducts) > 0)
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-4 gap-2 flex-wrap">
                        <h4 class="m-0">{{ __('vendorwebsite.related_products') }}</h4>
                        <a href="{{ route('shop') }}" class="btn btn-secondary">{{ __('vendorwebsite.view_all') }}</a>
                    </div>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                        @foreach ($relatedProducts as $relatedProduct)
                            <div class="col">
                                <x-product_card :product="$relatedProduct" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInput = document.getElementById('quantity-input');
        const displayPrice = document.getElementById('display-price');
        const originalPrice = document.getElementById('original-price');
        const stockStatus = document.getElementById('stock-status');
        const stockQtyDisplay = document.getElementById('stock-qty-display');
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        var removeFromCartBtn = document.getElementById('remove-from-cart-btn');

        let currentMaxStock = parseInt(quantityInput.max, 10);

        function formatCurrency(value) {
            return '$' + parseFloat(value).toFixed(2);
        }

        function clampQuantity() {
            let value = parseInt(quantityInput.value, 10);
            if (isNaN(value) || value < 1) {
                quantityInput.value = 1;
            } else if (value > currentMaxStock) {
                quantityInput.value = currentMaxStock;
            }
        }

        window.incrementQuantity = function() {
            let currentValue = parseInt(quantityInput.value, 10);
            if (isNaN(currentValue) || currentValue < 1) {
                currentValue = 1;
            }
            // Only increment by 1
            if (currentValue <= currentMaxStock) {
                quantityInput.value = currentValue;
            } else {
                quantityInput.value = currentMaxStock;
            }
        }

        window.decrementQuantity = function() {
            let currentValue = parseInt(quantityInput.value, 10);
            if (isNaN(currentValue) || currentValue < 2) {
                quantityInput.value = 1;
            } else {
                quantityInput.value = currentValue;
            }
        }

        quantityInput.addEventListener('input', clampQuantity);
        quantityInput.addEventListener('blur', clampQuantity);

        // Handle variation selection
        document.querySelectorAll('.variation-radio').forEach(radio => {
            radio.addEventListener('change', function() {
                const newPrice = parseFloat(this.dataset.price);

                // Update displayed price
                if (originalPrice && !isNaN(parseFloat(originalPrice.textContent.replace('$',
                        '')))) {
                    const productDiscountValue = {{ $product->discount_value ?? 0 }};
                    const productDiscountType = '{{ $product->discount_type ?? '' }}';
                    let priceToDisplay = newPrice;

                    if (productDiscountValue > 0) {
                        priceToDisplay = productDiscountType === 'percent' ?
                            newPrice - (newPrice * productDiscountValue / 100) :
                            newPrice - productDiscountValue;
                    }
                    displayPrice.textContent = formatCurrency(priceToDisplay);
                    originalPrice.textContent = formatCurrency(newPrice);
                    originalPrice.style.display = (productDiscountValue > 0) ? '' : 'none';
                } else {
                    displayPrice.textContent = formatCurrency(newPrice);
                    if (originalPrice) originalPrice.style.display = 'none';
                }
            });
        });

        // Trigger change for the initially checked radio button
        const initialCheckedRadio = document.querySelector('.variation-radio:checked');
        if (initialCheckedRadio) {
            initialCheckedRadio.dispatchEvent(new Event('change'));
        }

        // Add to Cart button handler
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', function() {
                var productId = this.getAttribute('data-product-id');
                var productVariationId = this.getAttribute('data-product-variation-id');
                var qty = parseInt(document.getElementById('quantity-input').value) || 1;
                var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                addToCartBtn.disabled = true;
                fetch("{{ route('cart.add') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            product_id: productId,
                            qty: qty,
                            _token: token,
                            product_variation_id: productVariationId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {

                        addToCartBtn.disabled = false;
                        if (data.status) {
                            if (window.toastr) toastr.success(
                                '{{ __('vendorwebsite.product_added_to_cart_successfully') }}');
                            if (typeof data.cart_count !== 'undefined') {
                                var cartCount = document.getElementById('cartCount');
                                var cartItemCount = document.getElementById('cartItemCount');
                                if (cartCount) cartCount.textContent = data.cart_count;
                                if (cartItemCount) cartItemCount.textContent = data.cart_count;
                            }
                            addToCartBtn.style.display = 'none';
                            if (removeFromCartBtn) removeFromCartBtn.style.display = 'inline-block';
                        } else {
                          
                            if (
                                (window.isLoggedIn === false && window.$ && $('#loginModal')
                                .length) ||
                                (data && data.error === 'Unauthenticated' && window.$ && $(
                                    '#loginModal').length) ||
                                (window.$ && $('#loginModal').length && (data.message ===
                                    'Unauthenticated.' || data.message === 'Unauthorized'))
                            ) {
                                $('#loginModal').modal('show');
                            } else if (window.toastr) {
                                toastr.error((data && (data.message || data.error)) ||
                                    'Failed to add to cart.');
                            }
                        }
                    })
                    .catch((err) => {

                

                        addToCartBtn.disabled = false;
                        // Force show login modal for guests on any error
                        if (window.isLoggedIn === false && window.$ && $('#loginModal').length) {
                            $('#loginModal').modal('show');
                        } else if (err && err.status === 401) {
                            if (window.$ && $('#loginModal').length) {
                                $('#loginModal').modal('show');
                            }
                        } else if (typeof $ !== 'undefined' && $('#loginModal').length) {
                            $('#loginModal').modal('show');
                        } else if (window.toastr) {
                            toastr.error('Failed to add to cart. Please try again.');
                        }
                    });
            });
        }
        // Remove from Cart button handler
        if (removeFromCartBtn) {
            removeFromCartBtn.addEventListener('click', function() {
                var productId = this.getAttribute('data-product-id');
                var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                removeFromCartBtn.disabled = true;
                fetch("{{ route('cart.remove') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            product_id: productId,
                            _token: token
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        removeFromCartBtn.disabled = false;
                        if (data.status) {
                            if (window.toastr) toastr.success(
                                '{{ __('vendorwebsite.product_removed_from_cart_successfully') }}');
                            if (typeof data.cart_count !== 'undefined') {
                                var cartCount = document.getElementById('cartCount');
                                var cartItemCount = document.getElementById('cartItemCount');
                                if (cartCount) cartCount.textContent = data.cart_count;
                                if (cartItemCount) cartItemCount.textContent = data.cart_count;
                            }
                            removeFromCartBtn.style.display = 'none';
                            if (addToCartBtn) addToCartBtn.style.display = 'inline-block';
                        } else {
                            if (window.toastr) toastr.error(data.message ||
                                'Failed to remove from cart.');
                        }
                    })
                    .catch(() => {
                        removeFromCartBtn.disabled = false;
                        if (window.toastr) toastr.error(
                            'Failed to remove from cart. Please try again.');
                    });
            });
        }

        window.handleWishlist = function(productId) {
            var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var url = "{{ route('wishlist.add') }}";

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    product_id: productId
                })
            })
            .then(response => response.json())
            .then(data => {
                var heartSpan = document.querySelector('.product-wishlist[data-product-id="' + productId + '"]');
                if (heartSpan) {
                    var heartIcon = heartSpan.querySelector('i');
                    if (heartIcon) {
                        if (data.status && data.action === 'added') {
                            heartIcon.classList.remove('ph-heart');
                            heartIcon.classList.add('ph-heart', 'ph-fill');
                        } else if (data.status && data.action === 'removed') {
                            heartIcon.classList.remove('ph-heart', 'ph-fill');
                            heartIcon.classList.add('ph-heart');
                        }
                        heartIcon.style.display = 'inline';
                    }
                    heartSpan.style.display = 'inline';
                    if (window.toastr) toastr.success(data.message);
                }
            })
            .catch(error => {
                if (window.toastr) toastr.error('Failed to update wishlist. Please try again.');
            });
        }
    });
</script>
