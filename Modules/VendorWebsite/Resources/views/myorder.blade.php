@extends('vendorwebsite::layouts.master')
@section('title') {{__('vendorwebsite.orders')}} @endsection

@section('content')

<x-breadcrumb />
<x-myorder_section/>

@endsection