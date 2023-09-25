<?php

declare(strict_types=1);

namespace Duyler\EventBus\Collection;

use Duyler\EventBus\Dto\Action;
use RuntimeException;

use function array_key_exists;

class ActionCollection extends AbstractCollection
{
    private array $byContract = [];

    public function save(Action $action): void
    {
        if (array_key_exists($action->id, $this->data)) {
            throw new RuntimeException('Action ' . $action->id . ' already registered');
        }
        $this->data[$action->id] = $action;

        if (empty($action->contract) === false) {
            $this->byContract[$action->contract][$action->id] = $action;
        }
    }

    public function get(string $actionId): Action
    {
        if ($this->isExists($actionId) === false) {
            throw new RuntimeException('Action ' . $actionId . ' not found in the collection');
        }
        return $this->data[$actionId];
    }

    public function isExists(string $actionId): bool
    {
        return array_key_exists($actionId, $this->data);
    }

    public function remove(string $actionId): void
    {
        unset($actionId, $this->data);
    }

    /** @return Action[] */
    public function getByContract(string $contract): array
    {
        return $this->byContract[$contract] ?? [];
    }
}
