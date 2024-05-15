<?php

declare(strict_types=1);

namespace Duyler\ActionBus;

use Duyler\DependencyInjection\Container;
use Duyler\DependencyInjection\ContainerConfig;
use Duyler\ActionBus\Contract\State\StateHandlerInterface;
use Duyler\ActionBus\Dto\Action;
use Duyler\ActionBus\Dto\Context;
use Duyler\ActionBus\Dto\Subscription;
use Duyler\ActionBus\Exception\ActionAlreadyDefinedException;
use Duyler\ActionBus\Exception\SubscriptionAlreadyDefinedException;
use Duyler\ActionBus\Internal\ListenerProvider;
use Duyler\ActionBus\Service\ActionService;
use Duyler\ActionBus\Service\StateService;
use Duyler\ActionBus\Service\SubscriptionService;
use Psr\EventDispatcher\ListenerProviderInterface;

class BusBuilder
{
    /** @var array<string, Action> */
    private array $actions = [];

    /** @var Subscription[] */
    private array $subscriptions = [];

    /** @var array<string, Action> */
    private array $doActions = [];

    /** @var StateHandlerInterface[] */
    private array $stateHandlers = [];

    /** @var object[] */
    private array $sharedServices = [];

    /** @var array<string, string> */
    private array $bind = [];

    /** @var Context[] */
    private array $contexts = [];

    public function __construct(private BusConfig $config) {}

    /**
     * @psalm-suppress MoreSpecificReturnType, LessSpecificReturnStatement
     */
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

        $termination = new Termination($container);
        $container->set($termination);

        /** @var ListenerProvider $listenerProvider */
        $listenerProvider = $container->get(ListenerProviderInterface::class);

        foreach ($this->config->getListeners() as $event => $listeners) {
            foreach ($listeners as $listener) {
                $listenerProvider->addListener($event, $container->get($listener));
            }
        }

        return $container->get(Bus::class);
    }

    public function addAction(Action $action): static
    {
        if (array_key_exists($action->id, $this->actions)) {
            throw new ActionAlreadyDefinedException($action->id);
        }

        $this->actions[$action->id] = $action;

        return $this;
    }

    public function addSubscription(Subscription $subscription): static
    {
        $id = $subscription->subjectId . '@' . $subscription->status->value . '@' . $subscription->actionId;

        if (array_key_exists($id, $this->subscriptions)) {
            throw new SubscriptionAlreadyDefinedException($subscription);
        }

        $this->subscriptions[$id] = $subscription;

        return $this;
    }

    public function doAction(Action $action): static
    {
        if (array_key_exists($action->id, $this->actions)) {
            throw new ActionAlreadyDefinedException($action->id);
        }

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

    /** @param array<string, string> $bind */
    public function addSharedService(object $service, array $bind = []): static
    {
        $this->sharedServices[] = $service;
        $this->bind = $bind + $this->bind;

        return $this;
    }
}
