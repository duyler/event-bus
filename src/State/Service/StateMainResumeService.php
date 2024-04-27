<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Bus\Task;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Service\ResultService;
use Duyler\EventBus\State\Service\Trait\ResultServiceTrait;
use UnitEnum;

class StateMainResumeService
{
    use ResultServiceTrait;

    public function __construct(
        private readonly Task $task,
        private readonly mixed $value,
        private readonly ResultService $resultService,
    ) {}

    public function getActionId(): string|UnitEnum
    {
        return IdFormatter::reverse($this->task->action->id);
    }

    public function getResumeValue(): mixed
    {
        return $this->value;
    }
}
