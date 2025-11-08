<?php

namespace Modules\QuickBooking\Http\Controllers\Backend;

use App\Events\Backend\UserCreated;
use App\Http\Controllers\Controller;
use App\Models\Address;
// Traits
use App\Models\Branch;
use Modules\BussinessHour\Models\BussinessHour;
use Modules\Holiday\Models\Holiday;
// Listing Models
use App\Models\User;
use App\Notifications\UserAccountCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Booking\Models\Booking;
// Events
use Modules\Booking\Trait\BookingTrait;
use Modules\Service\Transformers\ServiceResource;
use Modules\Tax\Models\Tax;
use Carbon\Carbon;
use Modules\Service\Models\Service;
use Illuminate\Support\Facades\Crypt;
use App\Models\Setting;
use  Modules\Subscriptions\Models\Subscription;

class QuickBookingsController extends Controller
{
    use BookingTrait;

    public function index(Request $request)
    {

        $id = $request->query('id');

        $id = $this->base62_decode_with_random($id);

        $isQuickBooking = Setting::where('name', 'is_quick_booking')->where('created_by', $id)->value('val') ?? 0;

        if (!$isQuickBooking) {
            abort(404);
        }

        return view('quickbooking::backend.quickbookings.index', compact('id'));
    }

    private function base62_decode_with_random($encoded, $length = 7)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base = strlen($chars);

        // Remove the random suffix (last 2 characters) and any left padding '0's used during encoding
        $encodedBase62 = substr((string)$encoded, 0, $length);
        $encodedBase62 = ltrim($encodedBase62, '0');

        if ($encodedBase62 === '') {
            return 0;
        }

        // Decode the Base62 string
        $decoded = 0;
        $strlen = strlen($encodedBase62);
        for ($i = 0; $i < $strlen; $i++) {
            $pos = strpos($chars, $encodedBase62[$i]);
            if ($pos === false) {
                // Invalid character (e.g., unexpected padding) – treat as 0 contribution
                $pos = 0;
            }
            $decoded = $decoded * $base + $pos;
        }

