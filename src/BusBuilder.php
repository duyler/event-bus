<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\DependencyInjection\Container;
use Duyler\DependencyInjection\ContainerConfig;
use Duyler\EventBus\Contract\State\StateHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Context;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\StateService;
use Duyler\EventBus\Service\SubscriptionService;

class BusBuilder
{
    /** @var Action[] */
    private array $actions = [];

    /** @var Subscription[] */
    private array $subscriptions = [];

    /** @var Action[] */
    private array $doActions = [];

    /** @var StateHandlerInterface[] */
    private array $stateHandlers = [];

    private array $sharedServices = [];

    private array $bind = [];

    /** @var Context[]  */
    private array $contexts = [];

    public function __construct(private BusConfig $config) {}

    public function build(): BusInterface
    {
        $containerConfig = new ContainerConfig();
        $containerConfig->withBind($this->config->bind);
        $containerConfig->withProvider($this->config->providers);

        foreach ($this->config->definitions as $definition) {
            $containerConfig->withDefinition($definition);
        }

        $container = new Container($containerConfig);
        $container->set($this->config);
        $container->bind($this->config->bind);

        /** @var ActionService $actionService */
        $actionService = $container->get(ActionService::class);

        /** @var SubscriptionService $subscriptionService */
        $subscriptionService = $container->get(SubscriptionService::class);

        /** @var StateService $stateService */
        $stateService = $container->get(StateService::class);

        $actionService->collect($this->actions);

        foreach ($this->sharedServices as $sharedService) {
            $actionService->addSharedService($sharedService, $this->bind);
        }

        foreach ($this->doActions as $action) {
            $actionService->doExistsAction($action->id);
        }

        foreach ($this->subscriptions as $subscription) {
            $subscriptionService->addSubscription($subscription);
        }

        foreach ($this->stateHandlers as $stateHandler) {
            $stateService->addStateHandler($stateHandler);
        }

        foreach ($this->contexts as $context) {
            $stateService->addStateContext($context);
        }

        return $container->get(BusFacade::class);
    }

    public function addAction(Action $action): static
    {
        $this->actions[$action->id] = $action;

        return $this;
    }

    public function addSubscription(Subscription $subscription): static
    {
        $this->subscriptions[] = $subscription;

        return $this;
    }

    public function doAction(Action $action): static
    {
        $this->actions[$action->id] = $action;
        $this->doActions[$action->id] = $action;

        return $this;
    }

    public function addStateHandler(StateHandlerInterface $stateHandler): static
    {
        $this->stateHandlers[get_class($stateHandler)] = $stateHandler;

        return $this;
    }

    public function addStateContext(Context $context): static
    {
        $this->contexts[] = $context;

        return $this;
    }

    public function addSharedService(object $service, array $bind = []): static
    {
        $this->sharedServices[] = $service;
        $this->bind = $bind + $this->bind;

        return $this;
    }
}
