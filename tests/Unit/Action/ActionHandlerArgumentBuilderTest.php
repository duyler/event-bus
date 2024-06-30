<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Unit\Action;

use Duyler\ActionBus\Action\ActionHandlerArgumentBuilder;
use Duyler\ActionBus\Action\ActionSubstitution;
use Duyler\ActionBus\Build\Action;
use Duyler\ActionBus\Bus\ActionContainer;
use Duyler\ActionBus\Storage\ActionArgumentStorage;
use Duyler\ActionBus\Storage\CompleteActionStorage;
use Duyler\ActionBus\Storage\EventRelationStorage;
use Duyler\ActionBus\Storage\EventStorage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class ActionHandlerArgumentBuilderTest extends TestCase
{
    private CompleteActionStorage $completeStorage;
    private ActionSubstitution $actionSubstitution;
    private ActionArgumentStorage $actionArgumentStorage;
    private ActionHandlerArgumentBuilder $argumentBuilder;
    private ActionContainer $actionContainer;
    private EventRelationStorage $eventRelationStorage;
    private EventStorage $eventStorage;

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
        $this->completeStorage = $this->createMock(CompleteActionStorage::class);
        $this->actionSubstitution = $this->createMock(ActionSubstitution::class);
        $this->actionArgumentStorage = $this->createMock(ActionArgumentStorage::class);
        $this->actionContainer = $this->createMock(ActionContainer::class);
        $this->eventRelationStorage = $this->createMock(EventRelationStorage::class);
        $this->eventStorage = $this->createMock(EventStorage::class);
        $this->argumentBuilder = new ActionHandlerArgumentBuilder(
            completeActionStorage: $this->completeStorage,
            actionSubstitution: $this->actionSubstitution,
            eventRelationStorage: $this->eventRelationStorage,
            actionArgumentStorage: $this->actionArgumentStorage,
            eventStorage: $this->eventStorage,
        );
    }
}
