<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Enum\StateType;
use Duyler\EventBus\State\StateAction;
use Duyler\EventBus\State\StateMain;
use Throwable;

readonly class State
{
    public function __construct(private StateMain $stateMain, private StateAction $stateAction)
    {
    }

    public function declare(StateType $stateType, mixed $data = null, Throwable $exception = null): void
    {
        match ($stateType) {
            StateType::MainBeforeStart => $this->stateMain->start(),
            StateType::MainBeforeAction => $this->stateMain->before($data),
            StateType::ActionBefore => $this->stateAction->before($data),
            StateType::ActionThrowing => $this->stateAction->throwing($data, $exception),
            StateType::MainSuspendAction => $this->stateMain->suspend($data),
            StateType::ActionAfter => $this->stateAction->after($data),
            StateType::MainAfterAction => $this->stateMain->after($data),
            StateType::MainFinal => $this->stateMain->final(),
        };
    }
}
