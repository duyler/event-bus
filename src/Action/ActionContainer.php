<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\DependencyInjection\Cache\FileCacheHandler;
use Duyler\DependencyInjection\Compiler;
use Duyler\DependencyInjection\Container;
use Duyler\DependencyInjection\DependencyMapper;
use Duyler\DependencyInjection\ReflectionStorage;
use Duyler\DependencyInjection\ServiceStorage;

class ActionContainer extends Container
{
    public function __construct(
        public readonly string $actionId,
        public readonly ?string $containerCacheDir = null,
    ) {
        $cacheHandler = new FileCacheHandler(
            $containerCacheDir ?? dirname('__DIR__'). '/../var/cache/event-bus/container/'
        );
        $reflectionStorage = new ReflectionStorage();
        $serviceStorage = new ServiceStorage();
        $dependencyMapper = new DependencyMapper($reflectionStorage);
        parent::__construct(new Compiler($serviceStorage), $dependencyMapper, $serviceStorage, $cacheHandler);
    }

    public static function build(string $actionId, ?string $containerCacheDir): self
    {
        return new self($actionId, $containerCacheDir);
    }
}
