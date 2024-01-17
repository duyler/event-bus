<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Bus\Task;

/**
 * @property Task $task
 */
trait TaskSuspendServiceTrait
{
    public function getValue(): mixed
    {
        return $this->task->getValue();
    }
}
