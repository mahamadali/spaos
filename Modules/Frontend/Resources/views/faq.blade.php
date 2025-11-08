@extends('frontend::layouts.master')
@section('title')
{{ __('messages.lbl_faq') }}
@endsection

@section('content')
<x-frontend::section.breadcrumb :data="$data['bread_crumb']" />

<div class="section-spacing-top">
        <div class="container">
            @if(!empty($data['faqs']) && $data['faqs']->count() > 0)
                <div class="accordion" id="faq">
                    @foreach($data['faqs']->sortBy('id') as $key => $value)
                        <div class="accordion-item custom-accordion rounded">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button  custom-accordion-button gap-3 p-0 {{ $key == 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne_{{ $value->id }}" aria-expanded="true" aria-controls="collapseOne_{{ $value->id }}">
                                    {{ $value->question }}
                                </button>
                            </h2>
                            <div id="collapseOne_{{ $value->id }}" class="accordion-collapse collapse {{ $key == 0 ? 'show' : '' }}" aria-labelledby="headingOne" data-bs-parent="#faq">
                                <div class="accordion-body custom-accordion-body p-0">
                                    <span> {{ $value->answer }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <p class="text-muted">{{__('messages.no_FAQs_found')}}</p>
                </div>
            @endif
        </div>
</div>

<x-frontend::section.get_started_section />
@endsection
