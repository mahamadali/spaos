@props(['product'])

@php
    $avgRating = null;
    if (isset($product)) {
        $reviews = \DB::table('product_review')->where('product_id', $product->id)->avg('rating');
        // Only set avgRating if there are actual reviews
        $avgRating = $reviews !== null ? round($reviews, 1) : null;
    }

    // Check if product is in user's wishlist directly from database
$isInWishlist = false;
if (auth()->check() && isset($product->id)) {
    $isInWishlist = \Modules\Product\Models\WishList::where('user_id', auth()->id())
        ->where('product_id', $product->id)
            ->exists();
    }

@endphp

<div class="product-card text-center position-relative h-100 d-flex flex-column product-card-fixed"
    data-rating="{{ $avgRating ?? 'no-rating' }}"
    @if (isset($product->slug) && !empty($product->slug)) data-url="{{ route('product-detail', ['slug' => $product->slug]) }}" @endif>

    {{-- Top Badge Section --}}
    <div class="position-absolute top-0 start-0 w-100 d-flex justify-content-between p-2 z-1">
        <div class="d-flex flex-column align-items-start gap-1">
            @if (isset($product->total_sale_count) && $product->total_sale_count >= 10)
                <span class="product-best-seller badge bg-primary">{{ __('vendorwebsite.best_seller') }}</span>
            @endif

            @if (isset($product->created_at) && $product->created_at >= now()->subHours(24))
                <span class="product-new badge bg-success">{{ __('vendorwebsite.new') }}</span>
            @endif
        </div>
        <div>
            <span class="product-wishlist cursor-pointer" data-product-id="{{ $product->id ?? '' }}"
                onclick="handleWishlist({{ $product->id ?? 'null' }})">
                <i class="ph {{ $isInWishlist ? 'ph-heart ph-fill' : 'ph-heart' }}"></i>
            </span>
        </div>
    </div>

    {{-- Product gallery images --}}
    <div class="productgallery-slider slick-general flex-shrink-0" data-spacing="10" data-items="1"
        data-items-desktop="1" data-items-laptop="1" data-items-tablet="1" data-items-mobile-sm="1"
        data-items-mobile="1" data-speed="500" data-autoplay="true" data-infinite="true" data-navigation="false"
        data-pagination="false" data-center="false" data-centerpadding="8%">

        @if (isset($product->media) && $product->media->count() > 0)
            <a href="{{ route('product-detail', ['slug' => $product->slug]) }}" class="text-truncate d-block">
                @foreach ($product->media as $media)
                    <div class="product-image">
                        <img src="{{ $media->getFullUrl() }}" alt="{{ $product->name ?? 'Product Image' }}">
                    </div>
                @endforeach
            </a>
        @else
            <a href="{{ route('product-detail', ['slug' => $product->slug]) }}" class="text-truncate d-block">
                <div class="product-image">
                    <img src="{{ asset('dummy-images/dummy.png') }}"
                        alt="{{ $product->name ?? 'Product Image' }}">
                </div>
            </a>
        @endif
    </div>

    {{-- Product info --}}
    <div class="product-info">
        <h5 class="product-title text-truncate">
            @if (isset($product->slug) && !empty($product->slug))
                <a href="{{ route('product-detail', ['slug' => $product->slug]) }}" class="text-truncate">
                    {{ $product->name ?? __('vendorwebsite.product_name') }}
                </a>
            @else
                <span class="text-truncate">{{ $product->name ?? __('vendorwebsite.product_name') }}</span>
            @endif
        </h5>

        @php
            $originalPrice = $product->product_variations->first()->price ?? 0;
            $discount = $product->discount_value ?? 0;
            $discountType = $product->discount_type ?? '';
            $discountedPrice =
                $discountType === 'percent'
                    ? $originalPrice - ($originalPrice * $discount) / 100
                    : $originalPrice - $discount;
        @endphp

        <div class="product-prices d-flex align-items-center gap-3 justify-content-center">
            @if ($discount > 0)
             <span class="product-price text-primary fw-medium">
                    {{ \Currency::vendorCurrencyFormate($discountedPrice) }}
                </span>
                <del>{{ \Currency::vendorCurrencyFormate($originalPrice) }}</del>
               
            @else
                <span class="product-price text-primary fw-medium">
                    {{ \Currency::vendorCurrencyFormate($originalPrice) }}
                </span>
            @endif
        </div>

        @auth
            <button id="addToCartBtn_{{ $product->id }}" class="btn btn-secondary mt-3 w-100 add-to-cart"
                onclick="handleAddToCart({{ $product->id }}, {{ $product->product_variations->first()->id }})"
                style="{{ isset($product->in_cart) && $product->in_cart ? 'display: none;' : '' }}">
                <i class="ph ph-shopping-cart"></i> {{ __('vendorwebsite.add_to_cart') }}
            </button>
            <button id="removeFromCartBtn_{{ $product->id }}" class="btn btn-danger mt-3 w-100 remove-from-cart"
                onclick="removeFromCart({{ $product->id }},{{ $product->product_variations->first()->id }})"
                style="{{ !isset($product->in_cart) || !$product->in_cart ? 'display: none;' : '' }}">
                <i class="ph ph-trash"></i> {{ __('vendorwebsite.remove_from_cart') }}
            </button>
        @else
            <button class="btn btn-secondary mt-3 w-100"
                onclick="handleAddToCart({{ $product->id }},{{ $product->product_variations->first()->id }})">
                <i class="ph ph-shopping-cart"></i> {{ __('vendorwebsite.add_to_cart') }}
            </button>
        @endauth
    </div>
