<?php

namespace Modules\Promotion\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\CustomField\Models\CustomField;
use Modules\CustomField\Models\CustomFieldGroup;
use Modules\Promotion\Models\Coupon;
use Modules\Promotion\Models\Promotion;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;
use Modules\Promotion\Http\Requests\PromotionRequest;
use Modules\Promotion\Models\PromotionsCouponPlanMapping;


class PromotionsController extends Controller
{

    protected string $exportClass = '\App\Exports\CouponsExport';


    public function __construct()
    {
        // Page Title
        $this->module_title = __('promotion.coupon_title');
        // module name
        $this->module_name = 'promotions';

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
     * @return Response
     */
    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $today = Carbon::now()->toDateString();
        $module_title = __('promotion.coupon_title');
        $userRole = auth()->user()->user_type;

        $module_action = __('messages.list');
        $columns = CustomFieldGroup::columnJsonValues(new Promotion());
        $customefield = CustomField::exportCustomFields(new Promotion());

        $export_import = true;
        $export_columns = [
            [
                'value' => 'name',
                'text' => __('messages.name'),
            ],
            [
                'value' => 'value',
                'text' => __('messages.lbl_value'),
            ],
            [
                'value' => 'promo_end_date',
                'text' => __('messages.end_date'),
            ],

        ];

        $export_url = route('backend.promotions.export');
        
        $createTitle = __('messages.new') . ' ' . __('promotion.singular_title');
        $editTitle = __('messages.edit') . ' ' . __('promotion.singular_title');

        return view('promotion::backend.promotions.index_datatable', compact('module_action', 'filter', 'columns', 'customefield', 'export_import', 'export_columns', 'export_url','module_title','userRole', 'createTitle', 'editTitle'));
    }

    /**
     * Select Options for Select 2 Request/ Response.
     *
     * @return Response
     */
    public function index_list(Request $request)
    {
        $term = trim($request->q);

        if (empty($term)) {
            return response()->json([]);
        }

        $query_data = Promotion::where('name', 'LIKE', "%$term%")->orWhere('slug', 'LIKE', "%$term%");

        $query_data =  $query_data->where('created_by', auth()->id());

        $query_data = $query_data->get();

        $data = [];

        foreach ($query_data as $row) {
            $data[] = [
                'id' => $row->id,
                'text' => $row->name . ' (Slug: ' . $row->slug . ')',
            ];
        }

        return response()->json($data);
    }

    public function index_data(Request $request)
    {

        $module_name = $this->module_name;
        $query = Promotion::query()->with(['coupon']);

        $query =  $query->where('promotions.created_by', auth()->id());



        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }

