<?php

declare(strict_types=1);

namespace Jine\EventBus;

use RuntimeException;
use Closure;

class PreloadDispatcher extends AbstractDispatcher
{
    private bool $preloaded = false;

    public function run(string $startAction, ?Closure $callback): void
    {
        if ($this->preloaded) {
            throw new RuntimeException('Bus is already preloaded!');
        }

        $this->startLoop($startAction, $callback);
        $this->preloaded = true;
    }
}
