<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

use Closure;

readonly class ActionHandlerSubstitution
{
    public function __construct(
        public string $actionId,
        public string|Closure $handler,
        public array $bind = [],
        public array $providers = [],
    ) {}
}
