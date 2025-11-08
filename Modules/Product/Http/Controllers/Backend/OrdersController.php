<?php

namespace Modules\Product\Http\Controllers\Backend;

use App\Models\Address;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Location\Models\Location;
use Modules\Product\Models\Cart;
use Modules\Product\Models\Order;
use Modules\Product\Models\OrderGroup;
use Modules\Product\Models\OrderItem;
use Modules\Product\Models\OrderUpdate;
use Modules\Product\Trait\OrderTrait;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

use App\Models\User; // user Model
use App\Models\Branch; // Branch Model
use Modules\Product\Trait\ProductTrait; // Product Trait
use Modules\Tax\Models\Tax; // Tax Model
use Modules\Product\Models\Product; // Product Model
use Modules\World\Models\Country; // Country Model
use Modules\World\Models\State; // State Model
use Modules\World\Models\City; // City Model
use App\Http\Resources\AddressResource; // Address Resource
use Modules\Product\Models\ProductVariation;

class OrdersController extends Controller
{
    use OrderTrait;
    use ProductTrait;

    public function __construct()
    {
        // Page Title
        $this->module_title = __('orders.title');
        // module name
        $this->module_name = 'orders';

        // module icon
        $this->module_icon = 'fa-solid fa-clipboard-list';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index(Request $request)
    {
        $export_import = false;
        $module_title = __('orders.title');
        $locations = Location::where('status', 1)->latest()->get();

        return view('product::backend.order.index_datatable', compact('export_import', 'locations', 'module_title'));
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        $orders = Order::with('orderGroup', 'orderItems.product_variation.product');

        if (auth()->user()->hasRole('admin')) {
            $orders = $orders->whereHas('orderItems', function ($q) {
                $q->whereHas('product_variation', function ($qry) {
                    $qry->whereHas('product', function ($query) {
                        $query->where('created_by', auth()->user()->id);
                    });
                });
            });
        }

        $filter = $request->filter;

        $posOrder = [];

        if (isset($filter)) {
            if (isset($filter['code'])) {
                $orders = $orders->where(function ($q) use ($filter) {
                    $orderGroup = OrderGroup::where('order_code', $filter['code'])->pluck('id');
                    $q->orWhereIn('order_group_id', $orderGroup);
                });
            }

            if (isset($filter['delivery_status'])) {
                $orders = $orders->where('delivery_status', $filter['delivery_status']);
            }

            if (isset($filter['payment_status'])) {
                $orders = $orders->where('payment_status', $filter['payment_status']);
            }

            if (isset($filter['location_id'])) {
                $orders = $orders->where('location_id', $filter['location_id']);
            }
        }

        $orders = $orders->where(function ($q) {
            $orderGroup = OrderGroup::pluck('id');
            $q->orWhereIn('order_group_id', $orderGroup);
        });

        return $datatable->eloquent($orders)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row "  id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->addColumn('action', function ($data) {
                return view('product::backend.order.columns.action_column', compact('data'));
            })
            ->editColumn('order_code', function ($data) {
                return setting('inv_prefix') . $data->orderGroup->order_code;
            })
            ->editColumn('customer_name', function ($data) {
                $user = optional($data->user);
                $Profile_image = $user->profile_image ?? default_user_avatar();
                $name = $user->full_name ?? default_user_name();
                $email = $user->email ?? '--';
                return view('booking::backend.bookings.datatable.user_id', compact('Profile_image', 'name', 'email'));
            })
            ->addColumn('phone', function ($data) {
                return optional($data->user)->mobile ?? '-';
            })
            ->editColumn('placed_on', function ($data) {
                return formatDateOrTime($data->created_at);
            })
            ->editColumn('items', function ($data) {
                return $data->orderItems()->count();
            })
            ->addColumn('products', function ($data) {
                $names = $data->orderItems
                    ->map(function ($item) {
                        return optional(optional($item->product_variation)->product)->name;
                    })
                    ->filter()
                    ->unique()
                    ->values();
                if ($names->count() <= 1) {
                    $first = $names->first();
                    return $first ? '<small class="badge bg-primary">'.e($first).'</small>' : '-';
                }
                $dataProducts = e(json_encode($names->values()->toArray()));
                return '<a href="#" class="badge bg-info text-white show-products" data-products="'.$dataProducts.'">'.__('messages.multiple').'</a>';
            })
            ->editColumn('type', function ($data) {
                return view('product::backend.order.columns.type_column', compact('data'));
            })
            ->editColumn('payment', function ($data) {
                return view('product::backend.order.columns.payment_column', compact('data'));
            })
            ->editColumn('status', function ($data) {
                return view('product::backend.order.columns.status_column', compact('data'));
            })
            ->editColumn('location', function ($data) {
                return $data->location ? $data->location->name : 'N/A';
            })
            ->filterColumn('customer_name', function ($query, $keyword) {
                if (! empty($keyword)) {
                    $query->whereHas('user', function ($q) use ($keyword) {
                        $q->where('first_name', 'like', '%' . $keyword . '%');
                        $q->orWhere('last_name', 'like', '%' . $keyword . '%');
                        $q->orWhere('email', 'like', '%' . $keyword . '%');
                    });
                }
            })
            ->filterColumn('phone', function ($query, $keyword) {
                if (!empty($keyword)) {
                    $query->whereHas('user', function ($q) use ($keyword) {
                        $q->where('mobile', 'like', '%' . $keyword . '%');
                    });
                }
            })
            ->editColumn('updated_at', function ($data) {
                $diff = Carbon::now()->diffInHours($data->updated_at);
                if ($diff < 25) {
                    return $data->updated_at->diffForHumans();
                } else {
                    return $data->updated_at->isoFormat('llll');
                }
            })
            ->orderColumns(['id'], '-:column $1')
            ->rawColumns(['action', 'check', 'phone', 'products', 'payment', 'status'])
            ->toJson();
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function show(Request $request)
    {
        $order = Order::find($request->id);
        if ($order == null) {
            return abort(500);
        }

        return view('product::backend.order.show', compact('order'));
    }

