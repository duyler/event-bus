<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State;

use Duyler\ActionBus\Contract\State\ActionAfterStateHandlerInterface;
use Duyler\ActionBus\Contract\State\ActionBeforeStateHandlerInterface;
use Duyler\ActionBus\Contract\State\ActionThrowingStateHandlerInterface;
use Duyler\ActionBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\ActionBus\Contract\State\MainBeforeStateHandlerInterface;
use Duyler\ActionBus\Contract\State\MainBeginStateHandlerInterface;
use Duyler\ActionBus\Contract\State\MainCyclicStateHandlerInterface;
use Duyler\ActionBus\Contract\State\MainEndStateHandlerInterface;
use Duyler\ActionBus\Contract\State\MainResumeStateHandlerInterface;
use Duyler\ActionBus\Contract\State\MainSuspendStateHandlerInterface;
use Duyler\ActionBus\Contract\State\StateHandlerInterface;
use InvalidArgumentException;

class StateHandlerStorage
{
    /** @var MainBeginStateHandlerInterface[] */
    private array $mainBegin = [];

    /** @var MainCyclicStateHandlerInterface[] */
    private array $mainCyclic = [];

    /** @var MainBeforeStateHandlerInterface[] */
    private array $mainBefore = [];

    /** @var MainSuspendStateHandlerInterface[] */
    private array $stateMainSuspend = [];

    /** @var MainResumeStateHandlerInterface[] */
    private array $stateMainResume = [];

    /** @var MainAfterStateHandlerInterface[] */
    private array $mainAfter = [];

    /** @var MainEndStateHandlerInterface[] */
    private array $mainEnd = [];

    /** @var ActionBeforeStateHandlerInterface[] */
    private array $actionBefore = [];

    /** @var ActionThrowingStateHandlerInterface[] */
    private array $actionThrowing = [];

    /** @var ActionAfterStateHandlerInterface[] */
    private array $actionAfter = [];

    public function addStateHandler(StateHandlerInterface $stateHandler): void
    {
        match (true) {
            $stateHandler instanceof MainBeginStateHandlerInterface => $this->mainBegin[] = $stateHandler,
            $stateHandler instanceof MainCyclicStateHandlerInterface => $this->mainCyclic[] = $stateHandler,
            $stateHandler instanceof MainBeforeStateHandlerInterface => $this->mainBefore[] = $stateHandler,
            $stateHandler instanceof MainSuspendStateHandlerInterface => $this->stateMainSuspend[] = $stateHandler,
            $stateHandler instanceof MainResumeStateHandlerInterface => $this->stateMainResume[] = $stateHandler,
            $stateHandler instanceof MainAfterStateHandlerInterface => $this->mainAfter[] = $stateHandler,
            $stateHandler instanceof MainEndStateHandlerInterface => $this->mainEnd[] = $stateHandler,
            $stateHandler instanceof ActionBeforeStateHandlerInterface => $this->actionBefore[] = $stateHandler,
            $stateHandler instanceof ActionThrowingStateHandlerInterface => $this->actionThrowing[] = $stateHandler,
            $stateHandler instanceof ActionAfterStateHandlerInterface => $this->actionAfter[] = $stateHandler,

            default => throw new InvalidArgumentException(
                sprintf(
                    'State handler %s must be compatibility with %s',
                    get_class($stateHandler),
                    StateHandlerInterface::class,
                ),
            ),
        };
    }

    /** @return MainBeginStateHandlerInterface[] */
    public function getMainBegin(): array
    {
        return $this->mainBegin;
    }

    /** @return MainCyclicStateHandlerInterface[] */
    public function getMainCyclic(): array
    {
        return $this->mainCyclic;
    }

    /** @return MainBeforeStateHandlerInterface[] */
    public function getMainBefore(): array
    {
        return $this->mainBefore;
    }

    /** @return MainSuspendStateHandlerInterface[] */
    public function getMainSuspend(): array
    {
        return $this->stateMainSuspend;
    }

    /** @return MainResumeStateHandlerInterface[] */
    public function getMainResume(): array
    {
        return $this->stateMainResume;
    }

    /** @return MainAfterStateHandlerInterface[] */
    public function getMainAfter(): array
    {
        return $this->mainAfter;
    }

    /** @return MainEndStateHandlerInterface[] */
    public function getMainEnd(): array
    {
        return $this->mainEnd;
    }

    /** @return ActionBeforeStateHandlerInterface[] */
    public function getActionBefore(): array
    {
        return $this->actionBefore;
    }

    /** @return ActionThrowingStateHandlerInterface[] */
    public function getActionThrowing(): array
    {
        return $this->actionThrowing;
    }

    /** @return ActionAfterStateHandlerInterface[] */
    public function getActionAfter(): array
    {
        return $this->actionAfter;
    }
}
