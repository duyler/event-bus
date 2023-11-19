<?php

declare(strict_types=1);

namespace Duyler\EventBus\Collection;

use Duyler\EventBus\Bus\Event;
use Duyler\EventBus\Dto\Result;

class EventCollection extends AbstractCollection
{
    public function save(Event $event): void
    {
        $this->data[$event->action->id] = $event;
    }

    /**
     * @return Event[]
     */
    public function getAllByArray(array $array): array
    {
        return array_intersect_key($this->data, array_flip($array));
    }

    public function getResult(string $actionId): ?Result
    {
        return $this->data[$actionId]->result ?? null;
    }

    public function get(string $actionId): Event
    {
        return $this->data[$actionId];
    }

    public function isExists(string $actionId): bool
    {
        return array_key_exists($actionId, $this->data);
    }
}
