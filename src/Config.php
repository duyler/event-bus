<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Dto\Config as ConfigDTO;

class Config
{
    private const ACTION_CONTAINER_CACHE_DIR = 'action_container';

    public readonly string $actionContainerCacheDir;

    public function __construct(ConfigDTO $config)
    {
        $this->actionContainerCacheDir = $config->defaultCacheDir . self::ACTION_CONTAINER_CACHE_DIR;
    }
}
