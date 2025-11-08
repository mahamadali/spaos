@extends('vendorwebsite::layouts.master')
@section('title') {{__('vendorwebsite.booking_details')}} @endsection

@section('content')
<div class="section-spacing-inner-pages">
    <div class="container">
        <!-- <a href="{{ route('booking.invoice.download', $booking->id) }}" class="btn btn-primary mb-3">Download Invoice</a> -->
        <x-bookingdetails_section :booking="$booking" :employee-review="$employeeReview"/>
    </div>
</div>

@endsection