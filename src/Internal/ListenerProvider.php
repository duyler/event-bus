<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal;

use Override;
use Psr\EventDispatcher\ListenerProviderInterface;

class ListenerProvider implements ListenerProviderInterface
{
    /** @var array<string, callable[]>  */
    private array $listeners = [];

    #[Override]
    public function getListenersForEvent(object $event): iterable
    {
        return $this->listeners[$event::class] ?? [];
    }

    public function addListener(string $eventClass, callable $listener): void
    {
        $this->listeners[$eventClass][] = $listener;
    }
}
