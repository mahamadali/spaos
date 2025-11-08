@extends('vendorwebsite::layouts.master')

@section('content')
<div class="section-spacing">
    <div class="container">
        <x-banklist_section :banks="$banks" />
    </div>
</div>
@endsection