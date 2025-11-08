@extends('frontend::layouts.master')
@section('title')
{{ setting('app_name') }}
@endsection
@section('content')

<x-frontend::section.banner_section :homepages="$homepages" />
<x-frontend::section.meet_frezka_section />
<x-frontend::section.frezka_feature_section  :data="$data" :features="$features" :plan="$plan"/>
<x-frontend::section.why_frezka_section :homepages="$homepages" />
<x-frontend::section.why_choose_section :homepages="$homepages"  :features="$features"  />
<x-frontend::section.price_plan_section :data="$data" />
<x-frontend::section.multiple_business_section :features="$features" />
<x-frontend::section.blog_section :blogs="$blogs"/>
<x-frontend::section.get_started_section />
@endsection
