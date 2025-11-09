@extends('backend.layouts.app')

@section('title')
    {{ __($module_title) }}
@endsection


@push('after-styles')
    <link rel="stylesheet" href="{{ mix('modules/constant/style.css') }}">
@endpush
@section('content')
    <div class="card">
        <div class="card-body">
            <x-backend.section-header>
                <x-slot name="toolbar">
                    <div class="input-group flex-nowrap top-input-search">
                        <span class="input-group-text" id="addon-wrapping"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}..."
                            aria-label="Search" aria-describedby="addon-wrapping">
                    </div>
                    <a href="{{ route('backend.blog.create') }}" class="btn btn-primary" title="Create Blog">
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
        .blog-text-title-1, .blog-text-description-1 {
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
                data: 'title',
                name: 'title',
                title: "{{ __('frontend.title') }}"
            },
            {
                data: 'auther_id',
                name: 'auther_id',
                title: "{{ __('frontend.author') }}"
            },
            {
                data: 'status',
                name: 'status',
                orderable: false,
                searchable: true,
                title: "{{ __('frontend.status') }}"
            },
            {
                data: 'image',
                name: 'image',
                title: "{{ __('frontend.image') }}",
                orderable: false, // Disable sorting on the image column
                render: function(data, type, row) {
                    // Check if image exists and render the image
                    if (data) {
                        return `<img src="${data}" alt="Image" class="img-thumbnail" style="width: 50px; height: 50px;">`;
                    }
                    return ''; // Return empty if no image
                }
            },
        ];

        const actionColumn = [{
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            title: "{{ __('frontend.action') }}",
            render: function(data, type, row) {
                let buttons = ` <button class="btn btn-primary btn-sm btn-edit" onclick="editBlog(${row.id})" title="Edit" data-bs-toggle="tooltip">
                                    <i class="fas fa-edit"></i>
                                </button>
                                                <a href="{{ route('backend.blog.delete', '') }}/${row.id}"
           id="delete-blog-${row.id}"
           class="btn btn-danger btn-sm"
           data-type="ajax"
           data-method="DELETE"
           data-token="{{ csrf_token() }}"
           data-bs-toggle="tooltip"
           title="{{ __('messages.delete') }}"
            data-confirm="{{ __('messages.are_you_sure?', ['module' => __('messages.lbl_blog'), 'name' => '']) }} ">
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
                ]
            })
        })

        function editBlog(blog_id) {
            var route = "{{ route('backend.blog.edit', 'blog_id') }}".replace('blog_id', blog_id);
            window.location.href = route;
        }

        function deleteBlog(blog_id) {

            var route = "{{ route('backend.blog.delete', 'blog_id') }}".replace('blog_id', blog_id);
            confirmDelete(route, blog_id);
        }
        // Function to toggle blog text between short and full view
        function toggleBlogText(id, type) {
            const shortElement = document.querySelector('.blog-short-' + type + '-' + id);
            const fullElement = document.querySelector('.blog-full-' + type + '-' + id);
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
        window.toggleBlogText = toggleBlogText;

        $(document).on('click', '[data-bs-toggle="tooltip"]', function () {
            $(this).tooltip('dispose');
            $('.tooltip').remove();
        });

    </script>
@endpush
