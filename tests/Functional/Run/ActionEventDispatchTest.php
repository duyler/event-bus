<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Action\Context\ActionContext;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Build\Event;
use Duyler\EventBus\Dto\Event as EventDto;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use stdClass;

class ActionEventDispatchTest extends TestCase
{
    #[Test]
    public function dispatchEvent_from_action_context(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());

        $busBuilder->addEvent(
            new Event(
                id: 'EventFromActionContext',
            ),
        );

        $busBuilder->doAction(
            new Action(
                id: 'TestEventDispatch',
                handler: function (ActionContext $context) {
                    $context->dispatchEvent(new EventDto('EventFromActionContext'));
                },
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'TestEventDispatchListener',
                handler: function (ActionContext $context) {},
                listen: ['EventFromActionContext'],
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('TestEventDispatchListener'));
        $this->assertTrue($bus->resultIsExists('EventFromActionContext'));
    }

    #[Test]
    public function dispatchEvent_from_action_with_event_dispatcher(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());

        $busBuilder->addEvent(
            new Event(
                id: 'EventFromActionContext',
            ),
        );

        $busBuilder->doAction(
            new Action(
                id: 'TestEventDispatch',
                handler: function (ActionContext $context) {
                    /** @var EventDispatcherInterface $eventDispatcher */
                    $eventDispatcher = $context->call(
                        fn(EventDispatcherInterface $eventDispatcher) => $eventDispatcher,
                    );

                    $eventDispatcher->dispatch(new EventDto('EventFromActionContext'));
                },
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'TestEventDispatchListener',
                handler: function (ActionContext $context) {},
                listen: ['EventFromActionContext'],
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('TestEventDispatchListener'));
        $this->assertTrue($bus->resultIsExists('EventFromActionContext'));
    }

    #[Test]
    public function dispatchEvent_from_action_with_dispatch_invalid_event(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());

        $busBuilder->addEvent(
            new Event(
                id: 'EventFromActionContext',
            ),
        );

        $busBuilder->doAction(
            new Action(
                id: 'TestEventDispatch',
                handler: function (ActionContext $context) {
                    /** @var EventDispatcherInterface $eventDispatcher */
                    $eventDispatcher = $context->call(
                        fn(EventDispatcherInterface $eventDispatcher) => $eventDispatcher,
                    );

                    $eventDispatcher->dispatch(new stdClass());
                },
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'TestEventDispatchListener',
                handler: function (ActionContext $context) {},
                listen: ['EventFromActionContext'],
            ),
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Event must be an instance of ' . EventDto::class);

        $bus = $busBuilder->build();
        $bus->run();
    }
}
