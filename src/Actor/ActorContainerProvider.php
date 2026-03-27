<?php

declare(strict_types=1);

namespace Duyler\EventBus\Actor;

use Duyler\DI\Container;
use Duyler\DI\ContainerInterface;
use Duyler\DI\Definition;
use Duyler\EventBus\Build\SharedService;
use Duyler\EventBus\Bus\Actor;
use Duyler\EventBus\Bus\ActorContainer;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Storage\ActorContainerStorage;
use InvalidArgumentException;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

use function array_flip;
use function array_intersect_key;
use function class_exists;
use function count;

class ActorContainerProvider
{
    /** @var array<string, SharedService> */
    private array $sharedServices = [];

    private readonly ContainerInterface $sharedContainer;

    public function __construct(
        private readonly BusConfig $config,
        private readonly ActorContainerStorage $containerStorage,
        private readonly ActorEventDispatcher $dispatcher,
    ) {
        $this->sharedContainer = new Container();
    }

    public function get(Actor $actor, string $scope = 'common'): ActorContainer
    {
        if (false === $this->containerStorage->isExists($actor->getId(), $scope)) {
            $this->buildContainer($actor, $scope);
        }

        return $this->containerStorage->get($actor->getId(), $scope);
    }

    private function buildContainer(Actor $actor, string $scope): void
    {
        $externalConfigDefinitions = [];

        foreach ($this->config->definitions as $definition) {
            $externalConfigDefinitions[$definition->id] = $definition;
        }

        $actorDefinitions = [];

        foreach ($actor->getDefinitions() as $key => $value) {
            if (class_exists($key)) {
                $actorDefinitions[$key] = new Definition(id: $key, arguments: $value);
            }
        }

        $actorContainer = new ActorContainer(
            $actor->getId(),
            $this->config,
            $scope,
        );

        $actorContainer->bind($actor->getBind());
        $actorContainer->addProviders($actor->getProviders());
        $actorClassMap = $actorContainer->getClassMap();

        $actorContainer->set($actorContainer);
        $actorContainer->bind([
            PsrContainerInterface::class => ActorContainer::class,
        ]);

        foreach ($this->sharedServices as $sharedService) {
            $container = new Container();
            $container->addProviders($sharedService->providers);
            $container->bind($sharedService->bind);
            $sharedClassMap = $container->getClassMap();

            if (0 < count(array_intersect_key($sharedClassMap, $actorClassMap))
                || 0 < count(array_intersect_key($sharedService->providers, $actor->getProviders()))
                || 0 < count(array_intersect_key(array_flip($sharedClassMap), $actorDefinitions))
                || 0 < count(array_intersect_key($actorDefinitions, $externalConfigDefinitions))
            ) {
                $actorContainer->bind($sharedService->bind);
                $actorContainer->addProviders($sharedService->providers);
                $actorContainer->bind($actor->getBind());
                $actorContainer->addProviders($actor->getProviders());
                continue;
            }

            $actorContainer->addProviders($sharedService->providers);
            $actorContainer->bind($sharedService->bind);

            if (null === $sharedService->service) {
                $this->sharedContainer->bind($sharedService->bind);
                $this->sharedContainer->addProviders($sharedService->providers);
                /** @var object $sharedObject */
                $sharedObject = $this->sharedContainer->get($sharedService->class);
                $actorContainer->set($sharedObject);
            } else {
                $actorContainer->set($sharedService->service);
            }
        }

        foreach ($actorDefinitions as $actorDefinition) {
            $actorContainer->addDefinition($actorDefinition);
        }

        $actorContainer->set($this->dispatcher)
            ->bind([
                EventDispatcherInterface::class => $this->dispatcher::class,
            ]);

        $this->containerStorage->save($actorContainer);
    }

    public function addSharedService(SharedService $sharedService): void
    {
        if (null !== $sharedService->service && false === $sharedService->service instanceof $sharedService->class) {
            throw new InvalidArgumentException('Service must be an instance of ' . $sharedService->class);
        }

        /** @var ActorContainer $actorContainer */
        foreach ($this->containerStorage->getAll() as $actorContainer) {
            if (false === $actorContainer->has($sharedService->class)) {
                $actorContainer->bind($sharedService->bind);
                $actorContainer->addProviders($sharedService->providers);

                /** @var object $service */
                $service = $sharedService->service ?? $actorContainer->get($sharedService->class);
                $actorContainer->set($service);
            }
        }

        $this->sharedServices[$sharedService->class] = $sharedService;
    }
}
