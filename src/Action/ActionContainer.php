<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\DependencyInjection\ContainerBuilder;
use Duyler\DependencyInjection\ContainerConfig;
use Duyler\DependencyInjection\ContainerInterface;
use Duyler\EventBus\Config;
use Override;

class ActionContainer implements ContainerInterface
{
    private readonly ContainerInterface $container;

    public function __construct(
        public readonly string $actionId,
        public readonly Config $config,
    ) {
        $this->container = ContainerBuilder::build(
            new ContainerConfig(
                enableCache: $config->enableCache,
                fileCacheDirPath: $config->fileCacheDirPath,
            )
        );
    }

    public static function build(
        string $actionId,
        Config $config,
    ): self {
        return new self(
            $actionId,
            $config,
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
    public function make(string $className, string $provider = '', bool $singleton = true): mixed
    {
        return $this->container->make($className, $provider, $singleton);
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
}
