<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Service\QueueService;
use Duyler\EventBus\State\Service\Trait\QueueServiceTrait;

class StateMainCyclicService
{
    use QueueServiceTrait;

    public function __construct(private QueueService $queueService) {}
}
