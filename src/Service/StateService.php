<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Contract\State\ActionAfterStateHandlerInterface;
use Duyler\EventBus\Contract\State\ActionBeforeStateHandlerInterface;
use Duyler\EventBus\Contract\State\ActionThrowingStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainBeforeStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainFinalStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainResumeStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainStartStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainSuspendStateHandlerInterface;
use Duyler\EventBus\State\StateHandlerStorage;

readonly class StateService
{
    public function __construct(private StateHandlerStorage $stateHandlerStorage)
    {
    }

    public function addMainStartStateHandler(MainStartStateHandlerInterface $startHandler): void
    {
        $this->stateHandlerStorage->addMainStartStateHandler($startHandler);
    }

    public function addMainBeforeStateHandler(MainBeforeStateHandlerInterface $beforeActionHandler): void
    {
        $this->stateHandlerStorage->addMainBeforeStateHandler($beforeActionHandler);
    }

    public function addMainSuspendStateHandler(MainSuspendStateHandlerInterface $suspendHandler): void
    {
        $this->stateHandlerStorage->addMainSuspendStateHandler($suspendHandler);
    }

    public function addMainResumeStateHandler(MainResumeStateHandlerInterface $resumeHandler): void
    {
        $this->stateHandlerStorage->addMainResumeStateHandler($resumeHandler);
    }

    public function addMainAfterStateHandler(MainAfterStateHandlerInterface $afterActionHandler): void
    {
        $this->stateHandlerStorage->addMainAfterStateHandler($afterActionHandler);
    }

    public function addMainFinalStateHandler(MainFinalStateHandlerInterface $finalHandler): void
    {
        $this->stateHandlerStorage->addMainFinalStateHandler($finalHandler);
    }

    public function addActionBeforeStateHandler(ActionBeforeStateHandlerInterface $actionBeforeHandler): void
    {
        $this->stateHandlerStorage->addActionBeforeStateHandler($actionBeforeHandler);
    }

    public function addActionThrowingStateHandler(ActionThrowingStateHandlerInterface $actionThrowingHandler): void
    {
        $this->stateHandlerStorage->addActionThrowingStateHandler($actionThrowingHandler);
    }

    public function addActionAfterStateHandler(ActionAfterStateHandlerInterface $actionAfterHandler): void
    {
        $this->stateHandlerStorage->addActionAfterStateHandler($actionAfterHandler);
    }
}