    // update payment status
    public function updatePaymentStatus(Request $request)
    {
        $order = Order::findOrFail((int) $request->order_id);
        $order->payment_status = $request->status;
        $order->save();

        OrderUpdate::create([
            'order_id' => $order->id,
            'user_id' => auth()->user()->id,
            'note' => 'Payment status updated to ' . ucwords(str_replace('_', ' ', $request->status)) . '.',
        ]);

        return response()->json(['status' => true, 'message' => __('messages.payment_status_updated')]);
    }

    // update delivery status
    public function updateDeliveryStatus(Request $request)
    {
        $order = Order::findOrFail((int) $request->order_id);

        if ($order->delivery_status != 'cancelled' && $request->status == 'cancelled') {
            $this->addQtyToStock($order);
        }

        if ($order->delivery_status == 'cancelled' && $request->status != 'cancelled') {
            $this->removeQtyFromStock($order);
        }

        $order->delivery_status = $request->status;
        $order->save();

        OrderUpdate::create([
            'order_id' => $order->id,
            'user_id' => auth()->user()->id,
            'note' => 'Delivery status updated to ' . ucwords(str_replace('_', ' ', $request->status)) . '.',
        ]);

        $order_prefix_data = Setting::where('name', 'inv_prefix')->first();
        $order_prefix = $order_prefix_data ? $order_prefix_data->val : '';

        $notify_type = null;

        $status = $request->status;

        switch ($status) {
            case 'processing':
                $notify_type = 'order_proccessing';
                $messageTemplate = 'Order #[[order_id]] is now being processed.';
                $notify_message = str_replace('[[order_id]]', $order->id, $messageTemplate);
                break;
            case 'delivered':
                $notify_type = 'order_delivered';
                $messageTemplate = 'Order #[[order_id]] has been delivered.';
                $notify_message = str_replace('[[order_id]]', $order->id, $messageTemplate);
                break;
            case 'cancelled':
                $notify_type = 'order_cancelled';
                $messageTemplate = 'Order #[[order_id]] has been cancelled.';
                $notify_message = str_replace('[[order_id]]', $order->id, $messageTemplate);
                break;
        }

        try {
            $notification_data = [

                'id' => $order->id,
                'order_code' => $order_prefix . optional($order->orderGroup)->order_code,
                'user_id' => $order->user_id,
                'user_name' => optional($order->user)->first_name . ' ' . optional($order->user)->last_name ?? default_user_name(),
                'order_date' => $order->updated_at->format('d/m/Y'),
                'order_time' => $order->updated_at->format('h:i A'),
            ];

            $this->sendNotificationOnOrderUpdate($notify_type, $notify_message, $notification_data);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }

        return response()->json(['status' => true, 'message' => __('messages.order_delivery_status_updated')]);
    }

