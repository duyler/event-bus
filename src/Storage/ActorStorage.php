<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use Duyler\EventBus\Bus\Actor;

use function array_key_exists;

class ActorStorage
{
    /**
     * @var array<string, Actor>
     */
    private array $data = [];

    /**
     * @var array<string, Actor>
     */
    private array $dynamic = [];

    /** @var array<string, array<string, Actor>> */
    private array $byType = [];

    /** @var array<string, array<string, Actor>> */
    private array $bySubscriptionEvent = [];

    public function save(Actor $actor): void
    {
        if (null !== $actor->getType()) {
            $this->byType[$actor->getType()][$actor->getId()] = $actor;
        }

        foreach ($actor->getSubscriptionEvents() as $eventId) {
            $this->bySubscriptionEvent[$eventId][$actor->getId()] = $actor;
        }

        $this->data[$actor->getId()] = $actor;
    }

    public function saveDynamic(Actor $actor): void
    {
        $this->dynamic[$actor->getId()] = $actor;
    }

    public function get(string $actorId): Actor
    {
        return $this->data[$actorId];
    }

    public function isExists(string $actorId): bool
    {
        return array_key_exists($actorId, $this->data);
    }

    public function isExistsDynamic(string $actorId): bool
    {
        return array_key_exists($actorId, $this->dynamic);
    }

    public function removeDynamic(string $actorId): void
    {
        if (array_key_exists($actorId, $this->dynamic)) {
            unset($this->data[$actorId]);
            unset($this->dynamic[$actorId]);
        }
    }

    /** @return array<string, Actor> */
    public function getByType(string $contract): array
    {
        return $this->byType[$contract] ?? [];
    }

    /** @return array<string, Actor> */
    public function getAll(): array
    {
        return $this->data;
    }

    /** @return array<string, Actor> */
    public function getBySubscriptionEvent(string $eventId): array
    {
        return $this->bySubscriptionEvent[$eventId] ?? [];
    }

    public function remove(string $actorId): void
    {
        if (false === array_key_exists($actorId, $this->data)) {
            return;
        }

        $actor = $this->data[$actorId];

        foreach ($actor->getSubscriptionEvents() as $eventId) {
            unset($this->bySubscriptionEvent[$eventId][$actorId]);
        }

        if (null !== $actor->getType()) {
            unset($this->byType[$actor->getType()][$actorId]);
        }

        unset($this->data[$actorId]);
    }

    public function reset(): void
    {
        $this->data = [];
        $this->dynamic = [];
        $this->byType = [];
        $this->bySubscriptionEvent = [];
    }
}
