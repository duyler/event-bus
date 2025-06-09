<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use function array_key_exists;

use Duyler\EventBus\Bus\Action;

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
    private array $byEvent = [];

    public function save(Action $action): void
    {
        if (null !== $action->getType()) {
            $this->byType[$action->getType()][$action->getId()] = $action;
        }

        foreach ($action->getListen() as $eventId) {
            $this->byEvent[$eventId][$action->getId()] = $action;
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
    public function getByEvent(string $eventId): array
    {
        return $this->byEvent[$eventId] ?? [];
    }
}
