<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Action;

use Duyler\EventBus\Action\ActionContainerProvider;
use Duyler\EventBus\Action\ActionRunnerProvider;
use Duyler\EventBus\Action\ActionHandlerArgumentBuilder;
use Duyler\EventBus\Action\ActionHandlerBuilder;
use Duyler\EventBus\Dto\Action;
use Exception;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class ActionRannerTest extends TestCase
{
    private ActionContainerProvider $containerBuilder;
    private ActionHandlerArgumentBuilder $argumentBuilder;
    private ActionHandlerBuilder $handlerBuilder;
    private EventDispatcherInterface $eventDispatcher;

    #[Test]
    public function runAction_with_exception(): void
    {
        $this->handlerBuilder->method('build')->willReturn(fn() => throw new Exception());
        $actionRunner = $this->createInstance();

        $this->expectException(Throwable::class);

        $action = new Action(
            id: 'Test',
            handler: fn() => '',
        );

        $runner = $actionRunner->getRunner($action);

        $runner->run($action);
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->containerBuilder = $this->createMock(ActionContainerProvider::class);
        $this->argumentBuilder = $this->createMock(ActionHandlerArgumentBuilder::class);
        $this->handlerBuilder = $this->createMock(ActionHandlerBuilder::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);


        parent::setUp();
    }

    private function createInstance(): ActionRunnerProvider
    {
        return new ActionRunnerProvider(
            actionContainerProvider: $this->containerBuilder,
            argumentBuilder: $this->argumentBuilder,
            handlerBuilder: $this->handlerBuilder,
            eventDispatcher: $this->eventDispatcher
        );
    }
}
