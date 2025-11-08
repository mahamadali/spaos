@extends('backend.layouts.app')

@section('title')  {{ $module_title }} @endsection



@section('content')

<div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-6 col-sm-12">
                <h2>{{ __('menu.settings') }}
                    <small class="text-muted">{{ config('app.name') }}</small>
                </h2>
            </div>
        </div>
        </div>
<div class="container-fluid">
<meta name="setting_local" content="none">

<div id="setting-app"></div>
</div>

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
