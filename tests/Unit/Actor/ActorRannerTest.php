<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Actor;

use Duyler\EventBus\Actor\ActorContainerProvider;
use Duyler\EventBus\Actor\ActorHandlerArgumentBuilder;
use Duyler\EventBus\Actor\ActorHandlerBuilder;
use Duyler\EventBus\Actor\ActorRunnerProvider;
use Duyler\EventBus\Build\Actor;
use Exception;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class ActorRannerTest extends TestCase
{
    private ActorContainerProvider $containerBuilder;
    private ActorHandlerArgumentBuilder $argumentBuilder;
    private ActorHandlerBuilder $handlerBuilder;
    private EventDispatcherInterface $eventDispatcher;

    #[Test]
    public function runActor_with_exception(): void
    {
        $this->handlerBuilder->method('build')->willReturn(fn() => throw new Exception());
        $actorRunner = $this->createInstance();

        $this->expectException(Throwable::class);

        $actor = new Actor(
            id: 'Test',
            handler: fn() => '',
        );

        $runner = $actorRunner->getRunner($actor);

        $runner->getCallback()();
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->containerBuilder = $this->createStub(ActorContainerProvider::class);
        $this->argumentBuilder = $this->createStub(ActorHandlerArgumentBuilder::class);
        $this->handlerBuilder = $this->createStub(ActorHandlerBuilder::class);
        $this->eventDispatcher = $this->createStub(EventDispatcherInterface::class);

        parent::setUp();
    }

    private function createInstance(): ActorRunnerProvider
    {
        return new ActorRunnerProvider(
            actorContainerProvider: $this->containerBuilder,
            argumentBuilder: $this->argumentBuilder,
            handlerBuilder: $this->handlerBuilder,
            eventDispatcher: $this->eventDispatcher,
        );
    }
}
