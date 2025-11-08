<?php

namespace App\Models;

use App\Models\Presenters\UserPresenter;
use App\Models\Traits\HasHashedMediaTrait;
use App\Trait\CustomFieldsTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingService;
use Modules\Commission\Models\CommissionEarning;
use Modules\Commission\Models\EmployeeCommission;
use Modules\Earning\Models\EmployeeEarning;
use Modules\Employee\Models\BranchEmployee;
use Modules\Employee\Models\EmployeeRating;
use Modules\Product\Models\Product;
use Modules\Package\Models\BookingPackages;
use Modules\Service\Models\Service;
use Modules\Service\Models\ServiceEmployee;
use Modules\Subscriptions\Models\Subscription;
use Modules\Tip\Models\TipEarning;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use CustomFieldsTrait;
    use HasApiTokens;
    use HasFactory;
    use HasHashedMediaTrait;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;
    use UserPresenter;

    const CUSTOM_FIELD_MODEL = 'App\Models\User';

    protected $guarded = [
        'id',
        'updated_at',
        '_token',
        '_method',
        'password_confirmation',
    ];

    protected $dates = [
        'deleted_at',
        'date_of_birth',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'user_setting' => 'array',
    ];

    protected $appends = ['full_name', 'profile_image'];

    public function getFullNameAttribute() // notice that the attribute name is in CamelCase.
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function providers()
    {
        return $this->hasMany('App\Models\UserProvider');
    }

    /**
     * Get the list of users related to the current User.
     *
     * @return [array] roels
     */
    public function getRolesListAttribute()
    {
        return array_map('intval', $this->roles->pluck('id')->toArray());
    }

    /**
     * Route notifications for the Slack channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForSlack($notification)
    {
        return env('SLACK_NOTIFICATION_WEBHOOK');
    }

    /**
     * Get all of the branches for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptionPackage()
    {
        return $this->hasOne(Subscription::class, 'user_id', 'id')->where('status', config('constant.SUBSCRIPTION_STATUS.ACTIVE'));
    }

    public function address()
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1)->where('is_banned', 0);
    }

    public function booking()
    {
        return $this->hasMany(Booking::class, 'user_id', 'id');
    }

    public function scopeCalenderResource($query)
    {
        $query->where('show_in_calender', 1);
    }

    protected function getProfileImageAttribute()
    {
        $media = $this->getFirstMediaUrl('profile_image');

        return isset($media) && !empty($media) ? $media : asset('img/vendorwebsite/user_image.png');
    }

    // Employee Relations
    public function commission_earning()
    {
        return $this->hasMany(CommissionEarning::class, 'employee_id');
    }

    public function tip_earning()
    {
        return $this->hasMany(TipEarning::class, 'employee_id');
    }

    public function branches()
    {
        return $this->hasMany(BranchEmployee::class, 'employee_id');
    }

    public function mainBranches()
    {
        return $this->hasMany(Branch::class, 'created_by')->whereNull('deleted_at');
    }

    public function branch()
    {
        return $this->hasOne(BranchEmployee::class, 'employee_id')->with('getBranch');
    }

    public function mainBranch()
    {
        return $this->hasManyThrough(Branch::class, BranchEmployee::class, 'employee_id', 'id', 'id', 'branch_id');
    }

    public function mainServices()
    {
        return $this->hasMany(Service::class, 'created_by')->whereNull('deleted_at');
    }

    public function services()
    {
        return $this->hasMany(ServiceEmployee::class, 'employee_id');
    }

    public function employeeBooking()
    {
        return $this->hasMany(BookingService::class, 'employee_id')
            ->whereHas('booking', function ($query) {
                $query->where('status', 'completed');
            });
    }
    public function bookingPackages()
    {
        return $this->hasMany(BookingPackages::class, 'employee_id')
            ->whereHas('booking', function ($query) {
                $query->where('status', 'completed');
            });
    }
    public function employeeEarnings()
    {
        return $this->hasMany(EmployeeEarning::class, 'employee_id');
    }

    public function commissions()
    {
        return $this->hasMany(EmployeeCommission::class, 'employee_id')->with('mainCommission');
    }

    public function wishlist()
    {
        return $this->belongsToMany(Product::class, 'wishlist', 'user_id', 'product_id');
    }

    public function wallet()
    {
        return $this->hasOne(\Modules\Wallet\Models\Wallet::class, 'user_id');
    }

    /**
     * Get or create wallet for user
     */
    public function getOrCreateWallet()
    {
        if (!$this->wallet) {
            $this->wallet()->create([
                'title' => 'Main Wallet',
                'amount' => 0,
                'status' => 1
            ]);
            $this->load('wallet'); // Reload the relationship
        }
        return $this->wallet;
    }

    /**
     * Get wallet balance
     */
    public function getWalletBalance()
    {
        $wallet = $this->getOrCreateWallet();
        return $wallet ? $wallet->amount : 0;
    }

    public function scopeEmployee($query)
    {
        $query->role('employee');
    }

    public function scopeBranch($query)
    {
        $branch_id = request()->selected_session_branch_id;

        if (isset($branch_id) &&  $branch_id  != 0) {

            $query->join('branch_employee', 'users.id', '=', 'branch_employee.employee_id')
                ->where('branch_employee.branch_id', $branch_id);
        }
    }

    public function scopeVarified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeBookingEmployeesList($query)
    {
        return $query->select('users.*')
            ->active()
            ->varified()
            ->calenderResource()->employee()->branch()->orderBy('id', 'ASC');
    }

    public function rating()
    {
        return $this->hasMany(EmployeeRating::class, 'employee_id', 'id')->orderBy('updated_at', 'desc');
    }

    public function bookings()
    {
        return $this->hasManyThrough(Booking::class, BookingService::class, 'booking_id', 'id', 'id', 'employee_id');
    }



    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function customers()
    {
        return $this->hasMany(User::class, 'created_by')->role('user');
    }
    public function staff()
    {
        return $this->hasMany(User::class, 'created_by')->role(['employee', 'manager']);
    }

    // Report Query
    public static function staffReport()
    {
        return self::role(['manager', 'employee'])->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.mobile', 'users.updated_at')
            ->withCount('employeeBooking', 'bookingPackages')
            ->withSum('employeeBooking', 'service_price')
            ->withSum('bookingPackages', 'package_price')
            ->withSum('commission_earning', 'commission_amount')
            ->withSum('tip_earning', 'tip_amount');
    }

    /**
     * Staff report with commission status filtering and completed bookings only
     */
    public static function staffReportWithCommissionStatus($commissionStatus = null)
    {
        // Get vendor/admin ID for branch filtering
        // Note: The controller already filters users by mainBranch, so we only need to filter bookings here
        $vendorId = auth()->check() && auth()->user()->hasRole('admin') ? auth()->id() : null;
        
        \Log::info('🔍 staffReportWithCommissionStatus Called', [
            'auth_check' => auth()->check(),
            'vendor_id' => $vendorId,
            'is_admin' => auth()->check() ? auth()->user()->hasRole('admin') : false,
            'user_id' => auth()->check() ? auth()->id() : null,
        ]);
        
        // Debug: Check if there are any bookings at all (without branch filter)
        if ($vendorId) {
            $testBookingCount = \DB::table('booking_services')
                ->join('bookings', 'booking_services.booking_id', '=', 'bookings.id')
                ->where('bookings.status', 'completed')
                ->where('booking_services.employee_id', '>', 0)
                ->count();
            $testBookingWithBranchCount = \DB::table('booking_services')
                ->join('bookings', 'booking_services.booking_id', '=', 'bookings.id')
                ->join('branches', 'bookings.branch_id', '=', 'branches.id')
                ->where('bookings.status', 'completed')
                ->where('branches.created_by', $vendorId)
                ->where('booking_services.employee_id', '>', 0)
                ->count();
            // Get actual employee IDs from bookings
            $testBookings = \DB::table('booking_services')
                ->join('bookings', 'booking_services.booking_id', '=', 'bookings.id')
                ->join('branches', 'bookings.branch_id', '=', 'branches.id')
                ->where('bookings.status', 'completed')
                ->where('branches.created_by', $vendorId)
                ->select('booking_services.employee_id', 'booking_services.service_price', 'bookings.id as booking_id')
                ->get();
            
            // Test the raw subquery for a specific user
            if ($testBookings->isNotEmpty()) {
                $testEmployeeId = $testBookings->first()->employee_id;
                $testRawCount = \DB::selectOne("
                    SELECT COUNT(*) as count 
                    FROM booking_services 
                    INNER JOIN bookings ON booking_services.booking_id = bookings.id 
                    INNER JOIN branches ON bookings.branch_id = branches.id 
                    WHERE booking_services.employee_id = ?
                    AND bookings.status = 'completed'
                    AND branches.created_by = ?
                ", [$testEmployeeId, $vendorId]);
                \Log::info('🔍 Raw Subquery Test', [
                    'test_employee_id' => $testEmployeeId,
                    'raw_count_result' => $testRawCount->count ?? 0,
                ]);
            }
            
            \Log::info('🔍 Booking Debug Counts', [
                'total_completed_bookings' => $testBookingCount,
                'completed_bookings_with_vendor_branch' => $testBookingWithBranchCount,
                'actual_bookings' => $testBookings->toArray(),
            ]);
        }
        
        $query = self::role(['manager', 'employee'])
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.mobile', 'users.updated_at');
        
        // Add counts with branch filtering if vendor is logged in
        if ($vendorId) {
            // Use raw subqueries to count bookings with branch filtering
            // Note: Using DB::raw() to properly escape and bind parameters
            $query->selectRaw('(
                SELECT COUNT(*) 
                FROM booking_services 
                INNER JOIN bookings ON booking_services.booking_id = bookings.id 
                INNER JOIN branches ON bookings.branch_id = branches.id 
                WHERE booking_services.employee_id = users.id 
                AND bookings.status = ?
                AND branches.created_by = ?
            ) as employee_booking_count', ['completed', $vendorId])
            ->selectRaw('(
                SELECT COUNT(*) 
                FROM booking_packages 
                INNER JOIN bookings ON booking_packages.booking_id = bookings.id 
                INNER JOIN branches ON bookings.branch_id = branches.id 
                WHERE booking_packages.employee_id = users.id 
                AND bookings.status = ?
                AND branches.created_by = ?
            ) as booking_packages_count', ['completed', $vendorId])
            ->selectRaw('(
                SELECT COALESCE(SUM(booking_services.service_price), 0) 
                FROM booking_services 
                INNER JOIN bookings ON booking_services.booking_id = bookings.id 
                INNER JOIN branches ON bookings.branch_id = branches.id 
                WHERE booking_services.employee_id = users.id 
                AND bookings.status = ?
                AND branches.created_by = ?
            ) as employee_booking_sum_service_price', ['completed', $vendorId])
            ->selectRaw('(
                SELECT COALESCE(SUM(booking_packages.package_price), 0) 
                FROM booking_packages 
                INNER JOIN bookings ON booking_packages.booking_id = bookings.id 
                INNER JOIN branches ON bookings.branch_id = branches.id 
                WHERE booking_packages.employee_id = users.id 
                AND bookings.status = ?
                AND branches.created_by = ?
            ) as booking_packages_sum_package_price', ['completed', $vendorId]);
            
            // Debug: Log the SQL for user 31 if it exists
            $testUserQuery = clone $query;
            $testUser31 = $testUserQuery->where('users.id', 31)->first();
            if ($testUser31) {
                \Log::info('🔍 Test User 31 Query Result', [
                    'user_id' => $testUser31->id ?? null,
                    'employee_booking_count' => $testUser31->employee_booking_count ?? null,
                    'employee_booking_sum_service_price' => $testUser31->employee_booking_sum_service_price ?? null,
                ]);
            }
        } else {
            // When no vendor/admin, use the default relationship filters (completed bookings only)
            $query->withCount(['employeeBooking', 'bookingPackages'])
                ->withSum('employeeBooking', 'service_price')
                ->withSum('bookingPackages', 'package_price');
        }
        
        $query->withSum('tip_earning', 'tip_amount');

        // Add commission earnings with status filtering
        if ($commissionStatus) {
            $query->withSum([
                'commission_earning' => function ($query) use ($commissionStatus, $vendorId) {
                    $query->where('commission_status', $commissionStatus)
                        ->whereHas('getbooking', function ($bookingQuery) use ($vendorId) {
                            $bookingQuery->where('status', 'completed');
                            // Filter by branch if vendor/admin is logged in
                            if ($vendorId) {
                                $bookingQuery->whereHas('branch', function ($branchQuery) use ($vendorId) {
                                    $branchQuery->where('created_by', $vendorId);
                                });
                            }
                        });
                }
            ], 'commission_amount');
        } else {
            $query->withSum([
                'commission_earning' => function ($query) use ($vendorId) {
                    $query->whereHas('getbooking', function ($bookingQuery) use ($vendorId) {
                        $bookingQuery->where('status', 'completed');
                        // Filter by branch if vendor/admin is logged in
                        if ($vendorId) {
                            $bookingQuery->whereHas('branch', function ($branchQuery) use ($vendorId) {
                                $branchQuery->where('created_by', $vendorId);
                            });
                        }
                    });
                }
            ], 'commission_amount');
        }

        return $query;
    }

    /**
     * Get staff commission earnings with specific status and completed bookings
     */
    public static function getStaffCommissionEarnings($commissionStatus = 'paid')
    {
        return self::role(['manager', 'employee'])
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.email')
            ->with([
                'commission_earning' => function ($query) use ($commissionStatus) {
                    $query->where('commission_status', $commissionStatus)
                        ->with(['getbooking' => function ($bookingQuery) {
                            $bookingQuery->where('status', 'completed')
                                ->select('id', 'status', 'start_date_time');
                        }])
                        ->select('id', 'employee_id', 'commissionable_id', 'commission_amount', 'commission_status', 'payment_date');
                }
            ])
            ->whereHas('commission_earning', function ($query) use ($commissionStatus) {
                $query->where('commission_status', $commissionStatus)
                    ->whereHas('getbooking', function ($bookingQuery) {
                        $bookingQuery->where('status', 'completed');
                    });
            });
    }

    /**
     * Get commission earnings summary by status for completed bookings
     */
    public function getCommissionSummary()
    {
        return $this->commission_earning()
            ->selectRaw('commission_status, COUNT(*) as count, SUM(commission_amount) as total_amount')
            ->whereHas('getbooking', function ($query) {
                $query->where('status', 'completed');
            })
            ->groupBy('commission_status')
            ->get();
    }

    public function scopeWithTotalUnpaidServiceAmount($query)
    {
        return $query->leftJoin('commission_earnings', 'users.id', '=', 'commission_earnings.employee_id')
            ->leftJoin('booking_services', 'booking_services.booking_id', '=', 'commission_earnings.commissionable_id')
            ->leftJoin('booking_packages', 'booking_packages.booking_id', '=', 'commission_earnings.commissionable_id')
            ->where('commission_earnings.commission_status', 'unpaid')
            ->selectRaw('users.id as user_id,
                         COALESCE(SUM(booking_services.service_price), 0) + COALESCE(SUM(booking_packages.package_price), 0) as total_service_amount')
            ->groupBy('users.id');
    }

    public function currentSubscription()
    {
        return $this->hasOne(Subscription::class)->where('status', 'active'); // Ensure the subscription is active
    }

    public function customerLimitReach()
    {
        return $this->customers->count() >= $this->currentSubscription->max_customer;
    }

    public function branchLimitReach()
    {
        return $this->mainBranches->count() >= $this->currentSubscription->max_branch;
    }

    /**
     * Update user slug while preserving the old slug
     *
     * @param string $newSlug
     * @return void
     */
    public function updateSlug($newSlug)
    {
        // Store current slug as old slug if it exists
        if ($this->slug) {
            $this->old_slug = $this->slug;
        }

        // Update with new slug
        $this->slug = $newSlug;
        $this->save();
    }

    /**
     * Generate slug from user's name
     *
     * @return string
     */
    public function generateSlug()
    {
        return \Illuminate\Support\Str::slug($this->first_name . ' ' . $this->last_name);
    }

    public function staffLimitReach()
    {
        return $this->staff->count() >= $this->currentSubscription->max_staff;
    }

    public function serviceLimitReach()
    {
        return $this->mainServices->where('created_at', '>=', $this->currentSubscription->start_date)->count() >= $this->currentSubscription->max_service;
    }
}
