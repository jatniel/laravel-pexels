<?php

use Jatniel\Pexels\Resources\Photo;

it('creates a photo from array', function () {
    $data = [
        'id' => 12345,
        'width' => 1920,
        'height' => 1080,
        'url' => 'https://www.pexels.com/photo/12345',
        'photographer' => 'John Doe',
        'photographer_url' => 'https://www.pexels.com/@johndoe',
        'photographer_id' => 999,
        'avg_color' => '#AABBCC',
        'src' => [
            'original' => 'https://images.pexels.com/photos/12345/original.jpg',
            'large2x' => 'https://images.pexels.com/photos/12345/large2x.jpg',
            'large' => 'https://images.pexels.com/photos/12345/large.jpg',
            'medium' => 'https://images.pexels.com/photos/12345/medium.jpg',
            'small' => 'https://images.pexels.com/photos/12345/small.jpg',
        ],
        'alt' => 'A beautiful landscape',
    ];

    $photo = Photo::fromArray($data);

    expect($photo->id)->toBe(12345)
        ->and($photo->width)->toBe(1920)
        ->and($photo->height)->toBe(1080)
        ->and($photo->photographer)->toBe('John Doe')
        ->and($photo->alt)->toBe('A beautiful landscape');
});

it('returns the correct url for a given size', function () {
    $photo = Photo::fromArray([
        'id' => 1,
        'width' => 100,
        'height' => 100,
        'url' => 'https://www.pexels.com/photo/1',
        'photographer' => 'Test',
        'photographer_url' => 'https://www.pexels.com/@test',
        'photographer_id' => 1,
        'avg_color' => '#000000',
        'src' => [
            'original' => 'https://example.com/original.jpg',
            'medium' => 'https://example.com/medium.jpg',
        ],
        'alt' => null,
    ]);

    expect($photo->getUrl('medium'))->toBe('https://example.com/medium.jpg')
        ->and($photo->getUrl('original'))->toBe('https://example.com/original.jpg')
        ->and($photo->getUrl('nonexistent'))->toBe('https://example.com/original.jpg');
});

it('generates attribution correctly', function () {
    $photo = Photo::fromArray([
        'id' => 1,
        'width' => 100,
        'height' => 100,
        'url' => 'https://www.pexels.com/photo/1',
        'photographer' => 'Jane Smith',
        'photographer_url' => 'https://www.pexels.com/@janesmith',
        'photographer_id' => 1,
        'avg_color' => '#000000',
        'src' => ['original' => 'https://example.com/original.jpg'],
        'alt' => null,
    ]);

    $attribution = $photo->getAttribution(withLink: false);

    expect($attribution)->toBe('Photo by Jane Smith on Pexels');
});
