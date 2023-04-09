<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Contract\State\StateBeforeHandlerInterface;
use Duyler\EventBus\Contract\State\StateFinalHandlerInterface;
use Duyler\EventBus\Contract\State\StateAfterHandlerInterface;
use Duyler\EventBus\Dto\StateAfterHandler;
use Duyler\EventBus\Dto\StateBeforeHandler;
use Duyler\EventBus\Dto\StateFinalHandler;
use Duyler\EventBus\State\StateAfterService;
use Duyler\EventBus\State\StateBeforeService;
use Duyler\EventBus\State\StateFinalService;
use Duyler\EventBus\State\StateHandlerBuilder;

class State
{
    public function __construct(
        private readonly Control             $control,
        private readonly Storage             $storage,
    ) {
    }

    public function after(Task $task): void
    {
        $stateService = new StateAfterService(
            $task->result->status,
            $task->result->data,
            $task->action->id,
            $this->control
        );

        foreach ($this->storage->state()->get(StateAfterHandlerInterface::TYPE_KEY) as $handler) {
            $handler->handle($stateService);
        }

        $this->control->validateSubscribers();
        $this->control->resolveSubscribers($task->action->id, $task->result->status);
    }

    public function before(Task $task): void
    {
        $stateService = new StateBeforeService(
            $task->action->id,
            $this->control
        );

        foreach ($this->storage->state()->get(StateBeforeHandlerInterface::TYPE_KEY) as $handler) {
            $handler->handle($stateService);
        }
    }

    public function final(): void
    {
        $stateService = new StateFinalService(
            $this->control
        );

        foreach ($this->storage->state()->get(StateFinalHandlerInterface::TYPE_KEY) as $handler) {
            $handler->handle($stateService);
        }
    }
}