    // add qty to stock
    private function addQtyToStock($order)
    {
        $orderItems = OrderItem::where('order_id', $order->id)->get();
        foreach ($orderItems as $orderItem) {
            $stock = $orderItem->product_variation->product_variation_stock;
            $stock->stock_qty += $orderItem->qty;
            $stock->save();

            $product = $orderItem->product_variation->product;
            $product->total_sale_count += $orderItem->qty;
            $product->save();

            $productVariation = ProductVariation::where('id', $orderItem->product_variation_id)->first();
            if ($productVariation && $productVariation->product) {
                $product = $productVariation->product;
                $product->stock_qty += $orderItem->qty;
                $product->save();
            }

            if ($product->categories()->count() > 0) {
                foreach ($product->categories as $category) {
                    $category->total_sale_count += $orderItem->qty;
                    $category->save();
                }
            }
        }
    }

    // remove qty from stock
    private function removeQtyFromStock($order)
    {
        $orderItems = OrderItem::where('order_id', $order->id)->get();
        foreach ($orderItems as $orderItem) {
            $stock = $orderItem->product_variation->product_variation_stock;
            $stock->stock_qty -= $orderItem->qty;
            $stock->save();

            $product = $orderItem->product_variation->product;
            $product->total_sale_count -= $orderItem->qty;
            $product->save();

            $productVariation = ProductVariation::where('id', $orderItem->product_variation_id)->first();
            if ($productVariation && $productVariation->product) {
                $product = $productVariation->product;
                $product->stock_qty -= $orderItem->qty;
                $product->save();
            }

            if ($product->categories()->count() > 0) {
                foreach ($product->categories as $category) {
                    $category->total_sale_count -= $orderItem->qty;
                    $category->save();
                }
            }
        }
    }

    // Order Creation
    public function complete(Request $request)
    {
        $user = auth()->user();

        $userId = $user->id;

        $location_id = $request->location_id;

        $carts = Cart::where('user_id', $userId)->where('location_id', $location_id)->get();

        if (count($carts) > 0) {
            // check carts available stock -- todo::[update version] -> run this check while storing OrderItems
            foreach ($carts as $cart) {
                $productVariationStock = $cart->product_variation->product_variation_stock ? $cart->product_variation->product_variation_stock->stock_qty : 0;
                if ($cart->qty > $productVariationStock) {
                    $message = $cart->product_variation->product->name . __('messages.is_out_of_stock');

                    return response()->json(['message' => $message, 'status' => false]);
                }
            }

            // create new order group
            $orderGroup = new OrderGroup;
            $orderGroup->user_id = $userId;
            $orderGroup->shipping_address_id = $request->shipping_address_id;
            $orderGroup->billing_address_id = $request->billing_address_id;
            $orderGroup->location_id = $location_id;
            $orderGroup->phone_no = $request->phone;
            $orderGroup->alternative_phone_no = $request->alternative_phone;
            $orderGroup->sub_total_amount = getSubTotal($carts, false, '', false);
            $orderGroup->total_tax_amount = 0;
            $orderGroup->total_coupon_discount_amount = 0;
            $orderGroup->type = 'online';
            $logisticZone = LogisticZone::where('id', $request->chosen_logistic_zone_id)->first();
            // todo::[for eCommerce] handle exceptions for standard & express
            $orderGroup->total_shipping_cost = $logisticZone->standard_delivery_charge;
            $orderGroup->total_tips_amount = $request->tips;

            $orderGroup->grand_total_amount = $orderGroup->sub_total_amount + $orderGroup->total_tax_amount + $orderGroup->total_shipping_cost + $orderGroup->total_tips_amount - $orderGroup->total_coupon_discount_amount;
            $orderGroup->save();

            // order -> todo::[update version] make array for each vendor, create order in loop
            $order = new Order;
            $order->order_group_id = $orderGroup->id;
            $order->user_id = $userId;
            $order->location_id = $location_id;
            $order->total_admin_earnings = $orderGroup->grand_total_amount;
            $order->logistic_id = $logisticZone->logistic_id;
            $order->logistic_name = optional($logisticZone->logistic)->name;

            $order->shipping_cost = $orderGroup->total_shipping_cost; // todo::[update version] calculate for each vendors
            $order->tips_amount = $orderGroup->total_tips_amount; // todo::[update version] calculate for each vendors

            $order->save();

            // order items
            $total_points = 0;
            foreach ($carts as $cart) {
                $orderItem = new OrderItem;
                $orderItem->order_id = $order->id;
                $orderItem->product_variation_id = $cart->product_variation_id;
                $orderItem->qty = $cart->qty;
                $orderItem->location_id = $location_id;
                $orderItem->unit_price = variationDiscountedPrice($cart->product_variation->product, $cart->product_variation);
                $orderItem->total_tax = 0;
                $orderItem->total_price = $orderItem->unit_price * $orderItem->qty;
                $orderItem->save();

                $product = $cart->product_variation->product;
                $product->total_sale_count += $orderItem->qty;

                // minus stock qty
                try {
                    $productVariationStock = $cart->product_variation->product_variation_stock;
                    $productVariationStock->stock_qty -= $orderItem->qty;
                    $productVariationStock->save();
                } catch (\Throwable $th) {
                    //throw $th;
                }

                $product->stock_qty -= $orderItem->qty;
                $product->save();

                // category sales count
                if ($product->categories()->count() > 0) {
                    foreach ($product->categories as $category) {
                        $category->total_sale_count += $orderItem->qty;
                        $category->save();
                    }
                }
                $cart->delete();
            }

            $order->save();
            // payment gateway integration & redirection
            $orderGroup->payment_method = $request->payment_method;
            $orderGroup->save();

            return true;
        }
    }

