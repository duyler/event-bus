<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\DI\Attribute\Finalize;

#[Finalize]
final class ActorRequiredMap
{
    /** @var array<string, Actor[]> */
    private array $map = [];

    public function create(Actor $actor): void
    {
        /** @var string $required */
        foreach ($actor->getRequired() as $required) {
            $this->map[$required][] = $actor;
        }
    }

    /** @return Actor[] */
    public function get(string $actorId): array
    {
        return $this->map[$actorId] ?? [];
    }

    public function remove(string $actorId): void
    {
        unset($this->map[$actorId]);
    }

    public function finalize(): void
    {
        $this->map = [];
    }
}
