<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto\State;

readonly class StateBeforeHandler
{
    public function __construct(
        public string $class,
        public array $providers = [],
        public array $classMap = [],
    ) {
    }
}
