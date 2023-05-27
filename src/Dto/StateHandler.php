<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

use Duyler\EventBus\Enum\StateType;

readonly class StateHandler
{
    public function __construct(
        public StateType $type,
        public string $class,
        public array $providers = [],
        public array $classMap = [],
        public string $alias = '',
    ) {
    }
}