        return Datatables::of($query)
            ->addColumn('check', function ($data) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $data->id . '"  name="datatable_ids[]" value="' . $data->id . '" onclick="dataTableRowCheck(' . $data->id . ')">';
            })
            ->addColumn('action', function ($data) {
                return view('promotion::backend.promotions.action_column', compact('data'));
            })
            ->addColumn('description', function ($data) {
                $maxLength = 50; // Set your desired maximum length
                return Str::limit($data->description, $maxLength);
            })
            ->orderColumn('description', function ($query, $order) {
                $query->orderBy('description', $order);
            })
            ->addColumn('is_expired', function ($data) {
                // every() error solved
                if ($data->coupon instanceof \Illuminate\Database\Eloquent\Collection) {
                    return $data->coupon->every(fn($coupon) => $coupon->is_expired ?? false) ? 'Yes' : 'No';
                }

                // If coupon is a single model, directly check its is_expired property
                return $data->coupon->is_expired ?? false ? 'Yes' : 'No';
            })
            ->editColumn('coupon_type', function ($data) {

                return ucfirst($data->coupon->coupon_type ?? 'N/A');
            })
            ->orderColumn('coupon_type', function ($query, $order) {
                $query->select('promotions.*') // Select columns from promotions table
                    ->leftJoin('promotions_coupon as coupon', 'promotions.id', '=', 'coupon.promotion_id') // Join with promotions_coupon table
                    ->orderBy('coupon.coupon_type', $order); // Order by coupon_type
            }, 2)
            ->filterColumn('coupon_type', function($query, $keyword) {
                $query->select('promotions.*')
                    ->leftJoin('promotions_coupon as coupon', 'promotions.id', '=', 'coupon.promotion_id')
                    ->where('coupon.coupon_type', 'like', "%{$keyword}%");
            })
            ->editColumn('coupon_price', function ($data) {
                if (!isset($data->coupon)) {
                    return 'N/A'; // Fallback for null coupon
                }

                if (($data->coupon->discount_type ?? null) === 'fixed') {
                    return \Currency::format($data->coupon->discount_amount ?? 0);
                }

                if (($data->coupon->discount_type ?? null) === 'percent') {
                    return ($data->coupon->discount_percentage ?? 0) . '%';
                }

                return 'N/A'; // Fallback in case no condition matches
            })
            ->orderColumn('coupon_price', function ($query, $order) {
                $query->select('promotions.*')
                    ->leftJoin('promotions_coupon as coupon', 'promotions.id', '=', 'coupon.promotion_id')
                    ->orderByRaw("CASE
                        WHEN coupon.discount_type = 'fixed' THEN coupon.discount_amount
                        WHEN coupon.discount_type = 'percent' THEN coupon.discount_percentage
                        END $order"); // Conditional ordering based on discount_type
            }, 3)

            ->editColumn('start_date_time', function ($data) {
                return formatDateOrTime($data->start_date_time,'date');
            })
            ->editColumn('end_date_time', function ($data) {
                return formatDateOrTime($data->end_date_time,'date');
            })

            ->editColumn('Select_Plan', function ($data) {
                return $data->coupon ? $data->coupon->Select_Plan : '-';
            })

            ->orderColumn('Select_Plan', function ($query, $order) {
                $query->select('promotions.*')
                    ->leftJoin('promotions_coupon as coupon', 'promotions.id', '=', 'coupon.promotion_id')
                    ->orderBy('coupon.Select_Plan', $order); // Order by use_limit
            }, 4)
            ->editColumn('status', function ($data) {
                $checked = '';
                if ($data->status) {
                    $checked = 'checked="checked"';
                }

                return '
                    <div class="form-check form-switch ">
                        <input type="checkbox" data-url="' . route('backend.promotions.update_status', $data->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $data->id . '"  name="status" value="' . $data->id . '" ' . $checked . '>
                    </div>
                ';
            })
            ->editColumn('updated_at', function ($data) {

                $diff = Carbon::now()->diffInHours($data->updated_at);

                if ($diff < 25) {
                    return $data->updated_at->diffForHumans();
                } else {
                    return $data->updated_at->isoFormat('llll');
                }
            })
            ->rawColumns(['action', 'status', 'check'])
            ->orderColumns(['id'], '-:column $1')
            ->make(true);
    }

    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = __('messages.bulk_update');
        switch ($actionType) {
            case 'change-status':
                $promotion = Promotion::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('messages.bulk_promotion_update');
                break;

            case 'delete':
                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }

                Promotion::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_promotion_update');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('branch.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => __('messages.bulk_update')]);
    }

    public function update_status(Request $request, $id)
    {
        $promotion = Promotion::find($id); // Using find() to directly get the model instance
        if ($promotion) {
            $promotion->update(['status' => $request->status]);
        }
        if ($request->status == 1) {
            $coupon = Coupon::where('promotion_id', $id)->first();
            if ($coupon) {
                $coupon->update(['is_expired' => 0]);
            }
        }

        return response()->json(['status' => true, 'message' => 'Status Updated']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $module_action = 'messages.create';
        $module_title = __('promotion.singular_title');
        
        // Get plans for super admin users
        $plans = [];
        if (auth()->user()->hasRole('super admin')) {
            $plans = \Modules\Subscriptions\Models\Plan::where('status', 1)->get();
        }
        
        return view('promotion::backend.promotions.create', compact('module_action', 'module_title', 'plans'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */


public function store(PromotionRequest $request)
{
        // Debug: Log the request data
        \Log::info('Store promotion request data:', [
            'discount_type' => $request->discount_type,
            'discount_percentage' => $request->discount_percentage,
            'discount_amount' => $request->discount_amount,
            'start_date_time' => $request->start_date_time,
            'end_date_time' => $request->end_date_time,
            'use_limit' => $request->use_limit,
            'all_request' => $request->all()
        ]);
        
        // Check if promotion with same name exists
    $existingPromotion = Promotion::where('name', $request->name)->first();
    if ($existingPromotion) {
        if ($request->ajax()) {
            return response()->json([
                'status' => false,
                'message' => __('messages.promotion_already_exists'),
                'errors' => ['name' => [__('messages.promotion_name_taken')]]
            ], 422);
        } else {
            return redirect()->back()->with('error', __('messages.promotion_already_exists'))->withInput();
        }
    }
    
    // Check if coupon code already exists
    if ($request->coupon_type == 'custom' && $request->coupon_code) {
        $existingCoupon = Coupon::where('coupon_code', $request->coupon_code)->first();
        if ($existingCoupon) {
            return response()->json([
                'status' => false,
                'message' => 'Coupon code already exists',
                'errors' => ['coupon_code' => ['This coupon code is already taken']]
            ], 422);
        }
    }

    $data = $request->except('feature_image');
    $promotion = Promotion::create($request->all());

    // Track all generated coupons
    $couponIds = [];

    // Create coupon for all users (not just admin/super admin)
    if ($request->coupon_type == 'custom') {
        $couponData = $data;
        $couponData['coupon_type'] = $request->coupon_type;
        $couponData['coupon_code'] = $request->coupon_code;
        $couponData['promotion_id'] = $promotion->id; // Set promotion_id
        $couponData['use_limit'] = $request->use_limit;
        $couponData['discount_type'] = $request->discount_type;
        $couponData['discount_percentage'] = $request->discount_percentage ?? 0;
        $couponData['discount_amount'] = $request->discount_amount ?? 0;
        $couponData['start_date_time'] = $request->start_date_time;
        $couponData['end_date_time'] = $request->end_date_time;
        
        // Debug: Log coupon data before creation
        \Log::info('Creating coupon with data:', $couponData);
        
        $coupon = $this->createCoupon($couponData);
        
        // Debug: Log created coupon data
        \Log::info('Created coupon:', $coupon->toArray());
        $couponIds[] = $coupon->id; // Save the generated coupon ID
    } else {
        for ($i = 1; $i <= $request->number_of_coupon ?? 1; $i++) {
            $couponData = $data;
            $couponData['coupon_type'] = $request->coupon_type;
            $couponData['coupon_code'] = strtoupper(randomString(8));
            $couponData['promotion_id'] = $promotion->id; // Set promotion_id
            $couponData['use_limit'] = $request->use_limit;
            $couponData['discount_type'] = $request->discount_type;
            $couponData['discount_percentage'] = $request->discount_percentage ?? 0;
            $couponData['discount_amount'] = $request->discount_amount ?? 0;
            $couponData['start_date_time'] = $request->start_date_time;
            $couponData['end_date_time'] = $request->end_date_time;
            
            // Debug: Log coupon data before creation
            \Log::info('Creating coupon with data:', $couponData);
            
            $coupon = $this->createCoupon($couponData);
            
            // Debug: Log created coupon data
            \Log::info('Created coupon:', $coupon->toArray());
            $couponIds[] = $coupon->id; // Save the generated coupon ID
        }
    }


    $isSuperAdmin = auth()->user()->hasRole('super admin');
    if($isSuperAdmin){
        // Debug logging
        \Log::info('Store promotion - plan_id data:', [
            'has_plan_id' => $request->has('plan_id'),
            'plan_id_value' => $request->input('plan_id'),
            'all_request' => $request->all()
        ]);
        
        // Check for both plan_id and plan_id[] formats
        $planIds = [];
        if ($request->has('plan_id') && $request->plan_id != null) {
            $planIds = is_array($request->plan_id) ? $request->plan_id : explode(',', $request->plan_id);
        } elseif ($request->has('plan_id.0')) {
            // Handle plan_id[] array format
            $planIds = $request->input('plan_id', []);
        }
        
        // Filter out empty values
        $planIds = array_filter($planIds, function($id) {
            return !empty($id) && $id != '';
        });
        
        \Log::info('Store promotion - processed plan IDs:', ['plan_ids' => $planIds]);
        
        if (!empty($planIds)) {
            \Log::info('Store promotion - creating plan mappings:', [
                'plan_ids' => $planIds,
                'coupon_ids' => $couponIds
            ]);

            foreach ($planIds as $planId) {
                foreach ($couponIds as $couponId) {
                    $mapping = PromotionsCouponPlanMapping::create([
                        'coupon_id' => $couponId, // Use the correct coupon ID for each plan
                        'plan_id' => $planId,
                    ]);
                    \Log::info('Store promotion - created mapping:', [
                        'mapping_id' => $mapping->id,
                        'coupon_id' => $couponId,
                        'plan_id' => $planId
                    ]);
                }
            }
        } else {
            \Log::info('Store promotion - no plan IDs to process');
        }
    }

    // Store the feature image
    storeMediaFile($promotion, $request->file('feature_image'));

    if ($request->ajax()) {
        return response()->json(['message' => __('messages.new_promotion'), 'status' => true], 200);
    } else {
        return redirect()->route('backend.promotions.index')->with('success', __('messages.new_promotion'));
    }
}

    protected function createCoupon($data)
    {
        // Ensure discount values are set properly based on discount_type
        if ($data['discount_type'] == 'percent') {
            $data['discount_amount'] = 0; // Set to 0 for percentage discounts
            // Keep the discount_percentage value from request
        } else {
            $data['discount_percentage'] = 0; // Set to 0 for fixed amount discounts
            // Keep the discount_amount value from request
        }
        
        return Coupon::create($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        \Log::info('Edit promotion request received', ['id' => $id]);
        
        try {
            $promotion = Promotion::with('coupon.promotionCouponPlanMappings.plan')->findOrFail($id);
            \Log::info('Promotion found', ['promotion' => $promotion->toArray()]);

            // If promotion doesn't have a coupon, create one
            if (!$promotion->coupon) {
                \Log::info('No coupon found for promotion, creating one');
                $coupon = \Modules\Promotion\Models\Coupon::create([
                    'promotion_id' => $promotion->id,
                    'coupon_code' => strtoupper(randomString(8)),
                    'coupon_type' => 'custom',
                    'discount_type' => 'percent',
                    'discount_percentage' => 0,
                    'discount_amount' => 0,
                    'use_limit' => 1,
                    'start_date_time' => now()->format('Y-m-d'),
                    'end_date_time' => now()->addDays(30)->format('Y-m-d'),
                    'is_expired' => 0,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
                $promotion->load('coupon'); // Reload the relationship
                \Log::info('Created coupon for promotion', ['coupon_id' => $coupon->id]);
            }

            $promotion['plan_ids'] = $promotion->coupon ? $promotion->coupon->promotionCouponPlanMappings->pluck('plan_id')->toArray() : [];
            $promotion['feature_image'] = $promotion->feature_image;
            
            \Log::info('Edit promotion response data', [
                'promotion' => $promotion->toArray(),
                'coupon' => $promotion->coupon ? $promotion->coupon->toArray() : 'No coupon',
                'discount_type' => $promotion->coupon ? $promotion->coupon->discount_type : 'N/A',
                'discount_percentage' => $promotion->coupon ? $promotion->coupon->discount_percentage : 'N/A',
                'discount_amount' => $promotion->coupon ? $promotion->coupon->discount_amount : 'N/A'
            ]);

            // Return view for regular request, JSON for AJAX
            if (request()->ajax()) {
                return response()->json(['data' => $promotion, 'status' => true]);
            } else {
                $module_action = 'messages.edit';
                $module_title = __('promotion.singular_title');
                
                // Get plans for super admin users
                $plans = [];
                if (auth()->user()->hasRole('super admin')) {
                    $plans = \Modules\Subscriptions\Models\Plan::where('status', 1)->get();
                }
                
                return view('promotion::backend.promotions.create', compact('promotion', 'module_action', 'module_title', 'plans'));
            }
        } catch (\Exception $e) {
            \Log::error('Edit promotion error', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (request()->ajax()) {
                return response()->json(['data' => null, 'status' => false, 'message' => $e->getMessage()], 500);
            } else {
                return redirect()->back()->with('error', 'Failed to load promotion for editing: ' . $e->getMessage());
            }
        }
    }





    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        // Debug: Log update request data
        \Log::info('Update promotion request data:', [
            'id' => $id,
            'discount_type' => $request->discount_type,
            'discount_percentage' => $request->discount_percentage,
            'discount_amount' => $request->discount_amount,
            'start_date_time' => $request->start_date_time,
            'end_date_time' => $request->end_date_time,
            'use_limit' => $request->use_limit,
            'all_request' => $request->all()
        ]);
        
        // Find the promotion
        $promotion = Promotion::findOrFail($id);
        
        // Check if coupon code already exists (excluding current promotion)
        if ($request->coupon_type == 'custom' && $request->coupon_code) {
            $existingCoupon = Coupon::where('coupon_code', $request->coupon_code)
                ->where('promotion_id', '!=', $id)
                ->first();
            if ($existingCoupon) {
                return response()->json([
                    'status' => false,
                    'message' => 'Coupon code already exists',
                    'errors' => ['coupon_code' => ['This coupon code is already taken']]
                ], 422);
            }
        }

        // Update the promotion details
        $data = $request->except('feature_image');
        $promotion->update($data);

        // Handle feature image
        if ($request->hasFile('feature_image')) {
            storeMediaFile($promotion, $request->file('feature_image'));
        }
        if ($request->feature_image == null) {
            $promotion->clearMediaCollection('feature_image');
        }
        $isAdmin =auth()->user()->hasRole('admin');
        // Find or create the coupon related to this promotion
        $coupon = Coupon::firstOrCreate(
            ['promotion_id' => $id],
            [
                'coupon_code' => $isAdmin ? strtoupper(randomString(8)) : null, // Fallback for new coupon
                'discount_type' => $request->discount_type,

            ]
        );

        // Prepare coupon data for update
        $couponData = [
            'discount_type' => $request->discount_type,
            'use_limit' => $request->use_limit ?? 1,
            'start_date_time' => $request->start_date_time,
            'end_date_time' => $request->end_date_time,
        ];
        
        // Debug: Log coupon data before update
        \Log::info('Updating coupon with data:', $couponData);

        if ($request->discount_type == 'percent') {
            $couponData['discount_amount'] = 0;
            $couponData['discount_percentage'] = $request->discount_percentage;
        } else {
            $couponData['discount_amount'] = $request->discount_amount;
            $couponData['discount_percentage'] = 0;
        }

        if ($request->status == 1) {
            $couponData['is_expired'] = 0;
        }

        // Update the coupon
        $coupon->update($couponData);
        
        // Debug: Log updated coupon data
        \Log::info('Updated coupon:', $coupon->fresh()->toArray());


        // Handle plan updates for super admin
        $isSuperAdmin = auth()->user()->hasRole('super admin');
        if($isSuperAdmin){
            // Debug logging
            \Log::info('Update promotion - plan_id data:', [
                'has_plan_id' => $request->has('plan_id'),
                'plan_id_value' => $request->input('plan_id'),
                'all_request' => $request->all()
            ]);
            
            // Check for both plan_id and plan_id[] formats
            $planIds = [];
            if ($request->has('plan_id') && $request->plan_id != null) {
                $planIds = is_array($request->plan_id) ? $request->plan_id : explode(',', $request->plan_id);
            } elseif ($request->has('plan_id.0')) {
                // Handle plan_id[] array format
                $planIds = $request->input('plan_id', []);
            }
            
            // Filter out empty values
            $planIds = array_filter($planIds, function($id) {
                return !empty($id) && $id != '';
            });
            
            \Log::info('Update promotion - processed plan IDs:', ['plan_ids' => $planIds]);
            
            // First, delete existing mappings for this coupon
            PromotionsCouponPlanMapping::where('coupon_id', $coupon->id)->delete();
            
            // Then create new mappings if plan IDs are provided
            if (!empty($planIds)) {
                \Log::info('Update promotion - creating plan mappings:', [
                    'plan_ids' => $planIds,
                    'coupon_id' => $coupon->id
                ]);

                foreach ($planIds as $planId) {
                    $mapping = PromotionsCouponPlanMapping::create([
                        'coupon_id' => $coupon->id,
                        'plan_id' => (int) $planId,
                    ]);
                    \Log::info('Update promotion - created mapping:', [
                        'mapping_id' => $mapping->id,
                        'coupon_id' => $coupon->id,
                        'plan_id' => $planId
                    ]);
                }
            } else {
                \Log::info('Update promotion - no plan IDs to process');
            }
        }


        $message = __('messages.promotion_updated');
        
        // Return JSON for AJAX requests, redirect for regular form submissions
        if (request()->ajax()) {
            return response()->json(['message' => $message, 'status' => true], 200);
        } else {
            return redirect()->route('backend.promotions.index')->with('success', $message);
        }
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
            return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
        }
        $data = Promotion::findOrFail($id);

        $coupon = Coupon::where('promotion_id', $id);
        $coupon->delete();

        $data->delete();

        $message = __('messages.promotion_delete');

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function couponValidate(Request $request)
    {
        $now = now();
        $coupon = Coupon::with('promotion')->where('coupon_code', $request->coupon_code)
            ->where('end_date_time', '>=', $now)
            ->where('is_expired', '!=', '1')
            ->whereHas('promotion', function ($query) {
                $query->where('status', '!=', '0');
            })
            ->first();

        if (!$coupon) {

            $message = __('messages.coupon_not_valid');

            return ['valid' => false, 'message' => $message, 'status' => false];
        }
        if(auth()->user()->hasRole('admin')){
            $coupon = $coupon->whereHas('promotion', function($query) {
                    $query->where('created_by', auth()->id());
                })->first();
        }
        $servicePrice = $request->service_price;

        $discountValue = 0;
        if ($coupon->discount_type == 'fixed') {
            $discountValue = $coupon->discount_amount;
        } elseif ($coupon->discount_type == 'percentage') {
            $discountValue = ($coupon->discount_percentage / 100) * $servicePrice;
        }

        // Ensure discount doesn't exceed the service price
        if ($discountValue > $servicePrice) {
            return response()->json(['valid' => false, 'message' => __('messages.discount_exceeds_service_price'), 'status' => false], 400);
        }
        $data = [
            'coupon_code' => $coupon->coupon_code,
            'discount_type' => $coupon->discount_type,
            'discount_value' => $coupon->discount_amount,
            'discount_percentage' => $coupon->discount_percentage,
        ];
        $message = __('messages.coupon_valid');

        return response()->json(['message' => $message, 'data' => $data, 'status' => true, 'valid' => true], 200);
    }

    public function couponsview(Request $request)
    {
        $promotion_id = $request->id ? $request->id : abort(404);
        $promotion = Promotion::find($promotion_id);



        $module_action = __('messages.list');
        $columns = CustomFieldGroup::columnJsonValues(new Promotion());
        $customefield = CustomField::exportCustomFields(new Promotion());

        $export_import = true;

        $export_columns = [
            [
                'value' => 'coupon_code' . ',' . $promotion_id,
                'text' => __('messages.coupon_code'),
            ],
            [
                'value' => 'value' . ',' . $promotion_id,
                'text' => __('messages.lbl_value'),
            ],
            [
                'value' => 'Select_Plan' . ',' . $promotion_id,
                'text' => __('messages.select_plan'),
            ],

            [
                'value' => 'is_expired' . ',' . $promotion_id,
                'text' => __('messages.lbl_expired'),
            ],
        ];

        if (!auth()->user()->hasRole('super admin')) {
            $export_columns[] = [
                'value' => 'used_by' . ',' . $promotion_id,
                'text' => __('messages.user'),
            ];
        }

        $export_url = route('backend.coupons.export', $promotion_id);

        return view('promotion::backend.promotions.coupon_datatable', compact('module_action', 'export_import', 'export_columns', 'export_url', 'promotion_id', 'promotion'));
    }

    public function coupon_data(Request $request, $id)
    {
        $module_name = $this->module_name;

        $query = Coupon::with('userRedeems')->where('promotion_id', $id);
        if(auth()->user()->hasRole('admin')){
            $query = $query->where('created_by', auth()->id());
        }

        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }

        return Datatables::of($query)
            ->addColumn('check', function ($data) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $data->id . '"  name="datatable_ids[]" value="' . $data->id . '" onclick="dataTableRowCheck(' . $data->id . ')">';
            })
            ->addColumn('action', function ($data) {
                return view('promotion::backend.promotions.action_column', compact('data'));
            })
            ->editColumn('Select_Plan', function ($data) {
                return $data->Select_Plan ? $data->Select_Plan : '-';
            })
            ->editColumn('value', function ($data) {

                if ($data->discount_type === 'fixed') {
                    $value = \Currency::format($data->discount_amount ?? 0);
                    return $value;
                }
                if ($data->discount_type === 'percent') {
                    $value = $data->discount_percentage . '%';

                    return $value;
                }
            })
            ->editColumn('used_by', function ($data) {
                $userNames = $data->userRedeems->pluck('full_name');
                $displayedNames = $userNames->take(2)->implode(', ');
                if ($userNames->count() > 2) {
                    $displayedNames .= ', ...';
                }

                return $displayedNames ?: " ";
            })
            ->editColumn('is_expired', function ($data) {

                return $data->is_expired === 1 ? 'Yes' : 'No';
            })
            ->editColumn('Select_Plan', function ($data) {
                try {
                    $planNames = $data->promotionCouponPlanMappings->pluck('plan.name')->filter();

                    if ($planNames->isEmpty()) {
                        return "No plans";
                    }

                    $displayedNames = $planNames->take(2)->implode(', ');
                    if ($planNames->count() > 2) {
                        $displayedNames .= ', ...';
                    }

                    return $displayedNames;
                } catch (\Exception $e) {
                    \Log::error('Error in Select_Plan: ' . $e->getMessage());
                    return "Error loading plans";
                }
            })

            ->rawColumns(['action', 'status', 'check'])
            ->orderColumns(['id'], '-:column $1')
            ->make(true);

    }

    public function couponExport(Request $request, $id)
    {
        $this->exportClass = 'App\Exports\couponsExport';

        return $this->export($request);
    }
    public function unique_coupon(Request $request)
    {
        $couponCode = implode('', $request->all());
        $isUnique = !Coupon::where('coupon_code', $couponCode)->exists();

        return response()->json(['isUnique' => $isUnique]);;
    }

    public function check_coupon_code(Request $request)
    {
        $coupon_code = $request->coupon_code;
        $promotion_id = $request->promotion_id;
        
        $query = Coupon::where('coupon_code', $coupon_code);
        
        // If editing an existing promotion, exclude its own coupon
        if ($promotion_id) {
            $query->where('promotion_id', '!=', $promotion_id);
        }
        
        $coupon = $query->first();
        
        return response()->json([
            'available' => !$coupon, // true if available, false if taken
            'message' => $coupon ? 'This coupon code is already taken' : 'Coupon code is available'
        ]);
    }
}
