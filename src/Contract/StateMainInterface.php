<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Contract;

use Duyler\ActionBus\Bus\Task;

interface StateMainInterface
{
    public function begin(): void;

    public function cyclic(): void;

    public function before(Task $task): void;

    public function suspend(Task $task): void;

    public function resume(Task $task): void;

    public function after(Task $task): void;

    public function end(): void;
}
