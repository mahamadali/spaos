@foreach ($items as $item)

@php
    // active detection
    $active = '';
    if($item->hasChildren()) {
        if($item->children()->where('isActive', true)->first()) {
            $active = 'active open';
        }
    } elseif($item->isActive) {
        $active = 'active';
    }

    // icon support (if item has icon in data['icon'])
    $icon = $item->data['icon'] ?? 'zmdi zmdi-dot-circle';
@endphp

<li class="{{ $active }}" @lm_attrs($item) @lm_endattrs>

    @if ($item->hasChildren())
        <a href="javascript:void(0);" class="menu-toggle" @lm_attrs($item->link) @lm_endattrs>
            <i class="{{ $icon }}"></i>
            <span>{!! $item->title !!}</span>
        </a>

        <ul class="ml-menu" style="display: {{ $active ? 'block' : 'none' }};">
            @include(config('laravel-menu.views.bootstrap-items'), ['items' => $item->children()])
        </ul>

    @else
        <a href="{!! $item->url() !!}" @lm_attrs($item->link) @lm_endattrs>
            <i class="{{ $icon }}"></i>
            <span>{!! $item->title !!}</span>
        </a>
    @endif

</li>

@if ($item->divider)
    <li {!! Lavary\Menu\Builder::attributes($item->divider) !!}></li>
@endif

@endforeach