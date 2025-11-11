<?php

namespace Modules\VendorWebsite\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Product\Models\Product;
use Yajra\DataTables\DataTables;
use Modules\Category\Models\Category;
use Illuminate\Support\Facades\DB;
use Modules\Product\Models\ProductVariation;
use Modules\Product\Models\Brands;
use Modules\Product\Models\Review;
use Modules\Product\Models\WishList;
use Modules\Product\Models\Cart;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Address;
use Auth;
use Modules\Logistic\Models\LogisticZone;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Product\Models\ProductCategory;
use Modules\Wallet\Models\Wallet;
use Modules\Product\Models\Order;
use Modules\Booking\Models\BookingProduct;
use Modules\Booking\Models\Booking;

use Currency;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        if(checkVendorMenuPermission('shop','header-menu-setting')){
        $products = Product::where('status', 1)->get();

        // Set in_wishlist for each product for the current user
        $wishlistItems = [];
        if (auth()->check()) {
            $wishlistItems = \Modules\Product\Models\WishList::where('user_id', auth()->id())
                ->pluck('product_id')
                ->toArray();
        }
        foreach ($products as $product) {
            $product->in_wishlist = in_array($product->id, $wishlistItems);
        }

        $mainCategories = ProductCategory::whereNull('parent_id')
            ->where('status', 1)
            ->withCount(['productMappings' => function ($query) {
                $query->whereHas('product', function ($q) {
                    $q->where('status', 1);
                });
            }]) // counts only active products via mapping
            ->with(['children' => function ($query) {
                $query->where('status', 1)->withCount(['productMappings' => function ($q) {
                    $q->whereHas('product', function ($productQuery) {
                        $productQuery->where('status', 1);
                    });
                }]);
            }])
            ->orderBy('name')
            ->get();




        // Get price statistics from active products
        $priceStats = Product::where('status', 1)
            ->selectRaw('
                MIN(min_price) as min_price,
                MAX(max_price) as max_price,
                AVG(max_price) as avg_price,
                COUNT(*) as total_products
            ')
            ->first();

        // Calculate dynamic price brackets based on quartiles
        $priceQuartiles = DB::table('products')
            ->where('status', 1)
            ->selectRaw('
                max_price,
                NTILE(4) OVER (ORDER BY max_price) as quartile
            ')
            ->orderBy('max_price')
            ->get()
            ->groupBy('quartile');

        // Get the maximum price for each quartile
        $quartileBoundaries = [];
        foreach ($priceQuartiles as $quartile => $prices) {
            $quartileBoundaries[$quartile] = $prices->max('max_price');
        }

        // Round the boundaries and ensure minimum $30 difference
        $minPrice = floor($priceStats->min_price);
        $maxPrice = ceil($priceStats->max_price);

        // Calculate total range and minimum step
        $totalRange = $maxPrice - $minPrice;
        $minStep = 30; // Minimum difference between brackets

        if ($totalRange < ($minStep * 3)) {
            // If total range is too small, create evenly spaced brackets
            $step = max($minStep, ceil($totalRange / 4));
            $bracket1 = $minPrice + $step;
            $bracket2 = $bracket1 + $step;
            $bracket3 = $bracket2 + $step;
        } else {
            // Use quartiles but ensure minimum difference
            $bracket1 = $this->roundPriceNicely($quartileBoundaries[1]);
            $bracket2 = max($bracket1 + $minStep, $this->roundPriceNicely($quartileBoundaries[2]));
            $bracket3 = max($bracket2 + $minStep, $this->roundPriceNicely($quartileBoundaries[3]));

            // Adjust if final bracket is too close to max price
            if (($maxPrice - $bracket3) < $minStep) {
                $bracket3 = $maxPrice - $minStep;
            }
        }

        $priceBrackets = [
            [
                'lower' => $minPrice,
                'upper' => $bracket1,
                'label' => "Under " . \Currency::format($bracket1),
                'row' => 1,
                'col' => 1
            ],
            [
                'lower' => $bracket1,
                'upper' => $bracket2,
                'label' => \Currency::format($bracket1) . " - " . \Currency::format($bracket2),
                'row' => 1,
                'col' => 2
            ],
            [
                'lower' => $bracket2,
                'upper' => $bracket3,
                'label' => \Currency::format($bracket2) . " - " . \Currency::format($bracket3),
                'row' => 2,
                'col' => 1
            ],
            [
                'lower' => $bracket3,
                'upper' => $maxPrice,
                'label' => \Currency::format($bracket3) . " & Above",
                'row' => 2,
                'col' => 2
            ]
        ];


        return view('vendorwebsite::shop', compact('mainCategories', 'products', 'priceStats', 'priceBrackets'));
    }else{

        abort(403);
    }
    }

    /**
     * Rounds a price to a "nice" number for display
     * Examples: 73.42 -> 75, 128.90 -> 130, 1234 -> 1250
     */
    private function roundPriceNicely($price)
    {
        if ($price <= 0) return 0;

        // For prices under 100, round to nearest 5
        if ($price < 100) {
            return ceil($price / 5) * 5;
        }

        // For prices under 1000, round to nearest 10
        if ($price < 1000) {
            return ceil($price / 10) * 10;
        }

        // For larger prices, round to nearest 50
        return ceil($price / 50) * 50;
    }

    // Helper function for consistent currency formatting
    private function currency_format($amount)
    {
        return \Currency::format($amount);
    }

    public function ProductDetail($slug)
    {
        $product = Product::with([
            'brand',
            'unit',
            'gallery',
            'categories',
            'product_review',
            'product_variations'
        ])
            ->where('slug', $slug)
            ->firstOrFail();

        // No longer processing size_guide field here, relying on variations
        $product->sizes = $product->product_variations->pluck('variation_key')->unique()->toArray();

        // Add wishlist status
        if (auth()->check()) {
            $product->in_wishlist = WishList::where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->exists();
            // Add in_cart status
            $product->in_cart = \Modules\Product\Models\Cart::where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->whereHas('product', function ($query) {
                    $query->where('created_by', session('current_vendor_id'));
                })
                ->exists();
        } else {
            $product->in_wishlist = false;
            $product->in_cart = false;
        }

        // Get related products from the same categories
        $relatedProducts = Product::with(['media'])
            ->whereHas('categories', function ($query) use ($product) {
                $query->whereIn('product_categories.id', $product->categories->pluck('id'));
            })
            ->where('products.id', '!=', $product->id)
            ->where('products.status', 1)
            ->limit(4)
            ->get();
        if (auth()->check()) {
            $wishlistItems = WishList::where('user_id', auth()->id())
                ->pluck('product_id')
                ->toArray();

            $cartItems = Cart::where('user_id', auth()->id())
                ->whereHas('product', function ($query) {
                    $query->where('created_by', session('current_vendor_id'));
                })
                ->pluck('product_id')
                ->toArray();

            $relatedProducts->each(function ($relatedProduct) use ($wishlistItems, $cartItems) {
                $relatedProduct->in_wishlist = in_array($relatedProduct->id, $wishlistItems);
                $relatedProduct->in_cart = in_array($relatedProduct->id, $cartItems);
            });
        } else {
            $relatedProducts->each(function ($relatedProduct) {
                $relatedProduct->in_wishlist = false;
                $relatedProduct->in_cart = false;
            });
        }

        return view('vendorwebsite::product_details', compact('product', 'relatedProducts'));
    }

    /**
     * Get product variation details via AJAX.
     */
    public function getProductVariationDetails(Request $request)
    {
        $productId = $request->input('product_id');
        $variationKey = $request->input('variation_key');

        $variation = ProductVariation::where('product_id', $productId)
            ->where('variation_key', $variationKey)
            ->first();

        if ($variation) {
            // Return the necessary details.
            // Assuming 'price' and 'stock_qty' (or a similar field) exist in your variations table.
            // If stock_qty doesn't exist, you'll need to adapt.
            return response()->json([
                'price' => \Currency::format($variation->price),
                'stock_qty' => $variation->stock_qty ?? null, // Assuming stock_qty exists
                'is_in_stock' => ($variation->stock_qty ?? 0) > 0
            ]);
        } else {
            return response()->json(['error' => 'Variation not found'], 404);
        }
    }

    public function productsData(Request $request)
    {

        $query = Product::with(['media', 'product_variations'])
            ->select([
                'products.id',
                'products.name',
                'products.slug',
                'products.max_price',
                'products.min_price',
                'products.discount_value',
                'products.discount_type',
                'products.created_at',
                'products.total_sale_count',
                'products.status'
            ])
            ->where('products.status', 1);

        // Track if any filter is active
        $isFilterActive = false;

        // Apply category filter if categories are selected
        if ($request->has('categories') && !empty($request->categories)) {
            $isFilterActive = true;
            $query->whereHas('categories', function ($q) use ($request) {
                $q->whereIn('product_categories.id', $request->categories);
            });
        }

        // Apply price filter with exact max_price filtering
        if ($request->has('min_price') && $request->has('max_price')) {
            $minPrice = (float) $request->min_price;
            $maxPrice = (float) $request->max_price;

            // Default min/max from first variations
            $defaultMin = floor(
                \DB::table('product_variations as pv1')
                    ->select('pv1.price')
                    ->whereRaw('pv1.id = (SELECT MIN(pv2.id) FROM product_variations pv2 WHERE pv2.product_id = pv1.product_id)')
                    ->min('pv1.price')
            );

            $defaultMax = ceil(
                \DB::table('product_variations as pv1')
                    ->select('pv1.price')
                    ->whereRaw('pv1.id = (SELECT MIN(pv2.id) FROM product_variations pv2 WHERE pv2.product_id = pv1.product_id)')
                    ->max('pv1.price')
            );

            if ($minPrice != $defaultMin || $maxPrice != $defaultMax) {
                $isFilterActive = true;

                $query->whereIn('id', function ($sub) use ($minPrice, $maxPrice, $defaultMin, $defaultMax) {
                    $sub->select('pv1.product_id')
                        ->from('product_variations as pv1')
                        ->whereRaw('pv1.id = (SELECT MIN(pv2.id) FROM product_variations pv2 WHERE pv2.product_id = pv1.product_id)')
                        ->where(function ($q) use ($minPrice, $maxPrice, $defaultMin, $defaultMax) {
                            if ($minPrice == $defaultMin) {
                                $q->where('pv1.price', '<=', $maxPrice);
                            } elseif ($maxPrice == $defaultMax) {
                                $q->where('pv1.price', '>=', $minPrice);
                            } else {
                                $q->whereBetween('pv1.price', [$minPrice, $maxPrice]);
                            }
                        });
                });
            }
        }

        // Apply rating filter
        if ($request->has('rating_ranges') && !empty($request->rating_ranges)) {
            $isFilterActive = true;
            $ratingRanges = $request->rating_ranges;

            $query->where(function ($q) use ($ratingRanges) {
                foreach ($ratingRanges as $range) {
                    $q->orWhereHas('product_review', function ($subQuery) use ($range) {
                        $subQuery->select(DB::raw('AVG(rating) as avg_rating'))
                            ->groupBy('product_id')
                            ->havingRaw('AVG(rating) >= ? AND AVG(rating) < ?', [$range['min'], $range['max']]);
                    });
                }
            });
        }

        if ($request->has('discount_ranges') && is_array($request->discount_ranges)) {
            $isFilterActive = true;
            $discountRanges = $request->discount_ranges;

            $query->where(function ($q) use ($discountRanges) {
                foreach ($discountRanges as $range) {
                    if (isset($range['min'], $range['max'])) {
                        $q->orWhere(function ($subQuery) use ($range) {
                            $subQuery->where('discount_type', 'percent')
                                ->where('discount_value', '>', 0);

                            if ($range['max'] < 100) {
                                $subQuery->where('discount_value', '>=', $range['min'])
                                    ->where('discount_value', '<', $range['max']);
                            } else {
                                $subQuery->where('discount_value', '>=', $range['min']);
                            }
                        });
                    }
                }
            });
        }

        // Apply filter dropdown
        if ($request->has('filter') && $request->filter) {
            $isFilterActive = true;
            switch ($request->filter) {
                case 'newest':
                    $query->whereRaw('DATE(products.created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)');
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'trending':
                    $query->where('total_sale_count', '>=', 10);
                    $query->orderBy('total_sale_count', 'desc');
                    break;
            }
        }

        // Apply default sorting if no specific filter is active
        if (!$isFilterActive) {
            $query->orderBy('created_at', 'desc');
        } else if (!$request->filter || $request->filter === '') {
            // If price filter is active but no sort filter, sort by price
            $query->orderBy('max_price', 'asc')
                ->orderBy('created_at', 'desc');
        }

        // Get the products
        $products = $query->get();

        // Add cart status to each product
        if (auth()->check()) {
            $cartItems = Cart::where('user_id', auth()->id())
                ->pluck('product_id')
                ->toArray();

            $wishlistItems = WishList::where('user_id', auth()->id())
                ->pluck('product_id')
                ->toArray();



            $products->each(function ($product) use ($cartItems, $wishlistItems) {
                $product->in_cart = in_array($product->id, $cartItems);
                $product->in_wishlist = in_array($product->id, $wishlistItems);
            });
        } else {
            // For non-authenticated users, set wishlist status to false
            $products->each(function ($product) {
                $product->in_wishlist = false;
                $product->in_cart = false;
            });
        }

        // Log the query here before DataTables processes it


        return DataTables::of($products)
            ->addColumn('card', function ($product) {
                return view('vendorwebsite::components.card.product_card', ['product' => $product])->render();
            })
            ->rawColumns(['card'])
            ->make(true);
    }

    public function Cart()
    {
        if (!auth()->check()) {
            return redirect()->route('vendor.login', ['vendor_slug' => request()->route('vendor_slug')]);
        }

        $cartItems = Cart::with(['product' => function ($query) {
            $query->with('media');
        }])
            ->where('user_id', auth()->id())
            ->whereHas('product', function ($query) {
                $query->where('created_by', session('current_vendor_id'));
            })
            ->get();

        $subtotal = 0;
        $discount = 0;
        $total = 0;

        foreach ($cartItems as $item) {
            $product = $item->product;
            if ($product && $item->product_variation) {
                $price = $item->product_variation->price;

                // Calculate discount if any
                if ($product->discount_type === 'percent' && $product->discount_value > 0) {
                    $itemDiscount = ($price * $product->discount_value / 100) * $item->qty;
                    $discount += $itemDiscount;
                    $price = $price - ($price * $product->discount_value / 100);
                } elseif ($product->discount_type === 'fixed' && $product->discount_value > 0) {
                    $itemDiscount = $product->discount_value * $item->qty;
                    $discount += $itemDiscount;
                    $price = $price - $product->discount_value;
                }

                $subtotal += $price * $item->qty;
            }
        }

        $taxes = \Modules\Tax\Models\Tax::where('status', 1)->where('created_by', session('current_vendor_id'))->where('module_type', 'product')->get();


        $total = $subtotal;

        if (count($cartItems) > 0) {

            return view('vendorwebsite::cart', compact('cartItems', 'subtotal', 'discount', 'total', 'taxes'));
        } else {


            return view('vendorwebsite::empty_cart');
        }
    }

    public function CheckOut()
    {
        if (!auth()->check()) {
            return redirect()->route('vendor.login', ['vendor_slug' => request()->route('vendor_slug')]);
        }

        // Get cart items with product details for current vendor only
        $cartItems = Cart::with(['product' => function ($query) {
            $query->with('media');
        }])
            ->where('user_id', auth()->id())
            ->whereHas('product', function ($query) {
                $query->where('created_by', session('current_vendor_id'));
            })
            ->get();

        // Clean up invalid cart items (products that no longer exist)
        $this->cleanupInvalidCartItems($cartItems);

        // If no cart items after cleanup, redirect to empty cart
        if ($cartItems->isEmpty()) {
            return view('vendorwebsite::empty_cart');
        }

        // Get user addresses
        $addresses = \App\Models\Address::where('addressable_id', auth()->id())->where('addressable_type', 'App\Models\User')->get();

        // Calculate cart totals
        $subtotal = 0;
        $discount = 0;
        $discountPercentage = 0;
        $deliveryCharge = 0;
        $total = 0;

        foreach ($cartItems as $item) {
            $product = $item->product;
            if ($product && $item->product_variation) {
                $price = $item->product_variation->price ?? 0;

                // Calculate discount if any
                if ($product->discount_type === 'percent' && $product->discount_value > 0) {
                    $itemDiscount = ($price * $product->discount_value / 100) * $item->qty;
                    $discount += $itemDiscount;
                    $price = $price - ($price * $product->discount_value / 100);
                    $discountPercentage = $product->discount_value;
                } elseif ($product->discount_type === 'fixed' && $product->discount_value > 0) {
                    $itemDiscount = $product->discount_value * $item->qty;
                    $discount += $itemDiscount;
                    $price = $price - $product->discount_value;
                }

                $subtotal += $price * $item->qty;
            }
        }

        $total = $subtotal + $deliveryCharge;

        $paymentMethods = [
            'cash'        => true, // Always enabled and default
            'stripe'      =>  getVendorSetting('str_payment_method') == 1,
            'razorpay'    =>  getVendorSetting('razor_payment_method') == 1,
            'paystack'    =>  getVendorSetting('paystack_payment_method') == 1,
            'paypal'      =>  getVendorSetting('paypal_payment_method') == 1,
            'flutterwave' =>  getVendorSetting('flutterwave_payment_method') == 1,
            // Add more as needed
        ];
        $wallet = Wallet::where('user_id', auth()->id())->first();
        if ($wallet) {
            $walletPayment = true;
            $walletBalance = $wallet->amount;
            $paymentMethods['wallet'] = true;
        } else {
            $walletPayment = false;
            $walletBalance = 0;
        }
        // Fetch active taxes
        $taxes = \Modules\Tax\Models\Tax::where('status', 1)->where('created_by', session('current_vendor_id'))->where('module_type', 'product')->get();

        return view('vendorwebsite::checkout', compact(
            'cartItems',
            'addresses',
            'subtotal',
            'discount',
            'discountPercentage',
            'deliveryCharge',
            'total',
            'paymentMethods',
            'taxes',
            'walletBalance',
            'walletPayment'
        ));
    }

    private function createSampleLogisticZones()
    {
        // Create sample logistics first
        $logistics = [
            ['name' => 'FedEx'],
            ['name' => 'DHL'],
            ['name' => 'UPS'],
        ];

        foreach ($logistics as $logisticData) {
            $logistic = \Modules\Logistic\Models\Logistic::firstOrCreate(
                ['name' => $logisticData['name']],
                ['status' => 1]
            );
        }

        // Get first country and state for sample data
        $country = \Modules\World\Models\Country::first();
        $state = \Modules\World\Models\State::first();
        $cities = \Modules\World\Models\City::take(5)->get(); // Get first 5 cities

        if ($country && $state && $cities->isNotEmpty()) {
            $sampleZones = [
                [
                    'name' => 'Local Delivery',
                    'description' => 'Local area delivery within city limits',
                    'logistic_id' => 1,
                    'country_id' => $country->id,
                    'state_id' => $state->id,
                    'standard_delivery_charge' => 5.00,
                    'express_delivery_charge' => 10.00,
                    'standard_delivery_time' => '1-2 days',
                    'express_delivery_time' => 'Same day',
                ],
                [
                    'name' => 'Standard Delivery',
                    'description' => 'Standard delivery to nearby areas',
                    'logistic_id' => 2,
                    'country_id' => $country->id,
                    'state_id' => $state->id,
                    'standard_delivery_charge' => 10.00,
                    'express_delivery_charge' => 15.00,
                    'standard_delivery_time' => '3-5 days',
                    'express_delivery_time' => '1-2 days',
                ],
                [
                    'name' => 'Express Delivery',
                    'description' => 'Express delivery for urgent orders',
                    'logistic_id' => 3,
                    'country_id' => $country->id,
                    'state_id' => $state->id,
                    'standard_delivery_charge' => 15.00,
                    'express_delivery_charge' => 25.00,
                    'standard_delivery_time' => '1 day',
                    'express_delivery_time' => 'Same day',
                ],
                [
                    'name' => 'Free Delivery',
                    'description' => 'Free delivery for orders over $50',
                    'logistic_id' => 1,
                    'country_id' => $country->id,
                    'state_id' => $state->id,
                    'standard_delivery_charge' => 0.00,
                    'express_delivery_charge' => 5.00,
                    'standard_delivery_time' => '5-7 days',
                    'express_delivery_time' => '3-5 days',
                ],
            ];

            foreach ($sampleZones as $zoneData) {
                $logisticZone = \Modules\Logistic\Models\LogisticZone::firstOrCreate(
                    ['name' => $zoneData['name']],
                    $zoneData
                );

                // Attach cities to the zone for address-based filtering
                if ($logisticZone->wasRecentlyCreated) {
                    foreach ($cities as $city) {
                        \Modules\Logistic\Models\LogisticZoneCity::create([
                            'logistic_id' => $logisticZone->logistic_id,
                            'logistic_zone_id' => $logisticZone->id,
                            'city_id' => $city->id,
                        ]);
                    }
                }
            }
        }
    }

    public function bookingPayment()
    {
        return view('vendorwebsite::booking_payment');
    }


    public function OrderDetail($order_id)
    {
        $order = Order::with([
            'orderItems.product_variation.product',
            'orderGroup'
        ])->findOrFail($order_id);

        if ($order == null) {
            abort(404, 'Order not found.');
        }

        if ($order->user_id != auth()->id()) {
            abort(404, 'You are not authorized to view this order.');
        }

        // Load reviews for each order item manually
        foreach ($order->orderItems as $orderItem) {
            $review = Review::where('product_variation_id', $orderItem->product_variation_id)
                ->where('user_id', auth()->id())
                ->first();
            $orderItem->review = $review;
        }

        $productReview = null;

        $address = null;
        if ($order->orderGroup && $order->orderGroup->shipping_address_id) {
            $address = Address::find($order->orderGroup->shipping_address_id);
        }
        if (!$address && $order->user && $order->user->addresses) {
            $address = $order->user->addresses->first();
        }
        return view('vendorwebsite::order_details', compact('order', 'address'));
    }

    public function CheckoutDetail()
    {
        return view('vendorwebsite::checkout_detail');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('vendorwebsite::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('vendorwebsite::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('vendorwebsite::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    public function addToCart(Request $request)
    {


        try {
            if (!auth()->check()) {

                return response()->json([
                    'status' => false,
                    'redirect' => true,
                    'message' => 'Please login to add items to cart'
                ]);
            }

            $productId = $request->input('product_id');
            $productVariationId = $request->input('product_variation_id');
            $qty = $request->input('qty', 1);

            $product = Product::where('status', 1)->find($productId);

            if (!$product) {

                return response()->json([
                    'status' => false,
                    'message' => 'Product not found'
                ]);
            }

            // Check if product is already in cart for this vendor
            $existingCartItem = Cart::where('user_id', auth()->id())
                ->where('product_id', $productId)
                ->where('product_variation_id', $productVariationId)
                ->whereHas('product', function ($query) {
                    $query->where('created_by', session('current_vendor_id'));
                })
                ->first();

            if ($existingCartItem) {

                $existingCartItem->qty += $qty;
                $existingCartItem->save();
            } else {

                Cart::create([
                    'user_id' => auth()->id(),
                    'product_id' => $productId,
                    'product_variation_id' => $productVariationId,
                    'qty' => $qty,
                    'location_id' => 1 // Default location
                ]);
            }

            // Get updated cart count for current vendor
            $cartCount = Cart::where('user_id', auth()->id())
                ->whereHas('product', function ($query) {
                    $query->where('created_by', session('current_vendor_id'));
                })
                ->count();



            return response()->json([
                'status' => true,
                'message' => 'Product added to cart successfully',
                'cart_count' => $cartCount
            ]);
        } catch (\Exception $e) {


            return response()->json([
                'status' => false,
                'message' => 'Failed to add item to cart. Please try again.'
            ]);
        }
    }

    public function removeFromCart(Request $request)
    {

        try {
            if (!auth()->check()) {

                return response()->json([
                    'status' => false,
                    'message' => 'Please login to remove items from cart'
                ]);
            }

            $productId = $request->input('product_id');

            $cartItem = Cart::where('user_id', auth()->id())
                ->where('product_id', $productId)
                ->whereHas('product', function ($query) {
                    $query->where('created_by', session('current_vendor_id'));
                })
                ->first();

            if ($cartItem) {
                $cartItem->delete();
            }
            // Get updated cart count for current vendor
            $cartCount = Cart::where('user_id', auth()->id())
                ->whereHas('product', function ($query) {
                    $query->where('created_by', session('current_vendor_id'));
                })
                ->count();

            return response()->json([
                'status' => true,
                'message' => 'Product removed from cart successfully',
                'cart_count' => $cartCount
            ]);
        } catch (\Exception $e) {


            return response()->json([
                'status' => false,
                'message' => 'Failed to remove item from cart. Please try again.'
            ]);
        }
    }

    public function getCartCount()
    {


        try {
            if (!auth()->check()) {

                return response()->json([
                    'status' => false,
                    'message' => 'Please login to view cart'
                ]);
            }

            // Return the sum of all product quantities in the cart
            $cartCount = \Modules\Product\Models\Cart::with('product')->where('user_id', auth()->id())->whereHas('product', function ($query) {
                $query->where('created_by', session('current_vendor_id'));
            })->count();


            return response()->json([
                'status' => true,
                'cart_count' => $cartCount
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Failed to get cart count. Please try again.'
            ]);
        }
    }

    public function updateCart(Request $request)
    {
        try {
            if (!auth()->check()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Please login to update cart'
                ]);
            }

            $cartItemId = $request->input('cart_item_id');
            $qty = $request->input('qty');

            if ($qty < 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Quantity must be at least 1'
                ]);
            }

            $cartItem = Cart::with('product')
                ->where('user_id', auth()->id())
                ->where('id', $cartItemId)
                ->whereHas('product', function ($query) {
                    $query->where('created_by', session('current_vendor_id'));
                })
                ->first();

            if (!$cartItem) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cart item not found'
                ]);
            }

            // Check if requested quantity exceeds stock
            if ($qty > $cartItem->product->stock_qty) {
                return response()->json([
                    'status' => false,
                    'message' => 'Only ' . $cartItem->product->stock_qty . ' items available in stock'
                ]);
            }

            $cartItem->qty = $qty;
            $cartItem->save();

            return response()->json([
                'status' => true,
                'message' => 'Cart updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update cart. Please try again.'
            ]);
        }
    }

    public function cartData()
    {
        $cartItems = Cart::with(['product', 'product_variation'])
            ->where('user_id', auth()->id())
            ->whereHas('product', function ($query) {
                $query->where('created_by', session('current_vendor_id'));
            })
            ->get();

        // Filter out cart items with null products or product_variations
        $validCartItems = $cartItems->filter(function ($item) {
            return $item->product && $item->product_variation;
        });

        return DataTables::of($validCartItems)
            ->addColumn('product_name', function ($item) {
                return $item->product ? $item->product->name : 'Product Not Found';
            })
            ->addColumn('product_image', function ($item) {
                if (!$item->product) {
                    return asset('img/vendorwebsite/product.png');
                }
                return $item->product->feature_image ?
                    $item->product->feature_image :
                    asset('img/vendorwebsite/product.png');
            })
            ->addColumn('price', function ($item) {
                return Currency::vendorCurrencyFormate($item->product_variation ? $item->product_variation->price : 0);
            })
            ->addColumn('original_price', function ($item) {
                return Currency::vendorCurrencyFormate($item->product_variation ? $item->product_variation->price : 0);
            })
            ->addColumn('discounted_price', function ($item) {
                if (!$item->product || !$item->product_variation) {
                    return Currency::vendorCurrencyFormate(0);
                }
                $price = $item->product_variation->price ?? 0;
                if ($item->product->discount_type === 'percent' && $item->product->discount_value > 0) {
                    $price = $price - ($price * $item->product->discount_value / 100);
                } elseif ($item->product->discount_type === 'fixed' && $item->product->discount_value > 0) {
                    $price = $price - $item->product->discount_value;
                }
                return Currency::vendorCurrencyFormate($price);
            })
            ->addColumn('discount_value', function ($item) {
                return $item->product ? $item->product->discount_value : 0;
            })
            ->addColumn('quantity', function ($item) {
                return $item->qty;
            })
            ->addColumn('subtotal', function ($item) {
                if (!$item->product || !$item->product_variation) {
                    return Currency::vendorCurrencyFormate(0);
                }
                $price = $item->product_variation->price ?? 0;
                if ($item->product->discount_type === 'percent' && $item->product->discount_value > 0) {
                    $price = $price - ($price * $item->product->discount_value / 100);
                } elseif ($item->product->discount_type === 'fixed' && $item->product->discount_value > 0) {
                    $price = $price - $item->product->discount_value;
                }
                return Currency::vendorCurrencyFormate($price * $item->qty);
            })
            ->addColumn('product', function ($item) {
                if (!$item->product || !$item->product_variation) {
                    return [
                        'name' => 'Product Not Found',
                        'stock_qty' => 0
                    ];
                }
                $stockQty = $item->product_variation->product_variation_stock->stock_qty ?? 0;

                return [
                    'name' => $item->product->name,
                    'stock_qty' => $stockQty
                ];
            })
            ->make(true);
    }

    public function checkoutData()
    {
        $cartItems = Cart::with(['product', 'product_variation'])
            ->where('user_id', auth()->id())
            ->whereHas('product', function ($query) {
                $query->where('created_by', session('current_vendor_id'));
            })
            ->get();

        // Filter out cart items with null products or product_variations
        $validCartItems = $cartItems->filter(function ($item) {
            return $item->product && $item->product_variation;
        });

        return DataTables::of($validCartItems)
            ->addColumn('product_name', function ($item) {
                return $item->product ? $item->product->name : 'Product Not Found';
            })
            ->addColumn('product_image', function ($item) {
                if (!$item->product) {
                    return asset('img/vendorwebsite/product.png');
                }
                return $item->product->media && $item->product->media->isNotEmpty() ?
                    $item->product->media->first()->getFullUrl() :
                    asset('img/vendorwebsite/product.png');
            })
            ->addColumn('price', function ($item) {
                return $item->product_variation ? $item->product_variation->price : 0;
            })
            ->addColumn('original_price', function ($item) {
                return $item->product_variation ? $item->product_variation->price : 0;
            })
            ->addColumn('discounted_price', function ($item) {
                if (!$item->product || !$item->product_variation) {
                    return 0;
                }
                $price = $item->product_variation->price ?? 0;
                if ($item->product->discount_type === 'percent' && $item->product->discount_value > 0) {
                    $price = $price - ($price * $item->product->discount_value / 100);
                } elseif ($item->product->discount_type === 'fixed' && $item->product->discount_value > 0) {
                    $price = $price - $item->product->discount_value;
                }
                return $price;
            })
            ->addColumn('discount_value', function ($item) {
                return $item->product ? $item->product->discount_value : 0;
            })
            ->addColumn('quantity', function ($item) {
                return $item->qty;
            })
            ->addColumn('subtotal', function ($item) {
                if (!$item->product || !$item->product_variation) {
                    return 0;
                }
                $price = $item->product_variation->price ?? 0;
                if ($item->product->discount_type === 'percent' && $item->product->discount_value > 0) {
                    $price = $price - ($price * $item->product->discount_value / 100);
                } elseif ($item->product->discount_type === 'fixed' && $item->product->discount_value > 0) {
                    $price = $price - $item->product->discount_value;
                }
                return number_format($price * $item->qty, 2);
            })
            ->addColumn('product', function ($item) {
                return [
                    'name' => $item->product ? $item->product->name : 'Product Not Found',
                    'stock_qty' => $item->product ? ($item->product->stock_qty ?? 0) : 0
                ];
            })
            ->make(true);
    }

    public function cartSummary(Request $request)
    {


        $cartItems = Cart::with(['product', 'product_variation'])
            ->where('user_id', auth()->id())
            ->whereHas('product', function ($query) {
                $query->where('created_by', session('current_vendor_id'));
            })
            ->get();

        $subtotal = 0;
        $discount = 0;
        $total = 0;
        $deliveryCharge = 0;
        $deliveryTime = '';

        foreach ($cartItems as $item) {
            $product = $item->product;
            if ($product && $item->product_variation) {
                $price = $item->product_variation->price ?? 0;

                if ($product->discount_type === 'percent' && $product->discount_value > 0) {
                    $itemDiscount = ($price * $product->discount_value / 100) * $item->qty;
                    $discount += $itemDiscount;
                    $price = $price - ($price * $product->discount_value / 100);
                } elseif ($product->discount_type === 'fixed' && $product->discount_value > 0) {
                    $itemDiscount = $product->discount_value * $item->qty;
                    $discount += $itemDiscount;
                    $price = $price - $product->discount_value;
                }

                $subtotal += $price * $item->qty;
            }
        }

        $deliveryCharge = 0;
        $deliveryTime = 0;

        // Get delivery charge based on selected zone
        $currentVendorId = session('current_vendor_id');
        if ($request->has('delivery_zone_id') && $request->delivery_zone_id && $currentVendorId) {
            $logisticZone = \Modules\Logistic\Models\LogisticZone::where('created_by', $currentVendorId)
                ->find($request->delivery_zone_id);
            if ($logisticZone) {
                $deliveryCharge = $logisticZone->standard_delivery_charge;
                $deliveryTime = $logisticZone->standard_delivery_time;
            }
        }
        // Note: If no delivery zone is selected, delivery charge remains 0

        $total = $subtotal;
        $totalWithDelivery = $total + $deliveryCharge;

        // Fetch active taxes and calculate total tax and breakdown
        $taxes = \Modules\Tax\Models\Tax::where('status', 1)->where('module_type', 'products')->where('created_by', session('current_vendor_id'))->get();


        $totalTax = 0;
        $taxBreakdown = [];
        foreach ($taxes as $tax) {
            if ($tax->type == 'fixed') {
                $taxAmount = $tax->value;
            } elseif ($tax->type == 'percent') {
                $taxAmount = $subtotal * $tax->value / 100;
            } else {
                $taxAmount = 0;
            }
            $totalTax += $taxAmount;
            $taxBreakdown[] = [
                'title' => $tax->title,
                'type' => $tax->type,
                'value' => $tax->value,
                'amount' => $taxAmount,
            ];
        }
        $totalWithoutDelivery = $total + $totalTax;

        $totalWithDeliveryAndTax = $total + $totalTax + $deliveryCharge;

        return response()->json([
            'status' => true,
            'cart_items_count' => $cartItems->count(),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'delivery_charge' => $deliveryCharge,
            'delivery_time' => $deliveryTime,
            'tax' => $totalTax,
            'tax_breakdown' => $taxBreakdown,
            'total_with_delivery' => $totalWithDeliveryAndTax,
            'total_without_delivery' => $totalWithoutDelivery
        ]);
    }

    // public function storeOrder(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'address_id' => 'required|exists:addresses,id',
    //             'delivery_zone_id' => 'required|exists:logistic_zones,id',
    //         ]);

    //         $user = auth()->user();
    //         $cartItems = Cart::with(['product'])
    //             ->where('user_id', $user->id)
    //             ->get();

    //         if ($cartItems->isEmpty()) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Your cart is empty'
    //             ]);
    //         }

    //         // Get selected logistic zone
    //         $logisticZone = \Modules\Logistic\Models\LogisticZone::find($request->delivery_zone_id);
    //         if (!$logisticZone) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Selected delivery zone not found'
    //             ]);
    //         }

    //         // Calculate totals
    //         $subtotal = 0;
    //         $discount = 0;
    //         $deliveryCharge = $logisticZone->standard_delivery_charge;

    //         foreach ($cartItems as $item) {
    //             $product = $item->product;
    //             $price = $product->max_price;

    //             // Calculate discount if any
    //             if ($product->discount_type === 'percent' && $product->discount_value > 0) {
    //                 $itemDiscount = ($price * $product->discount_value / 100) * $item->qty;
    //                 $discount += $itemDiscount;
    //                 $price = $price - ($price * $product->discount_value / 100);
    //             } elseif ($product->discount_type === 'fixed' && $product->discount_value > 0) {
    //                 $itemDiscount = $product->discount_value * $item->qty;
    //                 $discount += $itemDiscount;
    //                 $price = $price - $product->discount_value;
    //             }

    //             $subtotal += $price * $item->qty;
    //         }

    //         // Calculate taxes
    //         $serviceTax = $subtotal * 0.05;
    //         $vat = $subtotal * 0.05;
    //         $tax = $serviceTax + $vat;
    //         $total = $subtotal - $discount + $tax + $deliveryCharge;

    //         // --- Create Order Group and save shipping_address_id ---
    //         $orderGroup = \Modules\Product\Models\OrderGroup::create([
    //             'user_id' => $user->id,
    //             'shipping_address_id' => $request->address_id,
    //             'billing_address_id' => $request->address_id,
    //             'sub_total_amount' => $subtotal,
    //             'total_shipping_cost' => $deliveryCharge,
    //             'grand_total_amount' => $total,
    //         ]);

    //         // Create order and link to order group, saving all payment fields
    //         $order = \Modules\Product\Models\Order::create([
    //             'order_group_id' => $orderGroup->id,
    //             'user_id' => $user->id,
    //             'payment_status' => 'paid',
    //             'delivery_status' => 'pending',
    //             'subtotal' => $subtotal,
    //             'coupon_discount_amount' => $discount,
    //             'shipping_cost' => $deliveryCharge,
    //             'service_tax' => $serviceTax,
    //             'vat' => $vat,
    //             'tax' => $tax,
    //             'total' => $total,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);

    //         // --- AUTO-APPLIED: Create Booking and BookingProduct(s) ---
    //         $branchId = session('selected_branch_id');
    //         $booking = \Modules\Booking\Models\Booking::create([
    //             'note' => '',
    //             'status' => 'confirmed',
    //             'start_date_time' => now(),
    //             'user_id' => $user->id,
    //             'branch_id' => $branchId,
    //             'created_by' => $user->id,
    //             'updated_by' => $user->id,
    //         ]);

    //         foreach ($cartItems as $item) {
    //             $variation = \Modules\Product\Models\ProductVariation::where('product_id', $item->product_id)->first();
    //             \Modules\Booking\Models\BookingProduct::create([
    //                 'booking_id' => $booking->id,
    //                 'order_id' => $order->id,
    //                 'product_id' => $item->product_id,
    //                 'product_variation_id' => $variation ? $variation->id : null,
    //                 'employee_id' => 1,
    //                 'product_qty' => $item->qty,
    //                 'product_price' => $item->product->max_price,
    //                 'discounted_price' => $item->product->max_price,
    //                 'discount_value' => 0,
    //                 'discount_type' => $item->product->discount_type,
    //                 'variation_name' => $variation ? $variation->sku : '',

    //             ]);
    //         }
    //         // --- END AUTO-APPLIED ---

    //         // Clear cart
    //         Cart::where('user_id', $user->id)->delete();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Order placed successfully',
    //             'order_id' => $order->id,
    //             'redirect_url' => route('order.success')
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Failed to place order: ' . $e->getMessage()
    //         ]);
    //     }
    // }

    public function getCountries()
    {
        $countries = \Modules\World\Models\Country::where('status', 1)->get(['id', 'name']);
        return response()->json($countries);
    }

    public function getStates(Request $request)
    {
        $countryId = $request->country_id;
        $states = \Modules\World\Models\State::where('country_id', $countryId)
            ->where('status', 1)
            ->get(['id', 'name']);
        return response()->json($states);
    }

    public function getCities(Request $request)
    {
        $stateId = $request->state_id;
        $cities = \Modules\World\Models\City::where('state_id', $stateId)
            ->where('status', 1)
            ->get(['id', 'name']);
        return response()->json($cities);
    }

    public function storeAddress(Request $request)
    {

        try {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'address' => 'required|string',
                'contact_number' => 'required',
                'country' => 'required|exists:countries,id',
                'state' => 'required|exists:states,id',
                'city' => 'required|exists:cities,id',
                'pin_code' => 'required|string|max:10',
                'address_id' => 'nullable|exists:addresses,id',
            ]);

            $user = User::find(Auth::id());


            $data = [
                'first_name'     => $request->first_name,
                'last_name'      => $request->last_name,
                'address_line_1' => $request->address,
                'email'          => $request->email,
                'contact_number' => $request->contact_number,
                'country'        => $request->country,
                'state'          => $request->state,
                'city'           => $request->city,
                'postal_code'    => $request->pin_code,

                'is_primary'     => $request->has('set_as_primary') ? 1 : 0,
            ];


            if (! $user) {
                return response()->json(['status' => false, 'message' => 'User not found'], 404);
            }

            // If set_as_primary is set, update all other addresses to is_primary = 0
            if ($request->has('set_as_primary') && $data['is_primary'] == 1) {
                $user->address()->update(['is_primary' => 0]);
            }

            $address = $request->filled('address_id')
                ? $user->address()->where('id', $request->address_id)->first()
                : null;

            if ($address) {
                $address->update($data);
            } else {
                $newAddress = new Address($data);
                if ($request->has('set_as_primary') && $data['is_primary'] == 1) {
                    $newAddress->is_primary = 1;
                }

                $user->address()->save($newAddress);
            }

            return response()->json([
                'status' => true,
                'message' => 'Address saved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save address: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getAddress($id)
    {
        $address = \App\Models\Address::where('id', $id)
            ->where('addressable_id', auth()->id())
            ->where('addressable_type', 'App\Models\User')
            ->first();

        if (!$address) {
            return response()->json([
                'status' => false,
                'message' => 'Address not found'
            ]);
        }

        return response()->json([
            'status' => true,
            'address' => $address
        ]);
    }

    public function getDeliveryZones(Request $request)
    {
        $addressId = $request->address_id;
        $zones = collect();
        $debug = [];

        if ($addressId) {
            $address = Address::find($addressId);
            $currentVendorId = session('current_vendor_id');

            $debug['address_found'] = $address ? true : false;
            $debug['current_vendor_id'] = $currentVendorId;
            $debug['auth_id'] = auth()->id();

            if ($address && $address->city && $currentVendorId) {
                $cityId = $address->city;

                $debug['address_details'] = [
                    'country' => $address->country,
                    'state' => $address->state,
                    'city' => $address->city
                ];

                // Only show logistic zones created by the current vendor
                $zones = LogisticZone::where('created_by', $currentVendorId)
                    ->whereHas('cities', function ($query) use ($cityId) {
                        $query->where('city_id', $cityId);
                    })
                    ->with('logistic')
                    ->get();

                $debug['zones_found'] = $zones->count();
                $debug['query_details'] = [
                    'vendor_id' => $currentVendorId,
                    'city_id' => $cityId
                ];
            } else {
                $debug['missing_data'] = [
                    'address' => !$address,
                    'city' => !($address && $address->city),
                    'vendor_id' => !$currentVendorId
                ];
            }
        } else {
            $debug['no_address_id'] = true;
        }

        return response()->json([
            'status' => true,
            'zones' => $zones,
            'debug' => $debug // Remove this in production
        ]);
    }

    public function orderSuccess()
    {
        return view('vendorwebsite::order_success');
    }

    // Save selected checkout address to session for all payment methods
    public function setCheckoutAddress(Request $request)
    {
        $request->validate(['address_id' => 'required|exists:addresses,id']);
        session(['checkout_address_id' => $request->address_id]);
        return response()->json(['success' => true]);
    }
    public function downloadInvoice($order_id)
    {
        $order = Order::with(['orderItems', 'orderGroup'])->findOrFail($order_id);
        $address = null;

        if ($order->orderGroup && $order->orderGroup->shipping_address_id) {
            $address = Address::find($order->orderGroup->shipping_address_id);
        }

        if (!$address && $order->user && $order->user->addresses) {
            $address = $order->user->addresses->first();
        }

        $logo = null;
        $logoSetting = getVendorSetting('logo');

        if ($logoSetting) {
            // If logo is set in settings, use it
            $logoPath = parse_url($logoSetting, PHP_URL_PATH) ?? $logoSetting;
            // ...existing code...
            $logoPath = ltrim($logoPath, '/');
            // Remove the first subfolder dynamically (not static like 'spaos')
            $logoPath = preg_replace('/^[^\/]+\//', '', $logoPath);
            // Build absolute public path
            $logoPath = public_path($logoPath);

            if (file_exists($logoPath) && is_readable($logoPath)) {
                try {
                    // Get MIME type of the image
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $logoPath);
                    finfo_close($finfo);

                    $extensions = [
                        'image/jpeg' => 'jpeg',
                        'image/png'  => 'png',
                        'image/gif'  => 'gif',
                        'image/svg+xml' => 'svg+xml',
                        'image/webp' => 'webp'
                    ];

                    $extension = $extensions[$mimeType] ?? 'jpeg';

                    $logoData = file_get_contents($logoPath);
                    $logo = 'data:image/' . $extension . ';base64,' . base64_encode($logoData);
                } catch (\Exception $e) {
                    \Log::error('Logo processing error: ' . $e->getMessage());
                }
            }
        }

        // If no logo from settings or failed to load, try default logo
        if (!$logo) {
            $defaultLogoPath = public_path('img/logo/logo.png');
            if (file_exists($defaultLogoPath)) {
                try {
                    $logoData = file_get_contents($defaultLogoPath);
                    $logo = 'data:image/png;base64,' . base64_encode($logoData);
                } catch (\Exception $e) {
                    \Log::error('Default logo processing error: ' . $e->getMessage());
                }
            }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoice', [
            'order' => $order,
            'address' => $address,
            'logo' => $logo,
        ]);

        $pdf->setPaper('a4')
            ->setOptions([
                'isRemoteEnabled' => false, // Disable remote loading for better performance
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);


        // $pdf = PDF::loadView('invoice', compact('order', 'address'))
        //     ->setPaper('a4')
        //     ->setOptions([
        //         'defaultFont' => 'DejaVu Sans', // for ₹ symbol and unicode
        //         'isHtml5ParserEnabled' => true,
        //         'isRemoteEnabled' => true, // allow images/fonts
        //     ]);

        return response()->streamDownload(
            function () use ($pdf) {
                echo $pdf->output();
            },
            "invoice_{$order->id}.pdf",
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename=\"invoice_{$order->id}.pdf\"',
            ]
        );
    }

    /**
     * Clean up invalid cart items (products that no longer exist)
     */
    private function cleanupInvalidCartItems($cartItems)
    {
        $invalidItems = $cartItems->filter(function ($item) {
            return !$item->product || !$item->product_variation;
        });

        if ($invalidItems->isNotEmpty()) {
            $invalidItemIds = $invalidItems->pluck('id');
            Cart::whereIn('id', $invalidItemIds)->delete();
            
            // Log the cleanup for debugging
            \Log::info('Cleaned up invalid cart items', [
                'user_id' => auth()->id(),
                'invalid_items_count' => $invalidItems->count(),
                'invalid_item_ids' => $invalidItemIds->toArray()
            ]);
        }
    }
}
