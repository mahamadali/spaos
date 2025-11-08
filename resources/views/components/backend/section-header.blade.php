@props(["toolbar"=>"", "subtitle"=>""])

<div class="d-flex justify-content-between align-items-md-center flex-column flex-md-row gap-3">
    <div>
      {{ $slot }}
    </div>
    @if($toolbar != "")
    <div class="btn-toolbar gap-3 align-items-md-center flex-column flex-md-row" role="toolbar" aria-label="Toolbar with buttons">
        {{ $toolbar }}
    </div>
    @endif
</div>
