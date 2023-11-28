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
use Duyler\EventBus\Contract\State\StateHandlerInterface;
use InvalidArgumentException;

class StateHandlerStorage
{
    /** @var MainStartStateHandlerInterface[] */
    private array $mainStart = [];

    /** @var MainBeforeStateHandlerInterface[] */
    private array $mainBefore = [];

    /** @var MainSuspendStateHandlerInterface[] */
    private array $stateMainSuspend = [];

    /** @var MainResumeStateHandlerInterface[] */
    private array $stateMainResume = [];

    /** @var MainAfterStateHandlerInterface[] */
    private array $mainAfter = [];

    /** @var MainFinalStateHandlerInterface[] */
    private array $mainFinal = [];

    /** @var ActionBeforeStateHandlerInterface[] */
    private array $actionBefore = [];

    /** @var ActionThrowingStateHandlerInterface[] */
    private array $actionThrowing = [];

    /** @var ActionAfterStateHandlerInterface[] */
    private array $actionAfter = [];

    public function addStateHandler(StateHandlerInterface $stateHandler): void
    {
        match (true) {
            $stateHandler instanceof MainStartStateHandlerInterface =>
                $this->mainStart[get_class($stateHandler)] = $stateHandler,
            $stateHandler instanceof MainBeforeStateHandlerInterface =>
                $this->mainBefore[get_class($stateHandler)] = $stateHandler,
            $stateHandler instanceof MainSuspendStateHandlerInterface =>
                $this->stateMainSuspend[get_class($stateHandler)] = $stateHandler,
            $stateHandler instanceof MainResumeStateHandlerInterface =>
                $this->stateMainResume[get_class($stateHandler)] = $stateHandler,
            $stateHandler instanceof MainAfterStateHandlerInterface =>
                $this->mainAfter[get_class($stateHandler)] = $stateHandler,
            $stateHandler instanceof MainFinalStateHandlerInterface =>
                $this->mainFinal[get_class($stateHandler)] = $stateHandler,
            $stateHandler instanceof ActionBeforeStateHandlerInterface =>
                $this->actionBefore[get_class($stateHandler)] = $stateHandler,
            $stateHandler instanceof ActionThrowingStateHandlerInterface =>
                $this->actionThrowing[get_class($stateHandler)] = $stateHandler,
            $stateHandler instanceof ActionAfterStateHandlerInterface =>
                $this->actionAfter[get_class($stateHandler)] = $stateHandler,

            default => throw new InvalidArgumentException(sprintf(
                'State handler %s must be compatibility with %s',
                get_class($stateHandler),
                StateHandlerInterface::class
            ))
        };
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
