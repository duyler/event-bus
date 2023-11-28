![build](https://github.com/duyler/event-bus/workflows/build/badge.svg)
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
