<?php

namespace App\Http\Controllers\Backend\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Models\User;
use Modules\World\Models\State;
use Modules\World\Models\City;
use Auth;
use Illuminate\Http\Request;
class AddressController extends Controller
{
    public function AddressList(Request $request)
    {
        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['message' => 'User not found', 'data' => [], 'status' => false], 200);
        }

        // Only fetch addresses for the selected user
        $user_addresses = Address::where('addressable_type', 'App\Models\User')
            ->where('addressable_id', $userId)
            ->get();

        $addressCollection = AddressResource::collection($user_addresses);
        $message = __('users.address_list');

        return response()->json(['message' => $message, 'data' => $addressCollection, 'status' => true], 200);
    }

    public function store(Request $request)
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


    public function RemoveAddress(Request $request)
    {
        $address_id = $request->id;

        $address = Address::where('id', $address_id)->first();

        if (! $address) {
            $message = __('users.address_not_found');

            return response()->json(['message' => $message, 'status' => true], 200);
        }

        $address->delete();

        $message = __('users.address_deleted');

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function EditAddress(Request $request)
    {
        $address_id = $request->id;

        $address = Address::find($address_id);

        if (! $address) {
            $message = __('users.address_not_found');

            return response()->json(['message' => $message, 'status' => true], 404);
        }

        $user_id = $request->input('user_id') ?? Auth::id();

        $user = User::where('id', $user_id)->first();

        if ($request->has('is_primary') && $request->is_primary == 1) {
            $user->addresses()->where('id', '!=', $address_id)->update(['is_primary' => 0]);
        }

        $address->update([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'address_line_1' => $request->input('address_line_1'),
            'address_line_2' => $request->input('address_line_2'),
            'postal_code' => $request->input('postal_code'),
            'city' => $request->input('city'),
            'state' => $request->input('state'),
            'country' => $request->input('country'),
            'is_primary' => $request->is_primary,
        ]);

        $message = __('users.address_updated');

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    /**
     * Get states based on country selection
     */
    public function getStates(Request $request)
    {
        $countryId = $request->input('country_id');
        
        if (!$countryId) {
            return response()->json(['message' => 'Country ID is required', 'data' => [], 'status' => false], 400);
        }

        $states = State::where('country_id', $countryId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json(['message' => 'States fetched successfully', 'data' => $states, 'status' => true], 200);
    }

    /**
     * Get cities based on state selection
     */
    public function getCities(Request $request)
    {
        $stateId = $request->input('state_id');
        
        if (!$stateId) {
            return response()->json(['message' => 'State ID is required', 'data' => [], 'status' => false], 400);
        }

        $cities = City::where('state_id', $stateId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json(['message' => 'Cities fetched successfully', 'data' => $cities, 'status' => true], 200);
    }
}
