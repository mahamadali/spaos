{{-- Meet Frezka Start --}}
<section class="section-spacing">
    <div class="container">
        <div class="action-free-box rounded">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h3 class="action-title mb-0 fw-500">{{__('messages.empowering_salons_to_Streamline_bookings_and_client_experiences')}}</h3>
                </div>
                <div class="col-lg-2 d-lg-block d-none"></div>
                <div class="col-lg-4 col-md-6 mt-lg-0 mt-4">
                    <h5 class="text-secondary">Meet {{setting('app_name') }}</h5>
                    <p class="m-0">{{__('messages.A_powerful_and_perfectly_managed_software_with_unique_features_and_updates')}}</p>
                    @if(!auth()->user())
                        <a href="{{ route('pricing_plan', ['id' => 1]) }}" class="btn btn-secondary action-free-box-btn">{{__('messages.try')}} {{setting('app_name') }} {{__('messages.for_free')}}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
{{-- Meet Frezka End --}}