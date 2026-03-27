<?php

declare(strict_types=1);

namespace Duyler\EventBus\Build;

use Closure;
use Duyler\EventBus\Formatter\IdFormatter;
use UnitEnum;

final readonly class ActorHandlerSubstitution
{
    public string $actorId;

    public function __construct(
        string|UnitEnum $actorId,
        public string|Closure $handler,
        /** @var array<string, string> */
        public array $bind = [],
        /** @var array<string, string> */
        public array $providers = [],
    ) {
        $this->actorId = IdFormatter::toString($actorId);
    }
}
