<?php

namespace Jatniel\Pexels\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Jatniel\Pexels\Resources\Photo;
use Jatniel\Pexels\Services\StorageService;

class DownloadPhotoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly Photo $photo,
        public readonly array $sizes = ['original'],
    ) {}

    public function handle(StorageService $storageService): void
    {
        $storageService->download($this->photo, $this->sizes);
    }

}
