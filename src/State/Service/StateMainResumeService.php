<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Bus\Task;
use Duyler\EventBus\Service\ResultService;
use Duyler\EventBus\State\Service\Trait\ResultServiceTrait;

class StateMainResumeService
{
    use ResultServiceTrait;

    public function __construct(
        private readonly Task $task,
        private readonly mixed $value,
        private readonly ResultService $resultService,
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
