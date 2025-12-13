<?php

namespace Jatniel\Pexels\Exceptions;

interface PexelsResourceInterface
{
    public function getId(): int;

    public function getUrl(): string;

    public function toArray(): array;
}
