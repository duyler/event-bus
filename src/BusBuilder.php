<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\DependencyInjection\ContainerBuilder;
use Duyler\EventBus\Action\ActionContainerBuilder;
use Duyler\EventBus\DI\ActionContainerBuilderProvider;
use Duyler\EventBus\Dto\Config;
use Duyler\DependencyInjection\Config as DIConfig;

class BusBuilder
{
    private const PROVIDERS = [
        ActionContainerBuilder::class => ActionContainerBuilderProvider::class
    ];

    private const CACHE_DIR = '/../var/cache/event-bus/';

    public static function build(Config $config = null): Bus
    {
        if ($config === null) {
            $config = new Config(
                defaultCacheDir: dirname('__DIR__'). self::CACHE_DIR,
            );
        }

        $diConfig = new DIConfig(
            cacheDirPath: $config->defaultCacheDir,
        );

        $container = ContainerBuilder::build($diConfig);
        $container->set($config);
        $container->setProviders(self::PROVIDERS);

        return $container->make(Bus::class);
    }
}
