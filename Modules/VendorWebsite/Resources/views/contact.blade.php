
@extends('vendorwebsite::layouts.master')
@section('title'){{__('vendorwebsite.contact_us')}} @endsection
@section('content')
<x-breadcrumb/>
<div class="contact-us-section section-spacing-inner-pages">
    <x-location_section/>
    <x-contact_banner_section :branch="$branch"/>
    @php
        $showLeaveSection = false;
        $vendorId = session('current_vendor_id');
        if ($vendorId) {
            $vendorUser = \App\Models\User::find($vendorId);
            if ($vendorUser && $vendorUser->can('view_inquiry')) {
                $showLeaveSection = true;
            }
        }
    @endphp
    @if($showLeaveSection)
    <x-leave_section/>
    @endif
</div>
@endsection