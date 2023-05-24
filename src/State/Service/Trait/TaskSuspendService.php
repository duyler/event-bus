<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Task;

/**
 * @property Task $task
 */
trait TaskSuspendService
{
    public function resume(mixed $data): void
    {
        $this->task->resume($data);
    }

    public function getValue(): mixed
    {
        return $this->task->getValue();
    }
}
