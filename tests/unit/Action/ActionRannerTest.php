<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\unit\Action;

use Duyler\EventBus\Action\ActionContainerBind;
use Duyler\EventBus\Action\ActionContainerProvider;
use Duyler\EventBus\Action\ActionRunner;
use Duyler\EventBus\Action\ActionHandlerArgumentBuilder;
use Duyler\EventBus\Action\ActionHandlerBuilder;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\State\StateAction;
use Exception;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Throwable;

class ActionRannerTest extends TestCase
{
    private ActionContainerProvider $containerBuilder;
    private StateAction $stateAction;
    private ActionHandlerArgumentBuilder $argumentBuilder;
    private ActionHandlerBuilder $handlerBuilder;
    private ActionContainerBind $actionContainerBind;

    #[Test]
    public function runAction_with_exception(): void
    {
        $this->handlerBuilder->method('build')->willReturn(fn() => throw new Exception());
        $actionRunner = $this->createInstance();

        $this->expectException(Throwable::class);

        $actionRunner->runAction(
            new Action(
                id: 'Test',
                handler: fn() => '',
            )
        );
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->containerBuilder = $this->createMock(ActionContainerProvider::class);
        $this->stateAction = $this->createMock(StateAction::class);
        $this->argumentBuilder = $this->createMock(ActionHandlerArgumentBuilder::class);
        $this->handlerBuilder = $this->createMock(ActionHandlerBuilder::class);
        $this->actionContainerBind = $this->createMock(ActionContainerBind::class);

        parent::setUp();
    }

    private function createInstance(): ActionRunner
    {
        return new ActionRunner(
            actionContainerProvider: $this->containerBuilder,
            stateAction: $this->stateAction,
            argumentBuilder: $this->argumentBuilder,
            handlerBuilder: $this->handlerBuilder,
            actionContainerBind: $this->actionContainerBind,
        );
    }
}
