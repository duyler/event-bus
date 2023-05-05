<?php 

declare(strict_types=1);

namespace Duyler\EventBus;

use Closure;
use Duyler\EventBus\Dto\Coroutine;
use Fiber;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;

class Task
{
    public readonly Action $action;
    public readonly ?Coroutine $coroutine;
    public readonly ?Result $result;
    private ?Fiber $fiber = null;
    private mixed $value = null;

    public function __construct(Action $action, ?Coroutine $coroutine = null)
    {
        $this->action = $action;
        $this->coroutine = $coroutine;
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

    public function resume(): void
    {
        $this->value = $this->fiber->resume($this->coroutine?->callback);
    }

    public function takeResult(): void
    {
        $this->result = $this->fiber->getReturn();
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
