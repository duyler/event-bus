<?php

declare(strict_types=1);

namespace Duyler\EventBus\Coroutine;

use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Coroutine;
use Duyler\EventBus\Storage;

readonly class CoroutineHandler
{
    public function __construct(
        private CoroutineDriverProvider $driverProvider,
        private Storage                 $storage,
    ) {
    }

    public function handle(Action $action, Coroutine $coroutine, mixed $value): void
    {
        if (is_callable($coroutine->handler)) {
            $handler = $coroutine->handler;
        } else {
            $container = $this->storage->container()->get($action->id);
            $container->bind($coroutine->classMap);
            $container->setProviders($coroutine->providers);
            $handler = $container->make($coroutine->handler);
        }

        $driver = $this->driverProvider->get($coroutine->driver);
        $driver->process($handler, $value);
    }
}
