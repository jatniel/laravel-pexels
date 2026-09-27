<?php

use Jatniel\Pexels\Resources\Photo;
use Jatniel\Pexels\Tests\Helpers;

it('returns the url for a size and falls back to original', function () {
    $photo = Photo::fromArray(Helpers::photoData());

    expect($photo->getUrl('medium'))->toBe('https://images.pexels.com/photos/12345/medium.jpg')
        ->and($photo->getUrl('nonexistent'))->toBe('https://images.pexels.com/photos/12345/original.jpg')
        ->and($photo->getUrl())->toBe('https://www.pexels.com/photo/12345');
});

it('generates attribution with and without link', function () {
    $photo = Photo::fromArray(Helpers::photoData());

    expect($photo->getAttribution(withLink: false))->toBe('Photo by John Doe on Pexels')
        ->and($photo->getAttribution())->toBe('<a href="https://www.pexels.com/@johndoe" target="_blank" rel="noopener noreferrer">Photo by John Doe on Pexels</a>');
});

it('serializes to json', function () {
    $photo = Photo::fromArray(Helpers::photoData());

    expect(json_decode(json_encode($photo), true))->toBe($photo->toArray());
});
