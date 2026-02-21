<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Action;

use Duyler\EventBus\Action\ActionHandlerArgumentBuilder;
use Duyler\EventBus\Action\ActionSubstitution;
use Duyler\EventBus\Action\Context\ActionContext;
use Duyler\EventBus\Bus\Action;
use Duyler\EventBus\Bus\ActionContainer;
use Duyler\EventBus\Bus\CompleteAction;
use Duyler\EventBus\Bus\EventRelation;
use Duyler\EventBus\Dto\Event;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Storage\CompleteActionStorage;
use Duyler\EventBus\Storage\EventRelationStorage;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

readonly class TestArgumentData
{
    public function __construct(
        public string $value = '',
    ) {}
}

readonly class TestArgumentData2
{
    public function __construct(
        public int $count = 0,
    ) {}
}

class ActionHandlerArgumentBuilderTest extends TestCase
{
    private CompleteActionStorage $completeStorage;
    private ActionSubstitution $actionSubstitution;
    private ActionHandlerArgumentBuilder $argumentBuilder;
    private ActionContainer $actionContainer;
    private EventRelationStorage $eventRelationStorage;

    #[Test]
    public function build_with_empty_action_required(): void
    {
        $action = new Action(id: 'Empty.Required.Action', externalId: 'Empty.Required.Action', handler: fn() => '', externalRequired: []);
        $this->assertInstanceOf(ActionContext::class, $this->argumentBuilder->build($action, $this->actionContainer));
    }

    #[Test]
    public function collect_subscription_results_from_external_event(): void
    {
        $eventData = new TestArgumentData('test-value');
        $eventDto = new Event(id: 'ExternalEvent' . IdFormatter::DELIMITER . 'Success', data: $eventData);
        $eventRelation = $this->createEventRelation($eventDto);

        $this->eventRelationStorage->method('isExists')
            ->with('ExternalEvent' . IdFormatter::DELIMITER . 'Success')
            ->willReturn(true);
        $this->eventRelationStorage->method('getLast')
            ->with('ExternalEvent' . IdFormatter::DELIMITER . 'Success')
            ->willReturn($eventRelation);

        $action = new Action(
            id: 'SubscriberAction',
            externalId: 'SubscriberAction',
            handler: fn(ActionContext $context) => $context->argument(),
            onOne: 'ExternalEvent' . IdFormatter::DELIMITER . 'Success',
            argument: TestArgumentData::class,
            externalRequired: [],
        );

        $result = $this->argumentBuilder->build($action, $this->actionContainer);

        $this->assertInstanceOf(ActionContext::class, $result);
        $this->assertInstanceOf(TestArgumentData::class, $result->argument());
        $this->assertSame('test-value', $result->argument()->value);
    }

    #[Test]
    public function collect_subscription_results_from_action_event(): void
    {
        $actionData = new TestArgumentData2(42);
        $sourceAction = new Action(
            id: 'SourceAction',
            externalId: 'SourceAction',
            handler: fn() => $actionData,
            type: TestArgumentData2::class,
            externalRequired: [],
        );
        $completeAction = $this->createCompleteAction($sourceAction, Result::success($actionData));

        $this->eventRelationStorage->method('isExists')
            ->willReturn(false);
        $this->completeStorage->method('isExists')
            ->with('SourceAction')
            ->willReturn(true);
        $this->completeStorage->method('get')
            ->with('SourceAction')
            ->willReturn($completeAction);

        $action = new Action(
            id: 'SubscriberAction',
            externalId: 'SubscriberAction',
            handler: fn(ActionContext $context) => $context->argument(),
            onOne: 'SourceAction' . IdFormatter::DELIMITER . 'Success',
            argument: TestArgumentData2::class,
            externalRequired: [],
        );

        $result = $this->argumentBuilder->build($action, $this->actionContainer);

        $this->assertInstanceOf(ActionContext::class, $result);
        $this->assertInstanceOf(TestArgumentData2::class, $result->argument());
        $this->assertSame(42, $result->argument()->count);
    }

