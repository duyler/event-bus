<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

readonly class Config
{
    public function __construct(
        public string $defaultCacheDir = '',
    ) {
    }
}
