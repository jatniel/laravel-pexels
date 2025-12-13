<?php

namespace Jatniel\Pexels\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Jatniel\Pexels\Exceptions\PexelsException;
use Jatniel\Pexels\Jobs\DownloadPhotoJob;
use Jatniel\Pexels\Resources\Photo;

class StorageService
{
    /**
     * Download a photo to local storage.
     */
    public function download(Photo $photo, string|array $sizes = 'original'): array
    {
        $sizes = (array) $sizes;
        $paths = [];

        foreach ($sizes as $size) {
            $url = $photo->getUrl($size);
            $path = $this->buildPath($photo->id, $size);

            $this->downloadFile($url, $path);
            $paths[$size] = $this->getPublicUrl($path);
        }

        return $paths;
    }

    /**
     * Queue a photo download for async processing.
     */
    public function downloadAsync(Photo $photo, string|array $sizes = 'original'): void
    {
        $connection = config('pexels.queue.connection');
        $queue = config('pexels.queue.name', 'pexels');

        DownloadPhotoJob::dispatch($photo, (array) $sizes)
            ->onConnection($connection)
            ->onQueue($queue);
    }

    /**
     * Check if a photo exists locally.
     */
    public function exists(int $photoId, string $size = 'original'): bool
    {
        $path = $this->buildPath($photoId, $size);

        return Storage::disk($this->getDisk())->exists($path);
    }

    /**
     * Get the local URL for a photo.
     */
    public function localUrl(int $photoId, string $size = 'original'): ?string
    {
        $path = $this->buildPath($photoId, $size);

        if (! $this->exists($photoId, $size)) {
            return null;
        }

        return $this->getPublicUrl($path);
    }

    /**
     * Delete a locally stored photo.
     */
    public function delete(int $photoId, ?string $size = null): bool
    {
        $disk = Storage::disk($this->getDisk());

        if ($size) {
            return $disk->delete($this->buildPath($photoId, $size));
        }

        // Delete all sizes
        $directory = $this->getBasePath() . '/' . $photoId;

        return $disk->deleteDirectory($directory);
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

        Storage::disk($this->getDisk())->put($path, $response->body());
    }

    /**
     * Build the storage path for a photo.
     */
    private function buildPath(int $photoId, string $size): string
    {
        $extension = 'jpg';

        return sprintf('%s/%d/%s.%s', $this->getBasePath(), $photoId, $size, $extension);
    }

    /**
     * Get the configured storage disk.
     */
    private function getDisk(): string
    {
        return config('pexels.storage.disk', 'public');
    }

    /**
     * Get the base storage path.
     */
    private function getBasePath(): string
    {
        return config('pexels.storage.path', 'pexels');
    }

    /**
     * Get the public URL for a stored file.
     */
    private function getPublicUrl(string $path): string
    {
        return Storage::disk($this->getDisk())->url($path);
    }

}