    #[Test]
    public function collect_subscription_results_with_multiple_events_on_any(): void
    {
        $eventData = new TestArgumentData('from-external');
        $eventDto = new Event(id: 'Event1' . IdFormatter::DELIMITER . 'Success', data: $eventData);
        $eventRelation = $this->createEventRelation($eventDto);

        $this->eventRelationStorage->method('isExists')
            ->willReturnMap([
                ['Event1' . IdFormatter::DELIMITER . 'Success', true],
                ['Event2' . IdFormatter::DELIMITER . 'Success', false],
            ]);
        $this->eventRelationStorage->method('getLast')
            ->with('Event1' . IdFormatter::DELIMITER . 'Success')
            ->willReturn($eventRelation);

        $action = new Action(
            id: 'SubscriberAction',
            externalId: 'SubscriberAction',
            handler: fn(ActionContext $context) => $context->argument(),
            onAny: [
                'Event1' . IdFormatter::DELIMITER . 'Success',
                'Event2' . IdFormatter::DELIMITER . 'Success',
            ],
            argument: TestArgumentData::class,
            externalRequired: [],
        );

        $result = $this->argumentBuilder->build($action, $this->actionContainer);

        $this->assertInstanceOf(ActionContext::class, $result);
        $this->assertInstanceOf(TestArgumentData::class, $result->argument());
        $this->assertSame('from-external', $result->argument()->value);
    }

    #[Test]
    public function collect_subscription_results_with_on_all(): void
    {
        $eventData1 = new TestArgumentData('event1');
        $eventDto1 = new Event(id: 'Event1' . IdFormatter::DELIMITER . 'Success', data: $eventData1);
        $eventRelation1 = $this->createEventRelation($eventDto1);

        $eventData2 = new TestArgumentData2(100);
        $sourceAction = new Action(
            id: 'SourceAction',
            externalId: 'SourceAction',
            handler: fn() => $eventData2,
            type: TestArgumentData2::class,
            externalRequired: [],
        );
        $completeAction = $this->createCompleteAction($sourceAction, Result::success($eventData2));

        $this->eventRelationStorage->method('isExists')
            ->willReturnMap([
                ['Event1' . IdFormatter::DELIMITER . 'Success', true],
                ['SourceAction' . IdFormatter::DELIMITER . 'Success', false],
            ]);
        $this->eventRelationStorage->method('getLast')
            ->with('Event1' . IdFormatter::DELIMITER . 'Success')
            ->willReturn($eventRelation1);
        $this->completeStorage->method('isExists')
            ->willReturnMap([
                ['Event1', false],
                ['SourceAction', true],
            ]);
        $this->completeStorage->method('get')
            ->with('SourceAction')
            ->willReturn($completeAction);

        $action = new Action(
            id: 'SubscriberAction',
            externalId: 'SubscriberAction',
            handler: fn(ActionContext $context) => $context,
            onAll: [
                'Event1' . IdFormatter::DELIMITER . 'Success',
                'SourceAction' . IdFormatter::DELIMITER . 'Success',
            ],
            externalRequired: [],
        );

        $result = $this->argumentBuilder->build($action, $this->actionContainer);

        $this->assertInstanceOf(ActionContext::class, $result);
    }

    #[Test]
    public function collect_subscription_results_empty_when_no_data(): void
    {
        $eventDto = new Event(id: 'ExternalEvent' . IdFormatter::DELIMITER . 'Success', data: null);
        $eventRelation = $this->createEventRelation($eventDto);

        $this->eventRelationStorage->method('isExists')
            ->willReturn(true);
        $this->eventRelationStorage->method('getLast')
            ->willReturn($eventRelation);
        $this->completeStorage->method('isExists')
            ->willReturn(false);

        $action = new Action(
            id: 'SubscriberAction',
            externalId: 'SubscriberAction',
            handler: fn(ActionContext $context) => $context,
            onOne: 'ExternalEvent' . IdFormatter::DELIMITER . 'Success',
            externalRequired: [],
        );

        $result = $this->argumentBuilder->build($action, $this->actionContainer);

        $this->assertInstanceOf(ActionContext::class, $result);
    }

