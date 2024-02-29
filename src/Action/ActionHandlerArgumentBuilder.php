<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Closure;
use Duyler\EventBus\Action\Exception\InvalidArgumentFactoryException;
use Duyler\EventBus\Bus\CompleteAction;
use Duyler\EventBus\Collection\CompleteActionCollection;
use Duyler\EventBus\Collection\TriggerRelationCollection;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Enum\ResultStatus;
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
        private CompleteActionCollection $completeActionCollection,
        private ActionSubstitution $actionSubstitution,
        private TriggerRelationCollection $triggerRelationCollection,
    ) {}

    /** @psalm-suppress MixedReturnStatement */
    public function build(Action $action, ActionContainer $container): mixed
    {
        if ($action->argument === null) {
            return null;
        }

        /** @var array<string, object> $results */
        $results = [];

        if ($this->triggerRelationCollection->has($action->id)) {
            $trigger = $this->triggerRelationCollection->shift($action->id)->trigger;
            if ($trigger->data !== null && $trigger->contract !== null) {
                $results[$trigger->contract] = $trigger->data;
            }
        }

        $completeActions = $this->completeActionCollection->getAllByArray($action->required->getArrayCopy());

        foreach ($completeActions as $completeAction) {
            $results = $this->prepareRequiredResults($completeAction) + $results;
        }

        if ($this->actionSubstitution->isSubstituteResult($action->id)) {
            $results = $this->actionSubstitution->getSubstituteResult($action->id) + $results;
        }

        if ($action->argumentFactory === null) {
            foreach ($results as $definition) {
                if ($definition instanceof $action->argument) {
                    return $definition;
                }
            }
            throw new LogicException(
                'Argument factory is not set to unresolved argument: ' . $action->argument . ' for ' . $action->id
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

        return $factory(...$factoryArguments);
    }

    /** @return array<string, object>  */
    private function prepareRequiredResults(CompleteAction $completeAction): array
    {
        $results = [];

        if (ResultStatus::Fail === $completeAction->result->status && $completeAction->action->contract !== null) {
            $alternatesActions = $this->completeActionCollection->getAllByArray($completeAction->action->alternates);

            foreach ($alternatesActions as $alternateAction) {
                if (ResultStatus::Success === $alternateAction->result->status) {
                    if ($alternateAction->result->data === null) {
                        continue;
                    }

                    $results[$completeAction->action->contract] = $alternateAction->result->data;

                    return $results;
                }
            }
        }

        if ($completeAction->result->data !== null && $completeAction->action->contract !== null) {
            $results[$completeAction->action->contract] = $completeAction->result->data;
        }

        return $results;
    }

    /**
     * @param array<string, object> $arguments
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
                if ($method->getName() === '__invoke') {
                    $methodReflection = $method;
                    break;
                }
            }

            if ($methodReflection === null) {
                throw new InvalidArgumentException(
                    'Method __invoke not found in ' . $factory
                );
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
            /** @var null|ReflectionNamedType $paramType */
            $paramType = $param->getType();

            if ($paramType === null) {
                throw new InvalidArgumentException('Type hint not set for ' . $param->getName());
            }

            $className = $paramType->getName();

            $result[$param->getName()] = $arguments[$className]
                ?? throw new InvalidArgumentException('Contract not found for ' . $className);
        }

        return $result;
    }
}
