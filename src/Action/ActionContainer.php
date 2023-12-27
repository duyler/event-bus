<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\DependencyInjection\Container;
use Duyler\DependencyInjection\ContainerConfig;
use Duyler\DependencyInjection\ContainerInterface;
use Duyler\DependencyInjection\Definition;
use Duyler\EventBus\Config;
use Override;

class ActionContainer implements ContainerInterface
{
    private readonly ContainerInterface $container;

    public function __construct(
        public readonly string $actionId,
        public readonly Config $config,
    ) {
        $containerConfig = new ContainerConfig();
        $containerConfig->withBind($config->classMap);
        $containerConfig->withProvider($config->providers);

        foreach ($config->definitions as $definition) {
            $containerConfig->withDefinition($definition);
        }

        $this->container = new Container(
            $containerConfig
        );
    }

    #[Override]
    public function bind(array $classMap): void
    {
        $this->container->bind($classMap);
    }

    #[Override]
    public function getClassMap(): array
    {
        return $this->container->getClassMap();
    }

    #[Override]
    public function get(string $id): mixed
    {
        return $this->container->get($id);
    }

    #[Override]
    public function has(string $id): bool
    {
        return $this->container->has($id);
    }

    #[Override]
    public function addProviders(array $providers): void
    {
        $this->container->addProviders($providers);
    }

    #[Override]
    public function set(object $definition): void
    {
        $this->container->set($definition);
    }

    #[Override]
    public function addDefinition(Definition $definition): void
    {
        $this->container->addDefinition($definition);
    }

    #[Override]
    public function reset(string $id): void
    {
        $this->container->reset($id);
    }
}
