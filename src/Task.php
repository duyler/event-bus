<?php 

declare(strict_types=1);

namespace Duyler\EventBus;

use Fiber;
use Duyler\EventBus\Action\ActionHandler;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Dto\Subscribe;

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
        $this->fiber = new Fiber(fn(): Result => $actionHandler->handle($this->action));
        $this->fiber->start();
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
