<?php

declare(strict_types=1);

namespace Duyler\EventBus\Collection;

use Duyler\EventBus\Bus\TriggerRelation;

class TriggerRelationCollection extends AbstractCollection
{
    /** @var array<string, TriggerRelation>  */
    private array $lastById = [];

    public function save(TriggerRelation $triggerRelation): void
    {
        $this->data[$triggerRelation->subscription->actionId][] = $triggerRelation;
        $this->lastById[$triggerRelation->trigger->id] = $triggerRelation;
    }

    public function has(string $actionId): bool
    {
        return isset($this->data[$actionId]);
    }

    public function shift(string $actionId): TriggerRelation
    {
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

    public function cleanUp(): void
    {
        $this->data = [];
    }
}
