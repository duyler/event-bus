<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

use Closure;

readonly class Coroutine
{
    public function __construct(
        public string           $id,
        public string | Closure $handler,
        public string | Closure $callback,
        public array            $classMap = [],
        public array            $providers = [],
        public string           $driver = 'pcntl',
    ) {
    }
}
