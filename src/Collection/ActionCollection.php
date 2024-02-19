<?php

declare(strict_types=1);

namespace Duyler\EventBus\Collection;

use Duyler\EventBus\Dto\Action;

use function array_key_exists;

class ActionCollection extends AbstractCollection
{
    private array $byContract = [];

    public function save(Action $action): void
    {
        $this->data[$action->id] = $action;

        if (false === empty($action->contract)) {
            $this->byContract[$action->contract][$action->id] = $action;
        }
    }

    public function get(string $actionId): Action
    {
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

    /** @return array<string, Action> */
    public function getByContract(string $contract): array
    {
        return $this->byContract[$contract] ?? [];
    }
}
