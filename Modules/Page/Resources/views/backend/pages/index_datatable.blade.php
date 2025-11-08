@extends('backend.layouts.app')

@section('title')
    {{ __($module_title) }}
@endsection


@push('after-styles')
    <link rel="stylesheet" href="{{ mix('modules/constant/style.css') }}">
@endpush
@section('content')

<div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-6 col-sm-12">
                <h2>{{ __($module_title) }}
                    <small class="text-muted">{{ config('app.name') }}</small>
                </h2>
            </div>
        </div>
        </div>

        <div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <x-backend.section-header>
                <x-slot name="toolbar">

                    <div>
                        <div class="datatable-filter" style="width: 100%; display: inline-block;">
                            {{ $filter['status'] }}
                            <select name="column_status" id="column_status" class="select2 form-control" style="width: 100%"
                                data-filter="select">
                                <option value="">{{ __('messages.all') }}</option>
                                <option value="1" {{ $filter['status'] == '1' ? 'selected' : '' }}>
                                    {{ __('messages.active') }}</option>
                                <option value="0" {{ $filter['status'] == '0' ? 'selected' : '' }}>
                                    {{ __('messages.inactive') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-group flex-nowrap top-input-search">
                        <span class="input-group-text" id="addon-wrapping"><i
                                class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}..."
                            aria-label="Search" aria-describedby="addon-wrapping">
                    </div>
                    @hasPermission('add_page')
                        <x-buttons.offcanvas target='#form-offcanvas'
                            title="">{{ __('messages.new') }}</x-buttons.offcanvas>
                    @endhasPermission
                </x-slot>
            </x-backend.section-header>
            <table id="datatable" class="table table-bordered table-striped table-hover js-basic-example dataTable">
            </table>
        </div>
    </div>

    <div data-render="app">

        <page-offcanvas create-title="{{ __('messages.new') }} {{ __('page.singular_title') }}"
            edit-title="{{ __('messages.edit') }} {{ __('page.singular_title') }}">
        </page-offcanvas>

        <x-backend.advance-filter>
            <x-slot name="title">
                <h4>{{ __('messages.advance_filter') }}</h4>
            </x-slot>
            <select name="" id="" class="select2">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
            </select>
        </x-backend.advance-filter>
    </div>
        </div>
@endsection

@push('after-styles')
    <!-- DataTables Core and Extensions -->
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push('after-scripts')
    <script src="{{ mix('modules/page/script.js') }}"></script>
    <script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>

    <!-- DataTables Core and Extensions -->
    <script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>

    <script type="text/javascript" defer>
        const columns = [{
                data: 'name',
                name: 'name',
                title: "{{ __('page.lbl_title') }}"
            },
            {
                data: 'sequence',
                name: 'sequence',
                title: "{{ __('page.lbl_sequence') }}"
            },
            {
                data: 'description',
                name: 'description',
                title: "{{ __('page.lbl_description') }}"
            },
            {
                data: 'status',
                name: 'status',
                orderable: false,
                searchable: true,
                title: "{{ __('page.lbl_status') }}"
            },
            {
                data: 'show_for_booking',
                name: 'show_for_booking',
                orderable: false,
                searchable: true,
                title: "{{ __('page.lbl_show_for_booking') }}"
            },
            {
                data: 'updated_at',
                name: 'updated_at',
                title: "{{ __('tax.lbl_updated') }}",
                width: '5%',
                visible: false,
            },
        ]

        const actionColumn = [{
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            title: "{{ __('page.lbl_action') }}"
        }]

        let finalColumns = [
            ...columns,
            ...actionColumn
        ]

        document.addEventListener('DOMContentLoaded', (event) => {
            initDatatable({
                url: '{{ route("backend.$module_name.index_data") }}',
                finalColumns,
                orderColumn: [
                    [4, "desc"]
                ],
            })
        })












        function resetQuickAction() {
            const actionValue = $('#quick-action-type').val();
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');

                if (actionValue == 'change-status') {
                    $('.quick-action-field').addClass('d-none');
                    $('#change-status-action').removeClass('d-none');
                } else {
                    $('.quick-action-field').addClass('d-none');
                }
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        }

        $('#quick-action-type').change(function() {
            resetQuickAction()
        });
        $(document).on('click', '[data-bs-toggle="tooltip"]', function() {
            $(this).tooltip('dispose');
            $('.tooltip').remove();
        });
    </script>
@endpush
