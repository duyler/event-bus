<?php 

declare(strict_types=1);

namespace Jine\EventBus;

use Jine\EventBus\Dto\Service;
use Jine\EventBus\Dto\Subscribe;

class Bus
{
    private Dispatcher $dispatcher;
    private ConfigProvider $config;
    private ServiceStorage $serviceStorage;
    private SubscribeStorage $subscribeStorage;
    private ActionStorage $actionStorage;
    private BusValidator $busValidator;
    private TaskManager $taskManager;
    
    public function __construct(
        Dispatcher $dispatcher,
        ConfigProvider $config,
        ServiceStorage $serviceStorage,
        SubscribeStorage $subscribeStorage,
        ActionStorage $actionStorage,
        BusValidator $busValidator,
        TaskManager $taskManager

    ) {
        $this->actionStorage = $actionStorage;
        $this->serviceStorage = $serviceStorage;
        $this->config = $config;
        $this->subscribeStorage = $subscribeStorage;
        $this->dispatcher = $dispatcher;
        $this->busValidator = $busValidator;
        $this->taskManager = $taskManager;
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

    public function run(string $startAction): void
    {
        $this->busValidator->validate();
        $this->dispatcher->startLoop($startAction);
    }

    public function setCachePath(string $path): static
    {
        $this->config->setCachePath($path);
        return $this;
    }

    public function actionIsExists(string $actionFullName): bool
    {
        return $this->actionStorage->isExists($actionFullName);
    }

    public function registerSharedDefinitions(array $definitions): static
    {
        $this->taskManager->registerSharedDefinitions($definitions);
        return $this;
    }

    public function getResult(): ?object
    {
        return $this->dispatcher->getResult();
    }
}
