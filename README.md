![build](https://github.com/duyler/event-bus/workflows/build/badge.svg)
# Event Bus

## Base usage

```php

<?php

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\Enum\ResultStatus;

$busBuilder = new BusBuilder();

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

$busBuilder->addAction($blogAction);

$blogActionSubscription = new Subscription(
    subject: 'Request.GetRequest',
    actionId: 'Blog.GetPostById',
    status: ResultStatus::Success,
);

$busBuilder->addSubscription($blogActionSubscription);

$busBuilder->doAction($requestAction);

$runner = $busBuilder->build();

$runner->run();

$blogPost = $runner->getResult('Blog.GetPostById');

```
