<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action\Context;

use Duyler\EventBus\Bus\ActionContainer;
use Duyler\EventBus\Dto\Event;
use LogicException;
use Psr\EventDispatcher\EventDispatcherInterface;

final class ActionContext extends BaseContext
{
    public function __construct(
        private string $actionId,
        private ActionContainer $actionContainer,
        private mixed $argument,
    ) {
        parent::__construct($this->actionContainer);
    }

    public function argument(): mixed
    {
        return $this->argument ?? throw new LogicException('Argument not defined for action ' . $this->actionId);
    }

    public function dispatchEvent(Event $event): void
    {
        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->actionContainer->get(EventDispatcherInterface::class);
        $eventDispatcher->dispatch($event);
    }
}
