<?php

declare(strict_types=1);

namespace Duyler\EventBus\Collection;

use Duyler\EventBus\Bus\TriggerRelation;

class TriggerRelationCollection extends AbstractCollection
{
    public function save(TriggerRelation $triggerRelation): void
    {
        $this->data[$triggerRelation->subscription->actionId][] = $triggerRelation;
    }

    public function has(string $actionId): bool
    {
        return isset($this->data[$actionId]);
    }

    public function shift(string $actionId): TriggerRelation
    {
        return array_shift($this->data[$actionId]);
    }

    public function cleanUp(): void
    {
        $this->data = [];
    }
}
