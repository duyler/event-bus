<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service;

use Duyler\ActionBus\Bus\Task;
use Duyler\ActionBus\Formatter\IdFormatter;
use Duyler\ActionBus\Service\ResultService;
use Duyler\ActionBus\State\Service\Trait\ResultServiceTrait;
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
