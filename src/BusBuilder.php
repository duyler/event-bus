<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\DependencyInjection\ContainerBuilder;
use Duyler\EventBus\Contract\State\StateActionAfterHandlerInterface;
use Duyler\EventBus\Contract\State\StateActionBeforeHandlerInterface;
use Duyler\EventBus\Contract\State\StateActionThrowingHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainAfterHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainBeforeHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainFinalHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainStartHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainSuspendHandlerInterface;
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
    private const CACHE_DIR = '/../var/cache/event-bus/';

    /** @var Action[] $actions */
    private array $actions = [];

    /** @var Subscription[] $subscriptions */
    private array $subscriptions = [];

    /** @var Action[] $doActions */
    private array $doActions = [];

    /** @var StateHandlerInterface[] $stateHandlers */
    private array $stateHandlers = [];

    private array $sharedServices = [];

    private ?Config $config = null;

    public function build(): ?Runner
    {
        $config = $this->config;

        if ($config === null) {
            $config = new Config(
                defaultCacheDir: dirname('__DIR__'). self::CACHE_DIR,
            );
        }

        $DIConfig = new DIConfig(
            cacheDirPath: $config->defaultCacheDir,
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
                $stateHandler instanceof StateMainStartHandlerInterface =>
                $stateService->addStateMainStartHandler($stateHandler),
                $stateHandler instanceof StateMainBeforeHandlerInterface =>
                $stateService->addStateMainBeforeHandler($stateHandler),
                $stateHandler instanceof StateMainSuspendHandlerInterface =>
                $stateService->setStateMainSuspendHandler($stateHandler),
                $stateHandler instanceof StateMainAfterHandlerInterface =>
                $stateService->addStateMainAfterHandler($stateHandler),
                $stateHandler instanceof StateMainFinalHandlerInterface =>
                $stateService->addStateMainFinalHandler($stateHandler),
                $stateHandler instanceof StateActionBeforeHandlerInterface =>
                $stateService->addStateActionBeforeHandler($stateHandler),
                $stateHandler instanceof StateActionThrowingHandlerInterface =>
                $stateService->addStateActionThrowingHandler($stateHandler),
                $stateHandler instanceof StateActionAfterHandlerInterface =>
                $stateService->addStateActionAfterHandler($stateHandler),

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

    public function setConfig(Config $config): static
    {
        $this->config = $config;
        return $this;
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
