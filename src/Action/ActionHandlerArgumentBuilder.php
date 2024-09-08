<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Closure;
use Duyler\EventBus\Action\Exception\InvalidArgumentFactoryException;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Bus\ActionContainer;
use Duyler\EventBus\Bus\CompleteAction;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Storage\ActionArgumentStorage;
use Duyler\EventBus\Storage\CompleteActionStorage;
use Duyler\EventBus\Storage\EventRelationStorage;
use Duyler\EventBus\Storage\EventStorage;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionNamedType;

class ActionHandlerArgumentBuilder
{
    public function __construct(
        private CompleteActionStorage $completeActionStorage,
        private ActionSubstitution $actionSubstitution,
        private EventRelationStorage $eventRelationStorage,
        private ActionArgumentStorage $actionArgumentStorage,
        private EventStorage $eventStorage,
    ) {}

    public function build(Action $action, ActionContainer $container): object|null
    {
        if (null === $action->argument) {
            return null;
        }

        /** @var array<string, object> $results */
        $results = [];

        if ($this->eventRelationStorage->has($action->id)) {
            $eventDto = $this->eventRelationStorage->shift($action->id)->event;
            $event = $this->eventStorage->get($eventDto->id);
            if (null !== $eventDto->data && null !== $event && null !== $event->contract) {
                $results[$event->contract] = $eventDto->data;
            }
        }

        $completeActions = $this->completeActionStorage->getAllByArray($action->required->getArrayCopy());

        foreach ($completeActions as $completeAction) {
            $results = $this->prepareRequiredResults($completeAction) + $results;
        }

        if ($this->actionSubstitution->isSubstituteResult($action->id)) {
            $actionResultSubstitution = $this->actionSubstitution->getSubstituteResult($action->id);
            $substitution = [
                $actionResultSubstitution->requiredContract => $actionResultSubstitution->substitution,
            ];
            $results = $substitution + $results;
        }

        if (null === $action->argumentFactory) {
            foreach ($results as $definition) {
                if ($definition instanceof $action->argument) {
                    $this->actionArgumentStorage->set($action->id, $definition);
                    return $definition;
                }
            }
            throw new LogicException(
                'Argument factory is not set to unresolved argument: ' . $action->argument . ' for ' . $action->id,
            );
        }

        $factoryArguments = $this->buildFactoryArguments($action->argumentFactory, $results);

        $factory = $action->argumentFactory;

        if (is_string($factory)) {
            $factory = $container->get($factory);
        }

        if (!is_callable($factory)) {
            throw new InvalidArgumentFactoryException($action->argument);
        }

        /** @var object $argument */
        $argument = $factory(...$factoryArguments);
        $this->actionArgumentStorage->set($action->id, $argument);
        return $argument;
    }

    /** @return array<string, object>  */
    private function prepareRequiredResults(CompleteAction $completeAction): array
    {
        $results = [];

        if (ResultStatus::Fail === $completeAction->result->status && null !== $completeAction->action->contract) {
            $alternatesActions = $this->completeActionStorage->getAllByArray($completeAction->action->alternates);

            foreach ($alternatesActions as $alternateAction) {
                if (ResultStatus::Success === $alternateAction->result->status) {
                    if (null === $alternateAction->result->data) {
                        continue;
                    }

                    $results[$completeAction->action->contract] = $alternateAction->result->data;

                    return $results;
                }
            }
        }

        if (null !== $completeAction->result->data && null !== $completeAction->action->contract) {
            $results[$completeAction->action->contract] = $completeAction->result->data;
        }

        return $results;
    }

    /**
     * @param array<string, object> $arguments
     *
     * @throws ReflectionException
     */
    private function buildFactoryArguments(string|Closure $factory, array $arguments = []): array
    {
        if (is_string($factory)) {
            /**
             * @psalm-suppress ArgumentTypeCoercion
             */
            $reflection = new ReflectionClass($factory);
            $methodReflection = null;
            foreach ($reflection->getMethods() as $method) {
                if ('__invoke' === $method->getName()) {
                    $methodReflection = $method;
                    break;
                }
            }

            if (null === $methodReflection) {
                throw new InvalidArgumentException('Method __invoke not found in ' . $factory);
            }

            return $this->matchArguments($methodReflection, $arguments);
        }

        return $this->matchArguments(new ReflectionFunction($factory), $arguments);
    }

    /** @param array<string, object> $arguments */
    private function matchArguments(ReflectionFunctionAbstract $reflection, array $arguments = []): array
    {
        $params = $reflection->getParameters();

        if (empty($params)) {
            return [];
        }

        $result = [];

        foreach ($params as $param) {
            /** @var ReflectionNamedType|null $paramType */
            $paramType = $param->getType();

            if (null === $paramType) {
                throw new InvalidArgumentException('Type hint not set for ' . $param->getName());
            }

            $className = $paramType->getName();

            $result[$param->getName()] = $arguments[$className]
                ?? throw new InvalidArgumentException('Contract not found for ' . $className);
        }

        return $result;
    }
}
