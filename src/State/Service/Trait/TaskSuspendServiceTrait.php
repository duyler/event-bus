<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service\Trait;

use Duyler\ActionBus\Bus\Task;

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
