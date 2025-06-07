<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Exception\ActionNotDefinedException;
use Override;
use RecursiveIterator;
use RecursiveIteratorIterator;

/** @extends RecursiveIteratorIterator<RecursiveIterator> */
final class ActionRequiredIterator extends RecursiveIteratorIterator
{
    /** @param array<string, Action> $actions */
    public function __construct(RecursiveIterator $iterator, private array $actions)
    {
        parent::__construct($iterator, self::SELF_FIRST);
    }

    #[Override]
    public function callHasChildren(): bool
    {
        /** @var string $current */
        $current = $this->current();

        if (false === array_key_exists($current, $this->actions)) {
            $this->throwNotFoundAction($current);
        }

        return 0 < $this->actions[$current]->required->count();
    }

    #[Override]
    public function callGetChildren(): ?RecursiveIterator
    {
        /** @var string $current */
        $current = $this->current();

        if (false === array_key_exists($current, $this->actions)) {
            $this->throwNotFoundAction($current);
        }

        return $this->actions[$current]->required;
    }

    private function throwNotFoundAction(string $actionId): never
    {
        throw new ActionNotDefinedException($actionId);
    }
}
