<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Dto\Config as ConfigDTO;

class Config
{
    private const ACTION_CONTAINER_CACHE_DIR = 'action_container';
    private const STATE_HANDLER_CONTAINER_CACHE_DIR  = 'state_handler_container';

    public readonly string $actionContainerCacheDir;
    public readonly string $stateHandlerContainerCacheDir;

    public function __construct(ConfigDTO $config)
    {
        $this->actionContainerCacheDir = $config->defaultCacheDir . self::ACTION_CONTAINER_CACHE_DIR;
        $this->stateHandlerContainerCacheDir = $config->defaultCacheDir . self::STATE_HANDLER_CONTAINER_CACHE_DIR;
    }
}
