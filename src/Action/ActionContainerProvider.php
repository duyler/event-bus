<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\DI\Container;
use Duyler\DI\ContainerInterface;
use Duyler\DI\Definition;
use Duyler\EventBus\Build\SharedService;
use Duyler\EventBus\Bus\Action;
use Duyler\EventBus\Bus\ActionContainer;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Storage\ActionContainerStorage;
use InvalidArgumentException;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

use function array_flip;
use function array_intersect_key;
use function class_exists;
use function count;

class ActionContainerProvider
{
    /** @var array<string, SharedService> */
    private array $sharedServices = [];

    private readonly ContainerInterface $sharedContainer;

    public function __construct(
        private readonly BusConfig $config,
        private readonly ActionContainerStorage $containerStorage,
        private readonly ActionEventDispatcher $dispatcher,
    ) {
        $this->sharedContainer = new Container();
    }

    public function get(Action $action): ActionContainer
    {
        if (false === $this->containerStorage->isExists($action->getId())) {
            $this->buildContainer($action);
        }

        return $this->containerStorage->get($action->getId());
    }

    public function buildContainer(Action $action): void
    {
        $externalConfigDefinitions = [];

        foreach ($this->config->definitions as $definition) {
            $externalConfigDefinitions[$definition->id] = $definition;
        }

        $actionDefinitions = [];

        foreach ($action->getDefinitions() as $key => $value) {
            if (class_exists($key)) {
                $actionDefinitions[$key] = new Definition(id: $key, arguments: $value);
            }
        }

        $actionContainer = new ActionContainer(
            $action->getId(),
            $this->config,
        );

        $actionContainer->bind($action->getBind());
        $actionContainer->addProviders($action->getProviders());
        $actionClassMap = $actionContainer->getClassMap();

        $actionContainer->set($actionContainer);
        $actionContainer->bind([
            PsrContainerInterface::class => ActionContainer::class,
        ]);

        foreach ($this->sharedServices as $sharedService) {
            $container = new Container();
            $container->addProviders($sharedService->providers);
            $container->bind($sharedService->bind);
            $sharedClassMap = $container->getClassMap();

            if (0 < count(array_intersect_key($sharedClassMap, $actionClassMap))
                || 0 < count(array_intersect_key($sharedService->providers, $action->getProviders()))
                || 0 < count(array_intersect_key(array_flip($sharedClassMap), $actionDefinitions))
                || 0 < count(array_intersect_key($actionDefinitions, $externalConfigDefinitions))
            ) {
                $actionContainer->bind($sharedService->bind);
                $actionContainer->addProviders($sharedService->providers);
                $actionContainer->bind($action->getBind());
                $actionContainer->addProviders($action->getProviders());
                continue;
            }

            $actionContainer->addProviders($sharedService->providers);
            $actionContainer->bind($sharedService->bind);

            if (null === $sharedService->service) {
                $this->sharedContainer->bind($sharedService->bind);
                $this->sharedContainer->addProviders($sharedService->providers);
                /** @var object $sharedObject */
                $sharedObject = $this->sharedContainer->get($sharedService->class);
                $actionContainer->set($sharedObject);
            } else {
                $actionContainer->set($sharedService->service);
            }
        }

        foreach ($actionDefinitions as $actionDefinition) {
            $actionContainer->addDefinition($actionDefinition);
        }

        $actionContainer->set($this->dispatcher)
            ->bind([
                EventDispatcherInterface::class => $this->dispatcher::class,
            ]);

        $this->containerStorage->save($actionContainer);
    }

    public function addSharedService(SharedService $sharedService): void
    {
        if (null !== $sharedService->service && false === $sharedService->service instanceof $sharedService->class) {
            throw new InvalidArgumentException('Service must be an instance of ' . $sharedService->class);
        }

        /** @var ActionContainer $actionContainer */
        foreach ($this->containerStorage->getAll() as $actionContainer) {
            if (false === $actionContainer->has($sharedService->class)) {
                $actionContainer->bind($sharedService->bind);
                $actionContainer->addProviders($sharedService->providers);

                /** @var object $service */
                $service = $sharedService->service ?? $actionContainer->get($sharedService->class);
                $actionContainer->set($service);
            }
        }

        $this->sharedServices[$sharedService->class] = $sharedService;
    }
}
