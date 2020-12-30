<?php 

declare(strict_types=1);

namespace Jine\EventBus;

use Jine\EventBus\Contract\ValidateCacheHandlerInterface;
use Jine\EventBus\Dto\Result;
use Jine\EventBus\Dto\Service;
use Jine\EventBus\Dto\Subscribe;

class Bus
{
    private PreloadDispatcher $preloadDispatcher;
    private Dispatcher $dispatcher;
    private ServiceStorage $serviceStorage;
    private SubscribeStorage $subscribeStorage;
    private ActionStorage $actionStorage;
    private BusValidator $busValidator;
    private ResultStorage $resultStorage;
    
    public function __construct(
        PreloadDispatcher $preloadDispatcher,
        Dispatcher $dispatcher,
        ServiceStorage $serviceStorage,
        SubscribeStorage $subscribeStorage,
        ActionStorage $actionStorage,
        BusValidator $busValidator,
        ResultStorage $resultStorage

    ) {
        $this->preloadDispatcher = $preloadDispatcher;
        $this->actionStorage = $actionStorage;
        $this->serviceStorage = $serviceStorage;
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

    public function registerService(string $serviceId): Service
    {
        $service = new Service($this->actionStorage);
        $service->id = $serviceId;

        $this->serviceStorage->save($service);
        
        return $service;
    }

    public function subscribe(string $subject, string $action): static
    {
        $this->subscribeStorage->save(new Subscribe($subject, $action));
        return $this;
    }

    public function preload(string $startAction): void
    {
        $this->preloadDispatcher->run($startAction);
    }

    public function run(string $startAction, callable $callback = null): void
    {
        $this->dispatcher->run($startAction, $callback);
    }

    public function validate(): static
    {
        $this->busValidator->validate();
        return $this;
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
