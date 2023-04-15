<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

class ActionContainerBuilder
{
    private const CACHE_DIR = 'action-container';

    public function __construct(private readonly string $containerCacheDir)
    {
    }

    public function build(string $actionId): ActionContainer
    {
        return ActionContainer::build($actionId, $this->containerCacheDir . self::CACHE_DIR);
    }
}
