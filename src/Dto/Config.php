<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

readonly class Config
{
    public function __construct(
        public bool $enableCache = false,
        public string $fileCacheDirPath = '',
        public array $classMap = [],
    ) {}
}