        return $decoded;
    }

    public function branch_list(Request $request)
    {
        $userId = $request->user_id;

        if (empty($userId)) {
            return $this->sendResponse([], __('booking.booking_branch'));
        }

        $subscription = Subscription::where('user_id', $userId)
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->first();

        if (!$subscription) {
            return $this->sendResponse([], __('booking.booking_branch'));
        }

        // Only active branches should be listed for booking selection
        $branchesQuery = Branch::active()
            ->with(['address', 'address.country_data', 'address.state_data', 'address.city_data'])
            ->select('id', 'name', 'branch_for', 'contact_number', 'contact_email')
            ->where('created_by', $userId);

        $branchIds = $branchesQuery->pluck('id');

        $currentBookingCount = Booking::whereIn('branch_id', $branchIds)->count();

        if (
            $subscription->max_appointment !== null &&
            $currentBookingCount >= $subscription->max_appointment
        ) {
            return $this->sendResponse([], __('booking.booking_branch'));
        }

        $branches = $branchesQuery->get();

        return $this->sendResponse($branches, __('booking.booking_branch'));
    }


    public function slot_time_list(Request $request)
    {
        $day = date('l', strtotime($request->date));

        $data = $this->requestData($request);
        $businessHours = BussinessHour::where('branch_id', $data['branch_id'])->get();
        $service = Service::where('id', $data['service_id'])->first();
        $serviceDuration = $service->duration_min;

        $slots = $this->getSlots($data['date'], $day, $data['branch_id'], $serviceDuration, $data['employee_id']);

        return $this->sendResponse($slots, $businessHours, __('booking.booking_timeslot'));
    }


    public function slot_date_list(Request $request)
    {
        $data = $this->requestData($request);

        $businessHours = BussinessHour::where('branch_id', $data['branch_id'])->get();
        $holidays = Holiday::where('branch_id', $data['branch_id'])->get();
        $holidayDates = $holidays->map(function ($holiday) {
            return Carbon::parse($holiday->date)->format('Y-m-d');
        });

        return response()->json([
            'data' => $businessHours,
            'holidays' => $holidayDates,
        ]);
    }

    public function services_list(Request $request)
    {
        $branch_id = $request->branch_id ?? session('selected_branch_id');

        $data = $this->requestData($request);


        $items = Service::with(['branches' => function ($query) use ($branch_id) {
            $query->where('branch_id', $branch_id)->limit(1);
        }])
        ->whereHas('branches', function ($query) use ($branch_id) {
            $query->where('branch_id', $branch_id);
        })
        ->where('status', 1)
        ->get();

        //$list = ServiceResource::collection($items);

        return response()->json([
            'success' => true,
            'data'    => $items,  // The resource collection is directly returned
            'message' => __('booking.booking_sevice'),
        ], 200);
    }

    public function employee_list(Request $request)
    {
        $data = $this->requestData($request);

        $list = User::whereHas('services', function ($query) use ($data) {
            $query->where('service_id', $data['service_id']);
        })
            ->whereHas('branches', function ($query) use ($data) {
                $query->where('branch_id', $data['branch_id']);
            })
            ->get();

        return $this->sendResponse($list, __('booking.booking_employee'));
    }

    public function experts_list(Request $request)
    {
        $branchId = $request->query('branch_id');
        $serviceId = $request->query('service_id');

        if (!$branchId || !$serviceId) {
            return response()->json([
                'success' => false,
                'message' => 'Branch ID and Service ID are required',
                'experts' => []
            ], 400);
        }

        $experts = User::role('employee')
            ->whereHas('branches', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->whereHas('services', function ($q) use ($serviceId) {
                $q->where('service_id', $serviceId);
            })
            ->with('rating')
            ->get()
            ->map(function ($user) {
                // Calculate average rating from the 'rating' relationship
                $avgRating = 0;
                if ($user->rating && $user->rating->count() > 0) {
                    $avgRating = round($user->rating->avg('rating'), 1);
                }
                return [
                    'id' => $user->id,
                    'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                    'full_name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                    'image_path' => $user->profile_image,
                    'profile_image' => $user->profile_image,
                    'rating' => $avgRating,
                    'speciality' => $user->speciality ?? '',
                ];
            });

        return response()->json([
            'success' => true,
            'experts' => $experts,
            'message' => 'Experts list retrieved successfully'
        ]);
    }

    // Create Method for Booking API
    public function create_booking(Request $request)
    {
        $userRequest = $request->user;
        $user = User::where('email', $userRequest['email'])->first();

        if (! isset($user)) {
            $userRequest['password'] = Hash::make('12345678');
            $userRequest['user_type'] = 'user';
            $user = User::create($userRequest);
            // Sync Roles
            $roles = ['user'];
            $user->syncRoles($roles);

            \Artisan::call('cache:clear');

            event(new UserCreated($user));

            $data = [
                'password' => '12345678',
            ];

            try {
                $user->notify(new UserAccountCreated($data));
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
            }
        }

        $bookingData = $request->booking;
        $bookingData['user_id'] = $user->id;
        $bookingData['created_by'] = $user->id;
        $bookingData['updated_by'] = $user->id;
        $booking = Booking::create($bookingData);

        $this->updateBookingService($bookingData['services'], $booking->id);

        $booking['user'] = $booking->user;

        $booking['services'] = $booking->services;

        $booking['branch'] = $booking->branch;

        $branchAddress = Address::where('addressable_id', $booking['branch']->id)
            ->where('addressable_type', get_class($booking['branch']))
            ->with(['city_data', 'state_data', 'country_data'])
            ->first();

        if ($branchAddress) {
            $fullAddress = $branchAddress->address_line_1 . ', ' .
                optional($branchAddress->city_data)->name . ', ' .
                optional($branchAddress->state_data)->name . ' ' .
                optional($branchAddress->country_data)->name . ', ' .
                $branchAddress->postal_code;

            $booking['branch_address'] = $fullAddress;
        }

        try {
            $notify_type = 'new_booking';
            $messageTemplate = 'New booking #[[booking_id]] has been booked.';
            $notify_message = str_replace('[[booking_id]]', $booking->id, $messageTemplate);
            $this->sendNotificationOnBookingUpdate($notify_type, $notify_message, $booking);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }



        $branch_detail = Branch::where('id', $bookingData['branch_id'])->first();

        $booking['tax'] = Tax::active()
            ->whereNull('module_type')
            ->orWhere('module_type', 'services')
            ->where('status', 1)
            ->where('created_by', $branch_detail->created_by)
            ->get()
            ->map(function ($tax) {
                return [
                    'name' => $tax->title,
                    'type' => $tax->type,
                    'percent' => $tax->type == 'percent' ? $tax->value : 0,
                    'tax_amount' => $tax->type != 'percent' ? $tax->value : 0,
                ];
            })
            ->toArray();

        return $this->sendResponse($booking, __('booking.booking_create'));
    }

    public function requestData($request)
    {
        return [
            'branch_id' => $request->branch_id,
            'service_id' => $request->service_id,
            'date' => $request->date,
            'employee_id' => $request->employee_id,
            'start_date_time' => $request->start_date_time,
        ];
    }
}
