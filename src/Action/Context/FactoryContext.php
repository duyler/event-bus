<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action\Context;

use Duyler\EventBus\Bus\ActionContainer;
use Duyler\EventBus\Formatter\IdFormatter;
use LogicException;
use UnitEnum;

final class FactoryContext extends BaseContext
{
    public function __construct(
        private readonly string $actionId,
        private readonly ActionContainer $actionContainer,
        /** @var array<string, mixed> */
        private array $context = [],
    ) {
        parent::__construct($this->actionContainer);
    }

    public function getType(string|UnitEnum $actionId): mixed
    {
        $id = IdFormatter::toString($actionId);
        if (false === array_key_exists($id, $this->context)) {
            throw new LogicException('Addressing an invalid context from ' . $this->actionId);
        }

        return $this->context[$id];
    }
}
