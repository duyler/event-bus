![build](https://github.com/jine-framework/event-bus/workflows/build/badge.svg)
# Event Bus

## Base usage
```

use Duyler\EventBus\BusFactory;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\Enum\ResultStatus;

$bus = BusFactory::create();

$requestAction = new Action(
    id: 'Request.GetRequest',
    handler: GetRequestAction::class,
);

$blogAction = new Action(
    id: 'Blog.GetPostById',
    handler: GetPostByIdActionInterface::class,
    require: [
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

$bus->addAction($requestAction);
$bus->addAction($blogAction);

$blogActionSubscription = new Subscription(
    subject: 'Request.GetRequest',
    actionId: 'Blog.GetPostById',
    status: ResultStatus::Success,
);

$bus->addSubscription($blogActionSubscription);

$bus->run('Request.GetRequest');

$result = $bus->getResult('Blog.GetPostById');
