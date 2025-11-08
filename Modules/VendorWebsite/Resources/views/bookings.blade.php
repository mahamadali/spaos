@extends('vendorwebsite::layouts.master')
@section('title') {{__('messages.booking')}} @endsection

@section('content')

<x-breadcrumb/>
<div class="section-spacing-inner-pages">
    <div class="container">
        <x-booking_section :bookings="$bookings" :allBookingsCount="$allBookingsCount" :upcomingBookingsCount="$upcomingBookingsCount" :completedBookingsCount="$completedBookingsCount"/>
    </div>
</div>

@endsection