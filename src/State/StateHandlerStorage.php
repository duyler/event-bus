<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\Contract\State\ActorAfterStateHandlerInterface;
use Duyler\EventBus\Contract\State\ActorBeforeStateHandlerInterface;
use Duyler\EventBus\Contract\State\ActorThrowingStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainBeforeStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainBeginStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainCyclicStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainEmptyStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainEndStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainResumeStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainSuspendStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainUnresolvedStateHandlerInterface;
use Duyler\EventBus\Contract\State\StateHandlerInterface;
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

    /** @var MainEmptyStateHandlerInterface[] */
    private array $mainEmpty = [];

    /** @var MainEndStateHandlerInterface[] */
    private array $mainEnd = [];

    /** @var MainUnresolvedStateHandlerInterface[] */
    private array $mainUnresolved = [];

    /** @var ActorBeforeStateHandlerInterface[] */
    private array $actorBefore = [];

    /** @var ActorThrowingStateHandlerInterface[] */
    private array $actorThrowing = [];

    /** @var ActorAfterStateHandlerInterface[] */
    private array $actorAfter = [];

    public function addStateHandler(StateHandlerInterface $stateHandler): void
    {
        match (true) {
            $stateHandler instanceof MainBeginStateHandlerInterface => $this->mainBegin[] = $stateHandler,
            $stateHandler instanceof MainCyclicStateHandlerInterface => $this->mainCyclic[] = $stateHandler,
            $stateHandler instanceof MainBeforeStateHandlerInterface => $this->mainBefore[] = $stateHandler,
            $stateHandler instanceof MainSuspendStateHandlerInterface => $this->stateMainSuspend[] = $stateHandler,
            $stateHandler instanceof MainResumeStateHandlerInterface => $this->stateMainResume[] = $stateHandler,
            $stateHandler instanceof MainAfterStateHandlerInterface => $this->mainAfter[] = $stateHandler,
            $stateHandler instanceof MainEmptyStateHandlerInterface => $this->mainEmpty[] = $stateHandler,
            $stateHandler instanceof MainEndStateHandlerInterface => $this->mainEnd[] = $stateHandler,
            $stateHandler instanceof MainUnresolvedStateHandlerInterface => $this->mainUnresolved[] = $stateHandler,
            $stateHandler instanceof ActorBeforeStateHandlerInterface => $this->actorBefore[] = $stateHandler,
            $stateHandler instanceof ActorThrowingStateHandlerInterface => $this->actorThrowing[] = $stateHandler,
            $stateHandler instanceof ActorAfterStateHandlerInterface => $this->actorAfter[] = $stateHandler,

            default => throw new InvalidArgumentException(
                sprintf(
                    'State handler %s must be compatibility with %s',
                    $stateHandler::class,
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

    /** @return MainEmptyStateHandlerInterface[] */
    public function getMainEmpty(): array
    {
        return $this->mainEmpty;
    }

    /** @return MainEndStateHandlerInterface[] */
    public function getMainEnd(): array
    {
        return $this->mainEnd;
    }

    /** @return MainUnresolvedStateHandlerInterface[] */
    public function getMainUnresolved(): array
    {
        return $this->mainUnresolved;
    }

    /** @return ActorBeforeStateHandlerInterface[] */
    public function getActorBefore(): array
    {
        return $this->actorBefore;
    }

    /** @return ActorThrowingStateHandlerInterface[] */
    public function getActorThrowing(): array
    {
        return $this->actorThrowing;
    }

    /** @return ActorAfterStateHandlerInterface[] */
    public function getActorAfter(): array
    {
        return $this->actorAfter;
    }
}
