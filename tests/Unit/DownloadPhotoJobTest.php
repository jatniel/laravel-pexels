<?php

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Jatniel\Pexels\Jobs\DownloadPhotoJob;
use Jatniel\Pexels\Resources\Photo;
use Jatniel\Pexels\Services\StorageService;
use Jatniel\Pexels\Tests\Helpers;

it('implements ShouldQueue', function () {
    $photo = Photo::fromArray(Helpers::photoData());
    $job = new DownloadPhotoJob($photo, ['original']);

    expect($job)->toBeInstanceOf(ShouldQueue::class);
});

it('downloads photo using storage service when handled', function () {
    Storage::fake('public');
    Http::fake([
        'images.pexels.com/*' => Http::response('fake-image-content'),
    ]);

    $photo = Photo::fromArray(Helpers::photoData());
    $job = new DownloadPhotoJob($photo, ['original', 'medium']);

    $job->handle(app(StorageService::class));

    Storage::disk('public')->assertExists('pexels/12345/original.jpg');
    Storage::disk('public')->assertExists('pexels/12345/medium.jpg');
});

it('can be dispatched to a queue', function () {
    Queue::fake();

    $photo = Photo::fromArray(Helpers::photoData());

    DownloadPhotoJob::dispatch($photo, ['original']);

    Queue::assertPushed(DownloadPhotoJob::class, function ($job) {
        return $job->photo->id === 12345
            && $job->sizes === ['original'];
    });
});

it('uses default sizes when none provided', function () {
    $photo = Photo::fromArray(Helpers::photoData());
    $job = new DownloadPhotoJob($photo);

    expect($job->sizes)->toBe(['original']);
});
