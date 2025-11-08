<?php

namespace Modules\Booking\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

use Modules\Package\Models\BookingPackageService;
use Modules\Package\Models\UserPackageServices;



class BookingPackageResource extends JsonResource
{

    public function toArray($request)
    {

        $userPackages = $this->bookedUserPackage->filter(function ($userPackage) {
            return $userPackage->user_id == $this->user_id
            && $userPackage->package_id == $this->package_id;
        })->map(function ($userPackage) {
            return [
                'id' => $userPackage->id,
                'booking_id' => $userPackage->booking_id,
                'employee_id' => $userPackage->employee_id,
                'package_price' => $userPackage->package_price,
                'package_id' => $userPackage->package_id,
                'type'=>$userPackage->type,
                // Include other fields as needed
            ];
        });

        $packageServices = BookingPackageService::where('booking_package_id', $this->id)
        ->with('packageservice.services')
        ->get()
        ->map(function($service){
            $packageService = $service->packageservice ?? null;
            $serviceDetails = $packageService->services ?? null;

            return [
                'id' => $service->id,
                'service_id' => $packageService->service_id ?? '-',
                'service_name' => $packageService->service_name ?? '-',
                'duration_min' => $serviceDetails->duration_min ?? 0,
                'service_image' => $serviceDetails->feature_image ?? '-',
                'total_qty' => $packageService->qty ?? 0,
                'remaining_qty' => $service->qty ?? 0,
                'service_price' => $packageService->service_price ?? 0,
            ];
        });

        return [
            'id' => $this->id,
            'package_name' => $this->package->name,
            'start_date' => $this->package->start_date,
            'end_date' => $this->package->end_date,
            'description' => $this->package->description,
            'image'=> $this->package->feature_image,
            'branch_id' => $this->package->branch_id,
            'branch_name'=>$this->package->branch->name,
            'package_price' => $this->package->package_price,
            'is_reclaim' => $this->is_reclaim,
            'userPackage' => $userPackages,
            'services' => $packageServices,
        ];
    }
}
