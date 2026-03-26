<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use Duyler\DI\Attribute\Finalize;
use Duyler\EventBus\Bus\EventRelation;
use RuntimeException;

use function array_shift;

#[Finalize(method: 'reset')]
class EventRelationStorage
{
    /**
     * @var array<string, array<string, array<array-key, EventRelation>>>
     */
    private array $data = [];

    /** @var array<string, EventRelation> */
    private array $lastById = [];

    public function save(EventRelation $eventRelation, string $correlationId = 'common'): void
    {
        $this->data[$eventRelation->action->getId() . '.' . $correlationId][$eventRelation->event->id . '.' . $correlationId][] = $eventRelation;
        $this->lastById[$eventRelation->event->id . '.' . $correlationId] = $eventRelation;
    }

    public function has(string $actionId, string $correlationId = 'common'): bool
    {
        return isset($this->data[$actionId . '.' . $correlationId]);
    }

    public function shift(string $actionId, string $eventId, string $correlationId = 'common'): EventRelation
    {
        $this->data[$actionId . '.' . $correlationId][$eventId . '.' . $correlationId]
            ?? throw new RuntimeException('Event relation for action ' . $actionId . ' not found');

        /** @var EventRelation $eventRelation */
        $eventRelation = array_shift($this->data[$actionId . '.' . $correlationId][$eventId . '.' . $correlationId]);

        return $eventRelation;
    }

    public function getLast(string $eventId, string $correlationId = 'common'): EventRelation
    {
        return $this->lastById[$eventId . '.' . $correlationId];
    }

    public function isExists(string $eventId, string $correlationId = 'common'): bool
    {
        return isset($this->lastById[$eventId . '.' . $correlationId]);
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

    // @toto Need refactor to remove from lastById without foreach
    public function removeByActionId(string $actionId, string $correlationId = 'common'): void
    {
        if (isset($this->data[$actionId . '.' . $correlationId])) {
            unset($this->data[$actionId . '.' . $correlationId]);
        }

        foreach ($this->lastById as $relation) {
            if ($relation->action->getId() . '.' . $correlationId === $actionId . '.' . $correlationId) {
                unset($this->lastById[$relation->event->id . '.' . $correlationId]);
            }
        }
    }
}
