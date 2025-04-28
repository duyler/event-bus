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

    /**
     * @var array<string, Action>
     */
    private array $dynamic = [];

    /** @var array<string, array<string, Action>> */
    private array $byContract = [];

    /** @var array<string, array<string, Action>> */
    private array $byEvent = [];

    public function save(Action $action): void
    {
        if (null !== $action->type) {
            $this->byContract[$action->type][$action->id] = $action;
        }

        foreach ($action->listen as $eventId) {
            $this->byEvent[$eventId][$action->id] = $action;
        }

        $this->data[$action->id] = $action;
    }

    public function saveDynamic(Action $action): void
    {
        $this->dynamic[$action->id] = $action;
    }

    public function get(string $actionId): Action
    {
        return $this->data[$actionId];
    }

    public function isExists(string $actionId): bool
    {
        return array_key_exists($actionId, $this->data);
    }

    public function isExistsDynamic(string $actionId): bool
    {
        return array_key_exists($actionId, $this->dynamic);
    }

    public function removeDynamic(string $actionId): void
    {
        if (array_key_exists($actionId, $this->dynamic)) {
            unset($this->data[$actionId]);
            unset($this->dynamic[$actionId]);
        }
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
        return $this->byEvent[$eventId] ?? [];
    }
}
