<figure {{ $attributes->class(['pexels-image']) }}>
    <img
        src="{{ $src }}"
        alt="{{ $photo->alt ?? $photo->photographer }}"
        width="{{ $photo->width }}"
        height="{{ $photo->height }}"
        loading="lazy"
    />

    @if($attribution)
        <figcaption class="pexels-attribution">
            {!! $photo->getAttribution() !!}
        </figcaption>
    @endif
</figure>
