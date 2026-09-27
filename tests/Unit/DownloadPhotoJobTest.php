<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Jatniel\Pexels\Jobs\DownloadPhotoJob;
use Jatniel\Pexels\Resources\Photo;
use Jatniel\Pexels\Services\StorageService;
use Jatniel\Pexels\Tests\Helpers;

it('downloads the requested sizes when handled', function () {
    Storage::fake('public');
    Http::fake([
        'images.pexels.com/*' => Http::response('fake-image-content'),
    ]);

    $job = new DownloadPhotoJob(Photo::fromArray(Helpers::photoData()), ['original', 'medium']);
    $job->handle(app(StorageService::class));

    Storage::disk('public')->assertExists('pexels/12345/original.jpg');
    Storage::disk('public')->assertExists('pexels/12345/medium.jpg');
});
