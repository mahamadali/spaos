@extends('backend.layouts.app')

@section('title') {{ __('messages.cancel_subscription') }} @endsection

@section('content')

<div class="card">
    <div class="card-body">


        <div class="section-spacing">
        <div class="container">

            <div class="section-spacing-bottom px-0">

            <h5 class="main-title text-capitalize mb-2">{{ __('messages.subscription_detail') }}</h5>
            <div class="container">
                    <div class="row">
                        <div class="col-lg-1 d-lg-block d-none"></div>
                        <div class="col-lg-12">
                            <div class="upgrade-plan d-flex flex-wrap gap-3 align-items-center justify-content-between rounded p-4 bg-warning-subtle border border-warning">
                                    @if(!empty($subscriptions))

                                            <div class="d-flex justify-content-center align-items-center gap-4">
                                                <i class="ph ph-crown text-warning"></i>
                                                <div>
                                                    <h6 class="super-plan">{{ $planDetails->name ?? '' }} {{ __('frontend.plan') }}</h6>
                                                    <p class="mb-0 text-body">{{ __('messages.expiring_on') }} {{ \Carbon\Carbon::parse($subscriptions['end_date'])->format('d M, Y') ?? '-' }}</p>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-3">
                                                <a href="{{ route('backend.upgrade-plan') }}" class="btn btn-light">{{ __('messages.upgrade') }}</a>
                                                @if($planDetails && $planDetails->name !== 'Free')
                                                <button type="button" class="btn btn-primary"  data-subscription-id="{{ $subscriptions->id }}" data-bs-toggle="modal" data-bs-target="#CancleSubscriptionModal">{{__('messages.cancel')}}</button>
                                                @endif
                                            </div>

                                    @else
                                    <div class="d-flex gap-3">
                                            <h6 class="super-plan">{{__('messages.you_do_not_have_an_active_subscription.')}}</h6>
                                            <p class="mb-0 text-body">{{ __('frontend.upgrade_consider') }}</p>
                                    </div>
                                    <div class="d-flex gap-3">
                                        <a href="{{ route('backend.upgrade-plan') }}" class="btn btn-light">{{ __('messages.upgrade') }}</a>
                                    </div>
                                    @endif
                            </div>
                        </div>
                        <div class="col-lg-1 d-lg-block d-none"></div>
                    </div>



                    <div class="modal fade" id="CancleSubscriptionModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-acoount-card">
                            <div class="modal-content position-relative">
                                <button type="button" class="btn btn-primary custom-close-btn rounded-2 text-end" data-bs-dismiss="modal">
                                <i class="ph ph-x text-white fw-bold "></i>
                                </button>
                            <div class="modal-body modal-acoount-info text-center">
                                <h6 class="mt-3 pt-2">{{ __('messages.are_you_sure_you_want_to_cancel') }}</h6>
                                <div class="d-flex justify-content-center gap-3 mt-4 pt-3">
                                    <button type="button" class=" btn btn-dark" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                                    <button type="button" class="btn btn-primary" id="proceedButton" onclick="cancelSubscription()">{{ __('messages.proceed') }}</button>
                                    <div id="loader" class="mt-3" style="display: none;">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">{{ __('messages.loading') }}</span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>

    </div>
</div>
@endsection

@push('after-scripts')
<script>

    let baseUrl = document.querySelector('meta[name="baseUrl"]').getAttribute('content');

    function cancelSubscription() {

        const subscriptionId = document.querySelector('[data-bs-target="#CancleSubscriptionModal"]').getAttribute('data-subscription-id');
        document.getElementById('proceedButton').style.display = 'none';
        document.getElementById('loader').style.display = 'block';
            fetch(`${baseUrl}/cancel-subscription`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id: subscriptionId })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loader').style.display = 'none';
                if (data.success) {
                    $('#CancleSubscriptionModal').modal('hide');
                    window.successSnackbar(data.message);

                    setTimeout(() => {
                        window.location.href = "{{ route('backend.billing-page') }}";
                    }, 2000);
                } else {
                    document.getElementById('proceedButton').style.display = 'block';
                    // Handle the case where cancellation was not successful
                }
            })
            .catch(error => {
                document.getElementById('loader').style.display = 'none';
                document.getElementById('proceedButton').style.display = 'block';
                console.error('Error:', error);

            });

    }
    </script>
@endpush
