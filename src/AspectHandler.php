<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Action\ActionContainer;
use Duyler\EventBus\Dto\Action;

class AspectHandler
{
    public function runBefore(Action $action, ActionContainer $container, array $arguments): void
    {
        $this->runAdvice($action->before, $container, $arguments);
    }

    public function runAfter(Action $action, ActionContainer $container, array $arguments): void
    {
        $this->runAdvice($action->after, $container, $arguments);
    }

    public function runAround(Action $action, ActionContainer $container, array $arguments): mixed
    {
        if (is_callable($action->around)) {
            return ($action->around)(...$arguments);
        }

        $around = $container->make($action->around);
        return ($around)(...$arguments);
    }

    private function runAdvice(array $advices, ActionContainer $container, array $arguments): void
    {
        foreach ($advices as $advice) {
            if (is_callable($advice) === false) {
                $advice = $container->make($advice);
            }
            $advice(...$arguments);
        }
    }
}
