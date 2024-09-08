<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Build\SharedService;
use Duyler\EventBus\Bus\ActionContainer;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Storage\ActionContainerStorage;
use Duyler\DependencyInjection\Container;
use Duyler\DependencyInjection\ContainerInterface;
use Duyler\DependencyInjection\Definition;
use Duyler\DependencyInjection\Provider\ProviderInterface;
use InvalidArgumentException;

class ActionContainerProvider
{
    /** @var array<string, SharedService> */
    private array $sharedServices = [];

    private ContainerInterface $sharedContainer;

    public function __construct(
        private readonly BusConfig $config,
        private readonly ActionContainerStorage $containerStorage,
    ) {
        $this->sharedContainer = new Container();
    }

    public function get(Action $action): ActionContainer
    {
        if (false === $this->containerStorage->isExists($action->id)) {
            $this->buildContainer($action);
        }

        return $this->containerStorage->get($action->id);
    }

    public function buildContainer(Action $action): void
    {
        $externalConfigDefinitions = [];

        foreach ($this->config->definitions as $definition) {
            $externalConfigDefinitions[$definition->id] = $definition;
        }

        $actionProviders = [];
        $actionBind = [];
        $actionDefinitions = [];

        foreach ($action->config as $key => $value) {
            if (class_exists($key) || interface_exists($key)) {
                if (is_string($value)) {
                    $implements = class_implements($value);

                    if (is_array($implements) && in_array(ProviderInterface::class, $implements)) {
                        $actionProviders[$key] = $value;
                        continue;
                    }

                    if (interface_exists($key) && class_exists($value)) {
                        $actionBind[$key] = $value;
                        continue;
                    }
                }

                if (is_array($value)) {
                    $actionDefinitions[$key] = new Definition(id: $key, arguments: $value);
                    continue;
                }

                if (is_object($value) && is_a($value, Definition::class)) {
                    $actionDefinitions[$key] = $value;
                }
            }
        }

        $actionContainer = new ActionContainer(
            $action->id,
            $this->config,
        );

        $actionContainer->bind($actionBind);
        $actionContainer->addProviders($actionProviders);
        $actionClassMap = $actionContainer->getClassMap();

        foreach ($this->sharedServices as $sharedService) {
            $container = new Container();
            $container->addProviders($sharedService->providers);
            $container->bind($sharedService->bind);
            $sharedClassMap = $container->getClassMap();

            if (0 < count(array_intersect_key($sharedClassMap, $actionClassMap))
                || 0 < count(array_intersect_key($sharedService->providers, $actionProviders))
                || 0 < count(array_intersect_key(array_flip($sharedClassMap), $actionDefinitions))
                || 0 < count(array_intersect_key($actionDefinitions, $externalConfigDefinitions))
            ) {
                $actionContainer->bind($sharedService->bind);
                $actionContainer->addProviders($sharedService->providers);
                $actionContainer->bind($actionBind);
                $actionContainer->addProviders($actionProviders);
                continue;
            }

            $actionContainer->addProviders($sharedService->providers);
            $actionContainer->bind($sharedService->bind);

            if (null === $sharedService->service) {
                $this->sharedContainer->bind($sharedService->bind);
                $this->sharedContainer->addProviders($sharedService->providers);
                $actionContainer->set($this->sharedContainer->get($sharedService->class));
            } else {
                $actionContainer->set($sharedService->service);
            }
        }

        foreach ($actionDefinitions as $actionDefinition) {
            $actionContainer->addDefinition($actionDefinition);
        }

        $this->containerStorage->save($actionContainer);
    }

    public function addSharedService(SharedService $sharedService): void
    {
        if (null !== $sharedService->service && false === $sharedService->service instanceof $sharedService->class) {
            throw new InvalidArgumentException('Service must be an instance of ' . $sharedService->class);
        }

        $this->sharedServices[$sharedService->class] = $sharedService;
    }
}
