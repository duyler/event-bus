<?php

declare(strict_types=1);

namespace Konveyer\EventBus;

use Closure;

class Action
{
    public function __construct(
        public readonly string $name,
        public readonly string $service,
        public readonly string | Closure $handler,
        public readonly array $require = [],
        public readonly array $classMap = [],
        public readonly string | Closure $rollback = '',
        public readonly array $arguments = [],
        public readonly array $before = [],
        public readonly array $after = [],
        public readonly string | Closure $around = '',
        public readonly bool $void = false
    ) {
    }
}
