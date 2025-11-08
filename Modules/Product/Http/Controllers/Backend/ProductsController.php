<?php

namespace Modules\Product\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Location\Models\Location;
use Modules\Product\Http\Requests\ProductRequest;
use Modules\Product\Models\Brands;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductCategory;
use Modules\Product\Models\ProductGallery;
use Modules\Product\Models\ProductVariation;
use Modules\Product\Models\ProductVariationCombination;
use Modules\Product\Models\ProductVariationStock;
use Modules\Product\Models\Variations;
use Modules\Product\Models\VariationValue;
use Modules\Tag\Models\Tag;
use Yajra\DataTables\DataTables;

class ProductsController extends Controller
{

    public function __construct()
    {
        // Page Title
        $this->module_title = __('product.title');
        // module name
        $this->module_name = 'products';

        // module icon
        $this->module_icon = 'fa-solid fa-clipboard-list';

        $this->middleware(['permission:view_product_variations'])->only('index');
        $this->middleware(['permission:edit_product_variations'])->only('edit', 'update');
        $this->middleware(['permission:add_product_variations'])->only('store');
        $this->middleware(['permission:delete_product_variations'])->only('destroy');

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $module_title = __('product.title');
        $module_action = __('messages.list');

        $export_import = true;
        $export_columns = [
            [
                'value' => 'name',
                'text' => __('messages.name'),
            ],
        ];

    $brands = Brands::where('status', 1)
        ->where('created_by', auth()->id())
        ->get();

    $categories = ProductCategory::where('status', 1)->get();

    $units = \Modules\Product\Models\Unit::where('status', 1)
        ->where('created_by', auth()->id())
        ->get();
    $tagsList = \Modules\Tag\Models\Tag::where('created_by', auth()->id())->get();

    // Provide variations with values for the form offcanvas
    $variationsCollection = Variations::with(['values' => function ($q) { $q->where('status', 1); }])
        ->where('status', 1)
        ->where('created_by', auth()->id())
        ->get();
    $variations = $variationsCollection->map(function ($row) {
        return [
            'id' => $row->id,
            'name' => $row->name,
            'values' => $row->values->map(function ($v) {
                return [
                    'id' => $v->id,
                    'name' => $v->name,
                ];
            })->values(),
        ];
    })->values();

    $export_url = route('backend.products.export');

    return view('product::backend.products.index_datatable', compact('module_action', 'filter', 'export_import', 'export_columns', 'export_url', 'brands', 'categories', 'module_title', 'tagsList', 'units', 'variations'));
    }

    /**
     * Select Options for Select 2 Request/ Response.
     *
     * @return Response
     */
    public function index_list(Request $request)
    {
        $query_data = Product::IsPublished();

        $categoryParam = $request->category_id;
        \Log::debug('Product index_list request', [
            'category_id' => $categoryParam,
            'user_id' => optional(auth()->user())->id,
        ]);
        if (!empty($categoryParam) && $categoryParam !== 'undefined') {
            $categoryIds = is_array($categoryParam) ? $categoryParam : explode(',', $categoryParam);
            // Remove empty values to avoid filtering with empty strings
            $categoryIds = array_values(array_filter($categoryIds, function ($v) {
                return $v !== '' && $v !== null;
            }));

            if (!empty($categoryIds)) {
                $query_data = $query_data->whereHas('product_category', function ($q) use ($categoryIds) {
                    $q->whereIn('category_id', $categoryIds);
                });
            }
        }

        $query_data = $query_data->get();

        $data = [];

        foreach ($query_data as $row) {
            $data[] = [
                'id' => $row->id,
                'name' => $row->name,
            ];
        }

        \Log::debug('Product index_list result', [
            'count' => count($data),
            'ids' => collect($data)->pluck('id'),
        ]);
        return response()->json($data);
    }

