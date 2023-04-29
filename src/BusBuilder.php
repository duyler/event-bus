<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\DependencyInjection\ContainerBuilder;
use Duyler\EventBus\Dto\Config;
use Duyler\DependencyInjection\Config as DIConfig;

class BusBuilder
{
    private const CACHE_DIR = '/../var/cache/event-bus/';

    public static function build(Config $config = null): Bus
    {
        if ($config === null) {
            $config = new Config(
                defaultCacheDir: dirname('__DIR__'). self::CACHE_DIR,
            );
        }

        $DIConfig = new DIConfig(
            cacheDirPath: $config->defaultCacheDir,
        );

        $container = ContainerBuilder::build($DIConfig);
        $container->set($config);

        return $container->make(Bus::class);
    }
}
