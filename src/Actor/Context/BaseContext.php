<?php

declare(strict_types=1);

namespace Duyler\EventBus\Actor\Context;

use Closure;
use Duyler\EventBus\Bus\ActorContainer;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionNamedType;

abstract class BaseContext
{
    public function __construct(
        private readonly ActorContainer $actorContainer,
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

            $arguments[$param->getName()] = $this->actorContainer->get($className);
        }


        return $callback(...$arguments);
    }
}
