<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Config;

readonly class ActionContainerBuilder
{
    public function __construct(private Config $config)
    {
    }

    public function build(string $actionId): ActionContainer
    {
        return ActionContainer::build($actionId, $this->config->actionContainerCacheDir);
    }
}
