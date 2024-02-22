<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Action;

use Duyler\EventBus\Action\ActionContainer;
use Duyler\EventBus\Action\ActionHandlerArgumentBuilder;
use Duyler\EventBus\Action\ActionSubstitution;
use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Collection\CompleteActionCollection;
use Duyler\EventBus\Collection\TriggerRelationCollection;
use Duyler\EventBus\Dto\Action;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class ActionHandlerArgumentBuilderTest extends TestCase
{
    private CompleteActionCollection $eventCollection;
    private ActionSubstitution $actionSubstitution;
    private ActionCollection $actionCollection;
    private ActionHandlerArgumentBuilder $argumentBuilder;
    private ActionContainer $actionContainer;
    private TriggerRelationCollection $triggerRelationCollection;

    #[Test]
    public function build_with_empty_action_required(): void
    {
        $action = new Action(id: 'Empty.Required.Action', handler: fn() => '', required: []);
        $this->assertEquals(null, $this->argumentBuilder->build($action, $this->actionContainer));
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->eventCollection = $this->createMock(CompleteActionCollection::class);
        $this->actionSubstitution = $this->createMock(ActionSubstitution::class);
        $this->actionCollection = $this->createMock(ActionCollection::class);
        $this->actionContainer = $this->createMock(ActionContainer::class);
        $this->triggerRelationCollection = $this->createMock(TriggerRelationCollection::class);
        $this->argumentBuilder = new ActionHandlerArgumentBuilder(
            completeActionCollection: $this->eventCollection,
            actionSubstitution: $this->actionSubstitution,
            triggerRelationCollection: $this->triggerRelationCollection,
        );
    }
}
