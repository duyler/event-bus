<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal;

use Override;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

final readonly class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(private ListenerProviderInterface $listenerProvider) {}

    #[Override]
    public function dispatch(object $event): void
    {
        /** @var callable $listener */
        foreach ($this->listenerProvider->getListenersForEvent($event) as $listener) {
            $listener($event);
        }
    }
}
