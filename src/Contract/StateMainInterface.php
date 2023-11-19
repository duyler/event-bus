<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

use Duyler\EventBus\Bus\Task;

interface StateMainInterface
{
    public function start(): void;
    public function before(Task $task): void;
    public function suspend(Task $task): void;
    public function resume(Task $task): void;
    public function after(Task $task): void;
    public function final(): void;
}
