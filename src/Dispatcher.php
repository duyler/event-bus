<?php 

declare(strict_types=1);

namespace Jine\EventBus;

use Closure;

class Dispatcher extends AbstractDispatcher
{
    public function run(string $startAction, ?Closure $callback): void
    {
        $this->runLoop($startAction, $callback);
    }
}
