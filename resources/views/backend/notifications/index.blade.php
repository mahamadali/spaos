@extends('backend.layouts.app')

@section('title', __($module_title))

@section('content')
    <div class="card mb-4">
        <div class="card-body">

            <div class="row mt-4">
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered">
                        <thead>
                            <tr>
                               <th>
                                    {{ __('notification.lbl_type') }}
                                </th>
                                <th>
                                    {{ __('notification.lbl_text') }}
                                </th>
                               
                                <th>
                                    {{ __('notification.lbl_update') }}
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($$module_name as $module_name_singular)

                        
                                <?php
                                $row_class = '';
                                $span_class = '';
                                if ($module_name_singular->read_at == '') {
                                    $row_class = 'bg-dark';
                                    $span_class = 'fw-bold';
                                }
                                ?>


                                <tr class="{{ $row_class }}">

        
                                    <td>
                                 
                                        {{ $module_name_singular->data['data']['type'] ?? '' }}
                                    </td>
                                    <td>
                                            <span class="{{ $span_class }}">
                                                {!! $module_name_singular['data']['data']['message'] ?? '' !!}
                                            </span>
                                    </td>
                                    
                                    <td>
                                        {{ $module_name_singular->updated_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">{{ __('messages.data_not_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-7">
                    <div class="float-left">
                        {{ __('messages.total') }} {{ $$module_name->total() }} {{ __($module_title) }}
                    </div>
                </div>
                <div class="col-5">
                    <div class="float-end">
                        {!! $$module_name->render() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
