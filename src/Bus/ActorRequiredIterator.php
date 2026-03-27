<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Exception\ActorNotDefinedException;
use Override;
use RecursiveIterator;
use RecursiveIteratorIterator;

use function array_key_exists;

/** @extends RecursiveIteratorIterator<RecursiveIterator> */
final class ActorRequiredIterator extends RecursiveIteratorIterator
{
    /** @param array<string, Actor> $actors */
    public function __construct(RecursiveIterator $iterator, private array $actors)
    {
        parent::__construct($iterator, self::SELF_FIRST);
    }

    #[Override]
    public function callHasChildren(): bool
    {
        /** @var string $current */
        $current = $this->current();

        if (false === array_key_exists($current, $this->actors)) {
            $this->throwNotFoundActor($current);
        }

        return 0 < $this->actors[$current]->getRequired()->count();
    }

    #[Override]
    public function callGetChildren(): ?RecursiveIterator
    {
        /** @var string $current */
        $current = $this->current();

        if (false === array_key_exists($current, $this->actors)) {
            $this->throwNotFoundActor($current);
        }

        return $this->actors[$current]->getRequired();
    }

    private function throwNotFoundActor(string $actorId): never
    {
        throw new ActorNotDefinedException($actorId);
    }
}
