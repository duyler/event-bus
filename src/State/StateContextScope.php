<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\DI\Attribute\Finalize;
use Duyler\EventBus\Build\Context;

#[Finalize]
class StateContextScope
{
    private StateContext $commonContext;

    /** @var array<string, StateContext> */
    private array $contexts = [];

    public function __construct()
    {
        $this->commonContext = new StateContext();
    }

    public function getContext(string $stateHandlerClass): StateContext
    {
        return $this->contexts[$stateHandlerClass] ?? $this->commonContext;
    }

    public function addContext(Context $context): void
    {
        $stateContext = new StateContext();

        foreach ($context->scope as $class) {
            $this->contexts[$class] = $stateContext;
        }
    }

    public function finalize(): void
    {
        $this->contexts = [];
        $this->commonContext = new StateContext();
    }
}
