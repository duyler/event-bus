<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Storage\ActionStorage;
use RecursiveIterator;
use RecursiveIteratorIterator;
use Traversable;

class ActionRequiredIterator extends RecursiveIteratorIterator
{
    private ActionStorage $actionStorage;

    public function __construct(Traversable $iterator, ActionStorage $actionStorage)
    {
        $this->actionStorage = $actionStorage;
        parent::__construct($iterator, self::CHILD_FIRST);
    }

    public function callHasChildren(): bool
    {
        return $this->actionStorage->get($this->current())->required->valid();
    }

    public function callGetChildren(): ?RecursiveIterator
    {
        return $this->actionStorage->get($this->current())->required;
    }
}
