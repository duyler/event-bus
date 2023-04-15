<?php

declare(strict_types=1);

namespace Duyler\EventBus\DI;

use Duyler\DependencyInjection\Provider\AbstractProvider;
use Duyler\EventBus\Dto\Config;

class ActionContainerBuilderProvider extends AbstractProvider
{
    public function __construct(private readonly Config $config)
    {
    }

    public function getParams(): array
    {
        return [
            'containerCacheDir' => $this->config->defaultCacheDir,
        ];
    }
}
