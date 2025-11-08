<?php

namespace Modules\VendorWebsite\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Address;
use Modules\World\Models\City;
use Modules\World\Models\Country;
use Modules\World\Models\State;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Modules\Bank\Models\Bank;
use Modules\Package\Models\UserPackage;
use Modules\Product\Models\Order;
use Modules\VendorWebsite\Models\Inquiry;

class UserController extends Controller
{
    public function Profile(Request $request)
    {
        $user = auth()->user();
        return view('vendorwebsite::profile', compact('user'));
    }
    public function markasread()
    {

        auth()->user()->unreadNotifications->markAsRead();
        return back();
    }
    public function updateProfile(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'mobile' => 'required|string|max:20',
            'gender' => 'required|in:male,female,other',
        ]);

        try {
            $user = auth()->user();
            // Debug log

            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'gender' => $request->gender,
            ]);

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $user->clearMediaCollection('profile_image');
                $user->addMediaFromRequest('profile_image')
                    ->toMediaCollection('profile_image');
            }

            if ($request->has('delete_profile_image') && $request->delete_profile_image == '1') {
                $user->clearMediaCollection('profile_image');
            }

            // Get updated profile image URL
            $profileImageUrl = $user->getFirstMediaUrl('profile_image');
            if (empty($profileImageUrl)) {
                $profileImageUrl = asset(default_user_avatar());
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'gender' => $user->gender,
                    'profile_image' => $profileImageUrl,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    public function Refferal(Request $request)
    {
        return view('vendorwebsite::refferal');
    }

    public function Profilemembership(Request $request)
    {
        return view('vendorwebsite::profilemembership');
    }

    public function Changepassword(Request $request)
    {
        return view('vendorwebsite::changepassword');
    }

    public function Product(Request $request)
    {
        return view('vendorwebsite::product');
    }

    public function Wallet(Request $request)
    {
        $banks = Bank::where('user_id', auth()->id())->where('status', 1)->get();

        return view('vendorwebsite::wallet', compact('banks'));
    }

    public function Myorder(Request $request)
    {
        return view('vendorwebsite::myorder', ['pageTitle' => 'My Order']);
    }

    public function Profilepackage(Request $request)
    {
        $activePackage = UserPackage::with([
            'package' => function ($query) {
                $query->with(['branch']);
            },
            'userPackageServices' => function ($query) {
                $query->with([
                    'packageService' => function ($q) {
                        $q->with('services');
                    }
                ]);
            },
            'bookingTransaction'
        ])
            ->where('user_id', 2)
            ->whereHas('package', function ($query) {
                $query->where('end_date', '>=', date('Y-m-d H:i:s'))
                    ->where('status', 1);
            })
            ->whereHas('bookingTransaction', function ($query) {
                $query->where('payment_status', 1);
            })
            ->latest()
            ->first();

        $columns = [
            [
                'data' => 'package_name',
                'name' => 'package_name',
                'title' => 'Plan Name',
                'width' => '20%'
            ],
            [
                'data' => 'price',
                'name' => 'price',
                'title' => 'Prices',
                'width' => '15%'
            ],
            [
                'data' => 'duration',
                'name' => 'duration',
                'title' => 'Duration',
                'width' => '10%'
            ],
            [
                'data' => 'start_date',
                'name' => 'start_date',
                'title' => 'Start Date',
                'width' => '15%'
            ],
            [
                'data' => 'end_date',
                'name' => 'end_date',
                'title' => 'End Date',
                'width' => '15%'
            ],
            [
                'data' => 'payment_mode',
                'name' => 'payment_mode',
                'title' => 'Payment Mode',
                'width' => '15%'
            ],
            [
                'data' => 'action',
                'name' => 'action',
                'title' => 'Action',
                'width' => '10%',
                'orderable' => false,
                'searchable' => false
            ]
        ];

        return view('vendorwebsite::profilepackage', compact('activePackage', 'columns'));
    }

    /**
     * Get package data for DataTables.
     */
    public function packageData()
    {
        $query = UserPackage::with([
            'package' => function ($query) {
                $query->with(['branch']);
            },
            'userPackageServices' => function ($query) {
                $query->with([
                    'packageService' => function ($q) {
                        $q->with('services');
                    }
                ]);
            },
            'bookingTransaction'
        ])
            ->where('user_id', 2);

        return DataTables::of($query)
            ->addColumn('package_name', function ($data) {
                return $data->package->name;
            })
            ->addColumn('price', function ($data) {
                return \Currency::format($data->package_price);
            })
            ->addColumn('duration', function ($data) {
                $startDate = new \DateTime($data->package->start_date);
                $endDate = new \DateTime($data->package->end_date);
                return $startDate->diff($endDate)->days . ' Days';
            })
            ->addColumn('start_date', function ($data) {
                return (new \DateTime($data->package->start_date))->format('d M, Y');
            })
            ->addColumn('end_date', function ($data) {
                return (new \DateTime($data->package->end_date))->format('d M, Y');
            })
            ->addColumn('payment_mode', function ($data) {
                return $data->bookingTransaction->transaction_type ?? 'N/A';
            })
            ->addColumn('action', function ($data) {
                if ($data->bookingTransaction) {
                    return '<a href="' . route('user.invoice.download', $data->bookingTransaction->id) . '" class="download-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Download Invoice">
                        <i class="ph ph-download-simple font-size-18 icon-color"></i>
                    </a>';
                }
                return '';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function Address(Request $request)
    {
        // Get dropdown data
        $countries = Country::where('status', 1)->orderBy('name')->get();
        $states = State::where('status', 1)->orderBy('name')->get();
        $cities = City::where('status', 1)->orderBy('name')->get();

        return view('vendorwebsite::address', compact('countries', 'states', 'cities'));
    }

    public function addressData(Request $request)
    {
        $user = auth()->user();
        $query = Address::with(['city_data', 'state_data', 'country_data'])
            ->where('addressable_id', $user->id)
            ->where('addressable_type', 'App\Models\User')
            ->orderByDesc('is_primary');


        return DataTables::of($query)
            ->addColumn('card', function ($address) {
                $primaryBadge = $address->is_primary ? '<span class="badge bg-secondary rounded-pill px-3 py-2">Primary</span>' : '';

                $nameText = $address->first_name . ' ' . $address->last_name;
                $addressText = $address->address_line_1;
                if ($address->address_line_2) {
                    $addressText .= ', ' . $address->address_line_2;
                }
                $addressText .= ', ' . ($address->city_data?->name ?? 'N/A') . ', ' .
                    ($address->state_data?->name ?? 'N/A') . ', ' .
                    ($address->country_data?->name ?? 'N/A') . ' - ' . $address->postal_code;

                $actions = '<div class="d-flex align-items-center gap-2 gap-md-4 flex-wrap flex-md-nowrap fw-medium">';
                $actions .= '<a href="#" class="text-success font-size-18 edit-address" data-id="' . $address->id . '"';
                $actions .= ' data-first_name="' . $address->first_name . '" data-last_name="' . $address->last_name . '"';
                $actions .= ' data-country="' . $address->country . '" data-state="' . $address->state . '"';
                $actions .= ' data-city="' . $address->city . '" data-pin_code="' . $address->postal_code . '"';
                $actions .= ' data-address="' . $address->address_line_1 . '" data-is_primary="' . $address->is_primary . '"';
                $actions .= ' data-email="' . ($address->email ?? '') . '" data-contact_number="' . ($address->contact_number ?? '') . '"';
                $actions .= ' data-bs-toggle="modal" data-bs-target="#editAddressModal">';
                $actions .= '<i class="ph ph-pencil-line"></i></a>';


                $actions .= '<form action="' . route('frontend.address.delete', $address->id) . '" method="POST">';
                $actions .= csrf_field() . method_field('DELETE');
                $actions .= '<button type="submit" class="text-danger font-size-18 border-0 bg-transparent p-0 delete-address-btn  cursor-pointer">';
                $actions .= '<i class="ph ph-trash"></i></button></form>';

                if (!$address->is_primary) {
                    $actions .= '<a href="' . route('frontend.address.set-primary', $address->id) . '" class="btn btn-link border-0">Set As Primary</a>';
                }
                $actions .= '</div>';

                return '<div class="address-card bg-gray-800 rounded mb-3">' .
                    '<div class="address-details d-flex flex-column flex-md-row justify-content-between align-items-start gap-2 mb-3">' .
                    '<div class="address-text">' .
                    '<p class="mb-1 fw-bold text-muted">' . $nameText . '</p>' .
                    '<p class="mb-0 fw-medium text-muted">' . $addressText . '</p>' .
                    '</div>' .
                    ($primaryBadge ? '<div>' . $primaryBadge . '</div>' : '') .
                    '</div>' . $actions . '</div>';
            })
            ->addColumn('name', function ($address) {
                return $address->first_name . ' ' . $address->last_name;
            })

            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $search = $request->input('search')['value']) {
                    $query->where(function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('address_line_1', 'like', "%{$search}%")
                            ->orWhere('postal_code', 'like', "%{$search}%")
                            ->orWhereHas('city_data', function ($q2) use ($search) {
                                $q2->where('name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('state_data', function ($q2) use ($search) {
                                $q2->where('name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('country_data', function ($q2) use ($search) {
                                $q2->where('name', 'like', "%{$search}%");
                            });
                    });
                }
            })
            ->rawColumns(['card'])
            ->make(true);
    }

    public function storeAddress(Request $request)
    {



        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'contact_number' => 'required',
            'country' => 'required|exists:countries,id',
            'state' => 'required|exists:states,id',
            'city' => 'required|exists:cities,id',
            'pin_code' => ['required', 'digits:7'],
            'address' => 'required|string|max:500',
            'addressable_type' => null, // <--- ADD THIS LINE
            'addressable_id' => null,
        ]);

        $user = auth()->user();

        // If setting as primary, remove primary from other addresses of this user
        if ($request->has('set_as_primary')) {
            Address::where('addressable_id', $user->id)
                ->where('addressable_type', get_class($user))
                ->where('is_primary', 1)
                ->update(['is_primary' => 0]);
        }

        $address = new Address([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'postal_code' => $request->pin_code,
            'address_line_1' => $request->address,
            'is_primary' => $request->has('set_as_primary') ? 1 : 0,
        ]);

        $user->addresses()->save($address);

        return redirect()->back()->with('success', 'Address added successfully!');
    }

    public function updateAddress(Request $request, $id)
    {

        try {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'contact_number' => 'required',
                'country' => 'required|exists:countries,id',
                'state' => 'required|exists:states,id',
                'city' => 'required|exists:cities,id',
                'pin_code' => 'required|string|max:7',
                'address' => 'required|string|max:500'
            ]);

            // Find address and check ownership
            $user = auth()->user();
            $address = Address::where('id', $id)
                ->where('addressable_id', $user->id)->where('addressable_type', 'App\Models\User')
                ->firstOrFail();

            // Handle primary address logic
            if ($request->has('set_as_primary') && $request->set_as_primary) {
                // Remove primary status from all other addresses for this user
                Address::where('addressable_id', $user->id)
                    ->where('addressable_type', get_class($user))
                    ->where('is_primary', 1)
                    ->where('id', '!=', $id)
                    ->update(['is_primary' => 0]);
                $isPrimary = 1;
            } else {
                $isPrimary = 0;
            }

            // Update address
            $address->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'country' => $request->country,
                'email' => $request->email,
                'contact_number' => $request->contact_number,
                'state' => $request->state,
                'city' => $request->city,
                'postal_code' => $request->pin_code,
                'address_line_1' => $request->address,
                'is_primary' => $isPrimary,
            ]);

            return redirect()->back()->with('success', 'Address updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Address not found!');
        } catch (\Exception $e) {

            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function deleteAddress($id)
    {
        $user = auth()->user();

        $address = Address::where('id', $id)
            ->where('addressable_id', $user->id)
            ->where('addressable_type', get_class($user))
            ->firstOrFail();

        if ($address->is_primary) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Primary address cannot be deleted.']);
            }

            return redirect()->back()->withErrors(['error' => 'Primary address cannot be deleted.']);
        }

        $address->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Address deleted successfully!']);
        }

        return redirect()->back()->with('success', 'Address deleted successfully!');
    }


    public function setPrimaryAddress($id)
    {
        $user = auth()->user();

        // Remove primary from all addresses of this user
        Address::where('addressable_id', $user->id)
            ->where('addressable_type', get_class($user))
            ->where('is_primary', 1)
            ->update(['is_primary' => 0]);

        // Set the selected address as primary
        $address = Address::where('id', $id)
            ->where('addressable_id', $user->id)
            ->where('addressable_type', get_class($user))
            ->firstOrFail();
        $address->update(['is_primary' => 1]);

        return redirect()->back()->with('success', 'Primary address updated successfully!');
    }

    /**
     * Get all countries for dropdown
     */
    public function getCountries()
    {
        try {
            $countries = Country::where('status', 1)
                ->select('id', 'name')
                ->orderBy('name', 'asc')
                ->get();

            return response()->json($countries);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load countries'], 500);
        }
    }

    public function getStates(Request $request)
    {
        $countryId = $request->get('country_id');

        if (!$countryId) {
            return response()->json(['error' => 'Country ID is required'], 400);
        }

        $states = State::where('country_id', $countryId)
            ->where('status', 1)
            ->select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json($states);
    }

    public function getCities(Request $request)
    {
        $cities = City::where('state_id', $request->state_id)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        return response()->json($cities);
    }

    public function getAddress($id)
    {
        try {
            $user = auth()->user();
            $address = Address::where('id', $id)
                ->where('addressable_id', $user->id)
                ->where('addressable_type', get_class($user))
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
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve address'
            ]);
        }
    }

    public function Wishlist(Request $request)
    {
        return view('vendorwebsite::wishlist');
    }

    public function BankList(Request $request)
    {
        $banks = Bank::all();
        return view('vendorwebsite::bank_list', compact('banks'));
    }

    public function storeInquiry(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required',
        ]);

        $inquiry = new Inquiry();
        $inquiry->name = $request->name;
        $inquiry->email = $request->email;
        $inquiry->subject = $request->subject;
        $inquiry->message = $request->message;
        $inquiry->vendor_id = session('current_vendor_id');
        $inquiry->save();

        return redirect()->back()->with('success', 'Inquiry submitted successfully!');
    }

    public function MyorderData(Request $request)
    {
        $orders = Order::with('orderItems')->where('user_id', auth()->id())->orderBy('id', 'desc');

        $orders = $orders->whereHas('orderItems', function ($q) {
            $q->whereHas('product_variation', function ($qry) {
                $qry->whereHas('product', function ($query) {
                    $query->where('created_by', session('current_vendor_id'));
                });
            });
        });


        if ($request->status) {
            $orders->where('delivery_status', $request->status);
        }
        // Add search filter for order id, product name, delivery status, payment status, and created_at
        $searchValue = $request->input('search.value');
        if (is_array($searchValue)) {
            $searchValue = $searchValue[0] ?? '';
        }

        if (!$searchValue) {
            $searchValue = $request->search;
        }

        if ($searchValue && is_string($searchValue)) {
            $orders->where(function ($q) use ($searchValue) {
                $q->where('id', 'like', "%{$searchValue}%")
                    ->orWhere('delivery_status', 'like', "%{$searchValue}%")
                    ->orWhere('payment_status', 'like', "%{$searchValue}%")
                    ->orWhereDate('created_at', $searchValue)
                    ->orWhereHas('orderItems.product_variation.product', function ($q2) use ($searchValue) {
                        $q2->where('name', 'like', "%{$searchValue}%");
                    });
            });
        }
        return DataTables::of($orders)
            ->addColumn('card', function ($order) {
                return view('vendorwebsite::components.card.myorder_card', compact('order'))->render();
            })
            ->addColumn('details', function ($order) {
                return $order->id . ' ' . $order->delivery_status . ' ' . $order->payment_status;
            })
            ->rawColumns(['card'])
            ->make(true);
    }

    public function userNotifications()
    {
        $module_name = 'notifications';
        $module_name_singular = 'notification';

        $user = auth()->user();

        if (count($user->unreadNotifications) > 0) {
            $user->unreadNotifications->markAsRead();
        }
        $perPage = request('per_page', 10);
        $$module_name = auth()->user()->notifications()->paginate($perPage);
        $unread_notifications_count = auth()->user()->unreadNotifications()->count();

        $notifications_count = 0;

        return view(
            "vendorwebsite::notification_list",
            compact('module_name', "$module_name", 'module_name_singular', 'unread_notifications_count', 'notifications_count')
        );
    }

    public function userNotifications_indexData(Request $request)
    {
        $module_name = 'notifications';
        $module_name_singular = 'notification';

        $user = auth()->user();

        if ($user->unreadNotifications->count() > 0) {
            $user->unreadNotifications->markAsRead();
        }

        $notifications = $user->notifications()->get();
        $unread_notifications_count = $user->unreadNotifications->count();
        $notifications_count = $notifications->count(); // Total count of all notifications
        // dd($notifications);

        return DataTables::of($notifications)
            ->addColumn('card', function ($notification) use (
                $module_name,
                $module_name_singular,
                $unread_notifications_count,
                $notifications_count,
            ) {
                return view('vendorwebsite::components.card.notification_card', compact(
                    'module_name',
                    'module_name_singular',
                    'notification',
                    'unread_notifications_count',
                    'notifications_count'
                ))->render();
            })
            ->rawColumns(['card'])
            ->make(true);
    }
}
