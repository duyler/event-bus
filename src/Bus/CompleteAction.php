<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Dto\Result;

final readonly class CompleteAction
{
    public function __construct(
        public Action $action,
        public Result $result,
        public string $taskId,
    ) {}
}
