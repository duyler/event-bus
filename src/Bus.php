<?php 

declare(strict_types=1);

namespace Jine\EventBus;

use Jine\EventBus\Contract\ValidateCacheHandlerInterface;
use Jine\EventBus\Dto\Result;
use Jine\EventBus\Dto\Subscribe;

class Bus
{
    private PreloadDispatcher $preloadDispatcher;
    private Dispatcher $dispatcher;
    private SubscribeStorage $subscribeStorage;
    private ActionStorage $actionStorage;
    private BusValidator $busValidator;
    private ResultStorage $resultStorage;
    
    public function __construct(
        PreloadDispatcher $preloadDispatcher,
        Dispatcher $dispatcher,
        SubscribeStorage $subscribeStorage,
        ActionStorage $actionStorage,
        BusValidator $busValidator,
        ResultStorage $resultStorage

    ) {
        $this->preloadDispatcher = $preloadDispatcher;
        $this->actionStorage = $actionStorage;
        $this->subscribeStorage = $subscribeStorage;
        $this->dispatcher = $dispatcher;
        $this->busValidator = $busValidator;
        $this->resultStorage = $resultStorage;
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

    public function subscribe(string $subject, string $action): static
    {
        $this->subscribeStorage->save(new Subscribe($subject, $action));
        return $this;
    }

    public function preload(string $startAction, callable $callback = null): void
    {
        $this->busValidator->validate();
        $this->preloadDispatcher->run($startAction, $callback);
    }

    public function run(string $startAction, callable $callback = null): void
    {
        $this->busValidator->validate();
        $this->dispatcher->run($startAction, $callback);
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
        if ($this->resultStorage->isExists($actionFullName)) {
            return $this->resultStorage->getResult($actionFullName);
        }
        return null;
    }
}
