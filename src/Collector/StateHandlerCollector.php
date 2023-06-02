<?php

declare(strict_types=1);

namespace Duyler\EventBus\Collector;

use Duyler\EventBus\Contract\State\StateActionAfterHandlerInterface;
use Duyler\EventBus\Contract\State\StateActionBeforeHandlerInterface;
use Duyler\EventBus\Contract\State\StateActionThrowingHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainAfterHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainBeforeHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainFinalHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainStartHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainSuspendHandlerInterface;
use Duyler\EventBus\Enum\StateType;
use Duyler\EventBus\State\StateHandlerCollection;

readonly class StateHandlerCollector
{
    public function __construct(private StateHandlerCollection $stateHandlerCollection)
    {
    }

    public function addStateMainStartHandler(StateMainStartHandlerInterface $startHandler): void
    {
        $this->stateHandlerCollection->add(
            new class (StateType::MainBeforeStart, $startHandler) {
                public function __construct(
                    public readonly StateType $type,
                    public readonly StateMainStartHandlerInterface $handler,
                ) {
                }
            }
        );
    }

    public function addStateMainBeforeHandler(StateMainBeforeHandlerInterface $beforeActionHandler): void
    {
        $this->stateHandlerCollection->add(
            new class (StateType::MainBeforeAction, $beforeActionHandler) {
                public function __construct(
                    public readonly StateType $type,
                    public readonly StateMainBeforeHandlerInterface $handler,
                ) {
                }
            }
        );
    }

    public function setStateMainSuspendHandler(StateMainSuspendHandlerInterface $suspendHandler): void
    {
        $this->stateHandlerCollection->set(StateType::MainSuspendAction->name,
            new class (StateType::MainSuspendAction, $suspendHandler) {
                public function __construct(
                    public readonly StateType $type,
                    public readonly StateMainSuspendHandlerInterface $handler,
                ) {
                }
            }
        );
    }

    public function addStateMainAfterHandler(StateMainAfterHandlerInterface $afterActionHandler): void
    {
        $this->stateHandlerCollection->add(
            new class (StateType::MainAfterAction, $afterActionHandler) {
                public function __construct(
                    public readonly StateType $type,
                    public readonly StateMainAfterHandlerInterface $handler,
                ) {
                }
            }
        );
    }

    public function addStateActionBeforeHandler(StateActionBeforeHandlerInterface $actionBeforeHandler): void
    {
        $this->stateHandlerCollection->add(
            new class (StateType::ActionBefore, $actionBeforeHandler) {
                public function __construct(
                    public readonly StateType $type,
                    public readonly StateActionBeforeHandlerInterface $handler,
                ) {
                }
            }
        );
    }

    public function addStateActionThrowingHandler(StateActionThrowingHandlerInterface $actionThrowingHandler): void
    {
        $this->stateHandlerCollection->add(
            new class (StateType::ActionThrowing, $actionThrowingHandler) {
                public function __construct(
                    public readonly StateType $type,
                    public readonly StateActionThrowingHandlerInterface $handler,
                ) {
                }
            }
        );
    }

    public function addStateActionAfterHandler(StateActionAfterHandlerInterface $actionAfterHandler): void
    {
        $this->stateHandlerCollection->add(
            new class (StateType::ActionAfter, $actionAfterHandler) {
                public function __construct(
                    public readonly StateType $type,
                    public readonly StateActionAfterHandlerInterface $handler,
                ) {
                }
            }
        );
    }

    public function addStateMainFinalHandler(StateMainFinalHandlerInterface $finalHandler)
    {
        $this->stateHandlerCollection->add(
            new class (StateType::MainFinal, $finalHandler) {
                public function __construct(
                    public readonly StateType $type,
                    public readonly StateMainFinalHandlerInterface $handler,
                ) {
                }
            }
        );
    }
}
