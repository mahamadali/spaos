@extends('backend.layouts.app')

@section('title')  {{ $module_title }} @endsection



@section('content')
<meta name="setting_local" content="none">

<div id="setting-app"></div>

@endsection

@push('after-styles')
  <style>
    .modal-backdrop {
      --bs-backdrop-zindex: 1030;
    }
  </style>
@endpush
@push('after-scripts')
<script src="{{ asset('js/setting-vue.min.js')}}"></script>
@endpush
