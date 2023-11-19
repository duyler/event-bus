<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\unit\Action;

use Duyler\EventBus\Action\ActionContainerBuilder;
use Duyler\EventBus\Action\ActionSubstitution;
use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Collection\ActionContainerCollection;
use Duyler\EventBus\Collection\EventCollection;
use Duyler\EventBus\State\StateAction;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class ActionHandlerTest extends TestCase
{
    private ActionContainerBuilder $containerBuilder;
    private StateAction $stateAction;
    private ActionContainerCollection $containerCollection;
    private ActionCollection $actionCollection;
    private ActionSubstitution $actionSubstitution;
    private EventCollection $eventCollection;

    #[Test()]
    public function handle_with_return_result(): void
    {

    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->containerBuilder = $this->createMock(ActionContainerBuilder::class);
        $this->stateAction = $this->createMock(StateAction::class);
        $this->containerCollection = $this->createMock(ActionContainerCollection::class);
        $this->actionCollection = $this->createMock(ActionCollection::class);
        $this->actionSubstitution = $this->createMock(ActionSubstitution::class);
        $this->eventCollection = $this->createMock(EventCollection::class);

        parent::setUp();
    }
}
