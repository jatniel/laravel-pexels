<?php

namespace Jatniel\Pexels\Tests;

class Helpers
{
    public static function photoData(array $overrides = []): array
    {
        return array_merge([
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
        ], $overrides);
    }

    public static function collectionData(array $overrides = []): array
    {
        return array_merge([
            'id' => 'abc123',
            'title' => 'Nature Photos',
            'description' => 'Beautiful nature photography',
            'private' => false,
            'media_count' => 50,
            'photos_count' => 45,
            'videos_count' => 5,
        ], $overrides);
    }

    public static function searchResponse(int $count = 2): array
    {
        $photos = [];
        for ($i = 1; $i <= $count; $i++) {
            $photos[] = self::photoData(['id' => $i]);
        }

        return [
            'total_results' => $count,
            'page' => 1,
            'per_page' => 15,
            'photos' => $photos,
        ];
    }

    public static function collectionsResponse(int $count = 2): array
    {
        $collections = [];
        for ($i = 1; $i <= $count; $i++) {
            $collections[] = self::collectionData(['id' => "col-{$i}", 'title' => "Collection {$i}"]);
        }

        return [
            'collections' => $collections,
            'page' => 1,
            'per_page' => 15,
            'total_results' => $count,
        ];
    }

    public static function collectionMediaResponse(int $count = 2): array
    {
        $media = [];
        for ($i = 1; $i <= $count; $i++) {
            $media[] = self::photoData(['id' => $i]);
        }

        return [
            'id' => 'abc123',
            'media' => $media,
            'page' => 1,
            'per_page' => 15,
            'total_results' => $count,
        ];
    }
}
