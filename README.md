[![Build status](https://github.com/duyler/event-bus/workflows/build/badge.svg)](https://github.com/duyler/event-bus/actions?query=workflow%3Aci)
[![type-coverage](https://shepherd.dev/github/duyler/event-bus/coverage.svg)](https://shepherd.dev/github/duyler/event-bus)
[![psalm-level](https://shepherd.dev/github/duyler/event-bus/level.svg)](https://shepherd.dev/github/duyler/event-bus)
[![Code Coverage](https://codecov.io/gh/duyler/event-bus/branch/main/graph/badge.svg)](https://codecov.io/gh/duyler/event-bus)
# Event Bus

## Base usage

```php

<?php

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\Enum\ResultStatus;

$requestAction = new Action(
    id: 'Request.GetRequest',
    handler: GetRequestAction::class,
);

$blogAction = new Action(
    id: 'Blog.GetPostById',
    handler: GetPostByIdAction::class,
    required: [
        'Request.GetRequest',
    ],
    argument: PostIdFactory::class,
    externalAccess: true,
    contract: Post::class,
);

$blogActionSubscription = new Subscription(
    subject: 'Request.GetRequest',
    actionId: 'Blog.GetPostById',
    status: ResultStatus::Success,
);

$busBuilder = new BusBuilder();

$bus = $busBuilder
    ->addAction($blogAction)
    ->addSubscription($blogActionSubscription)
    ->doAction($requestAction)
    ->build()
    ->run();

$blogPost = $bus->getResult('Blog.GetPostById');

```
