<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;

readonly class Event
{
    public function __construct(
        public Action $action,
        public Result $result,
    ) {}
}
