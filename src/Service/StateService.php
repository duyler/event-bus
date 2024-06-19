<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Service;

use Duyler\ActionBus\Build\Context;
use Duyler\ActionBus\Contract\State\StateHandlerInterface;
use Duyler\ActionBus\State\StateContextScope;
use Duyler\ActionBus\State\StateHandlerStorage;

readonly class StateService
{
    public function __construct(
        private StateHandlerStorage $stateHandlerStorage,
        private StateContextScope $contextScope,
    ) {}

    public function addStateHandler(StateHandlerInterface $stateHandler): void
    {
        $this->stateHandlerStorage->addStateHandler($stateHandler);
    }

    public function addStateContext(Context $context): void
    {
        $this->contextScope->addContext($context);
    }
}
