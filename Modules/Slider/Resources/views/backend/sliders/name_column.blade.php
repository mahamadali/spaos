<div class="d-flex align-items-center gap-2">
    <?php
        $img = $row->getFirstMediaUrl('feature_image');
        $name = $row->name;
    ?>
    <img src="{{ $img ?: default_feature_image() }}" alt="banner" class="rounded-circle" style="width:34px;height:34px;object-fit:cover;">
    <div class="d-flex flex-column">
        <span class="fw-semibold">{{ $name }}</span>
    </div>
</div>

