<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Action\ActionContainer;
use Duyler\EventBus\Dto\Action;

class AspectHandler
{
    public function runBefore(Action $action, ActionContainer $container, array $arguments): void
    {
        $before = [];

        foreach ($action->before as $advice) {
            if (is_callable($advice)) {
                $before[] = $advice;
                continue;
            }
            $before[] = $container->make($advice);
        }

        foreach ($before as $advice) {
            $advice(...$arguments);
        }
    }

    public function runAfter(Action $action, ActionContainer $container, array $arguments): void
    {
        $after = [];

        foreach ($action->after as $advice) {
            if (is_callable($advice)) {
                $after[] = $advice;
                continue;
            }
            $after[] = $container->make($advice);
        }

        foreach ($after as $advice) {
            $advice(...$arguments);
        }
    }

    public function runAround(Action $action, ActionContainer $container, array $arguments): mixed
    {
        if (is_callable($action->around)) {
            return ($action->around)(...$arguments);
        }

        $around = $container->make($action->around);
        return ($around)(...$arguments);
    }
}
