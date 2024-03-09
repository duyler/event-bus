[![Build status](https://github.com/duyler/event-bus/workflows/build/badge.svg)](https://github.com/duyler/event-bus/actions?query=workflow%3Aci)
[![type-coverage](https://shepherd.dev/github/duyler/event-bus/coverage.svg)](https://shepherd.dev/github/duyler/event-bus)
[![psalm-level](https://shepherd.dev/github/duyler/event-bus/level.svg)](https://shepherd.dev/github/duyler/event-bus)
[![codecov](https://codecov.io/gh/duyler/event-bus/graph/badge.svg?token=Z60T9EMXD6)](https://codecov.io/gh/duyler/event-bus)
# Event Bus

Event bus implements cooperative multitasking by design. All work is done in "actions" and run inside php fibers.
You can control the execution of actions using state handlers. Any action instantiate and run in isolate di container.

### Types of states

**States of main context:**

* StateMainBegin - run before start
* StateMainCyclic - run always while queue is not empty or cyclic if bus mode set as "Loop"
* StateMainBefore - run before build and executing action
* StateMainSuspend - run when action call `Fiber::suspend()`
* StateMainResume - run before returning of control to suspended action
* StateMainAfter - run after executing action
* StateMainEnd - run when bus queue is empty and all actions is complete ("Queue" mode only)

**States of action context (inside fiber):**

* StateActionBefore - run before executing action in fiber context
* StateActionAfter - run after executing action in fiber context
* StateActionThrowing - run if action throwing exception

Any state available set methods for control execution of actions in custom state handlers.

### Overview of Action properties


`string $id` - Unique action id. Example: 'MyService.DoWork'.

`string|Closure $handler` - Closure or invokable class.

`array $required` - Array of actions ids. Required action it is condition for action executing. Results of required action must be received into target action argument or argument factory.

`?string $triggeredOn` - Action will be triggered for executing after trigger be push into bus.

`array $bind` - Array class map for action dependency. Example: `[MyInterface::class => MyClass::class]`. See https://github.com/duyler/dependency-injection

`array $providers` Array dependency provider for action. See https://github.com/duyler/dependency-injection

`?string $argument` - Type of action argument.

`string|Closure|null $argumentFactory` - Closure or invokable class for build action argument.

`?string $contract` - Return type of action result data.

`string|Closure|null $rollback` - Closure or invokable class, try for rollback action, after thrown bus exception from eny points.

`bool $externalAccess` - Allow to receive action result from `BusInterface` or state handlers

`bool $repeatable` - Allow to repeat action

`bool $lock` - If action used parallel executing (e.g. Parallel php extension), it guarantees the sequence of execution repeatable action.

`bool $private` - If set as true, this action NOT MUST BE required from other actions and receive result.

`array $sealed` - Array of action ids, allowed to require this action and receive result

`bool $silent` - If set as true, action NOT BE generate internal event of execution this action. Subscription on silent action impossible and thrown exception.

`array $alternates` - Array of action ids of this action result. Will be replace if this action be required of other action, but action return result with ResultStatus::Fail.

`int $retries` - Count of retries if action return result with ResultStatus:Fail.


## Example amqp worker

### Create state handler for connect to amqp queue

```php

<?php

declare(strict_types=1);

use AMQPChannel;
use AMQPConnection;
use AMQPQueue;
use Duyler\EventBus\Contract\State\MainBeginStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainBeginService;
use Duyler\EventBus\State\StateContext;
use AccountEventQueueConfig;
use Override;

class ConnectToQueueStateHandler implements MainBeginStateHandlerInterface
{
    public function __construct(
        private AccountEventQueueConfig $queueConfig,
    ) {}

    #[Override]
    public function handle(StateMainBeginService $stateService, StateContext $context): void
    {
        $connection = new AMQPConnection();
        $connection->setHost($this->queueConfig->host);
        $connection->setPort($this->queueConfig->port);
        $connection->setLogin($this->queueConfig->login);
        $connection->setPassword($this->queueConfig->password);
        $connection->connect();
        
        $channel = new AMQPChannel($connection);

        $queue = new AMQPQueue($channel);
        $queue->setName($this->queueConfig->queueName);
        $queue->declareQueue();

        $context->write('queue', $queue);
    }
}

```

### Create state handler for listening queue

```php

<?php

declare(strict_types=1);

use AMQPEnvelope;
use AMQPQueue;
use Duyler\EventBus\Contract\State\MainCyclicStateHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Trigger;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\State\Service\StateMainCyclicService;
use Duyler\EventBus\State\StateContext;
use Override;

class ListeningQueueStateHandler implements MainCyclicStateHandlerInterface
{
    #[Override]
    public function handle(StateMainCyclicService $stateService, StateContext $context): void
    {
        /** @var AMQPQueue $queue */
        $queue = $context->read('queue');

        $message = $queue->get();
        
        if ($message === null) {
            return;
        }

        $content = json_decode($message->getBody(), true);

        $actionId = 'account_id_' . $content['account_id'];

        if ($stateService->actionIsExists($actionId) === false) {
            $stateService->addAction(
                new Action(
                    id: $actionId,
                    handler: HandleAccountEventAction::class,
                    triggeredOn: $actionId,
                    argument: AMQPEnvelope::class,
                    contract: AMQPEnvelope::class,
                    repeatable: true,
                )
            );
        }

        $stateService->doTrigger(
            new Trigger(
                id: $actionId,
                data: $message,
                contract: AMQPEnvelope::class,
            )
        );
    }
}

```

### Create state handler for send ack into queue

```php

<?php

declare(strict_types=1);

use AMQPEnvelope;
use AMQPQueue;
use Duyler\EventBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\StateContext;
use Override;

class AckMessageStateHandler implements MainAfterStateHandlerInterface
{
    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        if ($stateService->getStatus() === ResultStatus::Success) {
            /** @var AMQPEnvelope $message */
            $message = $stateService->getResultData();
            /** @var AMQPQueue $queue */
            $queue = $context->read('queue');
            $queue->ack($message->getDeliveryTag());
        }
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return [];
    }
}

```

### Create action handler

```php

<?php

declare(strict_types=1);

use AMQPEnvelope;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Enum\ResultStatus;
use Fiber;

class HandleAccountEventAction
{
    public function __invoke(AMQPEnvelope $message): Result
    {
        $content = json_decode($message->getBody(), true);

        echo Fiber::suspend(
            fn() => 'Account id: ' . $content['account_id'] . '. Event id: ' . $content['event_id'] . PHP_EOL
        );
        
        return new Result(
            status: ResultStatus::Success,
            data: $message,
        );
    }
}

```

### Build and run

```php

// run.php

<?php

declare(strict_types=1);

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Enum\Mode;
use AccountEventQueueConfig;

$busBuilder = new BusBuilder(
    new BusConfig(
        mode: Mode::Loop,
    )
);

$config = new AccountEventQueueConfig(
        host: 'localhost',
        port: 5672,
        logi: 'user',
        password: 'password',
        queueName: 'account_events_queue',
);

$busBuilder->addStateHandler(
    new ConnectToQueueStateHandler($config),
);

$busBuilder->addStateHandler(
    new ListeningQueueStateHandler(),
);

$busBuilder->addStateHandler(
    new AckMessageStateHandler(),
);

$bus = $busBuilder
    ->build()
    ->run();

```

```shell
    $ php run.php
```

## Example content receive

```php

<?php

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\Enum\ResultStatus;
use Psr\Http\Message\ServerRequestInterface;

$requestAction = new Action(
    id: 'Request.GetRequest',
    handler: GetRequestAction::class,
    contract: ServerRequestInterface::class,
);

$blogAction = new Action(
    id: 'Blog.GetPostById',
    handler: GetPostByIdAction::class,
    required: [
        'Request.GetRequest',
    ],
    argument: PostId::class,
    argumentFactory: fn(ServerRequestInterface $request): PostId => new PostId($request->getAttribute('id')),
    externalAccess: true,
    contract: Post::class,
);

$blogCommentListAction = new Action(
    id: 'Blog.GetPostComments',
    handler: GetCommentsByPostAction::class,
    required: [
        'Blog.GetPostById',
    ],
    argument: Post,
    externalAccess: true,
    contract: CommentList::class,
);

$blogActionSubscription = new Subscription(
    subject: 'Request.GetRequest',
    actionId: 'Blog.GetPostById',
    status: ResultStatus::Success,
);

$blogCommentListActionSubscription = new Subscription(
    subject: 'Blog.GetPostById',
    actionId: 'Blog.GetPostComments',
    status: ResultStatus::Success,
);

$busBuilder = new BusBuilder(new BusConfig());

$bus = $busBuilder
    ->addAction($blogAction)
    ->addAction($blogCommentListAction)
    ->addSubscription($blogActionSubscription)
    ->addSubscription($blogCommentListActionSubscription)
    ->doAction($requestAction)
    ->build()
    ->run();

$blogPost = $bus->getResult('Blog.GetPostById');
$blogPostComments = $bus->getResult('Blog.GetPostComments');

```
