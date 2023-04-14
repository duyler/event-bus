<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Contract\State\StateBeforeHandlerInterface;
use Duyler\EventBus\Contract\State\StateFinalHandlerInterface;
use Duyler\EventBus\Contract\State\StateAfterHandlerInterface;
use Duyler\EventBus\Contract\State\StateStartHandlerInterface;
use Duyler\EventBus\State\StateAfterService;
use Duyler\EventBus\State\StateBeforeService;
use Duyler\EventBus\State\StateFinalService;
use Duyler\EventBus\State\StateHandlerInterface;
use Duyler\EventBus\State\StateServiceInterface;
use Duyler\EventBus\State\StateStartService;

readonly class State
{
    public function __construct(
        private Control $control,
        private Storage $storage,
    ) {
    }

    public function start(): void
    {
        $stateService = new StateStartService(
            $this->control,
        );

        /** @var StateStartHandlerInterface $handler */
        foreach ($this->storage->state()->get(StateStartHandlerInterface::TYPE_KEY) as $handler) {
            $handler->handle($stateService);
        }
    }

    public function before(Task $task): void
    {
        $stateService = new StateBeforeService(
            $task->action->id,
            $this->control
        );

        /** @var StateBeforeHandlerInterface $handler */
        foreach ($this->storage->state()->get(StateBeforeHandlerInterface::TYPE_KEY) as $handler) {
            $this->handle($handler, $stateService, $task->action->id);
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
            $this->handle($handler, $stateService, $task->action->id);
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

    private function handle(
        StateHandlerInterface $handler,
        StateServiceInterface $stateService,
        string $actionId): void
    {
        if (empty($handler->observed()) || in_array($actionId, $handler->observed())) {
            $handler->handle($stateService);
        }
    }
}
