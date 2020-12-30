<?php

declare(strict_types=1);

namespace Jine\EventBus;

class PreloadDispatcher extends AbstractDispatcher
{
    public function run(string $startAction, ?callable $callback): void
    {
        $this->startLoop($startAction, $callback);
    }
}
