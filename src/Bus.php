<?php 

declare(strict_types=1);

namespace Konveyer\EventBus;

use Konveyer\EventBus\Contract\ValidateCacheHandlerInterface;
use Konveyer\EventBus\Dto\Result;
use Konveyer\EventBus\DTO\Subscribe;
use Konveyer\EventBus\Enum\ResultStatus;
use Konveyer\EventBus\Storage\SubscribeStorage;
use Konveyer\EventBus\Storage\ActionStorage;
use Konveyer\EventBus\Storage\TaskStorage;
use Throwable;

class Bus
{
    private Dispatcher $dispatcher;
    private SubscribeStorage $subscribeStorage;
    private ActionStorage $actionStorage;
    private BusValidator $busValidator;
    private Loop $loop;
    private Rollback $rollback;
    private TaskStorage $taskStorage;
    
    public function __construct(
        Dispatcher $dispatcher,
        SubscribeStorage $subscribeStorage,
        ActionStorage $actionStorage,
        BusValidator $busValidator,
        Loop $loop,
        Rollback $rollback,
        TaskStorage $taskStorage
    ) {
        $this->actionStorage = $actionStorage;
        $this->subscribeStorage = $subscribeStorage;
        $this->dispatcher = $dispatcher;
        $this->busValidator = $busValidator;
        $this->loop = $loop;
        $this->rollback = $rollback;
        $this->taskStorage = $taskStorage;
    }
    
    public static function create(): static
    {
        $container = new Container();
        return $container->instance(static::class);
    }

    public function addAction(Action $action): static
    {
        $this->actionStorage->save($action);
        return $this;
    }

    public function addSubscribe(Subscribe $subscribe): static
    {
        $this->subscribeStorage->save($subscribe);
        return $this;
    }

    public function run(string $startAction): void
    {
        $this->dispatcher->prepareStartedTask($startAction);

        $this->loop->run();
        // try {
        //     $this->loop->run();
        // } catch (Throwable $th) {
        //     $this->rollback->run();
        //     throw $th;
        // }
    }

    public function setValidateCacheHandler(ValidateCacheHandlerInterface $validateCacheHandler): static
    {
        $this->busValidator->setValidateCacheHandler($validateCacheHandler);
        return $this;
    }

    public function actionIsExists(string $actionFullName): bool
    {
        return $this->actionStorage->isExists($actionFullName);
    }

    public function getResult(string $actionFullName): ?Result
    {
        return $this->taskStorage->getResult($actionFullName);
    }
}
