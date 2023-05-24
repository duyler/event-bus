<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Collection\ActionCollection;
use RecursiveIterator;
use RecursiveIteratorIterator;
use Traversable;

class ActionRequiredIterator extends RecursiveIteratorIterator
{
    private ActionCollection $actionCollection;

    public function __construct(Traversable $iterator, ActionCollection $actionCollection)
    {
        $this->actionCollection = $actionCollection;
        parent::__construct($iterator, self::CHILD_FIRST);
    }

    public function callHasChildren(): bool
    {
        return $this->actionCollection->get($this->current())->required->valid();
    }

    public function callGetChildren(): ?RecursiveIterator
    {
        return $this->actionCollection->get($this->current())->required;
    }
}
