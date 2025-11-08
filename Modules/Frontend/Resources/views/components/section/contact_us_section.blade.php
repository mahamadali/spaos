@props(['contact_us', 'superadmin_email'])

<section class="section-spacing-top">
    <div class="container">
        <div class="section-title-wrap center">
            <span class="subtitle">{{__('messages.contact_us')}}</span>
            <h3 class="section-title">{{__('messages.need_assistance?_we’re_Here_to_help!')}}</h3>
            <p class="title-description">{{__('messages.Looking_to_get_in_touch?')}}</p>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="inquire-card rounded-3">
                    <div class="inquirie-icon">
                        <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_111_794)">
                                <path d="M22.75 5.6875L13 14.625L3.25 5.6875" stroke="#19235A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M3.25 5.6875H22.75V19.5C22.75 19.7155 22.6644 19.9222 22.512 20.0745C22.3597 20.2269 22.153 20.3125 21.9375 20.3125H4.0625C3.84701 20.3125 3.64035 20.2269 3.48798 20.0745C3.3356 19.9222 3.25 19.7155 3.25 19.5V5.6875Z"
                                    stroke="#19235A"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                /> 
                                <path d="M11.2277 13L3.50085 20.083" stroke="#19235A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M22.4991 20.083L14.7722 13" stroke="#19235A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </g>
                            <defs>
                                <clipPath id="clip0_111_794">
                                    <rect width="26" height="26" fill="white" />
                                </clipPath>
                            </defs>
                        </svg>
                    </div>
                    <div>
                        <h5>{{__('messages.general_inquiries')}}</h5>
                        <p class="mb-4">
                            {{__('messages.Have_a_question?_email_us_at')}} 
                            <a class="text-secondary text-decoration-none">{{setting('inquriy_email') }}</a>, 
                            {{__('messages.and_our_team_will_get_back_to_you_ASAP.')}}
                        </p>
                        <a href="mailto:{{ setting('inquriy_email') }}" class="btn btn-secondary">{{__('messages.email_us')}}</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mt-lg-0 mt-4">
                <div class="inquire-card rounded-3">
                    <div class="inquirie-icon">
                        <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_111_802)">
                                <path d="M9.75 11.375H16.25" stroke="#19235A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9.75 14.625H16.25" stroke="#19235A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8.11794 21.4411C10.166 22.6264 12.5752 23.0264 14.8964 22.5666C17.2176 22.1068 19.2924 20.8185 20.7339 18.942C22.1754 17.0654 22.8853 14.7286 22.7311 12.3673C22.577 10.006 21.5694 7.7814 19.8961 6.10816C18.2229 4.43492 15.9983 3.4273 13.637 3.27316C11.2757 3.11902 8.93891 3.82888 7.06235 5.27039C5.18578 6.7119 3.8975 8.78668 3.43769 11.1079C2.97788 13.4291 3.37793 15.8383 4.56325 17.8864L3.29271 21.6797C3.24497 21.8229 3.23804 21.9765 3.2727 22.1234C3.30736 22.2703 3.38224 22.4046 3.48895 22.5113C3.59566 22.618 3.72998 22.6929 3.87686 22.7275C4.02373 22.7622 4.17736 22.7553 4.32052 22.7075L8.11794 21.4411Z" stroke="#19235A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </g>
                            <defs>
                            <clipPath id="clip0_111_802">
                            <rect width="26" height="26" fill="white"/>
                            </clipPath>
                            </defs>
                        </svg>
                    </div>
                    <div>
                        <h5>{{__('messages.Call_or_email_us_today!')}}</h5>
                        <p class="mb-4">{{('messages.Want_to_learn_more_about')}} {{setting('app_name') }}? {{__('messages.Start_a_chat_a_call_or_book_a_demo_today.')}}</p>
                        <a href="tel:{{ setting('helpline_number') }}" class="btn btn-secondary">{{__('messages.Call_now')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
