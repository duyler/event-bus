<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\Contract\State\StateActionAfterHandlerInterface;
use Duyler\EventBus\Contract\State\StateActionBeforeHandlerInterface;
use Duyler\EventBus\Contract\State\StateActionThrowingHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainAfterHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainBeforeHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainFinalHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainStartHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainSuspendHandlerInterface;

class StateHandlerStorage
{
    /** @var StateMainStartHandlerInterface[] $stateMainStart */
    private array $stateMainStart = [];

    /** @var StateMainBeforeHandlerInterface[] $stateMainBefore */
    private array $stateMainBefore = [];

    private ?StateMainSuspendHandlerInterface $stateMainSuspend = null;

    /** @var StateMainAfterHandlerInterface[] $stateMainAfter */
    private array $stateMainAfter = [];

    /** @var StateMainFinalHandlerInterface[] $stateMainFinal */
    private array $stateMainFinal = [];

    /** @var StateActionBeforeHandlerInterface[] $stateActionBefore */
    private array $stateActionBefore = [];

    /** @var StateActionThrowingHandlerInterface[] $stateActionThrowing */
    private array $stateActionThrowing = [];

    /** @var StateActionAfterHandlerInterface[] $stateActionAfter */
    private array $stateActionAfter = [];

    public function addStateMainStartHandler(StateMainStartHandlerInterface $startHandler): void
    {
        $this->stateMainStart[get_class($startHandler)] = $startHandler;
    }

    public function addStateMainBeforeHandler(StateMainBeforeHandlerInterface $beforeHandler): void
    {
        $this->stateMainBefore[get_class($beforeHandler)] = $beforeHandler;
    }

    public function setStateMainSuspendHandler(StateMainSuspendHandlerInterface $suspendHandler): void
    {
        $this->stateMainSuspend = $suspendHandler;
    }

    public function addStateMainAfterHandler(StateMainAfterHandlerInterface $afterHandler): void
    {
        $this->stateMainAfter[get_class($afterHandler)] = $afterHandler;
    }

    public function addStateMainFinalHandler(StateMainFinalHandlerInterface $finalHandler): void
    {
        $this->stateMainFinal[get_class($finalHandler)] = $finalHandler;
    }

    public function addStateActionBeforeHandler(StateActionBeforeHandlerInterface $actionBeforeHandler): void
    {
        $this->stateActionBefore[get_class($actionBeforeHandler)] = $actionBeforeHandler;
    }

    public function addStateActionThrowingHandler(StateActionThrowingHandlerInterface $actionThrowingHandler): void
    {
        $this->stateActionThrowing[get_class($actionThrowingHandler)] = $actionThrowingHandler;
    }

    public function addStateActionAfterHandler(StateActionAfterHandlerInterface $actionAfterHandler): void
    {
        $this->stateActionAfter[get_class($actionAfterHandler)] = $actionAfterHandler;
    }

    public function getStateMainStart(): array
    {
        return $this->stateMainStart;
    }

    public function getStateMainBefore(): array
    {
        return $this->stateMainBefore;
    }

    public function getStateMainSuspend(): ?StateMainSuspendHandlerInterface
    {
        return $this->stateMainSuspend;
    }

    public function getStateMainAfter(): array
    {
        return $this->stateMainAfter;
    }

    public function getStateMainFinal(): array
    {
        return $this->stateMainFinal;
    }

    public function getStateActionBefore(): array
    {
        return $this->stateActionBefore;
    }

    public function getStateActionThrowing(): array
    {
        return $this->stateActionThrowing;
    }

    public function getStateActionAfter(): array
    {
        return $this->stateActionAfter;
    }
}
