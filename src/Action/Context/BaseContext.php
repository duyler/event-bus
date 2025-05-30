<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action\Context;

use Closure;
use Duyler\EventBus\Bus\ActionContainer;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionNamedType;

abstract class BaseContext
{
    public function __construct(
        private readonly ActionContainer $actionContainer,
    ) {}

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
