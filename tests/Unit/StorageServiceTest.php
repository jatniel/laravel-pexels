<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Jatniel\Pexels\Exceptions\PexelsException;
use Jatniel\Pexels\Jobs\DownloadPhotoJob;
use Jatniel\Pexels\Resources\Photo;
use Jatniel\Pexels\Services\StorageService;
use Jatniel\Pexels\Tests\Helpers;

beforeEach(function () {
    Storage::fake('public');
    $this->photo = Photo::fromArray(Helpers::photoData());
    $this->service = new StorageService;
});

it('downloads a photo and returns its public url', function () {
    Http::fake(['images.pexels.com/*' => Http::response('fake-image-content')]);

    $paths = $this->service->download($this->photo, 'original');

    expect($paths['original'])->toContain('pexels/12345/original.jpg')
        ->and(Storage::disk('public')->get('pexels/12345/original.jpg'))->toBe('fake-image-content');
});

it('skips sizes already stored unless forced', function () {
    Http::fake(['images.pexels.com/*' => Http::response('new-content')]);
    Storage::disk('public')->put('pexels/12345/original.jpg', 'old-content');

    $this->service->download($this->photo, 'original');
    Http::assertNothingSent();
    expect(Storage::disk('public')->get('pexels/12345/original.jpg'))->toBe('old-content');

    $this->service->download($this->photo, 'original', force: true);
    expect(Storage::disk('public')->get('pexels/12345/original.jpg'))->toBe('new-content');
});

it('throws exception when download fails', function () {
    Http::fake(['images.pexels.com/*' => Http::response('Not Found', 404)]);

    $this->service->download($this->photo, 'original');
})->throws(PexelsException::class, 'Failed to download photo.');

it('dispatches the download job on the configured queue', function () {
    Queue::fake();

    (new StorageService(queue: 'custom-queue'))->downloadAsync($this->photo, ['original', 'medium']);

    Queue::assertPushedOn('custom-queue', DownloadPhotoJob::class, fn ($job) => $job->photo->id === 12345
        && $job->sizes === ['original', 'medium']
    );
});

it('checks, resolves and deletes local photos', function () {
    Storage::disk('public')->put('pexels/12345/medium.jpg', 'content');
    Storage::disk('public')->put('pexels/12345/original.jpg', 'content');

    expect($this->service->exists(12345, 'medium'))->toBeTrue()
        ->and($this->service->localUrl(12345, 'medium'))->toContain('pexels/12345/medium.jpg')
        ->and($this->service->localUrl(12345, 'small'))->toBeNull();

    $this->service->delete(12345, 'medium');
    Storage::disk('public')->assertMissing('pexels/12345/medium.jpg');
    Storage::disk('public')->assertExists('pexels/12345/original.jpg');

    $this->service->delete(12345);
    Storage::disk('public')->assertMissing('pexels/12345/original.jpg');
});

it('is configured from the package config', function () {
    Storage::fake('custom');
    config()->set('pexels.storage.disk', 'custom');
    config()->set('pexels.storage.path', 'custom-path');
    Http::fake(['images.pexels.com/*' => Http::response('fake-image-content')]);

    app(StorageService::class)->download($this->photo, 'original');

    Storage::disk('custom')->assertExists('custom-path/12345/original.jpg');
});
