<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Closure;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Fiber;

class Task
{
    public readonly Action $action;
    public readonly ?Result $result;
    private mixed $value = null;
    private ?Fiber $fiber = null;

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

    public function resume(mixed $data = null): void
    {
        $this->value = $this->fiber->resume($data);
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
