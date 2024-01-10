<?php

declare(strict_types=1);

namespace Duyler\EventBus\Collection;

use Duyler\EventBus\Bus\TriggerRelation;

class TriggerRelationCollection extends AbstractCollection
{
    public function save(TriggerRelation $triggerRelation): void
    {
        $this->data[$triggerRelation->subscription->actionId] = $triggerRelation;
    }

    public function get(string $actionId): TriggerRelation
    {
        return $this->data[$actionId];
    }

    public function has(string $actionId): bool
    {
        return isset($this->data[$actionId]);
    }
}
