<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

use Duyler\EventBus\Bus\Actor;

interface ActorRunnerProviderInterface
{
    public function getRunner(Actor $actor, string $scope): ActorRunnerInterface;
}
