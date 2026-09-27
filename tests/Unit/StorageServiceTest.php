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

it('downloads a photo in a single size', function () {
    Http::fake([
        'images.pexels.com/*' => Http::response('fake-image-content'),
    ]);

    $paths = $this->service->download($this->photo, 'original');

    expect($paths)->toHaveKey('original')
        ->and(Storage::disk('public')->get('pexels/12345/original.jpg'))->toBe('fake-image-content');
});

it('downloads a photo in multiple sizes', function () {
    Http::fake([
        'images.pexels.com/*' => Http::response('fake-image-content'),
    ]);

    $paths = $this->service->download($this->photo, ['original', 'medium', 'small']);

    expect($paths)->toHaveKeys(['original', 'medium', 'small']);
    Storage::disk('public')->assertExists('pexels/12345/original.jpg');
    Storage::disk('public')->assertExists('pexels/12345/medium.jpg');
    Storage::disk('public')->assertExists('pexels/12345/small.jpg');
});

it('skips sizes already stored unless forced', function () {
    Http::fake([
        'images.pexels.com/*' => Http::response('new-content'),
    ]);
    Storage::disk('public')->put('pexels/12345/original.jpg', 'old-content');

    $this->service->download($this->photo, 'original');
    Http::assertNothingSent();
    expect(Storage::disk('public')->get('pexels/12345/original.jpg'))->toBe('old-content');

    $this->service->download($this->photo, 'original', force: true);
    expect(Storage::disk('public')->get('pexels/12345/original.jpg'))->toBe('new-content');
});

it('throws exception when download fails', function () {
    Http::fake([
        'images.pexels.com/*' => Http::response('Not Found', 404),
    ]);

    $this->service->download($this->photo, 'original');
})->throws(PexelsException::class, 'Failed to download photo.');

it('dispatches download job for async processing', function () {
    Queue::fake();

    $this->service->downloadAsync($this->photo, ['original', 'medium']);

    Queue::assertPushed(DownloadPhotoJob::class, function ($job) {
        return $job->photo->id === 12345
            && $job->sizes === ['original', 'medium'];
    });
});

it('dispatches download job on configured queue', function () {
    Queue::fake();
    $this->service = new StorageService(queue: 'custom-queue');

    $this->service->downloadAsync($this->photo, 'original');

    Queue::assertPushedOn('custom-queue', DownloadPhotoJob::class);
});

it('checks if a photo exists locally', function () {
    expect($this->service->exists(12345, 'original'))->toBeFalse();

    Storage::disk('public')->put('pexels/12345/original.jpg', 'content');

    expect($this->service->exists(12345, 'original'))->toBeTrue();
});

it('returns local url when photo exists', function () {
    Storage::disk('public')->put('pexels/12345/medium.jpg', 'content');

    $url = $this->service->localUrl(12345, 'medium');

    expect($url)->toBeString()->toContain('pexels/12345/medium.jpg');
});

it('returns null when photo does not exist locally', function () {
    $url = $this->service->localUrl(12345, 'medium');

    expect($url)->toBeNull();
});

it('deletes a specific size', function () {
    Storage::disk('public')->put('pexels/12345/medium.jpg', 'content');
    Storage::disk('public')->put('pexels/12345/original.jpg', 'content');

    $this->service->delete(12345, 'medium');

    Storage::disk('public')->assertMissing('pexels/12345/medium.jpg');
    Storage::disk('public')->assertExists('pexels/12345/original.jpg');
});

it('deletes all sizes for a photo', function () {
    Storage::disk('public')->put('pexels/12345/medium.jpg', 'content');
    Storage::disk('public')->put('pexels/12345/original.jpg', 'content');

    $this->service->delete(12345);

    Storage::disk('public')->assertMissing('pexels/12345/medium.jpg');
    Storage::disk('public')->assertMissing('pexels/12345/original.jpg');
});

it('uses configured storage disk', function () {
    Storage::fake('custom');

    Http::fake([
        'images.pexels.com/*' => Http::response('fake-image-content'),
    ]);

    $service = new StorageService(disk: 'custom');
    $service->download($this->photo, 'original');

    Storage::disk('custom')->assertExists('pexels/12345/original.jpg');
});

it('uses configured storage path', function () {

    Http::fake([
        'images.pexels.com/*' => Http::response('fake-image-content'),
    ]);

    $service = new StorageService(path: 'custom-path');
    $service->download($this->photo, 'original');

    Storage::disk('public')->assertExists('custom-path/12345/original.jpg');
});