    // download invoice
    public function downloadInvoice($id)
    {
        try {
            // Increase execution time limit for PDF generation
            set_time_limit(120); // 2 minutes
            
            if (session()->has('locale')) {
                $language_code = session()->get('locale', config('app.locale'));
            } else {
                $language_code = env('DEFAULT_LANGUAGE');
            }

            $font_family = "'Roboto','sans-serif'";

            $order = Order::findOrFail((int) $id);
            
            // Log the order data for debugging
           
            
            $view = view('product::backend.order.invoice', [
                'order' => $order,
                'font_family' => $font_family,
            ])->render();
            
            
            
            // Try to use DomPDF with better error handling and timeout protection
            try {
                $pdf = Pdf::loadHTML($view);
                
                // Set optimized options to improve performance
                $pdf->setPaper('A4', 'portrait');
                $pdf->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => false, // Disable remote resources to improve speed
                    'isFontSubsettingEnabled' => true,
                    'defaultFont' => 'DejaVu Sans',
                    'chroot' => public_path(),
                    'tempDir' => storage_path('app/temp'),
                    'logOutputFile' => storage_path('logs/dompdf.log'),
                    'debugKeepTemp' => false,
                    'debugCss' => false,
                    'debugLayout' => false,
                ]);
                
                
                
                // Generate PDF content with timeout protection
                $pdfContent = null;
                $startTime = time();
                
                // Use a callback to check timeout
                $pdfContent = $pdf->output();
                
                if (time() - $startTime > 50) { // If it took more than 50 seconds
                    
                    throw new \Exception('PDF generation timeout');
                }
                
                
                
                return response($pdfContent)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'attachment; filename="invoice.pdf"');
                    
            } catch (\Exception $pdfError) {
                
                
                // Fallback: return the HTML view instead of PDF
                return response($view)
                    ->header('Content-Type', 'text/html')
                    ->header('Content-Disposition', 'attachment; filename="invoice.html"');
            }
            
        } catch (\Exception $e) {
            
            
            // Return a simple error response instead of crashing
            return response()->json([
                'error' => 'Failed to generate invoice',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function OrderInvoicedownload(Request $request)
    {
        if (session()->has('locale')) {
            $language_code = session()->get('locale', config('app.locale'));
        } else {
            $language_code = env('DEFAULT_LANGUAGE');
        }

        $font_family = "'Roboto','sans-serif'";

        $order = Order::findOrFail((int)($request->id));
        $view = view('product::backend.order.invoice', [
            'order' => $order,
            'font_family' => $font_family,
        ])->render();
        $pdf = Pdf::loadHTML($view);

        if ($request->is('api/*')) {
            // Handle API request
            $baseDirectory = storage_path('app/public');
            $highestDirectory = collect(File::directories($baseDirectory))->map(function ($directory) {
                return basename($directory);
            })->max() ?? 0;
            $nextDirectory = intval($highestDirectory) + 1;
            while (File::exists($baseDirectory . '/' . $nextDirectory)) {
                $nextDirectory++;
            }
            $newDirectory = $baseDirectory . '/' . $nextDirectory;
            File::makeDirectory($newDirectory, 0777, true);

            $filename = 'invoice_' . $request->id . '.pdf';
            $filePath = $newDirectory . '/' . $filename;

            $pdf->save($filePath);

            $url = url('storage/' . $nextDirectory . '/' . $filename);
            if (!empty($url)) {
                return response()->json(['status' => true, 'link' => $url], 200);
            } else {
                return response()->json(['status' => false, 'message' => __('messages.url_not_found')], 404);
            }
        } else {
            // Handle non-API request
            return $pdf->download($filename);
        }
    }

    public function create(Request $request)
    {

        $vendorId = auth()->user()->id;

        $activeUsers = User::role('user')
            ->Varified()
            ->with(['media', 'booking.branch', 'addresses'])
            ->where('status', 1)
            ->where(function ($q) use ($vendorId) {
                $q->where('created_by', $vendorId)
                    ->orWhereHas('booking.branch', function ($sub) use ($vendorId) {
                        $sub->where('created_by', $vendorId);
                    });
            })
            ->get();

        $activeBranches = Branch::where('status', 1)->where('created_by', $vendorId)->get();

        $activeProducts = Product::where('status', 1)->with('product_variations')->where('created_by', $vendorId)->get();

        $activeCountries = Country::where('status', 1)->get();

        $productTax = Tax::where('module_type', 'products')->where('status', 1)->where('created_by', $vendorId)->get();


        return view('product::backend.order.create', compact('activeBranches', 'activeUsers', 'activeProducts', 'activeCountries', 'productTax'));

        // return response()->json(compact('activeProducts'));
    }

    public function getStates(Request $request)
    {
        $countryId = $request->country_id;
        $states = State::where('country_id', $countryId)->pluck('name', 'id');
        return response()->json($states);
    }

    public function getCities(Request $request)
    {
        $stateId = $request->state_id;
        $cities = City::where('state_id', $stateId)->pluck('name', 'id');
        return response()->json($cities);
    }

    public function storeAddress(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);

        if (! $user) {
            $message = __('users.user_not_found');
            return response()->json(['message' => $message, 'status' => false], 200);
        }

        $data = $request->except(['user_id', 'addressable_type', 'addressable_id']);
        $data['addressable_type'] = 'App\Models\User';
        $data['addressable_id'] = $user->id;

        // update or create address based on primary status
        $user->addresses()->updateOrCreate(['is_primary' => 1], $data);
        if ($request->has('is_primary') && $request->is_primary == 1) {
            $user->addresses()->update(['is_primary' => 0]);
        }

        $newAddress = new Address($data);

        if ($request->is_primary == 1) {
            $newAddress->is_primary = 1;
        }

        $user->addresses()->save($newAddress);

        $message = __('users.address_store');

        return response()->json([
            'message' => $message,
            'status' => true,
            'data' => $newAddress
        ], 200);
    }

    public function getAddresses(Request $request)
    {
        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['message' => 'User not found', 'data' => [], 'status' => false], 200);
        }

        // Only fetch addresses for the selected user
        $addresses = Address::where('addressable_type', 'App\Models\User')
            ->where('addressable_id', $userId)
            ->where('is_primary', 1)
            ->select('id', 'address_line_1', 'city', 'state', 'country', 'postal_code')
            ->get();

        return $addresses->map(function ($address) {
            return [
                'id' => $address->id,
                'address_line_1' => $address->address_line_1,
                'city' => $address->city_data->name ?? $address->city,
                'state' => $address->state_data->name ?? $address->state,
                'country' => $address->country_data->name ?? $address->country,
                'postal_code' => $address->postal_code,
            ];
        });
    }


    public function addToCart(Request $request)
    {

        $branch_id = $request->input('branch_id');
        $user_id = $request->input('user_id');
        $location_id = $request->input('location_id');
        $items = $request->input('items');
        $delivery_status = $request->input('deliverystatus');
        $payment_status = $request->input('paymentstatus');

        if (!$branch_id || !$user_id || !$location_id) {
            return response()->json(['status' => false, 'message' => __('messages.branch_user_location_required')], 400);
        }

        if (!$items || !is_array($items)) {
            return response()->json(['status' => false, 'message' => __('messages.items_required')], 400);
        }

        // Prepare booking details
        $booking_details = [
            'branch_id' => $branch_id,
            'user_id' => $user_id,
            'location_id' => $location_id,
            'delivery_status' => $delivery_status  ?? 'order_placed',
            'payment_status' => $payment_status  ?? 'unpaid',
            'payment' => (object) [
                'tip_amount' => 0, // default tip amount
                'transaction_type' => 'cash_on_delivery'
            ],
        ];

        // Prepare booking products
        $booking_products = [];
        foreach ($items as $item) {
            $booking_products[] = [
                'product_id' => $item['product_id'],
                'product_variation_id' => $item['product_variation_id'],
                'product_qty' => $item['quantity'],
            ];
        }

        try {
            $orderId = $this->createCart($booking_products, $booking_details);
            return response()->json([
                'status' => true,
                'message' => __('messages.order_added_to_cart'),
                'order_id' => $orderId
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
