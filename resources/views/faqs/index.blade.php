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
                    <div class="input-group flex-nowrap top-input-search">
                        <span class="input-group-text" id="addon-wrapping"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}..."
                            aria-label="Search" aria-describedby="addon-wrapping">
                    </div>
                    <a href="{{ route('backend.faq.create') }}" class="btn btn-primary" title="Create Faq">
                        <i class="fas fa-plus-circle"></i>
                        {{ __('messages.new') }}
                    </a>

                </x-slot>
            </x-backend.section-header>
            <table id="datatable" class="table border table-responsive rounded">
            </table>
        </div>
    </div>

    <div data-render="app">

    </div>
        </div>
@endsection

@push('after-styles')
    <!-- DataTables Core and Extensions -->
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
    <style>
        .show-more-link {
            color: var(--bs-primary) !important;
            text-decoration: none !important;
            font-size: 0.875rem;
        }
        .show-more-link:hover {
            color: var(--bs-primary) !important;
            text-decoration: underline !important;
            opacity: 0.8;
        }
        .faq-text-question-1, .faq-text-answer-1 {
            word-wrap: break-word;
            white-space: pre-wrap;
        }
    </style>
@endpush

@push('after-scripts')
    <script src="{{ mix('modules/subscriptions/script.js') }}"></script>
    <script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>

    <!-- DataTables Core and Extensions -->
    <script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
    <script type="text/javascript" defer>
        const columns = [{
                data: 'id',
                name: 'id',
                visible: false
            },
            {
                data: 'question',
                name: 'question',
                title: "{{ __('frontend.question') }}"
            },
            {
                data: 'answer',
                name: 'answer',
                title: "{{ __('frontend.answer') }}"
            },
            {
                data: 'status',
                name: 'status',
                orderable: false,
                searchable: true,
                title: "{{ __('frontend.status') }}"
            },
        ];

        const actionColumn = [{
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            title: "{{ __('frontend.action') }}",
            render: function(data, type, row) {
                let buttons = ` <button class="btn btn-primary btn-sm btn-edit" onclick="editFaq(${row.id})" title="Edit" data-bs-toggle="tooltip">
                                    <i class="fas fa-edit"></i>
                                </button>
                                    <a href="{{ route('backend.faq.delete', '') }}/${row.id}"
           id="delete-subscription-${row.id}"
           class="btn btn-danger btn-sm"
           data-type="ajax"
           data-method="DELETE"
           data-token="{{ csrf_token() }}"
           data-bs-toggle="tooltip"
           title="{{ __('messages.delete') }}"
            data-confirm="{{ __('messages.are_you_sure?', ['module' => __('messages.lbl_faq'), 'name' => '']) }} ">
            <i class="fa-solid fa-trash"></i>
        </a>
    `;
                return buttons;
            }
        }];

        let finalColumns = [
            ...columns,
            ...actionColumn
        ]

        document.addEventListener('DOMContentLoaded', (event) => {
            initDatatable({
                url: '{{ route("backend.$module_name.index_data") }}',
                finalColumns,
                order: [
                    [0, 'desc']
                ],
            })
        })

        function editFaq(faq_id) {
            var route = "{{ route('backend.faq.edit', 'faq_id') }}".replace('faq_id', faq_id);
            window.location.href = route;
        }

        function deleteFaq(faq_id) {
            var route = "{{ route('backend.faq.delete', 'faq_id') }}".replace('faq_id', faq_id);
            confirmDelete(route, faq_id);
        }
        $(document).on('click', '[data-bs-toggle="tooltip"]', function () {
            $(this).tooltip('dispose');
            $('.tooltip').remove();
        });

        // Function to toggle FAQ text between short and full view
        function toggleFaqText(id, type) {
            const shortElement = document.querySelector('.faq-short-' + type + '-' + id);
            const fullElement = document.querySelector('.faq-full-' + type + '-' + id);
            const linkElement = document.querySelector('.show-more-link[data-id="' + id + '"][data-type="' + type + '"]');
            
            if (shortElement && fullElement && linkElement) {
                if (shortElement.style.display !== 'none') {
                    // Show full text
                    shortElement.style.display = 'none';
                    fullElement.style.display = 'inline';
                    linkElement.innerHTML = '<small>Read less</small>';
                } else {
                    // Show short text
                    shortElement.style.display = 'inline';
                    fullElement.style.display = 'none';
                    linkElement.innerHTML = '<small>Read more</small>';
                }
            }
        }

        // Make function globally available
        window.toggleFaqText = toggleFaqText;

    </script>
@endpush
