<?php

declare(strict_types=1);

namespace Jine\EventBus;

class ConfigProvider
{
    private string $validateCachePath = '';

    public function setCachePath(string $path): void
    {
        $this->validateCachePath = $path;
    }

    public function getCachePath(): string
    {
        return $this->validateCachePath;
    }
}

