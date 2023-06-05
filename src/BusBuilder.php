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

class BusBuilder
{
    private const CACHE_DIR = '/../var/cache/event-bus/';

    /** @var Action[] $actions */
    private array $actions = [];

    /** @var Subscription[] $subscriptions */
    private array $subscriptions = [];

    /** @var Action[] $doActions */
    private array $doActions = [];

    /** @var StateMainStartHandlerInterface[] $stateMainStartHandlers */
    private array $stateMainStartHandlers = [];

    /** @var StateMainBeforeHandlerInterface[] $stateMainBeforeHandlers */
    private array $stateMainBeforeHandlers = [];

    private StateMainSuspendHandlerInterface|null $stateMainSuspendHandler = null;

    /** @var StateMainAfterHandlerInterface[] $stateMainAfterHandlers */
    private array $stateMainAfterHandlers = [];

    /** @var StateMainFinalHandlerInterface[] $stateMainFinalHandlers */
    private array $stateMainFinalHandlers = [];

    /** @var StateActionBeforeHandlerInterface[] $stateActionBeforeHandlers */
    private array $stateActionBeforeHandlers = [];

    /** @var StateActionThrowingHandlerInterface[] $stateActionThrowingHandlers */
    private array $stateActionThrowingHandlers = [];

    /** @var StateActionAfterHandlerInterface[] $stateActionAfterHandlers */
    private array $stateActionAfterHandlers = [];

    public function build(Config $config = null): ?Runner
    {
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

        foreach ($this->actions as $action) {
            $actionService->addAction($action);
        }

        foreach ($this->doActions as $action) {
            $actionService->doAction($action);
        }

        foreach ($this->subscriptions as $subscription) {
            $subscriptionService->addSubscription($subscription);
        }

        foreach ($this->stateMainStartHandlers as $handler) {
            $stateService->addStateMainStartHandler($handler);
        }

        foreach ($this->stateMainBeforeHandlers as $handler) {
            $stateService->addStateMainBeforeHandler($handler);
        }

        if ($this->stateMainSuspendHandler !== null) {
            $stateService->setStateMainSuspendHandler($this->stateMainSuspendHandler);
        }

        foreach ($this->stateMainAfterHandlers as $handler) {
            $stateService->addStateMainAfterHandler($handler);
        }

        foreach ($this->stateMainFinalHandlers as $handler) {
            $stateService->addStateMainFinalHandler($handler);
        }

        foreach ($this->stateActionBeforeHandlers as $handler) {
            $stateService->addStateActionBeforeHandler($handler);
        }

        foreach ($this->stateActionThrowingHandlers as $handler) {
            $stateService->addStateActionThrowingHandler($handler);
        }

        foreach ($this->stateActionAfterHandlers as $handler) {
            $stateService->addStateActionAfterHandler($handler);
        }

        /** @var Runner $runner */
        $runner = $container->make(Runner::class);

        return $runner;
    }

    public function addAction(Action $action): static
    {
        $this->actions[] = $action;
        return $this;
    }

    public function addSubscription(Subscription $subscription): static
    {
        $this->subscriptions[] = $subscription;
        return $this;
    }

    public function doAction(Action $action): static
    {
        $this->doActions[] = $action;
        return $this;
    }

    public function addStateMainStartHandler(StateMainStartHandlerInterface $startHandler): static
    {
        $this->stateMainStartHandlers[] = $startHandler;
        return $this;
    }

    public function addStateMainBeforeHandler(StateMainBeforeHandlerInterface $beforeActionHandler): static
    {
        $this->stateMainBeforeHandlers[] = $beforeActionHandler;
        return $this;
    }

    public function setStateMainSuspendHandler(StateMainSuspendHandlerInterface $suspendHandler): static
    {
        $this->stateMainSuspendHandler = $suspendHandler;
        return $this;
    }

    public function addStateMainAfterHandler(StateMainAfterHandlerInterface $afterActionHandler): static
    {
        $this->stateMainAfterHandlers[] = $afterActionHandler;
        return $this;
    }

    public function addStateMainFinalHandler(StateMainFinalHandlerInterface $finalHandler): static
    {
        $this->stateMainFinalHandlers[] = $finalHandler;
        return $this;
    }

    public function addStateActionBeforeHandler(StateActionBeforeHandlerInterface $actionBeforeHandler): static
    {
        $this->stateActionBeforeHandlers[] = $actionBeforeHandler;
        return $this;
    }

    public function addStateActionThrowingHandler(StateActionThrowingHandlerInterface $actionThrowingHandler): static
    {
        $this->stateActionThrowingHandlers[] = $actionThrowingHandler;
        return $this;
    }

    public function addStateActionAfterHandler(StateActionAfterHandlerInterface $actionAfterHandler): static
    {
        $this->stateActionAfterHandlers[] = $actionAfterHandler;
        return $this;
    }
}
