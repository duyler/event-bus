<?php 

declare(strict_types=1);

namespace Konveyer\EventBus;

use Fiber;
use Konveyer\EventBus\DTO\Result;
use Konveyer\EventBus\Action;
use Konveyer\EventBus\ActionHandler;
use Konveyer\EventBus\DTO\Subscribe;

class Task
{
    public readonly Action $action;
    public readonly ?Subscribe $subscribe;
    public readonly ?Result $result;
    private ?Fiber $fiber = null;

    public function __construct(Action $action, Subscribe $subscribe = null)
    {
        $this->action = $action;
        $this->subscribe = $subscribe;
    }

    public function run(ActionHandler $actionHandler): void
    {
        $this->fiber = new Fiber(
            function (ActionHandler $actionHandler): Result {
                return $actionHandler->handle();
            }
        );

        $this->fiber->start($actionHandler);
    }

    public function isRunning(): bool
    {
        return $this->fiber && $this->fiber->isSuspended();
    }

    public function resume(): void
    {
        $this->fiber->resume();
    }

    public function takeResult(): void
    {
        $this->result = $this->fiber->getReturn();
    }
}
