<?php

namespace Modules\Holiday\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Holiday\Models\Holiday;
use Modules\BussinessHour\Models\BussinessHour;

class HolidaysController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => [],
            'message' => 'Holidays listing endpoint'
        ]);
    }

    public function __construct()
    {
        // Page Title
        $this->module_title = __('messages.holidays');

        // module name
        $this->module_name = 'holidays';

        // directory path of the module
        $this->module_path = 'holiday::backend';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => 'fa-regular fa-sun',
            'module_name' => $this->module_name,
            'module_path' => $this->module_path,
        ]);
    }

    /**
     * Get holidays for a specific branch
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHolidays(Request $request)
    {
        try {
            $branchId = $request->query('branch_id');
            
            $query = Holiday::query();
            
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }
            
            $holidays = $query->select(['id' ,'date', 'branch_id'])
                ->orderBy('date')
                ->get();
                
            return response()->json([
                'status' => true,
                'data' => $holidays,
                'message' => 'Holidays retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve holidays',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Select Options for Select 2 Request/ Response.
     *
     * @return Response
     */
    public function index_list(Request $request)
    {
        $branch_id = $request->branch_id;

        $data = Holiday::where('branch_id', $branch_id)->get();

        return response()->json(['data' => $data, 'status' => true]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $holidays = collect($request->holidays);
        $today = now()->startOfDay();

        // Validate that no past dates are being saved
        foreach ($holidays as $holiday) {
            $holidayDate = \Carbon\Carbon::parse($holiday['date'])->startOfDay();
            if ($holidayDate->isBefore($today)) {
                return response()->json([
                    // 'message' => 'Past dates cannot be selected for holidays',
                    'status' => false
                ], 422);
            }
        }

        $branch_id = $request->branch_id;
        $existingDate = $holidays->pluck('date')->toArray();
        Holiday::where('branch_id', $branch_id)->whereNotIn('date', $existingDate)->delete();

        foreach ($holidays as $key => $value) {
            $holiday = [
                'title' => $value['title'],
                'date' => $value['date'],
                'branch_id' => $branch_id,
            ];
            Holiday::updateOrCreate($holiday, $holiday);
        }

        $message = __('messages.holiday_update');

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function isHoliday(Request $request)
    {
        $branch_id = $request->branch_id;
    
        $isHoliday = Holiday::where('branch_id', $branch_id)->get();
    
        $businessHours = BussinessHour::where('branch_id', $branch_id)->get();
    
        $holidayDays = $businessHours->where('is_holiday', 1)->pluck('day')->toArray();
    
        return response()->json([
            'isHoliday' => $isHoliday,
            'holidayDays' => $holidayDays,
            'status' => true,
        ]);
    }
    
}
