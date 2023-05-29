<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\DependencyInjection\ContainerBuilder;
use Duyler\EventBus\Dto\Config;
use Duyler\DependencyInjection\Config as DIConfig;
use Duyler\EventBus\Dto\StateHandler;
use Duyler\EventBus\Enum\StateType;
use Duyler\EventBusCoroutine\CoroutineStateHandlerMain;

class BusBuilder
{
    private const CACHE_DIR = '/../var/cache/event-bus/';

    public static function build(Config $config = null): Bus
    {
        $coroutineHandler = new StateHandler(
            type: StateType::MainSuspendAction,
            class: CoroutineStateHandlerMain::class,
            alias: 'default',
        );

        if ($config === null) {
            $config = new Config(
                defaultCacheDir: dirname('__DIR__'). self::CACHE_DIR,
                coroutineHandler: $coroutineHandler->alias,
            );
        }

        $DIConfig = new DIConfig(
            cacheDirPath: $config->defaultCacheDir,
        );

        $container = ContainerBuilder::build($DIConfig);
        $container->set($config);

        /** @var Bus $bus */
        $bus = $container->make(Bus::class);
        $bus->addStateHandler($coroutineHandler);

        return $bus;
    }
}
