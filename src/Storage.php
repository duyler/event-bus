<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Storage\ActionStorage;
use Duyler\EventBus\Storage\ContainerStorage;
use Duyler\EventBus\Storage\StateHandlerStorage;
use Duyler\EventBus\Storage\SubscriptionStorage;
use Duyler\EventBus\Storage\TaskStorage;

class Storage
{
    public function __construct(
        private readonly ActionStorage       $actionStorage,
        private readonly ContainerStorage    $containerStorage,
        private readonly SubscriptionStorage    $subscriptionStorage,
        private readonly TaskStorage         $taskStorage,
        private readonly StateHandlerStorage $stateHandlerStorage,
    ) {
    }

    public function action(): ActionStorage
    {
        return $this->actionStorage;
    }

    public function container(): ContainerStorage
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

    public function state(): StateHandlerStorage
    {
        return $this->stateHandlerStorage;
    }
}
