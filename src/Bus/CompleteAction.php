<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Bus;

use Duyler\ActionBus\Build\Action;
use Duyler\ActionBus\Dto\Result;

final readonly class CompleteAction
{
    public function __construct(
        public Action $action,
        public Result $result,
    ) {}
}
