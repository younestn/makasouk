<div class="ui-section-header {{ $align ?? '' }}">
    @if(!empty($eyebrow))
        <p class="ui-section-eyebrow">{{ $eyebrow }}</p>
    @endif

    <h2 class="ui-section-title">{{ $title }}</h2>

    @if(!empty($description))
        <p class="ui-section-description">{{ $description }}</p>
    @endif
</div>