    public function index_list_with_varient()
    {
        $products = Product::where('status', 1)->where('created_by', auth()->user()->id)->get();

        $location_id = 1;

        $data = [];

        foreach ($products as $key => $product) {
            if ($product->has_variation) {
                foreach ($product->product_variations as $key => $product_variation) {

                    $code_array = array_filter(explode('/', $product_variation->variation_key));
                    $lstKey = array_key_last($code_array);
                    $name = '';
                    foreach ($code_array as $key2 => $comb) {
                        $comb = explode(':', $comb);
                        $variation = Variations::find($comb[0]);
                        $variationVal = VariationValue::find($comb[1]);
                        if ($variation && $variationVal) {
                            $option_name = $variation->name;
                            $choice_name = $variationVal->name;

                            $name .= $choice_name;

                            if ($lstKey != $key2) {
                                $name .= '-';
                            }
                        }
                    }

                    if ($name !== '') {

                        $stock = $product_variation->product_variation_stock()
                            ->where('location_id', $location_id)
                            ->first();
                        if ($stock && $stock->stock_qty > 0) {
                            $data[] = [
                                'id' => $product_variation->id,
                                'text' => $product->name . ' - ' . $name,
                                'extra_data' => json_encode(['variation_id' => $product_variation->id, 'product_id' => $product->id, 'discounted_price' => getDiscountedProductPrice($product_variation->price, $product->id), 'discount_value' => $product->discount_value, 'discount_type' => $product->discount_type, 'qty' => $stock->stock_qty, 'price' => $product_variation->price, 'variation_name' => $name]),
                            ];
                        }
                    }
                }
            } else {
                $first_variation = $product->product_variations->first();
                $first_variation_stock = $first_variation
                    ->product_variation_stock()
                    ->where('location_id', $location_id)
                    ->first();

                $price = $first_variation->price;
                $stock_qty = 0;
                if ($first_variation_stock) {
                    $stock_qty = $first_variation_stock->stock_qty;
                }
                $sku = $first_variation->sku;
                if ($stock_qty > 0) {
                    $data[] = [
                        'id' => $first_variation->id,
                        'text' => $product->name,
                        'extra_data' => json_encode(['variation_id' => $first_variation->id, 'product_id' => $product->id, 'qty' => $stock_qty, 'price' => $first_variation->price, 'discounted_price' => getDiscountedProductPrice($first_variation->price, $product->id), 'discount_value' => $product->discount_value, 'discount_type' => $product->discount_type, 'variation_name' => null]),
                    ];
                }
            }
        }

        return $data;
    }

    public function index_data(Request $request, Datatables $datatable)
    {
        $query = Product::with(['brand', 'categories']);

        if (auth()->user()->hasRole('admin')) {
            $query = $query->where('created_by', auth()->id());
        }

        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }

        if (isset($filter)) {
            if (isset($filter['brand_id'])) {
                $query->where('brand_id', $filter['brand_id']);
            }
        }

        if (isset($filter) && isset($filter['category_id'])) {
            $query->whereHas('categories', function ($q) use ($filter) {
                $q->where('category_id', $filter['category_id']);
            });
        }

