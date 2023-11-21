<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\Contract\State\ActionAfterStateHandlerInterface;
use Duyler\EventBus\Contract\State\ActionBeforeStateHandlerInterface;
use Duyler\EventBus\Contract\State\ActionThrowingStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainBeforeStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainFinalStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainResumeStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainStartStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainSuspendStateHandlerInterface;

class StateHandlerStorage
{
    /** @var MainStartStateHandlerInterface[] $mainStart */
    private array $mainStart = [];

    /** @var MainBeforeStateHandlerInterface[] $mainBefore */
    private array $mainBefore = [];

    /** @var MainSuspendStateHandlerInterface[]  */
    private array $stateMainSuspend = [];

    /** @var MainResumeStateHandlerInterface[]  */
    private array $stateMainResume = [];

    /** @var MainAfterStateHandlerInterface[] $mainAfter */
    private array $mainAfter = [];

    /** @var MainFinalStateHandlerInterface[] $mainFinal */
    private array $mainFinal = [];

    /** @var ActionBeforeStateHandlerInterface[] $actionBefore */
    private array $actionBefore = [];

    /** @var ActionThrowingStateHandlerInterface[] $actionThrowing */
    private array $actionThrowing = [];

    /** @var ActionAfterStateHandlerInterface[] $actionAfter */
    private array $actionAfter = [];

    public function addMainStartStateHandler(MainStartStateHandlerInterface $startHandler): void
    {
        $this->mainStart[get_class($startHandler)] = $startHandler;
    }

    public function addMainBeforeStateHandler(MainBeforeStateHandlerInterface $beforeHandler): void
    {
        $this->mainBefore[get_class($beforeHandler)] = $beforeHandler;
    }

    public function addMainSuspendStateHandler(MainSuspendStateHandlerInterface $suspendHandler): void
    {
        $this->stateMainSuspend[get_class($suspendHandler)] = $suspendHandler;
    }

    public function addMainResumeStateHandler(MainResumeStateHandlerInterface $resumeHandler): void
    {
        $this->stateMainResume[get_class($resumeHandler)] = $resumeHandler;
    }

    public function addMainAfterStateHandler(MainAfterStateHandlerInterface $afterHandler): void
    {
        $this->mainAfter[get_class($afterHandler)] = $afterHandler;
    }

    public function addMainFinalStateHandler(MainFinalStateHandlerInterface $finalHandler): void
    {
        $this->mainFinal[get_class($finalHandler)] = $finalHandler;
    }

    public function addActionBeforeStateHandler(ActionBeforeStateHandlerInterface $actionBeforeHandler): void
    {
        $this->actionBefore[get_class($actionBeforeHandler)] = $actionBeforeHandler;
    }

    public function addActionThrowingStateHandler(ActionThrowingStateHandlerInterface $actionThrowingHandler): void
    {
        $this->actionThrowing[get_class($actionThrowingHandler)] = $actionThrowingHandler;
    }

    public function addActionAfterStateHandler(ActionAfterStateHandlerInterface $actionAfterHandler): void
    {
        $this->actionAfter[get_class($actionAfterHandler)] = $actionAfterHandler;
    }

    public function getMainStart(): array
    {
        return $this->mainStart;
    }

    public function getMainBefore(): array
    {
        return $this->mainBefore;
    }

    public function getMainSuspend(): array
    {
        return $this->stateMainSuspend;
    }

    public function getMainResume(): array
    {
        return $this->stateMainResume;
    }

    public function getMainAfter(): array
    {
        return $this->mainAfter;
    }

    public function getMainFinal(): array
    {
        return $this->mainFinal;
    }

    public function getActionBefore(): array
    {
        return $this->actionBefore;
    }

    public function getActionThrowing(): array
    {
        return $this->actionThrowing;
    }

    public function getActionAfter(): array
    {
        return $this->actionAfter;
    }
}
