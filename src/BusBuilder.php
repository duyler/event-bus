<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\DI\Container;
use Duyler\DI\ContainerConfig;
use Duyler\EventBus\Build\Action as ExternalAction;
use Duyler\EventBus\Build\Context;
use Duyler\EventBus\Build\Event;
use Duyler\EventBus\Build\SharedService;
use Duyler\EventBus\Build\Trigger;
use Duyler\EventBus\Bus\Action as InternalAction;
use Duyler\EventBus\Bus\ErrorHandler;
use Duyler\EventBus\Bus\State;
use Duyler\EventBus\Channel\Channel;
use Duyler\EventBus\Contract\ErrorHandlerInterface;
use Duyler\EventBus\Contract\State\StateHandlerInterface;
use Duyler\EventBus\Event\EventDispatcher;
use Duyler\EventBus\Exception\ActionAlreadyDefinedException;
use Duyler\EventBus\Exception\TriggerAlreadyDefinedException;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Internal\ListenerProvider;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\EventService;
use Duyler\EventBus\Service\StateService;
use Duyler\EventBus\Service\TriggerService;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use UnitEnum;

use function array_key_exists;

class BusBuilder
{
    /** @var array<string, InternalAction> */
    private array $actions = [];

    /** @var Trigger[] */
    private array $triggers = [];

    /** @var array<string, InternalAction> */
    private array $doActions = [];

    /** @var StateHandlerInterface[] */
    private array $stateHandlers = [];

    /** @var SharedService[] */
    private array $sharedServices = [];

    /** @var array<string, string> */
    private array $bind = [];

    /** @var Context[] */
    private array $contexts = [];

    /** @var array<string, Event> */
    private array $events = [];

    /** @var array<string, callable[]> */
    private array $listeners = [];

    private LoggerInterface $logger;

    private ?ErrorHandlerInterface $errorHandler = null;

    public function __construct(private readonly BusConfig $config)
    {
        $this->logger = new NullLogger();
    }

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

        $container->set($this->logger);
        $container->bind([
            LoggerInterface::class => $this->logger::class,
        ]);

        if (null === $this->errorHandler) {
            $container->get(ErrorHandler::class);
            $container->bind([
                ErrorHandlerInterface::class => ErrorHandler::class,
            ]);
        } else {
            $container->set($this->errorHandler);
            $container->bind([
                ErrorHandlerInterface::class => $this->errorHandler::class,
            ]);
        }

        $container->get(IdFormatter::class);

        /** @var ListenerProvider $listenerProvider */
        $listenerProvider = $container->get(ListenerProvider::class);

        foreach ($this->listeners as $event => $listeners) {
            foreach ($listeners as $listener) {
                $listenerProvider->addListener($event, $listener);
            }
        }

        /** @var ActionService $actionService */
        $actionService = $container->get(ActionService::class);

        /** @var EventService $eventService */
        $eventService = $container->get(EventService::class);

        /** @var TriggerService $triggerService */
        $triggerService = $container->get(TriggerService::class);

        /** @var StateService $stateService */
        $stateService = $container->get(StateService::class);

        $eventService->collect($this->events);

        foreach ($this->sharedServices as $sharedService) {
            $actionService->addSharedService($sharedService);
        }

        $actionService->collect($this->actions);

        foreach ($this->doActions as $action) {
            $actionService->doExistsAction($action->getId());
        }

        foreach ($this->triggers as $trigger) {
            $triggerService->addTrigger($trigger);
        }

        foreach ($this->stateHandlers as $stateHandler) {
            $stateService->addStateHandler($stateHandler);
        }

        foreach ($this->contexts as $context) {
            $stateService->addStateContext($context);
        }

        /** @var State $state */
        $state = $container->get(State::class);
        $termination = new Termination($container, $state);
        $container->set($termination);

        foreach ($this->config->getListeners() as $event => $listeners) {
            foreach ($listeners as $listener) {
                /** @var callable $listenerObj */
                $listenerObj = $container->get($listener);
                $listenerProvider->addListener($event, $listenerObj);
            }
        }

        $container->get(Channel::class);
        $container->get(EventDispatcher::class);

        /** @var BusInterface $bus */
        $bus = $container->get(Bus::class);

        gc_collect_cycles();

        return $bus;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function setErrorHandler(ErrorHandlerInterface $errorHandler): void
    {
        $this->errorHandler = $errorHandler;
    }

    public function actionIsExists(string|UnitEnum $actionId): bool
    {
        return array_key_exists(IdFormatter::toString($actionId), $this->actions);
    }

    public function addAction(ExternalAction $action): static
    {
        $internalAction = InternalAction::fromExternal($action);

        if (array_key_exists($internalAction->getId(), $this->actions)) {
            throw new ActionAlreadyDefinedException($internalAction->getId());
        }

        $this->actions[$internalAction->getId()] = $internalAction;

        return $this;
    }

    public function addTrigger(Trigger $trigger): static
    {
        $id = $trigger->subjectId . '@' . $trigger->status->value . '@' . $trigger->actionId;

        if (array_key_exists($id, $this->triggers)) {
            throw new TriggerAlreadyDefinedException($trigger);
        }

        $this->triggers[$id] = $trigger;

        return $this;
    }

    public function doAction(ExternalAction $action): static
    {
        $internalAction = InternalAction::fromExternal($action);

        if (array_key_exists($internalAction->getId(), $this->actions)) {
            throw new ActionAlreadyDefinedException($internalAction->getId());
        }

        $this->actions[$internalAction->getId()] = $internalAction;
        $this->doActions[$internalAction->getId()] = $internalAction;

        return $this;
    }

    public function addStateHandler(StateHandlerInterface $stateHandler): static
    {
        $this->stateHandlers[$stateHandler::class] = $stateHandler;

        return $this;
    }

    public function addStateContext(Context $context): static
    {
        $this->contexts[] = $context;

        return $this;
    }

    public function addSharedService(SharedService $sharedService): static
    {
        $this->sharedServices[] = $sharedService;

        return $this;
    }

    public function addEvent(Event $event): static
    {
        $this->events[$event->id] = $event;

        return $this;
    }

    public function eventIsExists(string|UnitEnum $eventId): bool
    {
        return array_key_exists(IdFormatter::toString($eventId), $this->events);
    }

    /**
     * @param class-string $event
     */
    public function addListener(string $event, callable $listener): static
    {
        if (false === in_array($event, $this->config->getExternalAllowedEvents())) {
            throw new InvalidArgumentException('Event is not allowed or not exists');
        }

        $this->listeners[$event][] = $listener;

        return $this;
    }
}
