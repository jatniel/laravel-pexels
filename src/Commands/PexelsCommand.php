<?php

namespace Jatniel\Pexels\Commands;

use Illuminate\Console\Command;

class PexelsCommand extends Command
{
    public $signature = 'laravel-pexels';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
