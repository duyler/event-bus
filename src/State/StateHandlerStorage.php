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
use Duyler\EventBus\Contract\State\MainBeginStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainSuspendStateHandlerInterface;
use Duyler\EventBus\Contract\State\StateHandlerInterface;
use InvalidArgumentException;

class StateHandlerStorage
{
    /** @var MainBeginStateHandlerInterface[] */
    private array $mainBegin = [];

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
            $stateHandler instanceof MainBeginStateHandlerInterface =>
                $this->mainBegin[] = $stateHandler,
            $stateHandler instanceof MainBeforeStateHandlerInterface =>
                $this->mainBefore[] = $stateHandler,
            $stateHandler instanceof MainSuspendStateHandlerInterface =>
                $this->stateMainSuspend[] = $stateHandler,
            $stateHandler instanceof MainResumeStateHandlerInterface =>
                $this->stateMainResume[] = $stateHandler,
            $stateHandler instanceof MainAfterStateHandlerInterface =>
                $this->mainAfter[] = $stateHandler,
            $stateHandler instanceof MainFinalStateHandlerInterface =>
                $this->mainFinal[] = $stateHandler,
            $stateHandler instanceof ActionBeforeStateHandlerInterface =>
                $this->actionBefore[] = $stateHandler,
            $stateHandler instanceof ActionThrowingStateHandlerInterface =>
                $this->actionThrowing[] = $stateHandler,
            $stateHandler instanceof ActionAfterStateHandlerInterface =>
                $this->actionAfter[] = $stateHandler,

            default => throw new InvalidArgumentException(sprintf(
                'State handler %s must be compatibility with %s',
                get_class($stateHandler),
                StateHandlerInterface::class
            ))
        };
    }

    public function getMainBegin(): array
    {
        return $this->mainBegin;
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
