<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Closure;
use Duyler\EventBus\Bus\ActionContainer;
use InvalidArgumentException;
use LogicException;
use ReflectionFunction;
use ReflectionNamedType;

final readonly class Context
{
    public function __construct(
        private string $actionId,
        private ActionContainer $actionContainer,
        private null|object $argument,
    ) {}

    public function argument(): object
    {
        return $this->argument ?? throw new LogicException('Argument not defined for action ' . $this->actionId);
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
