![build](https://github.com/duyler/event-bus/workflows/build/badge.svg)
# Event Bus

## Base usage

```php

<?php

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\Enum\ResultStatus;

$bus = BusBuilder::build();

$requestAction = new Action(
    id: 'Request.GetRequest',
    handler: GetRequestAction::class,
);

$blogAction = new Action(
    id: 'Blog.GetPostById',
    handler: GetPostByIdActionInterface::class,
    required: [
        'Request.GetRequest',
    ],
    classMap: [
        GetPostByIdActionInterface::class => GetPostByIdAction::class,
    ],
    providers: [
        PostRepository::class => BlogRepositoryProvider::class,
        GetPostByIdAction::class => GetPostByIdActionProvider::class,
    ],
    arguments: [
        'postId' => PostIdFactory::class
    ],
);

$bus->addAction($blogAction);

$blogActionSubscription = new Subscription(
    subject: 'Request.GetRequest',
    actionId: 'Blog.GetPostById',
    status: ResultStatus::Success,
);

$bus->addSubscription($blogActionSubscription);

$bus->doAction($requestAction);

$bus->run();

$blogPost = $bus->getResult('Blog.GetPostById');

```
