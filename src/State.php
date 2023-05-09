<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\State\Service\StateAfterService;
use Duyler\EventBus\State\Service\StateBeforeService;
use Duyler\EventBus\State\Service\StateFinalService;
use Duyler\EventBus\State\Service\StateStartService;
use Duyler\EventBus\State\Service\StateSuspendService;
use Duyler\EventBus\State\StateHandlerProvider;
use Duyler\EventBus\State\StateHandlerInterface;
use Duyler\EventBus\State\StateServiceInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

readonly class State
{
    public function __construct(
        private Control              $control,
        private StateHandlerProvider $stateHandlerProvider,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function start(): void
    {
        $stateService = new StateStartService(
            $this->control,
        );

        foreach ($this->stateHandlerProvider->getStartHandlers() as $handler) {
            $handler->handle($stateService);
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function before(Task $task): void
    {
        $stateService = new StateBeforeService(
            $task->action->id,
            $this->control
        );

        foreach ($this->stateHandlerProvider->getBeforeHandlers() as $handler) {
            $this->handle($handler, $stateService, $task->action->id);
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function suspend(Task $task): void
    {
        $stateService = new StateSuspendService(
            $this->control
        );

        foreach ($this->stateHandlerProvider->getSuspendHandlers() as $handler) {
            $this->handle($handler, $stateService, $task->action->id);
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function after(Task $task): void
    {
        $stateService = new StateAfterService(
            $task->result->status,
            $task->result->data,
            $task->action->id,
            $this->control
        );

        foreach ($this->stateHandlerProvider->getAfterHandlers() as $handler) {
            $this->handle($handler, $stateService, $task->action->id);
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function final(): void
    {
        $stateService = new StateFinalService(
            $this->control
        );

        foreach ($this->stateHandlerProvider->getFinalHandlers() as $handler) {
            $handler->handle($stateService);
        }
    }

    private function handle(
        StateHandlerInterface $handler,
        StateServiceInterface $stateService,
        string $actionId,
    ): void {
        if (empty($handler->observed()) || in_array($actionId, $handler->observed())) {
            $handler->handle($stateService);
        }
    }
}
