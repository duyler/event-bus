<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use Duyler\EventBus\Bus\Action;

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
    private array $byType = [];

    /** @var array<string, array<string, Action>> */
    private array $bySubscriptionEvent = [];

    public function save(Action $action): void
    {
        if (null !== $action->getType()) {
            $this->byType[$action->getType()][$action->getId()] = $action;
        }

        foreach ($action->getSubscriptionEvents() as $eventId) {
            $this->bySubscriptionEvent[$eventId][$action->getId()] = $action;
        }

        $this->data[$action->getId()] = $action;
    }

    public function saveDynamic(Action $action): void
    {
        $this->dynamic[$action->getId()] = $action;
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
    public function getByType(string $contract): array
    {
        return $this->byType[$contract] ?? [];
    }

    /** @return array<string, Action> */
    public function getAll(): array
    {
        return $this->data;
    }

    /** @return array<string, Action> */
    public function getBySubscriptionEvent(string $eventId): array
    {
        return $this->bySubscriptionEvent[$eventId] ?? [];
    }

    public function remove(string $actionId): void
    {
        if (false === array_key_exists($actionId, $this->data)) {
            return;
        }

        $action = $this->data[$actionId];

        foreach ($action->getSubscriptionEvents() as $eventId) {
            unset($this->bySubscriptionEvent[$eventId][$actionId]);
        }

        if (null !== $action->getType()) {
            unset($this->byType[$action->getType()][$actionId]);
        }

        unset($this->data[$actionId]);
    }

    public function reset(): void
    {
        $this->data = [];
        $this->dynamic = [];
        $this->byType = [];
        $this->bySubscriptionEvent = [];
    }
}
