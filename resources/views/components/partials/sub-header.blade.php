

<div class="iq-navbar-header navs-bg-color" style="height: 9rem;">
    <div class="container-fluid iq-container">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="px-md-4">
                        <h2>{{ $module_title ?? '' }}</h2>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                      @if (!isset($global_booking) && !auth()->user()->hasRole('super admin'))
                        @hasPermission('add_booking')
                        <a href="javascript:void(0)" class="btn btn-secondary appointment-create-btn" id="appointment-button" data-bs-toggle="offcanvas" data-bs-target="#appointmentOffcanvas"><i class="fa-solid fa-plus"></i> {{ __('messages.appointment') }}</a>
                        @endhasPermission
                        @endif
                      @yield('banner-button')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="iq-header-img">
    </div>
</div>
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>
<!-- <script>
    $(document).ready(function () {
    const formBookOffcanvas = document.getElementById("booking-form");
    const offcanvasBookingInstance = bootstrap.Offcanvas.getOrCreateInstance(formBookOffcanvas);

    // Ensure the offcanvas is hidden initially
    offcanvasBookingInstance.hide();

    $(document).on("click", ".appointment-create-btn", function (event) {
        let button = $(this);

        $.ajax({
            url: "{{ route('backend.customers.verify') }}",
            type: "GET",
            data: { type: 'booking' },
            dataType: "json",
            success: function (response) {
                console.log(response);

                if (!response.status) {
                    event.preventDefault(); // Prevent default action
                    window.errorSnackbar(response.message); // Show error message
                    // button.removeAttr("data-crud-id");

                    // Ensure the offcanvas remains closed
                    offcanvasBookingInstance.hide();
                } else {
                    // button.attr("data-crud-id", 0);
                    offcanvasBookingInstance.show();
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
            }
        });
    });
});

    
    </script> -->
