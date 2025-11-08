@extends('backend.layouts.quick-booking')

@section('title') {{ __('messages.quick_booking') }} @endsection

@push('after-styles')
    <link rel="stylesheet" href='{{ mix("modules/quickbooking/style.css") }}'>
@endpush

@section('content')
  <div class="container">
    <div class="row justify-content-center align-items-center vh-100">
      <div class="col">
        @include('quickbooking::backend.quickbookings.quick-booking', ['user_id' => $id])
      </div>
    </div>
  </div>
@endsection

@push ('after-scripts')
{{-- Vue script removed since Blade version is used --}}
@endpush
