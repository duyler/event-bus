<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action\Context;

use Duyler\EventBus\Bus\Action;
use Duyler\EventBus\Bus\ActionContainer;
use Duyler\EventBus\Bus\CompleteAction;
use Duyler\EventBus\Formatter\IdFormatter;
use LogicException;
use UnitEnum;

final class FactoryContext extends BaseContext
{
    public function __construct(
        private readonly string $actionId,
        private readonly ActionContainer $actionContainer,

        /** @var array<string, mixed> */
        private $results = [],
        /** @var array<string, CompleteAction> */
        private array $byType = [],
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

    /**
     * @param class-string $type
     */
    public function getType(string $type): mixed
    {
        if (false === array_key_exists($type, $this->byType)) {
            throw new LogicException(
                'Type ' . $type . ' not defined for ' . $this->actionId . ' factory',
            );
        }

        return $this->byType[$type]->result->data;
    }

    /**
     * @param class-string $type
     */
    public function getTypeCollection(string $type): mixed
    {
        $typeId = Action::COLLECTION_PREFIX . $type;
        if (false === array_key_exists($typeId, $this->byType)) {
            throw new LogicException(
                'Type collection not defined with type: ' . $type . ' for ' . $this->actionId . ' factory',
            );
        }

        return $this->byType[$typeId]->result->data;
    }
}
