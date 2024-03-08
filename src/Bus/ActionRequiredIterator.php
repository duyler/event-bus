<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Exception\ActionNotDefinedException;
use RecursiveIterator;
use RecursiveIteratorIterator;

/** @extends RecursiveIteratorIterator<RecursiveIterator> */
final class ActionRequiredIterator extends RecursiveIteratorIterator
{
    /** @var array<string, Action> */
    private iterable $actions;

    /** @param array<string, Action> $actions */
    public function __construct(RecursiveIterator $iterator, array $actions)
    {
        $this->actions = $actions;
        parent::__construct($iterator, self::CHILD_FIRST);
    }

    public function callHasChildren(): bool
    {
        /** @var string $current */
        $current = $this->current();

        if (false === array_key_exists($current, $this->actions)) {
            $this->throwNotFoundAction($current);
        }

        return $this->actions[$current]->required->valid();
    }

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
