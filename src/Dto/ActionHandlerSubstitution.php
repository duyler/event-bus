<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Dto;

use Closure;
use Duyler\ActionBus\Formatter\ActionIdFormatter;
use UnitEnum;

readonly class ActionHandlerSubstitution
{
    public string $actionId;

    public function __construct(
        string|UnitEnum $actionId,
        public string|Closure $handler,
        public array $bind = [],
        public array $providers = [],
    ) {
        $this->actionId = ActionIdFormatter::toString($actionId);
    }
}
