@extends('vendorwebsite::layouts.master')

@section('content')

<x-breadcrumb/>

<div class="branch-section-wrapper section-spacing">
    <div class="container">
        <a href="javascript:history.back()" class="text-body fw-medium d-inline-block mb-3">
            <span class="d-flex align-items-center gap-1">
                <i class="ph ph-caret-left"></i>
                <span>{{__("vendorwebsite.back")}}</span>
            </span>
        </a>

        <div class="section-title d-flex flex-wrap gap-3 justify-content-between align-items-center mt-5">
            <div class="title-left">
                <span class="decorator-title decorator-font text-primary text-uppercase text-decoration-underline">
                    {{__("vendorwebsite.our_branches")}}
                </span>
                <h4 class="title mb-0">{{__("vendorwebsite.nearby_branches")}}</h4>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="input-group mb-0">
                    <span class="input-group-text"><i class="ph ph-magnifying-glass"></i></span>
                    <input type="text" class="form-control p-2" id="searchInput" placeholder='{{__("vendorwebsite.example_branch_city_address")}}'>
                </div>
            </div>
        </div>

        <div id="branchCardContainer"></div>

       <div id="shimmer-loader" class="d-flex gap-3 flex-wrap p-4 shimmer-loader">
          @for ($i = 0; $i < 4; $i++)
              @include('vendorwebsite::components.card.shimmer_branch_card')
          @endfor
       </div>

        <table id="branch-cards-table" class="table d-none w-100">
            <thead>
                <tr>
                    <th>Card</th>
                    <th>Name</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    const $table = $('#branch-cards-table');
    const $container = $('#branchCardContainer');
    const shimmerLoader = document.querySelector('.shimmer-loader');

    const table = $table.DataTable({
       processing: false,
        serverSide: true,
        ajax: "{{ route('frontend.branches.data') }}",
        columns: [
            { data: 'card', name: 'card', orderable: false, searchable: false },
            { data: 'name', name: 'name', visible: false }
        ],
        pageLength: 4,
        searching: true,
        lengthChange: false,
        pagingType: 'simple_numbers',
        dom: 'rt<"row mt-3"<"col-12 d-flex justify-content-between align-items-center flex-wrap gap-2"ip>>',
        language: {
            searchPlaceholder: 'Search branches...',
            search: '',
            emptyTable: "<div class='text-center p-4'>{{__('vendorwebsite.no_branches_available_at_the_moment')}}</div>",
            zeroRecords: "<div class='text-center p-4'>{{__('vendorwebsite.no_matching_branches_found')}}</div>",

        },
        drawCallback: function (settings) {
            const data = table.rows().data();
            $container.empty();
            if (data.length === 0) {
                $container.html('<div class="text-center p-4">{{__("vendorwebsite.no_matching_branches_found")}}</div>');
                return;
            }
            for (let i = 0; i < data.length; i += 4) {
                const row = $('<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 gy-4 mb-4"></div>');
                for (let j = i; j < i + 4 && j < data.length; j++) {
                    const cardHtml = `<div class='col branch-selectable' data-branch-id='${data[j].branch_id || ''}'>${data[j].card}</div>`;
                    row.append(cardHtml);
                }
                $container.append(row);
            }

            // Handle branch card clicks (entire card is clickable)
            $('.branch-select-badge-card').off('click').on('click', function(e) {
                // Don't trigger if clicking on the branch info link (let it redirect to detail page)
                if ($(e.target).hasClass('branch-info-box') || $(e.target).closest('.branch-info-box').length) {
                    return;
                }

                const branchId = this.getAttribute('data-branch-id');
                const badge = this.querySelector('span[data-branch-id]');

                // Show loading spinner on the badge
                badge.innerHTML = '<i class="ph ph-spinner ph-spin font-size-16"></i>';

                fetch('{{ route("branch.select") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ branch_id: branchId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Clear all badges first
                        document.querySelectorAll('span[data-branch-id]').forEach(b => {
                            b.innerHTML = '';
                        });

                        // Add check icon to selected branch
                        badge.innerHTML = '<i class="ph-fill ph-check-circle fs-4 text-primary"></i>';

                        const branchSelectedEvent = new CustomEvent('branchSelected', {
                            detail: {
                                branchId: data.branch_id
                            }
                        });
                        document.dispatchEvent(branchSelectedEvent);

                        window.location.href = '{{ route('index') }}';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while selecting the branch.');
                    badge.innerHTML = '';
                });
            });
        }
    });

    $('#searchInput').on('keyup', function () {
        table.search(this.value).draw();
    });

    table.on('preXhr.dt', function () {
        $('#branchCardContainer').empty();
        shimmerLoader.classList.remove('d-none');

    });

    table.on('xhr.dt', function () {
        shimmerLoader.classList.add('d-none');

    });

});
</script>

@endpush
