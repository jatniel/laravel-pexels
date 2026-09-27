<?php

namespace Jatniel\Pexels\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Jatniel\Pexels\Exceptions\PexelsException;
use Jatniel\Pexels\Jobs\DownloadPhotoJob;
use Jatniel\Pexels\Resources\Photo;

class StorageService
{
    public function __construct(
        private readonly string $disk = 'public',
        private readonly string $path = 'pexels',
        private readonly ?string $queueConnection = null,
        private readonly ?string $queue = 'pexels',
    ) {}

    /**
     * Download a photo to local storage.
     *
     * @param  string|list<string>  $sizes
     * @return array<string, string> Public URL keyed by size.
     */
    public function download(Photo $photo, string|array $sizes = 'original'): array
    {
        $paths = [];

        foreach ((array) $sizes as $size) {
            $path = $this->buildPath($photo->id, $size);

            $this->downloadFile($photo->getUrl($size), $path);
            $paths[$size] = $this->disk()->url($path);
        }

        return $paths;
    }

    /**
     * Queue a photo download for async processing.
     *
     * @param  string|list<string>  $sizes
     */
    public function downloadAsync(Photo $photo, string|array $sizes = 'original'): void
    {
        DownloadPhotoJob::dispatch($photo, (array) $sizes)
            ->onConnection($this->queueConnection)
            ->onQueue($this->queue);
    }

    /**
     * Check if a photo exists locally.
     */
    public function exists(int $photoId, string $size = 'original'): bool
    {
        return $this->disk()->exists($this->buildPath($photoId, $size));
    }

    /**
     * Get the local URL for a photo.
     */
    public function localUrl(int $photoId, string $size = 'original'): ?string
    {
        $path = $this->buildPath($photoId, $size);

        return $this->disk()->exists($path) ? $this->disk()->url($path) : null;
    }

    /**
     * Delete a locally stored photo, or all its sizes when no size is given.
     */
    public function delete(int $photoId, ?string $size = null): bool
    {
        if ($size) {
            return $this->disk()->delete($this->buildPath($photoId, $size));
        }

        return $this->disk()->deleteDirectory("{$this->path}/{$photoId}");
    }

    /**
     * Download a file from URL to storage.
     */
    private function downloadFile(string $url, string $path): void
    {
        $response = Http::get($url);

        if ($response->failed()) {
            throw PexelsException::invalidResponse('Failed to download photo.');
        }

        $this->disk()->put($path, $response->body());
    }

    private function buildPath(int $photoId, string $size): string
    {
        return "{$this->path}/{$photoId}/{$size}.jpg";
    }

    private function disk(): FilesystemAdapter
    {
        return Storage::disk($this->disk);
    }
}
