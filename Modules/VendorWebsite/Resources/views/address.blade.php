@extends('vendorwebsite::layouts.master')
@section('title') {{__('vendorwebsite.manage_address')}} @endsection

@section('content')

<x-breadcrumb />
<x-address_section
    :countries="$countries"
    :states="$states"
    :cities="$cities"
/>
@endsection 