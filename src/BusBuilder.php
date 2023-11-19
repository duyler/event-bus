<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\DependencyInjection\ContainerBuilder;
use Duyler\EventBus\Contract\State\ActionAfterStateHandlerInterface;
use Duyler\EventBus\Contract\State\ActionBeforeStateHandlerInterface;
use Duyler\EventBus\Contract\State\ActionThrowingStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainBeforeStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainFinalStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainStartStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainSuspendStateHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Config;
use Duyler\DependencyInjection\Config as DIConfig;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\StateService;
use Duyler\EventBus\Service\SubscriptionService;
use Duyler\EventBus\State\StateHandlerInterface;
use InvalidArgumentException;

class BusBuilder
{
    /** @var Action[] $actions */
    private array $actions = [];

    /** @var Subscription[] $subscriptions */
    private array $subscriptions = [];

    /** @var Action[] $doActions */
    private array $doActions = [];

    /** @var StateHandlerInterface[] $stateHandlers */
    private array $stateHandlers = [];

    private array $sharedServices = [];

    public function __construct(private ?Config $config = null)
    {
    }

    public function build(): Runner
    {
        $config = new \Duyler\EventBus\Config(
            $this->config,
        );

        $DIConfig = new DIConfig(
            cacheDirPath: $this->config->defaultCacheDir,
        );

        $container = ContainerBuilder::build($DIConfig);
        $container->set($config);

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
            match (true) {
                $stateHandler instanceof MainStartStateHandlerInterface =>
                $stateService->addMainStartStateHandler($stateHandler),
                $stateHandler instanceof MainBeforeStateHandlerInterface =>
                $stateService->addMainBeforeStateHandler($stateHandler),
                $stateHandler instanceof MainSuspendStateHandlerInterface =>
                $stateService->setMainSuspendStateHandler($stateHandler),
                $stateHandler instanceof MainAfterStateHandlerInterface =>
                $stateService->addMainAfterStateHandler($stateHandler),
                $stateHandler instanceof MainFinalStateHandlerInterface =>
                $stateService->addMainFinalStateHandler($stateHandler),
                $stateHandler instanceof ActionBeforeStateHandlerInterface =>
                $stateService->addActionBeforeStateHandler($stateHandler),
                $stateHandler instanceof ActionThrowingStateHandlerInterface =>
                $stateService->addActionThrowingStateHandler($stateHandler),
                $stateHandler instanceof ActionAfterStateHandlerInterface =>
                $stateService->addActionAfterStateHandler($stateHandler),

                default => throw new InvalidArgumentException(sprintf(
                    'State handler %s must be compatibility with %s',
                    get_class($stateHandler),
                    StateHandlerInterface::class,
                ))
            };
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
