<?php

declare(strict_types=1);

namespace Duyler\EventBus\Build;

use Duyler\EventBus\Formatter\IdFormatter;
use UnitEnum;

final readonly class ActorResultSubstitution
{
    public string $actorId;
    public string $requiredActorId;

    public function __construct(
        string|UnitEnum $actorId,
        string|UnitEnum $requiredActorId,
        public object $substitution,
    ) {
        $this->actorId = IdFormatter::toString($actorId);
        $this->requiredActorId = IdFormatter::toString($requiredActorId);
    }
}