    #[Test]
    public function collect_subscription_results_subject_id_extraction(): void
    {
        $actionData = new TestArgumentData('action-data');
        $sourceAction = new Action(
            id: 'MyAction',
            externalId: 'MyAction',
            handler: fn() => $actionData,
            type: TestArgumentData::class,
            externalRequired: [],
        );
        $completeAction = $this->createCompleteAction($sourceAction, Result::success($actionData));

        $this->eventRelationStorage->method('isExists')
            ->willReturn(false);
        $this->completeStorage->method('isExists')
            ->with('MyAction')
            ->willReturn(true);
        $this->completeStorage->method('get')
            ->with('MyAction')
            ->willReturn($completeAction);

        $action = new Action(
            id: 'SubscriberAction',
            externalId: 'SubscriberAction',
            handler: fn(ActionContext $context) => $context->argument(),
            onOne: 'MyAction' . IdFormatter::DELIMITER . 'Success',
            argument: TestArgumentData::class,
            externalRequired: [],
        );

        $result = $this->argumentBuilder->build($action, $this->actionContainer);

        $this->assertInstanceOf(ActionContext::class, $result);
        $this->assertInstanceOf(TestArgumentData::class, $result->argument());
        $this->assertSame('action-data', $result->argument()->value);
    }

    #[Test]
    public function build_throws_logic_exception_when_argument_not_found(): void
    {
        $this->eventRelationStorage->method('isExists')
            ->willReturn(false);
        $this->completeStorage->method('isExists')
            ->willReturn(false);

        $action = new Action(
            id: 'TestAction',
            externalId: 'TestAction',
            handler: fn(TestArgumentData $data) => $data,
            argument: TestArgumentData::class,
            externalRequired: [],
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Argument factory is not set to unresolved argument: ' . TestArgumentData::class . ' for TestAction');

        $this->argumentBuilder->build($action, $this->actionContainer);
    }

    #[Test]
    public function build_returns_argument_directly_when_handler_not_callable(): void
    {
        $eventData = new TestArgumentData('direct-return');
        $eventDto = new Event(id: 'ExternalEvent' . IdFormatter::DELIMITER . 'Success', data: $eventData);
        $eventRelation = $this->createEventRelation($eventDto);

        $this->eventRelationStorage->method('isExists')
            ->willReturn(true);
        $this->eventRelationStorage->method('getLast')
            ->willReturn($eventRelation);

        $action = new Action(
            id: 'TestAction',
            externalId: 'TestAction',
            handler: 'nonCallableHandler',
            onOne: 'ExternalEvent' . IdFormatter::DELIMITER . 'Success',
            argument: TestArgumentData::class,
            externalRequired: [],
        );

        $result = $this->argumentBuilder->build($action, $this->actionContainer);

        $this->assertInstanceOf(TestArgumentData::class, $result);
        $this->assertSame('direct-return', $result->value);
    }

    private function createEventRelation(Event $event): EventRelation
    {
        $action = new Action(
            id: 'EventPublisher',
            externalId: 'EventPublisher',
            handler: fn() => null,
            externalRequired: [],
        );
        return new EventRelation($action, $event);
    }

    private function createCompleteAction(Action $action, Result $result): CompleteAction
    {
        return new CompleteAction($action, $result, 'task-id');
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->completeStorage = $this->createMock(CompleteActionStorage::class);
        $this->actionSubstitution = $this->createMock(ActionSubstitution::class);
        $this->actionContainer = $this->createMock(ActionContainer::class);
        $this->eventRelationStorage = $this->createMock(EventRelationStorage::class);
        $this->argumentBuilder = new ActionHandlerArgumentBuilder(
            completeActionStorage: $this->completeStorage,
            actionSubstitution: $this->actionSubstitution,
            eventRelationStorage: $this->eventRelationStorage,
        );
    }
}
