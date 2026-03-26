<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action\Context;

use Duyler\EventBus\Bus\ActionContainer;
use Duyler\EventBus\Formatter\IdFormatter;
use LogicException;
use UnitEnum;

use function array_key_exists;

final class FactoryContext extends BaseContext
{
    public function __construct(
        private readonly string $actionId,
        private readonly ActionContainer $actionContainer,

        /** @var array<string, mixed> */
        private $results = [],
    ) {
        parent::__construct($this->actionContainer);
    }

    public function getTypeById(string|UnitEnum $actionId): mixed
    {
        $id = IdFormatter::toString($actionId);
        if (false === array_key_exists($id, $this->results)) {
            throw new LogicException(
                'Type not defined with action id ' . $id . ' for ' . $this->actionId . ' factory',
            );
        }

        return $this->results[$id];
    }
}
