<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

use Closure;
use RecursiveArrayIterator;

class Action
{
    public readonly RecursiveArrayIterator $require;

    public function __construct(
        public readonly string           $id,
        public readonly string | Closure $handler,
        array                            $require = [],
        public readonly array            $classMap = [],
        public readonly array            $providers = [],
        public readonly string | Closure $rollback = '',
        public readonly array            $arguments = [],
        public readonly array            $before = [],
        public readonly array            $after = [],
        public readonly string | Closure $around = '',
        public readonly bool $void = false
    ) {
        $this->require = new RecursiveArrayIterator($require);
    }
}
