<?php 

declare(strict_types=1);

namespace Duyler\EventBus;

use Closure;
use Duyler\EventBus\Exception\ActionCoroutineNotSetException;
use Fiber;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;

class Task
{
    public readonly Action $action;
    public readonly ?Result $result;
    private ?Fiber $fiber = null;
    private mixed $value = null;

    public function __construct(Action $action)
    {
        $this->action = $action;
    }

    public function run(Closure $actionHandler): void
    {
        $this->fiber = new Fiber($actionHandler);
        $this->value = $this->fiber->start();
    }

    public function isRunning(): bool
    {
        return $this->fiber && $this->fiber->isSuspended();
    }

    public function resume(Closure $coroutineHandler): void
    {
        if (empty($this->action->coroutine) && $this->value !== null) {
            throw new ActionCoroutineNotSetException($this->action->id);
        }

        if (empty($this->action->coroutine)) {
            $this->fiber->resume();
        } else {
            $this->value = $coroutineHandler($this->value, fn (mixed $value): mixed
                => $this->fiber->resume($value)
            );
        }
    }

    public function takeResult(): void
    {
        $this->result = $this->fiber->getReturn();
    }
}
