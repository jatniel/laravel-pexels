<?php

namespace Jatniel\Pexels\Contracts;

interface PexelsResourceInterface
{
    public function getId(): int;
    public function getUrl(): string;
    public function toArray(): array;
}
