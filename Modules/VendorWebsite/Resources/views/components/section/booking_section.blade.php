@props(['bookings', 'allBookingsCount' => 0, 'upcomingBookingsCount' => 0, 'completedBookingsCount' => 0])

<div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-4">
    <nav class="booking-tab-container">
        <div class="nav nav-tabs" id="booking-tabs" role="tablist">
            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button"
                role="tab" aria-controls="all" aria-selected="true">{{ __('vendorwebsite.all') }}
                ({{ $allBookingsCount }})</button>
            <button class="nav-link" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button"
                role="tab" aria-controls="upcoming" aria-selected="false">{{ __('vendorwebsite.upcoming') }}
                ({{ $upcomingBookingsCount }})</button>
            <button class="nav-link" id="complete-tab" data-bs-toggle="tab" data-bs-target="#complete" type="button"
                role="tab" aria-controls="complete" aria-selected="false">{{ __('vendorwebsite.completed') }}
                ({{ $completedBookingsCount }})</button>
        </div>
    </nav>
    <div class="">
        <div class="input-group mb-0">
            <span class="input-group-text"><i class="ph ph-magnifying-glass"></i></span>
            <input type="search" class="form-control p-2" id="bookingSearchInput"
                placeholder="{{ __('vendorwebsite.eg_expert_service_branch') }}">
        </div>
    </div>
</div>

<div class="tab-content" id="booking-tabsContent">
    <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab" tabindex="0">
        <div id="allBookingsContainer"></div>

        <div id="shimmer-loader-all" class="d-flex gap-3 flex-wrap p-4 shimmer-loader-all list-inline">
            @for ($i = 0; $i < 4; $i++)
                @include('vendorwebsite::components.card.shimmer_appointment_card')
            @endfor
        </div>

        <table id="all-bookings-table" class="table d-none w-100">
            <thead>
                <tr>
                    <th>{{ __('vendorwebsite.card') }}</th>
                    <th>{{ __('vendorwebsite.details') }}</th>
                </tr>
            </thead>
        </table>
    </div>
    <div class="tab-pane fade" id="upcoming" role="tabpanel" aria-labelledby="upcoming-tab" tabindex="0">
        <div id="upcomingBookingsContainer"></div>

        <div id="shimmer-loader-upcoming" class="d-flex gap-3 flex-wrap p-4 shimmer-loader-upcoming list-inline">
            @for ($i = 0; $i < 4; $i++)
                @include('vendorwebsite::components.card.shimmer_appointment_card')
            @endfor
        </div>
        <table id="upcoming-bookings-table" class="table d-none w-100">
            <thead>
                <tr>
                    <th>{{ __('vendorwebsite.card') }}</th>
                    <th>{{ __('vendorwebsite.details') }}</th>
                </tr>
            </thead>
        </table>
    </div>
    <div class="tab-pane fade" id="complete" role="tabpanel" aria-labelledby="complete-tab" tabindex="0">
        <div id="completedBookingsContainer"></div>
        <div id="shimmer-loader-complete" class="d-flex gap-3 flex-wrap p-4 shimmer-loader-complete list-inline">
            @for ($i = 0; $i < 4; $i++)
                @include('vendorwebsite::components.card.shimmer_appointment_card')
            @endfor
        </div>
        <table id="completed-bookings-table" class="table d-none w-100">
            <thead>
                <tr>
                    <th>{{ __('vendorwebsite.card') }}</th>
                    <th>{{ __('vendorwebsite.details') }}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Reschedule Modal (replace time slot UI with dropdown like quick_booking) -->
