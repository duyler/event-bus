<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Storage\ActionContainerStorage;
use Duyler\EventBus\Storage\ActionStorage;
use Duyler\EventBus\Storage\SubscriptionStorage;
use Duyler\EventBus\Storage\TaskStorage;

readonly class Storage
{
    public function __construct(
        private ActionStorage          $actionStorage,
        private ActionContainerStorage $containerStorage,
        private SubscriptionStorage    $subscriptionStorage,
        private TaskStorage            $taskStorage,
    ) {
    }

    public function action(): ActionStorage
    {
        return $this->actionStorage;
    }

    public function container(): ActionContainerStorage
    {
        return $this->containerStorage;
    }

    public function subscription(): SubscriptionStorage
    {
        return $this->subscriptionStorage;
    }

    public function task(): TaskStorage
    {
        return $this->taskStorage;
    }
}
