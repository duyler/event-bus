<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use Duyler\EventBus\Build\Action;

use function array_key_exists;

class ActionStorage
{
    /**
     * @var array<string, Action>
     */
    private array $data = [];

    /** @var array<string, array<string, Action>> */
    private array $byContract = [];

    /** @var array<string, array<string, Action>> */
    private array $byTrigger = [];

    public function save(Action $action): void
    {
        if (null !== $action->contract) {
            $this->byContract[$action->contract][$action->id] = $action;
        }

        if (null !== $action->listen) {
            $this->byTrigger[$action->listen][$action->id] = $action;
        }

        $this->data[$action->id] = $action;
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
        unset($this->data[$actionId]);
    }

    /** @return array<string, Action> */
    public function getByContract(string $contract): array
    {
        return $this->byContract[$contract] ?? [];
    }

    /** @return array<string, Action> */
    public function getAll(): array
    {
        return $this->data;
    }

    /** @return array<string, Action> */
    public function getByEvent(string $eventId): array
    {
        return $this->byTrigger[$eventId] ?? [];
    }
}