<div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h5 class="modal-title" id="rescheduleModalLabel">{{ __('vendorwebsite.reschedule_booking') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="rescheduleForm">
                    <div class="mb-3">
                        <label for="reschedule_date" class="form-label">{{ __('vendorwebsite.date') }}</label>
                        <div class="input-group custom-input-group">
                            <input type="text" class="form-control date-picker" id="reschedule_date"
                                name="reschedule_date" placeholder="Select Date" required autocomplete="off">
                            <span class="input-group-text" id="calendar-icon">
                                <i class="ph ph-calendar"></i>
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('vendorwebsite.time_slot') }}</label>
                        <div class="input-group custom-input-group select-time">
                            <a class="form-control dropdown-toggle" id="rescheduleTimeDropdown"
                                data-bs-toggle="dropdown" href="#" aria-expanded="false">
                                <span id="selected_reschedule_time">{{ __('vendorwebsite.select_time') }}</span>
                            </a>
                            <span class="input-group-text cursor-pointer" id="clock-icon">
                                <i class="ph ph-clock"></i>
                            </span>
                            <div class="dropdown-menu dropdown-menu-start dropdown-time-panel w-100"
                                id="reschedule-time-dropdown-menu" data-bs-auto-close="false">
                                <ul class="list-unstyled m-0 p-0 d-flex flex-wrap gap-2"
                                    id="reschedule-time-slots-list" style="max-width: 350px;">
                                    <li class="disabled">
                                        <a href="#">{{ __('vendorwebsite.please_select_date') }}</a>
                                    </li>
                                </ul>
                            </div>
                            <input type="hidden" id="reschedule_time_input" name="reschedule_time">

                        </div>
                        <span id="reschedule_time_error" class="text-danger"></span>
                    </div>
                    <input type="hidden" id="reschedule_booking_id" name="booking_id">
                    <button type="submit" class="btn btn-primary">{{ __('vendorwebsite.reschedule') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Rate Us Modal -->
<div class="modal fade rating-modal" id="rateUsModal" tabindex="-1" aria-labelledby="rateUsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content bg-gray-900 rounded">
            <div class="modal-body modal-body-inner rate-us-modal">
                <div class="text-center">
                    <h5 class="font-size-21-3 mb-0 text-center" id="rateUsModalLabel">
                        {{ __('vendorwebsite.rate_our_service_now') }}</h5>
                </div>
                <p class="mb-0 mt-2 font-size-14 text-center">
                    {{ __('vendorwebsite.your_honest_feedback_helps_us_improve_and_serve_you_better') }}</p>
                <form id="rateUsForm" class="mt-5 pt-2">
                    <label class="form-label">{{ __('vendorwebsite.your_rating') }}</label>
                    <div class="bg-gray-800 form-control">
                        {{-- <label class="form-label">{{ __('vendorwebsite.your_rating') }}</label> --}}
                        <div id="starRating"
                            class="list-inline m-0 p-0 d-flex align-items-center justify-content-start gap-1 rating-list"
                            style="font-size:2rem; text-align:center;">
                            <span class="star" data-value="1">&#9733;</span>
                            <span class="star" data-value="2">&#9733;</span>
                            <span class="star" data-value="3">&#9733;</span>
                            <span class="star" data-value="4">&#9733;</span>
                            <span class="star" data-value="5">&#9733;</span>
                        </div>
                    </div>
                    <div class="from-group mt-4">
                        <label class="form-label">{{ __('vendorwebsite.enter_your_feedback') }}</label>
                        <textarea class="form-control" name="feedback" rows="3"
                            placeholder="{{ __('vendorwebsite.eg_Amazing_service_My_stylist_really_listened_to_what_I_wanted_and_delivered_exactly_that_Can’t_wait_to_come_back') }}"></textarea>
                    </div>
                    <input type="hidden" id="rateUs_booking_id" name="booking_id">
                    <div
                        class="mt-5 pt-3 d-flex align-items-center justify-content-center row-gap-3 column-gap-4 flex-wrap">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('vendorwebsite.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('vendorwebsite.submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        let rescheduleBranchId = null;
        let rescheduleEmployeeId = null;

        // When date changes, render slots
        $(document).on('change', '#reschedule_date', function() {
            const selectedDate = $(this).val();

            if (selectedDate) {

                clearTimeSelection();
                renderRescheduleTimeSlots(selectedDate);
            }
        });
        $(document).ready(function() {

            const shimmerLoaderAll = document.querySelector('.shimmer-loader-all');
            // Initialize DataTables for each tab
            const allBookingsTable = $('#all-bookings-table').DataTable({
                processing: false,
                serverSide: true,
                ajax: {
                    url: "{{ route('bookings.data') }}",
                    data: function(d) {
                        d.type = 'all';
                    }
                },
                columns: [{
                        data: 'card',
                        name: 'card',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'details',
                        name: 'details',
                        visible: false
                    } // hidden but searchable
                ],
                pageLength: 5,
                searching: true,
                lengthChange: false,
                pagingType: 'simple_numbers',
                dom: 'rt<"row mt-3"<"col-12 d-flex justify-content-between align-items-center flex-wrap gap-2"ip>>',
                language: {
                    searchPlaceholder: '{{ __('vendorwebsite.search_bookings') }}',
                    search: '',
                    emptyTable: "<div class='text-center p-4'>{{ __('vendorwebsite.no_bookings_found') }}</div>",
                    zeroRecords: "<div class='text-center p-4'>{{ __('vendorwebsite.no_matching_bookings_found') }}</div>",

                },
                initComplete: function(settings, json) {
                    $('#all-tab').text(`{{ __('vendorwebsite.all') }} (${json.allBookingsCount})`);
                    $('#upcoming-tab').text(
                        `{{ __('vendorwebsite.upcoming') }} (${json.upcomingBookingsCount})`);
                    $('#complete-tab').text(
                        `{{ __('vendorwebsite.completed') }} (${json.completedBookingsCount})`);
                },
                drawCallback: function(settings) {
                    const data = this.api().rows().data();
                    $('#allBookingsContainer').empty();

                    if (data.length === 0) {
                        $('#allBookingsContainer').append(
                            `<div class="text-center p-4">{{ __('vendorwebsite.no_bookings_available') }}</div>`
                        );
                    } else {
                        data.each(function(row) {
                            $('#allBookingsContainer').append(
                                `<div class="row gy-5 g-0 mb-4"><div class="col-12">${row.card}</div></div>`
                            );
                        });
                    }

                    const json = settings.json;
                    if (json) {
                        $('#all-tab').text(`{{ __('vendorwebsite.all') }} (${json.allBookingsCount})`);
                        $('#upcoming-tab').text(
                            `{{ __('vendorwebsite.upcoming') }} (${json.upcomingBookingsCount})`);
                        $('#complete-tab').text(
                            `{{ __('vendorwebsite.completed') }} (${json.completedBookingsCount})`);
                    }
                }
            }); // <-- Make sure this line ends the DataTable initialization properly

            // Show loader before AJAX call
            allBookingsTable.on('preXhr.dt', function() {
                $('#allBookingsContainer').empty();
                shimmerLoaderAll.classList.remove('d-none');
            });

            // // Hide loader after data loads
            allBookingsTable.on('xhr.dt', function() {
                shimmerLoaderAll.classList.add('d-none');
            });
            const shimmerLoaderUpcoming = document.querySelector('.shimmer-loader-upcoming');

            const upcomingBookingsTable = $('#upcoming-bookings-table').DataTable({
                processing: false,
                serverSide: true,
                ajax: {
                    url: "{{ route('bookings.data') }}",
                    data: function(d) {
                        d.type = 'upcoming';
                    }
                },
                columns: [{
                        data: 'card',
                        name: 'card',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'details',
                        name: 'details',
                        visible: false
                    } // hidden but searchable
                ],
                pageLength: 5,
                searching: true,
                lengthChange: false,
                pagingType: 'simple_numbers',
                dom: 'rt<"row mt-3"<"col-12 d-flex justify-content-between align-items-center flex-wrap gap-2"ip>>',
                language: {
                    searchPlaceholder: '{{ __('vendorwebsite.search_bookings') }}',
                    search: '',
                    emptyTable: "<div class='text-center p-4'>{{ __('vendorwebsite.no_upcoming_bookings_found') }}</div>",
                    zeroRecords: "<div class='text-center p-4'>{{ __('vendorwebsite.no_matching_upcoming_bookings_found') }}</div>",

                },
                initComplete: function(settings, json) {
                    $('#all-tab').text(`{{ __('vendorwebsite.all') }}(${json.allBookingsCount})`);
                    $('#upcoming-tab').text(
                        `{{ __('vendorwebsite.upcoming') }} (${json.upcomingBookingsCount})`);
                    $('#complete-tab').text(
                        `{{ __('vendorwebsite.completed') }}(${json.completedBookingsCount})`);
                },
                drawCallback: function(settings) {
                    const data = this.api().rows().data();
                    $('#upcomingBookingsContainer').empty();

                    if (data.length === 0) {
                        $('#upcomingBookingsContainer').append(
                            `<div class="text-center p-4">{{ __('vendorwebsite.no_bookings_available') }}</div>`
                        );
                    } else {
                        data.each(function(row) {
                            $('#upcomingBookingsContainer').append(
                                `<div class="row gy-5 g-0 mb-4"><div class="col-12">${row.card}</div></div>`
                            );
                        });
                    }

                    const json = settings.json;
                    if (json) {
                        $('#all-tab').text(`{{ __('vendorwebsite.all') }}(${json.allBookingsCount})`);
                        $('#upcoming-tab').text(
                            `{{ __('vendorwebsite.upcoming') }}(${json.upcomingBookingsCount})`);
                        $('#complete-tab').text(
                            `{{ __('vendorwebsite.completed') }}(${json.completedBookingsCount})`);
                    }
                }
            });

            upcomingBookingsTable.on('preXhr.dt', function() {
                $('#upcomingBookingsContainer').empty();
                shimmerLoaderUpcoming.classList.remove('d-none');
            });

            // // Hide loader after data loads
            upcomingBookingsTable.on('xhr.dt', function() {
                shimmerLoaderUpcoming.classList.add('d-none');
            });

            const shimmerLoaderComplete = document.querySelector('.shimmer-loader-complete');

            const completedBookingsTable = $('#completed-bookings-table').DataTable({
                processing: false,
                serverSide: true,
                ajax: {
                    url: "{{ route('bookings.data') }}",
                    data: function(d) {
                        d.type = 'completed';
                    }
                },
                columns: [{
                        data: 'card',
                        name: 'card',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'details',
                        name: 'details',
                        visible: false
                    } // hidden but searchable
                ],
                pageLength: 5,
                searching: true,
                lengthChange: false,
                pagingType: 'simple_numbers',
                dom: 'rt<"row mt-3"<"col-12 d-flex justify-content-between align-items-center flex-wrap gap-2"ip>>',
                language: {
                    searchPlaceholder: '{{ __('vendorwebsite.search_bookings') }}',
                    search: '',
                    emptyTable: "<div class='text-center p-4'>{{ __('vendorwebsite.no_completed_bookings_found') }}</div>",
                    zeroRecords: "<div class='text-center p-4'>{{ __('vendorwebsite.no_matching_completed_bookings_found') }}</div>",

                },
                initComplete: function(settings, json) {
                    $('#all-tab').text(`{{ __('vendorwebsite.all') }} (${json.allBookingsCount})`);
                    $('#upcoming-tab').text(
                        `{{ __('vendorwebsite.upcoming') }} (${json.upcomingBookingsCount})`);
                    $('#complete-tab').text(
                        `{{ __('vendorwebsite.completed') }} (${json.completedBookingsCount})`);
                },
                drawCallback: function(settings) {
                    const data = this.api().rows().data();
                    $('#completedBookingsContainer').empty();

                    if (data.length === 0) {

                        $('#completedBookingsContainer').append(
                            `<div class="text-center p-4">{{ __('vendorwebsite.no_bookings_available') }}</div>`
                        );
                    } else {

                        data.each(function(row) {
                            $('#completedBookingsContainer').append(
                                `<div class="row gy-5 g-0 mb-4"><div class="col-12">${row.card}</div></div>`
                            );
                        });
                    }

                    const json = settings.json;
                    if (json) {
                        $('#all-tab').text(`{{ __('vendorwebsite.all') }}(${json.allBookingsCount})`);
                        $('#upcoming-tab').text(
                            `{{ __('vendorwebsite.upcoming') }} (${json.upcomingBookingsCount})`);
                        $('#complete-tab').text(
                            `{{ __('vendorwebsite.completed') }}(${json.completedBookingsCount})`);
                    }
                }
            });


            completedBookingsTable.on('preXhr.dt', function() {
                $('#completedBookingsContainer').empty();
                shimmerLoaderComplete.classList.remove('d-none');
            });

            // // Hide loader after data loads
            completedBookingsTable.on('xhr.dt', function() {
                shimmerLoaderComplete.classList.add('d-none');
            });

            // Handle tab changes
            $('#booking-tabs button').on('click', function() {
                const target = $(this).data('bs-target');
                if (target === '#all') {
                    allBookingsTable.ajax.reload();
                } else if (target === '#upcoming') {
                    upcomingBookingsTable.ajax.reload();
                } else if (target === '#complete') {
                    completedBookingsTable.ajax.reload();
                }
            });

            // Handle search input
            $('#bookingSearchInput').on('keyup', function() {
                const activeTab = $('#booking-tabs .nav-link.active');
                const target = activeTab.data('bs-target');

                if (target === '#all') {
                    allBookingsTable.search(this.value).draw();
                } else if (target === '#upcoming') {
                    upcomingBookingsTable.search(this.value).draw();
                } else if (target === '#complete') {
                    completedBookingsTable.search(this.value).draw();
                }
            });
        });

        $(document).on('click', '.cancel-booking-btn', function() {
            const bookingId = $(this).data('booking-id');
            Swal.fire({
                title: '{{ __('vendorwebsite.are_you_sure') }}',
                text: '{{ __('vendorwebsite.do_you_really_want_to_cancel_this_booking') }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '{{ __('vendorwebsite.yes_cancel_it') }}',
                cancelButtonText: '{{ __('vendorwebsite.no_keep_it') }}',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-secondary'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('bookings.cancel', ':id') }}".replace(':id', bookingId),
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Canceled!',
                                    text: response.message ||
                                        '{{ __('vendorwebsite.your_booking_has_been_cancelled') }}',
                                    icon: 'success',
                                    confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                    customClass: {
                                        confirmButton: 'btn btn-primary'
                                    },
                                    buttonsStyling: false
                                });
                                $('#booking-status-' + bookingId)
                                    .text('Canceled')
                                    .removeClass('text-success').addClass('text-danger');
                                $('.cancel-booking-btn[data-booking-id="' + bookingId + '"]')
                                    .prop('disabled', true);
                                $('.reschedule-booking-btn[data-booking-id="' + bookingId +
                                    '"]').prop('disabled', true);

                                // Reload the DataTable to refresh the data
                                const activeTab = $('#booking-tabs .nav-link.active');
                                const target = activeTab.data('bs-target');

                                if (target === '#all') {
                                    $('#all-bookings-table').DataTable().ajax.reload();
                                } else if (target === '#upcoming') {
                                    $('#upcoming-bookings-table').DataTable().ajax.reload();
                                } else if (target === '#complete') {
                                    $('#completed-bookings-table').DataTable().ajax.reload();
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message ||
                                        '{{ __('vendorwebsite.could_not_cancel_booking') }}',
                                    confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                    customClass: {
                                        confirmButton: 'btn btn-primary'
                                    }
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage =
                                '{{ __('vendorwebsite.could_not_cancel_booking') }}';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMessage,
                                confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                }
                            });
                        }
                    });
                }
            });
        });

        $(document).on('click', '.reschedule-booking-btn', function() {
            const bookingId = $(this).data('booking-id');
            if (!bookingId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{{ __('vendorwebsite.booking_id_not_found_please_try_again') }}',
                    confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
                return;
            }
            const url = "{{ route('bookings.details', ':id') }}".replace(':id', bookingId);
            $.ajax({
                url: url,
                type: 'GET',
                beforeSend: function(xhr) {
                    const token = $('meta[name="csrf-token"]').attr('content');
                    if (token) {
                        xhr.setRequestHeader('X-CSRF-TOKEN', token);
                    }
                },
                success: function(data) {

                    $('#reschedule_booking_id').val(bookingId);
                    $('#reschedule_booking_id').data('original-date', data.date);

                    let formattedDate = data.date;
                    if (formattedDate) {

                        const dateObj = new Date(formattedDate);
                        if (!isNaN(dateObj.getTime())) {
                            formattedDate = dateObj.toISOString().split('T')[0];
                        }
                    }

                    $('#reschedule_date').val(formattedDate);

                    if (window.flatpickr && $('#reschedule_date')[0]._flatpickr) {
                        $('#reschedule_date')[0]._flatpickr.setDate(formattedDate);
                    }

                    setTimeout(function() {

                        if (!$('#reschedule_date').val() && formattedDate) {

                            $('#reschedule_date').val(formattedDate);
                            if (window.flatpickr && $('#reschedule_date')[0]._flatpickr) {
                                $('#reschedule_date')[0]._flatpickr.setDate(formattedDate);
                            }
                        }
                    }, 200);

                    let displayTime = convertTo12HourFormat(data.time);
                    $('#selected_reschedule_time').text(displayTime);
                    $('#reschedule_time_input').val(data.time);

                    // Show current time slot as selected
                    $('#reschedule-time-slots-list').html(
                        '<li class="disabled"><a href="#">Loading current slot...</a></li>');

                    rescheduleBranchId = data.branch_id;
                    rescheduleEmployeeId = data.employee_id;

                    window.fetchRescheduleBusinessHours(data.branch_id);

                    renderRescheduleTimeSlots(data.date);

                    $('#rescheduleModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        xhr: xhr,
                        status: status,
                        error: error
                    });
                    let errorMessage = '{{ __('vendorwebsite.could_not_fetch_booking_details') }}';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', errorMessage, 'error');
                }
            });
        });

        $(document).on('submit', '#rescheduleForm', function(e) {
            e.preventDefault();

            const bookingId = $('#reschedule_booking_id').val();
            const date = $('#reschedule_date').val();
            let time = $('#reschedule_time_input').val();

            if (!bookingId) {
                $('#rescheduleModal').modal('hide');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{{ __('vendorwebsite.booking_id_not_found_please_try_again') }}',
                    confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
                return;
            }

            if (!date) {
                $('#rescheduleModal').modal('hide');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{{ __('vendorwebsite.please_select_a_date') }}',
                    confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
                $('#reschedule_date').focus();
                return;
            }

            if (!time || time === 'Select time') {

                $('#reschedule_time_error').text('{{ __('vendorwebsite.please_select_a_time_slot') }}');
                // Swal.fire({
                //     icon: 'error',
                //     title: 'Error',
                //     text: '{{ __('vendorwebsite.please_select_a_time_slot') }}',
                //     confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                //         customClass: {
                //             confirmButton: 'btn btn-primary'
                //         },
                // });
                $('#rescheduleTimeDropdown').focus();
                return;
            }

            if (typeof time === 'string' && time.includes(' ')) {
                time = time.split(' ')[1];
            }

            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.text();
            submitBtn.prop('disabled', true).text('Rescheduling...');

            $.ajax({
                url: "{{ route('bookings.reschedule', ':id') }}".replace(':id', bookingId),
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    date: date,
                    time: time
                },
                success: function(response) {

                    submitBtn.prop('disabled', false).text(originalText);

                    if (response.success) {
                        $('#rescheduleModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message ||
                                '{{ __('vendorwebsite.your_booking_has_been_rescheduled') }}',
                            showConfirmButton: true,
                            confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false,
                            timer: 1500
                        });

                        const formattedDate = new Date(date).toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric'
                        });
                        const formattedTime = convertTo12HourFormat(time);

                        $('#booking-date-' + bookingId).text(formattedDate);
                        $('#booking-time-' + bookingId).text(formattedTime);

                        const activeTab = $('#booking-tabs .nav-link.active');
                        const target = activeTab.data('bs-target');

                        if (target === '#all') {
                            $('#all-bookings-table').DataTable().ajax.reload();
                        } else if (target === '#upcoming') {
                            $('#upcoming-bookings-table').DataTable().ajax.reload();
                        } else if (target === '#complete') {
                            $('#completed-bookings-table').DataTable().ajax.reload();
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Could not reschedule booking.',
                            confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        });
                    }
                },
                error: function(xhr) {
                    $('#rescheduleModal').modal('hide');
                    // Restore button state
                    submitBtn.prop('disabled', false).text(originalText);

                    let errorMessage = '{{ __('vendorwebsite.could_not_reschedule_booking') }}';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        title: 'Error',
                        text: errorMessage || 'Could not reschedule booking.',
                        icon: 'error',
                        confirmButtonColor: 'btn btn-secondary'
                    });

                }
            });
        });

        // Global variables for reschedule functionality
        var rescheduleOffDays = [];
        var rescheduleBusinessHours = [];
        var rescheduleHolidayDates = [];

        // Helper function to convert 24-hour format to 12-hour format
        function convertTo12HourFormat(time24) {
            if (!time24 || !time24.includes(':')) return time24;

            const [hours, minutes] = time24.split(':');
            const hour = parseInt(hours);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour > 12 ? hour - 12 : (hour === 0 ? 12 : hour);
            return `${displayHour}:${minutes} ${ampm}`;
        }

        // Helper function to clear time selection
        function clearTimeSelection() {
            $('#selected_reschedule_time').text('Select time');
            $('#reschedule_time_input').val('');
            $('#reschedule-time-slots-list a').removeClass('active');
        }

        // --- Date Picker: Use same logic as quick_booking ---
        document.addEventListener('DOMContentLoaded', function() {
            var today = new Date();
            var yyyy = today.getFullYear();
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var dd = String(today.getDate()).padStart(2, '0');
            var todayStr = yyyy + '-' + mm + '-' + dd;
            var dateInput = document.getElementById('reschedule_date');

            function updateRescheduleDatePickerDisabledDays() {
                if (window.flatpickr && dateInput && dateInput._flatpickr) {
                    try {
                        // Format holiday dates to YYYY-MM-DD for comparison (local timezone)
                        const formattedHolidayDates = rescheduleHolidayDates.map(dateStr => {
                            // Parse the date string (assuming format YYYY-MM-DD)
                            const [year, month, day] = dateStr.split('-').map(Number);
                            // Create date in local timezone
                            const date = new Date(year, month - 1, day);
                            // Format as YYYY-MM-DD in local time
                            return date.toLocaleDateString('en-CA'); // YYYY-MM-DD format
                        });

                        dateInput._flatpickr.set('disable', [
                            function(date) {
                                // Format the date to YYYY-MM-DD in local timezone
                                const dateStr = date.toLocaleDateString('en-CA');

                                // Check if date is in holidayDates
                                const isHoliday = formattedHolidayDates.includes(dateStr);

                                // Disable days of the week (weekly off days)
                                const dayName = date.toLocaleDateString('en-US', {
                                    weekday: 'long'
                                });
                                const isWeeklyOff = rescheduleOffDays.includes(dayName);

                                // Disable past dates
                                const isPastDate = date < new Date(todayStr);

                                console.log('Reschedule Date:', dateStr, 'isHoliday:', isHoliday,
                                    'isWeeklyOff:', isWeeklyOff, 'isPastDate:', isPastDate);
                                return isPastDate || isWeeklyOff || isHoliday;
                            }
                        ]);

                        // Force a redraw to apply the changes
                        dateInput._flatpickr.redraw();
                    } catch (error) {
                        console.error('Error updating reschedule date picker disabled days:', error);
                        // If there's an error, try again after a short delay
                        setTimeout(updateRescheduleDatePickerDisabledDays, 100);
                    }
                } else {
                    // If not initialized yet, try again shortly
                    setTimeout(updateRescheduleDatePickerDisabledDays, 100);
                }
            }

            // Make fetchRescheduleBusinessHours globally accessible
            window.fetchRescheduleBusinessHours = function(branchId) {
                const useBranchId = branchId || 1;

                // First, fetch business hours for weekly off days
                const businessHoursUrl = "{{ url('/app/bussinesshours/index_list') }}?branch_id=" +
                    useBranchId;
                const holidaysUrl = "{{ url('/app/get_holidays') }}" + (useBranchId ? '?branch_id=' +
                    useBranchId : '');

                // Reset the arrays
                rescheduleOffDays = [];
                rescheduleHolidayDates = [];

                // Fetch business hours for weekly off days
                const businessHoursPromise = fetch(businessHoursUrl, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content') || ''
                    },
                    credentials: 'same-origin'
                }).then(res => {
                    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                    return res.json();
                }).then(data => {
                    if (data.status && Array.isArray(data.data)) {
                        rescheduleBusinessHours = data.data;
                        // Normalize to capitalized day names (e.g., 'Sunday')
                        rescheduleOffDays = data.data
                            .filter(d => d.is_holiday == 1)
                            .map(d => d.day.charAt(0).toUpperCase() + d.day.slice(1).toLowerCase());
                    }
                }).catch(error => {
                    console.error('Error fetching business hours:', error);
                });

                // Fetch holiday dates
                const holidaysPromise = fetch(holidaysUrl, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content') || ''
                    },
                    credentials: 'same-origin'
                }).then(res => {
                    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                    return res.json();
                }).then(data => {
                    if (data.status && Array.isArray(data.data)) {
                        // Store all holiday dates
                        rescheduleHolidayDates = data.data.map(holiday => holiday.date);
                    }
                }).catch(error => {
                    console.error('Error fetching holidays:', error);
                });

                // When both requests complete, update the date picker
                Promise.all([businessHoursPromise, holidaysPromise])
                    .then(() => updateRescheduleDatePickerDisabledDays())
                    .catch(() => updateRescheduleDatePickerDisabledDays());
            };

            // Initialize date picker when modal is shown
            $('#rescheduleModal').on('show.bs.modal', function() {
                if (typeof rescheduleBranchId !== 'undefined' && rescheduleBranchId) {
                    window.fetchRescheduleBusinessHours(rescheduleBranchId);
                }

                // Initialize flatpickr for the date input if not already initialized
                if (window.flatpickr && dateInput && !dateInput._flatpickr) {
                    flatpickr(dateInput, {
                        allowInput: true,
                        minDate: todayStr,
                        onReady: function() {
                            updateRescheduleDatePickerDisabledDays();
                        },
                        onMonthChange: updateRescheduleDatePickerDisabledDays,
                        onYearChange: updateRescheduleDatePickerDisabledDays,
                        onChange: function(selectedDates, dateStr) {
                            // When date changes, render time slots

                            if (dateStr) {
                                clearTimeSelection();
                                renderRescheduleTimeSlots(dateStr);
                            }
                        }
                    });
                }
            });

            // Clear form when modal is hidden
            $('#rescheduleModal').on('hidden.bs.modal', function() {
                $('#rescheduleForm')[0].reset();
                $('#selected_reschedule_time').text('Select time');
                $('#reschedule_time_input').val('');
                $('#reschedule_date').val(''); // Clear the date as well
                $('#reschedule-time-slots-list').html(
                    '<li class="disabled"><a href="#">{{ __('vendorwebsite.please_select_a_date') }}</a></li>'
                );

                // Reset button state
                const submitBtn = $('#rescheduleForm button[type="submit"]');
                submitBtn.prop('disabled', false).text('{{ __('vendorwebsite.reschedule') }}');
            });
        });

        // --- Time Slot Rendering: Fetch from backend like quick_booking ---
        function renderRescheduleTimeSlots(date) {

            const bookingId = $('#reschedule_booking_id').val();

            if (!bookingId) {
                $('#reschedule-time-slots-list').html(
                    '<li class="disabled"><a href="#">{{ __('vendorwebsite.please_select_a_booking_first') }}</a></li>'
                );
                return;
            }

            // Show loading state
            $('#reschedule-time-slots-list').html(
                '<li class="disabled"><a href="#">{{ __('vendorwebsite.loading_slots') }}</a></li>');

            // Fetch booking details to get branch_id and employee_id

            $.ajax({
                url: "{{ route('bookings.details', ':id') }}".replace(':id', bookingId),
                type: 'GET',
                success: function(bookingData) {

                    if (!bookingData.branch_id || !bookingData.employee_id) {
                        console.error('Missing branch_id or employee_id in booking data');
                        $('#reschedule-time-slots-list').html(
                            '<li class="disabled"><a href="#">{{ __('vendorwebsite.unable_to_get_booking_details') }}</a></li>'
                        );
                        return;
                    }

                    // Calculate total service duration from all services in the booking
                    let totalServiceDuration = 0;
                    if (bookingData.booking_service && Array.isArray(bookingData.booking_service)) {
                        bookingData.booking_service.forEach(service => {
                            totalServiceDuration += service.duration_min || 60;
                        });
                    } else {
                        totalServiceDuration = bookingData.service_duration || 60; // default to 60 minutes
                    }

                    // Fetch available slots from backend using the same API as quick_booking
                    const getAvailableSlotsUrl = "{{ route('get-available-slots') }}";
                    const slotsUrl =
                        `${getAvailableSlotsUrl}?date=${date}&branch_id=${bookingData.branch_id}&employee_id=${bookingData.employee_id}&service_duration=${totalServiceDuration}`;

                    fetch(slotsUrl)
                        .then(response => response.json())
                        .then(data => {

                            let slots = data.status === 'success' && Array.isArray(data.data) ? data.data :
                                [];

                            // Filter out past slots for today
                            const today = new Date();
                            const yyyy = today.getFullYear();
                            const mm = String(today.getMonth() + 1).padStart(2, '0');
                            const dd = String(today.getDate()).padStart(2, '0');
                            const todayStr = `${yyyy}-${mm}-${dd}`;

                            if (date === todayStr) {
                                const now = new Date();
                                slots = slots.filter(slot => {
                                    if (!slot.value || slot.value === 'No Slot Available')
                                        return false;
                                    const slotDateTime = new Date(slot.value.replace(' ', 'T'));
                                    return slotDateTime > now;
                                });
                            }


                            if (slots.length > 0) {
                                let html = '';
                                const currentTime = $('#reschedule_time_input').val();
                                const selectedDate = $('#reschedule_date').val();
                                const originalDate = $('#reschedule_booking_id').data('original-date');



                                slots.forEach(slot => {
                                    const isDisabled = slot.disabled || slot.value ===
                                        'No Slot Available' || !slot.value;
                                    // Only mark as current if it's the same date as the original booking
                                    const isCurrentSlot = slot.value === currentTime &&
                                        selectedDate === originalDate;


                                    let slotClass = isDisabled ? 'disabled' : 'time-slot';
                                    if (isCurrentSlot) {
                                        slotClass += ' current';
                                    }

                                    html +=
                                        `<li${isDisabled ? ' class="disabled"' : ''}><a href="#" data-time="${slot.value}" class="${slotClass}" onclick="event.preventDefault(); if(!${isDisabled})selectRescheduleTime('${slot.label}', '${slot.value}')">${slot.label}${isCurrentSlot ? ' (Current)' : ''}</a></li>`;
                                });

                                $('#reschedule-time-slots-list').html(html);
                            } else {

                                $('#reschedule-time-slots-list').html(
                                    '<li class="disabled"><a href="#">{{ __('vendorwebsite.no_slots_available_for_the_selected_date') }}</a></li>'
                                );
                            }
                        })
                        .catch(error => {
                            console.error('Error loading slots:', error);
                            $('#reschedule-time-slots-list').html(
                                '<li class="disabled"><a href="#">{{ __('vendorwebsite.error_loading_slots') }}</a></li>'
                            );
                        });
                },
                error: function(xhr) {
                    console.error('Error fetching booking details:', xhr);
                    $('#reschedule-time-slots-list').html(
                        '<li class="disabled"><a href="#">{{ __('vendorwebsite.error_loading_booking_details') }}</a></li>'
                    );
                }
            });
        }

        function selectRescheduleTime(label, value) {
            $('#selected_reschedule_time').text(label);
            $('#reschedule_time_input').val(value);
            $('#reschedule-time-slots-list a').removeClass('active');
            $('#reschedule-time-slots-list a').filter(function() {
                return $(this).text().trim() === label;
            }).addClass('active');

            // Close the dropdown after selection
            const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('rescheduleTimeDropdown'));
            if (dropdown) {
                dropdown.hide();
            }
        }

        // Helper to round a time string to nearest 15 minutes
        function roundTo15(timeStr) {
            if (!timeStr) return "";
            var [h, m] = timeStr.split(':').map(Number);
            var rounded = Math.round(m / 15) * 15;
            if (rounded === 60) {
                h = (h + 1) % 24;
                rounded = 0;
            }
            return (h < 10 ? '0' : '') + h + ':' + (rounded < 10 ? '0' : '') + rounded;
        }



        // Handle Flatpickr date selection
        function setupRescheduleDatePicker() {
            const dateInput = document.getElementById('reschedule_date');
            if (!dateInput || !dateInput._flatpickr) return;

            // Remove any existing change event listeners to prevent duplicates
            $(dateInput).off('change');

            // Use Flatpickr's native onValueUpdate event
            dateInput._flatpickr.config.onValueUpdate.push(function(selectedDates, dateStr) {

                if (dateStr) {
                    clearTimeSelection();
                    renderRescheduleTimeSlots(dateStr);
                }
            });

            // Also listen for the change event as a fallback
            $(dateInput).on('change', function() {
                const selectedDate = $(this).val();

                if (selectedDate) {
                    clearTimeSelection();
                    renderRescheduleTimeSlots(selectedDate);
                }
            });
        }

        // Initialize the date picker when the modal is shown
        $('#rescheduleModal').on('shown.bs.modal', function() {
            // Small timeout to ensure Flatpickr is initialized
            setTimeout(setupRescheduleDatePicker, 100);
        });

        $(document).ready(function() {
            // Add Rate Us button to each completed booking card dynamically (if not already present)


            // Open modal on button click (for debugging)
            $(document).on('click', '.rate-us-btn', function() {

                // This should be handled by Bootstrap data attributes, but we log for debug
                // $('#rateUsModal').modal('show'); // Uncomment if you want to force open via JS
                const bookingId = $(this).data('booking-id');
                $('#rateUs_booking_id').val(bookingId);
                $('#rateUsForm')[0].reset();
                $('#starRating').data('selected', 0);
                $('#starRating .star').css('color', '#E0E0E0');
            });

            // Star rating logic
            $('#starRating .star').on('mouseenter', function() {
                var val = $(this).data('value');
                $('#starRating .star').each(function(i, el) {
                    $(el).css('color', i < val ? '#FFC107' : '#E0E0E0');
                });
            }).on('mouseleave', function() {
                var selected = $('#starRating').data('selected') || 0;
                $('#starRating .star').each(function(i, el) {
                    $(el).css('color', i < selected ? '#FFC107' : '#E0E0E0');
                });
            }).on('click', function() {
                var val = $(this).data('value');
                $('#starRating').data('selected', val);
                $('#starRating .star').each(function(i, el) {
                    $(el).css('color', i < val ? '#FFC107' : '#E0E0E0');
                });
            });

            // Optional: handle form submit
            $('#rateUsForm').on('submit', function(e) {
                e.preventDefault();
                var rating = $('#starRating').data('selected') || 0;
                var feedback = $(this).find('textarea[name="feedback"]').val();
                var bookingId = $(this).find('#rateUs_booking_id').val();

                if (!bookingId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: '{{ __('vendorwebsite.booking_id_not_found_please_try_again') }}',
                        confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                    return;
                }

                if (rating === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: '{{ __('vendorwebsite.please_select_a_rating') }}',
                        confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                    return;
                }

                // Submit review via AJAX
                fetch("{{ route('review.submit') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            rating: rating,
                            review: feedback,
                            booking_id: bookingId
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Hide the modal using jQuery
                            $('#rateUsModal').modal('hide');

                            // Remove the modal backdrop and reset body styles
                            $('body').removeClass('modal-open');
                            $('.modal-backdrop').remove();
                            $('body').css({
                                'overflow': '',
                                'padding-right': ''
                            });

                            // Then show the success message
                            Swal.fire({
                                title: '{{ __('vendorwebsite.thank_you_for_your_feedback') }}',
                                text: '{{ __('vendorwebsite.we_appriciate_your_rating_and_cooments_your_feedback_helps_us_to_improve_our_services') }}',
                                icon: 'success',
                                confirmButtonText: 'Back to Bookings',
                                customClass: {
                                    popup: 'swal2-booking-feedback',
                                    confirmButton: 'btn btn-secondary',
                                    title: 'swal2-title-custom',
                                    content: 'swal2-content-custom'
                                },
                                buttonsStyling: false,
                                allowOutsideClick: false
                            }).then((result) => {
                                // Clean up any remaining backdrops
                                const backdrops = document.querySelectorAll('.modal-backdrop');
                                backdrops.forEach(backdrop => backdrop.remove());

                                // Reset body styles
                                document.body.classList.remove('modal-open');
                                document.body.style.overflow = '';
                                document.body.style.paddingRight = '';

                                // After cleanup, refresh the bookings table
                                const activeTab = $('#booking-tabs .nav-link.active');
                                const target = activeTab.data('bs-target');

                                if (target === '#all') {
                                    $('#all-bookings-table').DataTable().ajax.reload();
                                } else if (target === '#upcoming') {
                                    $('#upcoming-bookings-table').DataTable().ajax.reload();
                                } else if (target === '#complete') {
                                    $('#completed-bookings-table').DataTable().ajax.reload();
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ __('vendorwebsite.failed_to_submit_review') }}',
                                text: data.error ||
                                    '{{ __('vendorwebsite.something_went_wrong_please_try_again') }}',
                                confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false,
                            });
                        }
                    })
                    .catch(err => {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __('vendorwebsite.failed_to_submit_review') }}',
                            text: '{{ __('vendorwebsite.a_network_or_server_error_occured') }}',
                            confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false,
                        });
                    });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Calendar icon click opens date picker
            var calendarIcon = document.getElementById('calendar-icon');
            var dateInput = document.getElementById('reschedule_date');
            if (calendarIcon && dateInput) {
                calendarIcon.addEventListener('click', function() {
                    if (window.flatpickr && dateInput._flatpickr) {
                        dateInput._flatpickr.open();
                    } else if (window.jQuery && typeof jQuery(dateInput).datepicker === 'function') {
                        jQuery(dateInput).datepicker('show');
                    } else {
                        dateInput.focus();
                    }
                });
            }
            // Clock icon click opens time slot dropdown
            var clockIcon = document.getElementById('clock-icon');
            var timeDropdown = document.getElementById('rescheduleTimeDropdown');
            if (clockIcon && timeDropdown) {
                clockIcon.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (window.bootstrap && bootstrap.Dropdown) {
                        var dropdown = bootstrap.Dropdown.getOrCreateInstance(timeDropdown);
                        // Always show the dropdown, even if already open
                        dropdown.show();
                        // Focus the dropdown for accessibility
                        timeDropdown.focus();
                    } else {
                        timeDropdown.click();
                    }
                });
            }
        });
    </script>
@endpush