</div>

<script>
    // Ensure this script is only included once per page
    if (typeof window.productCardAuthHandlerLoaded === 'undefined') {
        window.productCardAuthHandlerLoaded = true;
        window.isLoggedIn = window.isLoggedIn ?? {{ auth()->check() ? 'true' : 'false' }};

        function handleWishlist(productId) {
            var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var url = window.wishlistAddRoute || "{{ route('wishlist.add') }}"; // Set this globally in your layout
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    product_id: productId,
                    _token: token
                },
                success: function(response) {
                    var heartSpan = document.querySelector('.product-wishlist[data-product-id="' +
                        productId + '"]');
                    if (heartSpan) {
                        var heartIcon = heartSpan.querySelector('i');
                        if (heartIcon) {
                            if (response.status && response.action === 'added') {
                                heartIcon.classList.remove('ph-heart');
                                heartIcon.classList.add('ph-heart', 'ph-fill');
                            } else if (response.status && response.action === 'removed') {
                                heartIcon.classList.remove('ph-heart', 'ph-fill');
                                heartIcon.classList.add('ph-heart');
                            }
                            heartIcon.style.display = 'inline'; // Always show the icon
                        }
                        heartSpan.style.display = 'inline'; // Always show the span
                        toastr.success(response.message);
                    } else if (response.redirect) {
                        $('#loginModal').modal('show');
                    } else {
                        toastr.error(response.message ||
                            '{{ __('vendorwebsite.wishlist_action_failed') }}');
                    }
                },
                error: function(jqXHR) {
                    if (jqXHR.status === 401) {
                        $('#loginModal').modal('show');
                    } else {
                        toastr.error('{{ __('vendorwebsite.wishlist_action_failed_please_try_again') }}');
                    }
                }
            });
        }

        function handleAddToCart(productId, productVariationId) {
            var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var url = window.addToCartRoute || "{{ route('cart.add') }}"; // Set this globally in your layout
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    product_id: productId,
                    product_variation_id: productVariationId,
                    _token: token
                },
                success: function(response) {
                    if (response.status) {
                        // Update UI instantly
                        $('#addToCartBtn_' + productId).hide();
                        $('#removeFromCartBtn_' + productId).show();
                        toastr.success("{{ __('vendorwebsite.add_to_cart') }}");
                        // Update cart count immediately if available
                        if (typeof response.cart_count !== 'undefined') {
                            $('#cartCount').text(response.cart_count);
                            $('#cartItemCount').text(response.cart_count);
                        }
                    } else if (response.redirect) {
                        $('#loginModal').modal('show');
                    } else {
                        toastr.error(response.message || '{{ __('vendorwebsite.add_to_cart_failed') }}');
                    }
                },
                error: function(jqXHR) {
                    if (jqXHR.status === 401) {
                        $('#loginModal').modal('show');
                    } else {
                        toastr.error('{{ __('vendorwebsite.add_to_cart_failed') }}');
                    }
                }
            });
        }

        // Add removeFromCart function for instant UI update
        window.removeFromCart = function(productId, productVariationId) {
            var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var url = window.removeFromCartRoute ||
                "{{ route('cart.remove') }}"; // Set this globally in your layout
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    product_id: productId,
                    product_variation_id: productVariationId,
                    _token: token
                },
                success: function(response) {
                    if (response.status) {
                        $('#removeFromCartBtn_' + productId).hide();
                        $('#addToCartBtn_' + productId).show();
                        toastr.success("{{ __('vendorwebsite.remove_from_cart') }}");
                        // Update cart count immediately if available
                        if (typeof response.cart_count !== 'undefined') {
                            $('#cartCount').text(response.cart_count);
                            $('#cartItemCount').text(response.cart_count);
                        }
                    } else {
                        toastr.error(response.message ||
                            '{{ __('vendorwebsite.remove_from_cart_failed') }}');
                    }
                },
                error: function(jqXHR) {
                    toastr.error('{{ __('vendorwebsite.remove_from_cart_failed') }}');
                }
            });
        }
    }

    // Make the product card clickable except for Add to Cart, Remove from Cart, or Wishlist buttons
    $(document).on('click', '.product-card', function(e) {
        // If the click is on a button, wishlist, or a link, do nothing
        if (
            $(e.target).closest('.add-to-cart, .remove-from-cart, .product-wishlist, button, a').length
        ) {
            return;
        }
        // Get the product detail URL from the link in the card
        var url = $(this).data('url');
        if (url) {
            // Use setTimeout to ensure navigation is not blocked by other handlers
            setTimeout(function() {
                window.location.assign(url);
            }, 0); // Reduced delay for instant navigation
        }
    });
</script>
