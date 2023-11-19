<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Bus\Task;

/**
 * @property Task $task
 */
trait TaskSuspendService
{
    public function getValue(): mixed
    {
        return $this->task->getValue();
    }
}
