<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\DependencyInjection\Config as DIConfig;
use Duyler\DependencyInjection\ContainerBuilder;
use Duyler\EventBus\Contract\State\StateHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Config;
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

    public function __construct(private ?Config $config = null) {}

    public function build(): BusInterface
    {
        $config = new \Duyler\EventBus\Config(
            $this->config,
        );

        $DIConfig = new DIConfig(
            cacheDirPath: $this->config->defaultCacheDir,
        );

        $container = ContainerBuilder::build($DIConfig);
        $container->set($config);
        $container->bind($config->classMap);

        /** @var ActionService $actionService */
        $actionService = $container->make(ActionService::class);

        /** @var SubscriptionService $subscriptionService */
        $subscriptionService = $container->make(SubscriptionService::class);

        /** @var StateService $stateService */
        $stateService = $container->make(StateService::class);

        $actionService->collect($this->actions);

        foreach ($this->sharedServices as $sharedService) {
            $actionService->addSharedService($sharedService);
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

        /** @var Runner $runner */
        $runner = $container->make(Runner::class);

        return $runner;
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
        $this->stateHandlers[] = $stateHandler;

        return $this;
    }

    public function addSharedService(object $service): static
    {
        $this->sharedServices[] = $service;

        return $this;
    }
}
