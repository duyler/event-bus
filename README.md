![build](https://github.com/jine-framework/event-bus/workflows/build/badge.svg)
# Event Bus

## Base usage
```

use Duyler\EventBus\BusFactory;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Subscribe;
use Duyler\EventBus\Enum\ResultStatus;

$bus = BusFactory::create();

$requestAction = new Action(
    id: 'Request.GetRequest',
    handler: GetRequestAction::class,
    require: [
        'Request.RequestReq'
    ],
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

$blogActionSubscribe = new Subscribe(
    subject: 'Request.GetRequest',
    actionId: 'Blog.GetPostById',
    status: ResultStatus::Success,
);

$bus->addSubscribe($blogActionSubscribe);

$bus->run('Request.GetRequest');

$result = $bus->getResult('Blog.GetPostById');
