<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use InvalidArgumentException;
use RecursiveIterator;
use RecursiveIteratorIterator;
use Traversable;

class ActionRequiredIterator extends RecursiveIteratorIterator
{
    private iterable $actions;

    public function __construct(Traversable $iterator, array $actions)
    {
        $this->actions = $actions;
        parent::__construct($iterator, self::CHILD_FIRST);
    }

    public function callHasChildren(): bool
    {
        $current = $this->current();

        if (array_key_exists($current, $this->actions) === false) {
            $this->throwNotFoundAction($current);
        }

        return $this->actions[$current]->required->valid();
    }

    public function callGetChildren(): ?RecursiveIterator
    {
        $current = $this->current();

        if (array_key_exists($current, $this->actions) === false) {
            $this->throwNotFoundAction($current);
        }

        return $this->actions[$current]->required;
    }

    private function throwNotFoundAction(string $actionId): never
    {
        throw new InvalidArgumentException(
            'Required action ' . $actionId . ' not found'
        );
    }
}
