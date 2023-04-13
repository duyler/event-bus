<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Contract\State\StateBeforeHandlerInterface;
use Duyler\EventBus\Contract\State\StateFinalHandlerInterface;
use Duyler\EventBus\Contract\State\StateAfterHandlerInterface;
use Duyler\EventBus\State\StateAfterService;
use Duyler\EventBus\State\StateBeforeService;
use Duyler\EventBus\State\StateFinalService;

readonly class State
{
    public function __construct(
        private Control $control,
        private Storage $storage,
    ) {
    }

    public function before(Task $task): void
    {
        $stateService = new StateBeforeService(
            $task->action->id,
            $this->control
        );

        /** @var StateBeforeHandlerInterface $handler */
        foreach ($this->storage->state()->get(StateBeforeHandlerInterface::TYPE_KEY) as $handler) {
            $handler->handle($stateService);
        }
    }

    public function after(Task $task): void
    {
        $stateService = new StateAfterService(
            $task->result->status,
            $task->result->data,
            $task->action->id,
            $this->control
        );

        /** @var StateAfterHandlerInterface $handler */
        foreach ($this->storage->state()->get(StateAfterHandlerInterface::TYPE_KEY) as $handler) {
            $handler->handle($stateService);
        }
    }

    public function final(): void
    {
        $stateService = new StateFinalService(
            $this->control
        );

        /** @var StateFinalHandlerInterface $handler */
        foreach ($this->storage->state()->get(StateFinalHandlerInterface::TYPE_KEY) as $handler) {
            $handler->handle($stateService);
        }
    }
}
