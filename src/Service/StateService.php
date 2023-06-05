<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Contract\State\StateActionAfterHandlerInterface;
use Duyler\EventBus\Contract\State\StateActionBeforeHandlerInterface;
use Duyler\EventBus\Contract\State\StateActionThrowingHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainAfterHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainBeforeHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainFinalHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainStartHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainSuspendHandlerInterface;
use Duyler\EventBus\State\StateHandlerStorage;

readonly class StateService
{
    public function __construct(private StateHandlerStorage $stateHandlerStorage)
    {
    }

    public function addStateMainStartHandler(StateMainStartHandlerInterface $startHandler): void
    {
        $this->stateHandlerStorage->addStateMainStartHandler($startHandler);
    }

    public function addStateMainBeforeHandler(StateMainBeforeHandlerInterface $beforeActionHandler): void
    {
        $this->stateHandlerStorage->addStateMainBeforeHandler($beforeActionHandler);
    }

    public function setStateMainSuspendHandler(StateMainSuspendHandlerInterface $suspendHandler): void
    {
        $this->stateHandlerStorage->setStateMainSuspendHandler($suspendHandler);
    }

    public function addStateMainAfterHandler(StateMainAfterHandlerInterface $afterActionHandler): void
    {
        $this->stateHandlerStorage->addStateMainAfterHandler($afterActionHandler);
    }

    public function addStateMainFinalHandler(StateMainFinalHandlerInterface $finalHandler): void
    {
        $this->stateHandlerStorage->addStateMainFinalHandler($finalHandler);
    }

    public function addStateActionBeforeHandler(StateActionBeforeHandlerInterface $actionBeforeHandler): void
    {
        $this->stateHandlerStorage->addStateActionBeforeHandler($actionBeforeHandler);
    }

    public function addStateActionThrowingHandler(StateActionThrowingHandlerInterface $actionThrowingHandler): void
    {
        $this->stateHandlerStorage->addStateActionThrowingHandler($actionThrowingHandler);
    }

    public function addStateActionAfterHandler(StateActionAfterHandlerInterface $actionAfterHandler): void
    {
        $this->stateHandlerStorage->addStateActionAfterHandler($actionAfterHandler);
    }
}
