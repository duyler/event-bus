<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Event;

use Duyler\EventBus\Bus\Actor;

readonly class ActorAfterRunEvent
{
    public function __construct(
        public Actor $actor,
        public mixed $result = null,
        public string $scope = 'common'
    ) {}
}
