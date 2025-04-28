<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action\Context;

use Duyler\EventBus\Bus\ActionContainer;
use LogicException;

final class FactoryContext extends BaseContext
{
    public function __construct(
        private string $actionId,
        private ActionContainer $actionContainer,
        /** @var array<string, mixed> */
        private array $context = [],
    ) {
        parent::__construct($this->actionContainer);
    }

    public function type(string $type): mixed
    {
        if (false === array_key_exists($type, $this->context)) {
            throw new LogicException('Addressing an invalid context from ' . $this->actionId);
        }

        return $this->context[$type];
    }
}
