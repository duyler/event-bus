<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\unit\Action;

use Duyler\EventBus\Action\ActionContainerBuilder;
use Duyler\EventBus\Action\ActionHandler;
use Duyler\EventBus\Action\ActionHandlerArgumentBuilder;
use Duyler\EventBus\Action\ActionHandlerBuilder;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\State\StateAction;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class ActionHandlerTest extends TestCase
{
    private ActionContainerBuilder $containerBuilder;
    private StateAction $stateAction;
    private ActionHandlerArgumentBuilder $argumentBuilder;
    private ActionHandlerBuilder $handlerBuilder;

    #[Test()]
    public function handle_with_exception(): void
    {
        $this->handlerBuilder->method('build')->willReturn(fn () => throw new \Exception());
        $actionHandler = $this->createInstance();

        $this->expectException(\Throwable::class);

        $actionHandler->handle(
            new Action(
                id: 'Test',
                handler: fn () => '',
            )
        );
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->containerBuilder = $this->createMock(ActionContainerBuilder::class);
        $this->stateAction = $this->createMock(StateAction::class);
        $this->argumentBuilder = $this->createMock(ActionHandlerArgumentBuilder::class);
        $this->handlerBuilder = $this->createMock(ActionHandlerBuilder::class);

        parent::setUp();
    }

    private function createInstance(): ActionHandler
    {
        return new ActionHandler(
            containerBuilder: $this->containerBuilder,
            stateAction: $this->stateAction,
            argumentBuilder: $this->argumentBuilder,
            handlerBuilder: $this->handlerBuilder,
        );
    }
}
