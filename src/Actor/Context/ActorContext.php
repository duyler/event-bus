<?php

declare(strict_types=1);

namespace Duyler\EventBus\Actor\Context;

use Duyler\EventBus\Bus\ActorContainer;
use LogicException;

final class ActorContext extends BaseContext
{
    public function __construct(
        private readonly string $actorId,
        private readonly ActorContainer $actorContainer,
        private readonly mixed $argument,
    ) {
        parent::__construct($this->actorContainer);
    }

    public function argument(): mixed
    {
        return $this->argument ?? throw new LogicException('Argument not defined for actor ' . $this->actorId);
    }
}
