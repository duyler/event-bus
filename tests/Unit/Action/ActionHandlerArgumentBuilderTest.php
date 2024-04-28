<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Unit\Action;

use Duyler\ActionBus\Action\ActionHandlerArgumentBuilder;
use Duyler\ActionBus\Action\ActionSubstitution;
use Duyler\ActionBus\Bus\ActionContainer;
use Duyler\ActionBus\Collection\ActionArgumentCollection;
use Duyler\ActionBus\Collection\CompleteActionCollection;
use Duyler\ActionBus\Collection\TriggerRelationCollection;
use Duyler\ActionBus\Dto\Action;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class ActionHandlerArgumentBuilderTest extends TestCase
{
    private CompleteActionCollection $eventCollection;
    private ActionSubstitution $actionSubstitution;
    private ActionArgumentCollection $actionArgumentCollection;
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
        $this->actionArgumentCollection = $this->createMock(ActionArgumentCollection::class);
        $this->actionContainer = $this->createMock(ActionContainer::class);
        $this->triggerRelationCollection = $this->createMock(TriggerRelationCollection::class);
        $this->argumentBuilder = new ActionHandlerArgumentBuilder(
            completeActionCollection: $this->eventCollection,
            actionSubstitution: $this->actionSubstitution,
            triggerRelationCollection: $this->triggerRelationCollection,
            actionArgumentCollection: $this->actionArgumentCollection,
        );
    }
}
