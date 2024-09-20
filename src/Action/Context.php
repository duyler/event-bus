<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Closure;
use Duyler\EventBus\Bus\ActionContainer;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionNamedType;
use RuntimeException;

final readonly class Context
{
    public function __construct(
        private string $actionId,
        private ActionContainer $actionContainer,
        /** @var array<string, object> */
        private array  $context = [],
    ) {}

    public function contract(string $contract): object
    {
        if (false === array_key_exists($contract, $this->context)) {
            throw new RuntimeException('Addressing an invalid context from ' . $this->actionId);
        }

        return $this->context[$contract];
    }

    public function definition(string $id): object
    {
        return $this->actionContainer->get($id);
    }

    public function call(Closure $callback): mixed
    {
        $reflection = new ReflectionFunction($callback);

        $params = $reflection->getParameters();

        $arguments = [];

        foreach ($params as $param) {
            /** @var ReflectionNamedType|null $paramType */
            $paramType = $param->getType();

            if (null === $paramType) {
                throw new InvalidArgumentException('Type hint not set for ' . $param->getName());
            }

            $className = $paramType->getName();

            $arguments[$param->getName()] = $this->actionContainer->get($className);
        }


        return $callback(...$arguments);
    }
}
