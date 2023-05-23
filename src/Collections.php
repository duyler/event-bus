<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Collection\ActionContainerCollection;
use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Collection\SubscriptionCollection;
use Duyler\EventBus\Collection\TaskCollection;

readonly class Collections
{
    public function __construct(
        private ActionCollection          $actionCollection,
        private ActionContainerCollection $containerCollection,
        private SubscriptionCollection    $subscriptionCollection,
        private TaskCollection            $taskCollection,
    ) {
    }

    public function action(): ActionCollection
    {
        return $this->actionCollection;
    }

    public function container(): ActionContainerCollection
    {
        return $this->containerCollection;
    }

    public function subscription(): SubscriptionCollection
    {
        return $this->subscriptionCollection;
    }

    public function task(): TaskCollection
    {
        return $this->taskCollection;
    }
}