        return $datatable->eloquent($query)
            ->addColumn('check', function ($data) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $data->id . '"  name="datatable_ids[]" value="' . $data->id . '" onclick="dataTableRowCheck(' . $data->id . ')">';
            })
            ->addColumn('action', function ($data) {
                return view('product::backend.products.action_column', compact('data'));
            })

            ->editColumn('name', function ($data) {
                return view('backend.branch.branch_id', compact('data'));
            })
            ->editColumn('is_featured', function ($data) {
                $checked = '';
                if ($data->is_featured) {
                    $checked = 'checked="checked"';
                }

                return '
                            <div class="form-check form-switch ">
                                <input type="checkbox" data-url="' . route('backend.products.update_is_featured', $data->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $data->id . '"  name="is_featured" value="' . $data->id . '" ' . $checked . '>
                            </div>
                          ';
            })
            ->editColumn('status', function ($data) {
                $checked = '';
                if ($data->status) {
                    $checked = 'checked="checked"';
                }

                return '
                            <div class="form-check form-switch  ">
                                <input type="checkbox" data-url="' . route('backend.products.update_status', $data->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $data->id . '"  name="status" value="' . $data->id . '" ' . $checked . '>
                            </div>
                          ';
            })
            ->editColumn('categories', function ($data) {
                $categories = '<div class="d-flex flex-wrap gap-2">';

                if (count($data->categories) > 0) {
                    foreach ($data->categories as $key => $value) {
                        $categories .= '<span class="badge rounded-pill bg-secondary">' . $value->name . '</span>';
                    }
                } else {
                    $categories .= '-';
                }
                $categories .= '</div>';

                return $categories;
            })
            ->filterColumn('categories', function ($query, $keyword) {
                $query->whereHas('categories', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', '%' . $keyword . '%');
                });
            })
            ->editColumn('min_price', function ($data) {
                if ($data->max_price != $data->min_price) {
                    return \Currency::format($data->min_price) . ' - ' . \Currency::format($data->max_price);
                } else {
                    return \Currency::format($data->min_price);
                }
            })
            ->editColumn('brand', function ($data) {
                return $data->brand->name ?? '-';
            })
            ->orderColumn('brand', function ($query, $order) {
                $query->select('products.*')
                    ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
                    ->orderBy('brands.name', $order);
            }, 1)
            ->filterColumn('brand', function ($query, $keyword) {
                if (! empty($keyword)) {
                    $query->whereHas('brand', function ($q) use ($keyword) {
                        $q->where('name', 'like', '%' . $keyword . '%');
                    });
                }
            })
            ->editColumn('updated_at', function ($data) {
                $module_name = $this->module_name;

                $diff = Carbon::now()->diffInHours($data->updated_at);

                if ($diff < 25) {
                    return $data->updated_at->diffForHumans();
                } else {
                    return $data->updated_at->isoFormat('llll');
                }
            })
            ->rawColumns(['action', 'status', 'image', 'check', 'categories', 'is_featured'])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(ProductRequest $request)
    {
        \Log::debug('Products.store payload start', [
            'has_variation' => $request->has('has_variation') ? (int) $request->has_variation : 0,
            'combinations_raw_type' => gettype($request->combinations),
            'combinations_raw_snippet' => is_string($request->combinations) ? substr($request->combinations, 0, 500) : (is_array($request->combinations) ? array_slice($request->combinations, 0, 2) : $request->combinations),
        ]);

        // Normalize combinations early for logging/validation
        $rawCombinations = $request->combinations;
        $decodedCombinations = null;
        if (is_string($rawCombinations)) {
            if ($rawCombinations === 'undefined' || trim($rawCombinations) === '') {
                $decodedCombinations = [];
            } else {
                $decodedCombinations = json_decode($rawCombinations, true);
                if ($decodedCombinations === null && json_last_error() !== JSON_ERROR_NONE) {
                    \Log::warning('Products.store json_decode combinations failed', [
                        'error' => json_last_error_msg(),
                        'raw_snippet' => substr($rawCombinations, 0, 500),
                    ]);
                }
            }
        } elseif (is_array($rawCombinations)) {
            $decodedCombinations = $rawCombinations;
        }
        if ($request->has('has_variation') && (int) $request->has_variation === 1) {
            $request->merge(['combinations' => $decodedCombinations]);
            \Log::debug('Products.store combinations normalized', [
                'count' => is_array($decodedCombinations) ? count($decodedCombinations) : null,
                'first' => is_array($decodedCombinations) && count($decodedCombinations) ? $decodedCombinations[0] : null,
            ]);
        }
        if ($request->has('has_variation') && $request->has_variation == 1) {
            $combinations = $request->combinations;
            \Log::debug('Products.store combinations decoded', [
                'count' => is_array($combinations) ? count($combinations) : null,
                'sample' => is_array($combinations) && count($combinations) ? array_slice($combinations, 0, 2) : $combinations,
            ]);
            if (empty($combinations) || $combinations === [] || $combinations === 'undefined') {
                \Log::warning('Products.store invalid_product_variation', ['reason' => 'empty_combinations']);
                return redirect()->back()
                    ->with('error', __('messages.invalid_product_variation'))
                    ->withInput();
            }
            // Validate required keys inside combinations for better diagnostics
            $missing = [];
            foreach ($combinations as $idx => $comb) {
                foreach (['variation_key','price','stock','sku','code'] as $key) {
                    if (!isset($comb[$key]) || $comb[$key] === '' || $comb[$key] === null) {
                        $missing[] = [$idx => $key];
                    }
                }
            }
            if (!empty($missing)) {
                \Log::warning('Products.store combinations missing fields', ['missing' => $missing]);
            }
        }

        $product = new Product;
        $product->name = $request->name;
        $product->slug = Str::slug($request->name, '-') . '-' . strtolower(Str::random(5));
        $product->brand_id = $request->brand_id;
        $product->unit_id = $request->unit_id ?? null;
        $product->sell_target = $request->sell_target ?? 0;

        $product->description = $request->description ?? '';
        $product->short_description = $request->short_description ?? '';

        if ($request->has('has_variation') && $request->has_variation == 1 && $request->has('combinations') && $request->combinations != 'undefined') {
            $normalizedComb = is_string($request->combinations)
                ? json_decode($request->combinations, true)
                : (is_array($request->combinations) ? $request->combinations : []);

            $request->merge(['combinations' => $normalizedComb]);

            if (is_array($normalizedComb) && ! empty($normalizedComb)) {
                $pricesRaw = array_column($normalizedComb, 'price');
                $prices = array_values(array_filter(array_map(function ($v) {
                    return is_numeric($v) ? (float) $v : null;
                }, $pricesRaw), function ($v) { return $v !== null; }));

                if (! empty($prices)) {
                    $product->min_price = min($prices);
                    $product->max_price = max($prices);
                } else {
                    $product->min_price = $request->price ?? 0;
                    $product->max_price = $request->price ?? 0;
                }
            } else {
                $product->min_price = $request->price ?? 0;
                $product->max_price = $request->price ?? 0;
            }
        } else {
            $product->min_price = $request->price ?? 0;
            $product->max_price = $request->price ?? 0;
        }

        // discount
        $product->discount_value = $request->discount_value ?? 0;
        $product->discount_type = $request->discount_type ?? 'percent';

        if ($request->date_range != null && $request->date_range !== '') {
            if (Str::contains($request->date_range, 'to')) {
                $date_var = explode(' to ', $request->date_range);
            } else {
                $date_var = [date('d-m-Y'), date('d-m-Y')];
            }
            $product->discount_start_date = strtotime($date_var[0]);
            $product->discount_end_date = strtotime($date_var[1]);
        } else {
            $product->discount_start_date = null;
            $product->discount_end_date = null;
        }

        // stock qty based on all variations / no variation
        if (
            $request->has('has_variation') && $request->has_variation == 1 && $request->has('combinations') && is_array($request->combinations) && ! empty($request->combinations)
        ) {
            \Log::debug('Products.store computing stock_qty from combinations', ['count' => count($request->combinations)]);
            $product->stock_qty = array_sum(array_column($request->combinations, 'stock'));
        } else {
            $product->stock_qty = $request->stock ?? 0;
        }

        $product->status = $request->has('status') ? 1 : 0;
        
        // Set has_variation based on request
        if ($request->has('has_variation') && $request->has_variation == 1) {
            $product->has_variation = 1;
        } else {
            $product->has_variation = 0;
        }

        // shipping info
        $product->standard_delivery_hours = $request->standard_delivery_hours ?? 72;
        $product->express_delivery_hours = $request->express_delivery_hours ?? 24;
        $product->min_purchase_qty = $request->min_purchase_qty ?? 1;
        $product->max_purchase_qty = $request->max_purchase_qty ?? 1;

        $product->is_featured = $request->has('is_featured') ? 1 : 0;
        $product->created_by = auth()->id();
        $product->save();

        // tags (accept array or JSON string)
        $tag_ids = [];
        if (! empty($request->tags) && $request->tags !== 'undefined') {
            $tagsInput = $request->tags;
            if (is_string($tagsInput)) {
                $decoded = json_decode($tagsInput, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $tagsInput = $decoded;
                } else {
                    // If comma-separated string, split it
                    $tagsInput = array_filter(array_map('trim', explode(',', $tagsInput)));
                }
            }
            if (is_array($tagsInput)) {
                foreach ($tagsInput as $value) {
                    if ($value === null || $value === '') continue;
                    // Check if tag exists and belongs to current user, otherwise create new one
                    $tag = Tag::where('name', $value)
                        ->where('created_by', auth()->id())
                        ->first();
                    if (!$tag) {
                        $tag = Tag::create(['name' => $value, 'created_by' => auth()->id()]);
                    }
                    $tag_ids[] = $tag->id;
                }
            }
        }
        $product->tags_data()->sync($tag_ids);

        // category

        if (! empty($request->category_ids) && $request->category_ids !== 'undefined') {
            // Handle both array and JSON string formats
            $category_ids = $request->category_ids;
            if (is_string($category_ids)) {
                $category_ids = json_decode($category_ids, true);
            }
            
            if (is_array($category_ids) && !empty($category_ids)) {
                $product->categories()->sync($category_ids);
            }
        }

        $location = Location::where('is_default', 1)->first();
        if ($request->has('has_variation') && $request->has_variation == 1) {
            if ($request->has('combinations') && is_array($request->combinations) && ! empty($request->combinations)) {
                \Log::debug('Products.store persisting variations', [
                    'count' => count($request->combinations),
                ]);
                foreach ($request->combinations as $variation) {
                    if (empty($variation['variation_key'])) {
                        \Log::warning('Products.store missing variation_key in combination', ['comb' => $variation]);
                        continue;
                    }
                    $product_variation = new ProductVariation;
                    $product_variation->product_id = $product->id;
                    $product_variation->variation_key = $variation['variation_key'];
                    $product_variation->price = $variation['price'];
                    $product_variation->sku = $variation['sku'];
                    $product_variation->code = $variation['code'];
                    $product_variation->save();

                    $product_variation_stock = new ProductVariationStock;
                    $product_variation_stock->product_variation_id = $product_variation->id;
                    $product_variation_stock->location_id = $location->id;
                    $product_variation_stock->stock_qty = $variation['stock'];
                    $product_variation_stock->save();

                    foreach (array_filter(explode('/', $variation['variation_key'])) as $combination) {
                        $product_variation_combination = new ProductVariationCombination;
                        $product_variation_combination->product_id = $product->id;
                        $product_variation_combination->product_variation_id = $product_variation->id;
                        $product_variation_combination->variation_id = explode(':', $combination)[0];
                        $product_variation_combination->variation_value_id = explode(':', $combination)[1];
                        $product_variation_combination->save();
                    }
                }
            }
        } else {
            $variation = new ProductVariation;
            $variation->product_id = $product->id;
            $variation->sku = $request->sku;
            $variation->code = $request->code;
            $variation->price = $request->price;
            $variation->save();
            $product_variation_stock = new ProductVariationStock;
            $product_variation_stock->product_variation_id = $variation->id;
            $product_variation_stock->location_id = $location->id;
            $product_variation_stock->stock_qty = $request->stock;
            $product_variation_stock->save();
        }

        if ($request->feature_image) {
            storeMediaFile($product, $request->file('feature_image'));
        }

        $message = __('messages.new_product');

        return redirect()->route('backend.products.index')
            ->with('success', $message);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $data = Product::findOrFail($id);
        $data->category_ids = $data->categories->pluck('id')->toArray();
        $data->tags = $data->tags_data->pluck('name')->toArray();
        $data->date_range = date('Y-m-d', $data->discount_start_date) . ' to ' . date('Y-m-d', $data->discount_end_date);

        if ($data->has_variation) {
            $varComb = $data->variation_combinations()->select('variation_id', 'variation_value_id')->get()->toArray();

            $groupedData = [];

            foreach ($varComb as $item) {
                $variationId = $item['variation_id'];
                $variationValueId = $item['variation_value_id'];

                if (! isset($groupedData[$variationId])) {
                    $groupedData[$variationId] = [];
                }

                if (! in_array($variationValueId, $groupedData[$variationId])) {
                    $groupedData[$variationId][] = $variationValueId;
                }
            }

            $finalGroupedData = [];
            foreach ($groupedData as $variationId => $variationValueIds) {
                $finalGroupedData[] = [
                    'variation' => $variationId,
                    'variationValue' => $variationValueIds,
                ];
            }

            $variations = $finalGroupedData;
            $combinations = $data->product_variations;



            $location = Location::where('is_default', 1)->first();
            foreach ($combinations as $key => $value) {

                $variationKey = $value->variation_key;
                $variationName = '';

                $variationPairs = explode('/', $variationKey);
                $variationNames = [];

                foreach ($variationPairs as $pair) {
                    list($typeId, $valueId) = explode(':', $pair);
                    $variationValue = VariationValue::find($valueId);

                    if ($variationValue) {
                        $variationNames[] = strtolower($variationValue->name);
                    }
                }

                $formattedName = implode('-', $variationNames);

                $combinations[$key] = [
                    'product_id' => $value->product_id,
                    'variation' => $formattedName,
                    'variation_key' => $value->variation_key,
                    'stock' => $value->product_variation_stock()->first()?->stock_qty ?? 0,
                    'code' => $value->code,
                    'sku' => $value->sku,
                    'price' => $value->price,
                ];
            }
            $data['variations'] = $variations;
            $data['combinations'] = $combinations;
        } else {
            $variation = $data->product_variations->first();
            $location = Location::where('is_default', 1)->first();
            $data['stock'] = $variation->product_variation_stock()->first()->stock_qty;
            $data['code'] = $variation->code;
            $data['price'] = $variation->price;
            $data['sku'] = $variation->sku;
        }


        return response()->json(['data' => $data, 'status' => true]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(ProductRequest $request, $id)
    {
        \Log::debug('Products.update payload start', [
            'id' => $id,
            'has_variation' => $request->has('has_variation') ? (int) $request->has_variation : 0,
            'combinations_raw_type' => gettype($request->combinations),
            'combinations_raw' => is_string($request->combinations) ? substr($request->combinations, 0, 500) : $request->combinations,
        ]);
        \Log::debug('Products.update request keys', [
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'keys' => array_keys($request->all()),
        ]);

        // Pull raw combinations value using input() to avoid magic prop null
        $combRawInput = $request->input('combinations');
        $combRawBag = $request->request->get('combinations');
        $combRawAlt = $request->input('combinations_json');
        \Log::debug('Products.update combinations raw comparisons', [
            'input_type' => gettype($combRawInput),
            'input_snippet' => is_string($combRawInput) ? substr($combRawInput, 0, 200) : $combRawInput,
            'bag_type' => gettype($combRawBag),
            'bag_snippet' => is_string($combRawBag) ? substr($combRawBag, 0, 200) : $combRawBag,
            'alt_type' => gettype($combRawAlt),
            'alt_snippet' => is_string($combRawAlt) ? substr($combRawAlt, 0, 200) : $combRawAlt,
        ]);
        // Normalize combinations to array if JSON string
        if ($request->has('combinations') && is_string($request->combinations)) {
            $decoded = json_decode($request->combinations, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request->merge(['combinations' => $decoded]);
                \Log::debug('Products.update combinations merged to array', ['count' => is_array($decoded) ? count($decoded) : null]);
            } else {
                \Log::warning('Products.update combinations json decode error', ['error' => json_last_error_msg()]);
            }
        }
        if ($request->has('has_variation') && $request->has_variation == 1) {
            $combinations = $request->input('combinations');
            if ($combinations === null) {
                // Try alternate name if present
                $combinations = $request->input('combinations_json');
            }
            if (is_string($combinations)) {
                $combinations = json_decode($combinations, true);
            }
            \Log::debug('Products.update combinations decoded', [
                'count' => is_array($combinations) ? count($combinations) : null,
                'sample' => is_array($combinations) && count($combinations) ? array_slice($combinations, 0, 2) : $combinations,
            ]);
            if (empty($combinations) || $combinations === [] || $combinations === 'undefined') {
                \Log::warning('Products.update invalid_product_variation', ['reason' => 'empty_combinations']);
                return redirect()->back()
                    ->with('error', __('messages.invalid_product_variation'))
                    ->withInput();
            }
        }

        $product = Product::findOrFail($id);

        $oldProduct = clone $product;

        $product->name = $request->name;
        $product->slug = (! is_null($request->slug)) ? Str::slug($request->slug, '-') : Str::slug($request->name, '-') . '-' . strtolower(Str::random(5));
        $product->description = $request->description;
        $product->sell_target = $request->sell_target;
        $product->brand_id = $request->brand_id;
        $product->unit_id = $request->unit_id;
        $product->short_description = $request->short_description;

        if ($request->has('has_variation') && $request->has_variation) {
            // Normalize combinations input: accept array or JSON string, with fallback field name
            $combinationsInput = $request->input('combinations');
            if ($combinationsInput === null || $combinationsInput === 'undefined') {
                $combinationsInput = $request->input('combinations_json');
            }

            if (is_string($combinationsInput)) {
                $decodedComb = json_decode($combinationsInput, true);
            } elseif (is_array($combinationsInput)) {
                $decodedComb = $combinationsInput;
            } else {
                $decodedComb = [];
            }

            // Merge normalized combinations back into request to keep downstream logic consistent
            $request->merge(['combinations' => $decodedComb]);

            $singlePrice = is_numeric($request->price) ? (float) $request->price : 0.0;
            if (is_array($decodedComb) && ! empty($decodedComb)) {
                $pricesRaw = array_column($decodedComb, 'price');
                $prices = array_values(array_filter(array_map(function ($v) {
                    return is_numeric($v) ? (float) $v : null;
                }, $pricesRaw), function ($v) { return $v !== null; }));

                if (! empty($prices)) {
                    $product->min_price = min($prices);
                    $product->max_price = max($prices);
                } else {
                    $product->min_price = $singlePrice;
                    $product->max_price = $singlePrice;
                }
            } else {
                $product->min_price = $singlePrice;
                $product->max_price = $singlePrice;
            }
        } else {
            $singlePrice = is_numeric($request->price) ? (float) $request->price : 0.0;
            $product->min_price = $singlePrice;
            $product->max_price = $singlePrice;
        }

        // discount
        $product->discount_value = $request->discount_value ?? 0;
        $product->discount_type = $request->discount_type;

        if ($request->date_range != null) {
            if (Str::contains($request->date_range, 'to')) {
                $date_var = explode(' to ', $request->date_range);
            } else {
                $date_var = [date('d-m-Y'), date('d-m-Y')];
            }
            $product->discount_start_date = strtotime($date_var[0]);
            $product->discount_end_date = strtotime($date_var[1]);
        }

        // stock qty based on all variations / no variation
        if (
            $request->has('has_variation') && $request->has('combinations') && is_array($request->combinations) && ! empty($request->combinations)
        ) {
            $stocksRaw = array_column($request->combinations, 'stock');
            $stocks = array_values(array_filter(array_map(function ($v) {
                return is_numeric($v) ? (int) $v : null;
            }, $stocksRaw), function ($v) { return $v !== null; }));
            $product->stock_qty = ! empty($stocks) ? array_sum($stocks) : 0;
        } else {
            $singleStock = is_numeric($request->stock) ? (int) $request->stock : 0;
            $product->stock_qty = $singleStock;
        }

        $product->status = $request->status ?? 0;
        $product->has_variation = ($request->has_variation == 1 && $request->has('combinations')) ? 1 : 0;

        // shipping info
        $product->standard_delivery_hours = $request->standard_delivery_hours ?? 0;
        $product->express_delivery_hours = $request->express_delivery_hours ?? 0;
        $product->min_purchase_qty = $request->min_purchase_qty ?? 0;
        $product->max_purchase_qty = $request->max_purchase_qty ?? 0;

        $product->is_featured = $request->is_featured ?? 0;
        $product->save();

        // tags (accept array or JSON string) - fix wrong key and broaden handling
        $tag_ids = [];
        if (! empty($request->tags) && $request->tags !== 'undefined') {
            $tagsInput = $request->tags;
            if (is_string($tagsInput)) {
                $decoded = json_decode($tagsInput, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $tagsInput = $decoded;
                } else {
                    $tagsInput = array_filter(array_map('trim', explode(',', $tagsInput)));
                }
            }
            if (is_array($tagsInput)) {
                foreach ($tagsInput as $value) {
                    if ($value === null || $value === '') continue;
                    // Check if tag exists and belongs to current user, otherwise create new one
                    $tag = Tag::where('name', $value)
                        ->where('created_by', auth()->id())
                        ->first();
                    if (!$tag) {
                        $tag = Tag::create(['name' => $value, 'created_by' => auth()->id()]);
                    }
                    $tag_ids[] = $tag->id;
                }
            }
        }
        $product->tags_data()->sync($tag_ids);

        // category

        if (! empty($request->category_ids) && $request->category_ids !== 'undefined') {
            // Handle both array and JSON string formats
            $category_ids = $request->category_ids;
            if (is_string($category_ids)) {
                $category_ids = json_decode($category_ids, true);
            }
            
            if (is_array($category_ids) && !empty($category_ids)) {
                $product->categories()->sync($category_ids);
            }
        }

        // taxes
        $tax = [];

        if (! empty($request->taxes) && is_string($request->taxes) && $request->taxes !== 'undefined') {
            $request->taxes = json_decode($request->taxes, true);

            foreach ($request->taxes as $key => $value) {
                if (isset($value['tax_id'], $value['tax_value'], $value['tax_type'])) {
                    $tax[] = [
                        'tax_id' => $value['tax_id'],
                        'tax_value' => $value['tax_value'],
                        'tax_type' => $value['tax_type'],
                    ];
                }
            }
            $product->product_taxes()->sync($tax);
        }

        $location = Location::where('is_default', 1)->first();

        if ($request->has_variation == 1 && $request->has('combinations') && is_array($request->combinations) && ! empty($request->combinations)) {

            $new_requested_variations = collect($request->combinations);
            $new_requested_variations_key = $new_requested_variations->pluck('variation_key')->toArray();
            $old_variations_keys = $product->product_variations->pluck('variation_key')->toArray();
            $old_matched_variations = $new_requested_variations->whereIn('variation_key', $old_variations_keys);
            $new_variations = $new_requested_variations->whereNotIn('variation_key', $old_variations_keys);

            // delete old variations that isn't requested
            $product->product_variations->whereNotIn('variation_key', $new_requested_variations_key)->each(function ($variation) use ($location) {
                foreach ($variation->combinations as $comb) {
                    $comb->delete();
                }
                $variation->product_variation_stock_without_location()->where('location_id', $location->id)->delete();
                $variation->delete();
            });

            // update old matched variations (coerce numeric fields)
            foreach ($old_matched_variations as $variation) {
                $p_variation = ProductVariation::where('product_id', $product->id)->where('variation_key', $variation['variation_key'])->first();
                $p_variation->price = is_numeric($variation['price']) ? (float) $variation['price'] : 0.0;
                $p_variation->sku = $variation['sku'];
                $p_variation->code = $variation['code'];
                $p_variation->save();

                // update stock of this variation
                $productVariationStock = $p_variation->product_variation_stock_without_location()->where('location_id', $location->id)->first();
                if (is_null($productVariationStock)) {
                    $productVariationStock = new ProductVariationStock;
                    $productVariationStock->product_variation_id = $p_variation->id;
                }
                $productVariationStock->stock_qty = is_numeric($variation['stock']) ? (int) $variation['stock'] : 0;
                $productVariationStock->location_id = $location->id;
                $productVariationStock->save();
            }

            // store new requested variations
            foreach ($new_variations as $variation) {
                $product_variation = new ProductVariation;
                $product_variation->product_id = $product->id;
                $product_variation->variation_key = $variation['variation_key'];
                $product_variation->price = is_numeric($variation['price']) ? (float) $variation['price'] : 0.0;
                $product_variation->sku = $variation['sku'];
                $product_variation->code = $variation['code'];
                $product_variation->save();

                $product_variation_stock = new ProductVariationStock;
                $product_variation_stock->product_variation_id = $product_variation->id;
                $product_variation_stock->stock_qty = is_numeric($variation['stock']) ? (int) $variation['stock'] : 0;
                $product_variation_stock->save();

                foreach (array_filter(explode('/', $variation['variation_key'])) as $combination) {
                    $product_variation_combination = new ProductVariationCombination;
                    $product_variation_combination->product_id = $product->id;
                    $product_variation_combination->product_variation_id = $product_variation->id;
                    $product_variation_combination->variation_id = explode(':', $combination)[0];
                    $product_variation_combination->variation_value_id = explode(':', $combination)[1];
                    $product_variation_combination->save();
                }
            }
        } else {
            // check if old product is variant then delete all old variation & combinations
            if ($oldProduct->has_variation) {
                if (isset($product->product_variations)) {
                    foreach ($product->product_variations as $variation) {
                        if (isset($variation->combinations)) {
                            foreach ($variation->combinations as $comb) {
                                $comb->delete();
                            }
                            $variation->delete();
                        }
                    }
                }
            }

            $variation = $product->product_variations->first();
            if (is_null($variation)) {
                $variation = new ProductVariation;
                $variation->product_id = $product->id;
            } else {
                $variation->product_id = $product->id;
            }
            $variation->variation_key = null;
            $variation->sku = $request->sku;
            $variation->code = $request->code;
            $variation->price = is_numeric($request->price) ? (float) $request->price : 0.0;
            $variation->save();

            if ($variation->product_variation_stock) {
                $productVariationStock = $variation->product_variation_stock_without_location()->where('location_id', $location->id)->first();

                if (is_null($productVariationStock)) {
                    $productVariationStock = new ProductVariationStock;
                }

                $productVariationStock->product_variation_id = $variation->id;
                $productVariationStock->stock_qty = $request->stock;
                $productVariationStock->location_id = $location->id;
                $productVariationStock->save();
            } else {
                $product_variation_stock = new ProductVariationStock;
                $product_variation_stock->product_variation_id = $variation->id;
                $product_variation_stock->stock_qty = $request->stock;
                $product_variation_stock->save();
            }
        }

        // Only remove image if user explicitly requested removal
        if ($request->boolean('remove_feature_image')) {
            $product->clearMediaCollection('feature_image');
        }

        // Replace image only when a new file is uploaded
        if ($request->hasFile('feature_image')) {
            storeMediaFile($product, $request->file('feature_image'));
        }

        $message = __('messages.update_product');

        return redirect()->route('backend.products.index')
            ->with('success', $message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        if (env('IS_DEMO')) {
            return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 403);
        }
        $data = Product::findOrFail($id);

        $data->delete();

        $message = __('messages.delete_product');

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function update_status(Request $request, Product $id)
    {
        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('product.status_update')]);
    }

    public function update_is_featured(Request $request, Product $id)
    {
        $id->update(['is_featured' => $request->status]);

        return response()->json(['status' => true, 'message' => __('product.is_featured_update')]);
    }

    public function bulk_action(Request $request)
    {

        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'change-is_featured':
                Product::whereIn('id', $ids)->update(['is_featured' => $request->is_featured]);
                break;

            case 'change-status':
                $products = Product::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('messages.bulk_status_update');
                break;

            case 'delete':
                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 403);
                }
                $products = Product::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_status_delete');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('product.invalid_action')], 422);
                break;
        }

        return response()->json(['status' => true, 'message' => $message], 200);
    }

    public function getGalleryImages($id)
    {
        $product = Product::findOrFail($id);

        $data = ProductGallery::where('product_id', $id)->get();

        return response()->json(['data' => $data, 'product' => $product, 'status' => true]);
    }

    public function uploadGalleryImages(Request $request, $id)
    {
        $gallery = collect($request->gallery, true);

        $images = ProductGallery::where('product_id', $id)->whereNotIn('id', $gallery->pluck('id'))->get();

        foreach ($images as $key => $value) {
            $value->clearMediaCollection('gallery_images');
            $value->delete();
        }

        foreach ($gallery as $key => $value) {
            if ($value['id'] == 'null') {
                $productGallery = ProductGallery::create([
                    'product_id' => $id,
                ]);

                $productGallery->addMedia($value['file'])->toMediaCollection('gallery_images');

                $productGallery->full_url = $productGallery->getFirstMediaUrl('gallery_images');
                $productGallery->save();
            }
        }

        return response()->json(['message' => __('product.update_product_gallery'), 'status' => true]);
    }
}
