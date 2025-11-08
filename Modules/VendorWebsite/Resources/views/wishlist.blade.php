@extends('vendorwebsite::layouts.master')
@section('title') {{__('vendorwebsite.wishlist')}} @endsection

@section('content')

<x-breadcrumb />
<x-mywishlist_section/>

@endsection