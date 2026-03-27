<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Dto\Result;

final readonly class CompleteActor
{
    public function __construct(
        public Actor $actor,
        public Result $result,
        public string $taskId,
        public string $scope,
    ) {}
}
