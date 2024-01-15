<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Contract\State\StateHandlerInterface;
use Duyler\EventBus\Dto\Context;
use Duyler\EventBus\State\StateContextScope;
use Duyler\EventBus\State\StateHandlerStorage;

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
