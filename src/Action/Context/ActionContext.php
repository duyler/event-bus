<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action\Context;

use Duyler\EventBus\Bus\ActionContainer;
use LogicException;

final class ActionContext extends BaseContext
{
    public function __construct(
        private readonly string $actionId,
        private readonly ActionContainer $actionContainer,
        private readonly mixed $argument,
    ) {
        parent::__construct($this->actionContainer);
    }

    public function argument(): mixed
    {
        return $this->argument ?? throw new LogicException('Argument not defined for action ' . $this->actionId);
    }
}
