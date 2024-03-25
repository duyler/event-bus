<?php

declare(strict_types=1);

namespace Duyler\EventBus\Collection;

use Duyler\EventBus\Bus\TriggerRelation;
use RuntimeException;

class TriggerRelationCollection
{
    /**
     * @var array<string, array<array-key, TriggerRelation>>
     */
    private array $data = [];

    /** @var array<string, TriggerRelation> */
    private array $lastById = [];

    public function save(TriggerRelation $triggerRelation): void
    {
        $this->data[$triggerRelation->action->id][] = $triggerRelation;
        $this->lastById[$triggerRelation->trigger->id] = $triggerRelation;
    }

    public function has(string $actionId): bool
    {
        return isset($this->data[$actionId]);
    }

    public function shift(string $actionId): TriggerRelation
    {
        $this->data[$actionId] ?? throw new RuntimeException('Trigger relation for action ' . $actionId . ' not found');

        return array_shift($this->data[$actionId]);
    }

    public function getLast(string $triggerId): TriggerRelation
    {
        return $this->lastById[$triggerId];
    }

    public function isExists(string $triggerId): bool
    {
        return isset($this->lastById[$triggerId]);
    }

    public function getAll(): array
    {
        return $this->data;
    }

    public function reset(): void
    {
        $this->data = [];
        $this->lastById = [];
    }
}
