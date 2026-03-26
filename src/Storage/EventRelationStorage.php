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

    public function save(EventRelation $eventRelation, string $scope = 'common'): void
    {
        $this->data[$eventRelation->action->getId() . '.' . $scope][$eventRelation->event->id . '.' . $scope][] = $eventRelation;
        $this->lastById[$eventRelation->event->id . '.' . $scope] = $eventRelation;
    }

    public function has(string $actionId, string $scope = 'common'): bool
    {
        return isset($this->data[$actionId . '.' . $scope]);
    }

    public function shift(string $actionId, string $eventId, string $scope = 'common'): EventRelation
    {
        $this->data[$actionId . '.' . $scope][$eventId . '.' . $scope]
            ?? throw new RuntimeException('Event relation for action ' . $actionId . ' not found');

        /** @var EventRelation $eventRelation */
        $eventRelation = array_shift($this->data[$actionId . '.' . $scope][$eventId . '.' . $scope]);

        return $eventRelation;
    }

    public function getLast(string $eventId, string $scope = 'common'): EventRelation
    {
        return $this->lastById[$eventId . '.' . $scope];
    }

    public function isExists(string $eventId, string $scope = 'common'): bool
    {
        return isset($this->lastById[$eventId . '.' . $scope]);
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
    public function removeByActionId(string $actionId, string $scope = 'common'): void
    {
        if (isset($this->data[$actionId . '.' . $scope])) {
            unset($this->data[$actionId . '.' . $scope]);
        }

        foreach ($this->lastById as $relation) {
            if ($relation->action->getId() . '.' . $scope === $actionId . '.' . $scope) {
                unset($this->lastById[$relation->event->id . '.' . $scope]);
            }
        }
    }
}
