<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Exception\ActionNotDefinedException;
use RecursiveIterator;
use RecursiveIteratorIterator;
use Override;

/** @extends RecursiveIteratorIterator<RecursiveIterator> */
final class ActionRequiredIterator extends RecursiveIteratorIterator
{
    /** @var array<string, Action> */
    private array $actions;

    /** @param array<string, Action> $actions */
    public function __construct(RecursiveIterator $iterator, array $actions)
    {
        $this->actions = $actions;
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
