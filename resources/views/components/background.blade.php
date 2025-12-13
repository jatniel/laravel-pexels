<div
    {{ $attributes->class(['pexels-background'])->style(["background-image: url('{$src}')"]) }}
>
    {{ $slot }}

    @if($attribution)
        <span class="pexels-attribution pexels-attribution--{{ $attributionPosition }}">
            {!! $photo->getAttribution() !!}
        </span>
    @endif
</div>
