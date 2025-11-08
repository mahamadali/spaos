<div class="location-section">
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-4 col-md-6">
                @if (getVendorsetting('bussiness_address_line_1') || getVendorsetting('bussiness_address_line_2'))
                    <div class="contact-info bg-gray-800 p-3 rounded">

                        <h4>{{ getVendorsetting('site_name') }} {{ __('vendorwebsite.location') }}</h4>
                        <p> {{ __('vendorwebsite.find_locations') }}</p>
                        <h6 class="mt-5 pt-lg-3 pt-0">{{ getVendorsetting('bussiness_address_city') }}
                            {{ getVendorsetting('bussiness_address_country') }}</h6>
                        @if (getVendorsetting('bussiness_address_line_1') || getVendorsetting('bussiness_address_line_2'))
                            <p class="mt-2 mb-0"><span class="heading-color">Address:</span>
                                {{ getVendorsetting('bussiness_address_line_1') }}
                                {{ getVendorsetting('bussiness_address_line_2') }}</p>
                        @endif
                        @if (getVendorsetting('inquriy_email'))
                            <p class="mt-2 mb-0"><span class="heading-color">Email:</span>
                                {{ getVendorsetting('inquriy_email') }}
                            </p>
                        @endif
                        @if (getVendorsetting('helpline_number'))
                            <p class="mt-2 mb-0"><span class="heading-color">Phone:</span>
                                {{ getVendorsetting('helpline_number') }}</p>
                        @endif
                    </div>
                @endif
            </div>
            <div class="col-lg-8 col-md-6">
                @php
                    $lat = getVendorsetting('bussiness_address_latitude') ?? '51.5033'; // Default: London Eye
                    $lng = getVendorsetting('bussiness_address_longitude') ?? '-0.1195';
                    $addressParts = array_filter([
                        getVendorsetting('bussiness_address_line_1'),
                        getVendorsetting('bussiness_address_line_2'),
                        getVendorsetting('bussiness_address_city'),
                        getVendorsetting('bussiness_address_state'),
                        getVendorsetting('bussiness_address_country'),
                    ]);
                    $fullAddress = trim(implode(', ', $addressParts));
                    $mapQuery = $fullAddress !== '' ? $fullAddress : $lat . ',' . $lng;
                @endphp

                <iframe loading="lazy" width="100%" height="350" class="iframe-map"
                    src="https://maps.google.com/maps?q={{ urlencode($mapQuery) }}&amp;t=m&amp;z=14&amp;output=embed&amp;iwloc=near"
                    title="Business Location" aria-label="Business Location"></iframe>

            </div>
        </div>
    </div>
</div>
