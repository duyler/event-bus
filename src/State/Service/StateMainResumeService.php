<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Bus\Task;

class StateMainResumeService
{
    public function __construct(
        private readonly Task $task,
        private readonly mixed $value,
    ) {}

    public function getActionId(): string
    {
        return $this->task->action->id;
    }

    public function getResumeValue(): mixed
    {
        return $this->value;
    }
}
