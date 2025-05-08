<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Action\Context\ActionContext;
use Duyler\EventBus\Action\Context\FactoryContext;
use Duyler\EventBus\Action\Exception\InvalidArgumentFactoryException;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Bus\ActionContainer;
use Duyler\EventBus\Bus\CompleteAction;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Storage\CompleteActionStorage;
use Duyler\EventBus\Storage\EventRelationStorage;
use Duyler\EventBus\Storage\EventStorage;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionNamedType;

class ActionHandlerArgumentBuilder
{
    public function __construct(
        private CompleteActionStorage $completeActionStorage,
        private ActionSubstitution $actionSubstitution,
        private EventRelationStorage $eventRelationStorage,
        private EventStorage $eventStorage,
    ) {}

    public function build(Action $action, ActionContainer $container): object|null
    {
        /** @var array<string, object> $results */
        $results = [];

        if ($this->eventRelationStorage->has($action->id)) {
            foreach ($action->listen as $eventId) {
                $eventDto = $this->eventRelationStorage->shift($action->id, $eventId)->event;
                $event = $this->eventStorage->get($eventDto->id);
                if (null !== $eventDto->data && null !== $event && null !== $event->type) {
                    $results[$event->id] = $eventDto->data;
                }
            }
        }

        $completeActions = $this->completeActionStorage->getAllByArray($action->required->getArrayCopy());

        foreach ($completeActions as $completeAction) {
            $results = $this->prepareRequiredResults($completeAction) + $results;
        }

        if ($this->actionSubstitution->isSubstituteResult($action->id)) {
            $actionResultSubstitution = $this->actionSubstitution->getSubstituteResult($action->id);
            $results[$actionResultSubstitution->requiredActionId] = $actionResultSubstitution->substitution;
        }

        if (null === $action->argument) {
            if (is_callable($action->handler)) {
                return new ActionContext(
                    $action->id,
                    $container,
                    null,
                );
            }
            return null;
        }

        if (null === $action->argumentFactory) {
            foreach ($results as $definition) {
                if ($definition instanceof $action->argument) {
                    if (is_callable($action->handler)) {
                        return new ActionContext(
                            $action->id,
                            $container,
                            $definition,
                        );
                    }
                    return $definition;
                }
            }
            throw new LogicException(
                'Argument factory is not set to unresolved argument: ' . $action->argument . ' for ' . $action->id,
            );
        }

        $factory = $action->argumentFactory;

        $factoryContext = new FactoryContext(
            $action->id,
            $container,
            $results,
        );

        if (is_callable($factory)) {
            /** @var object $argument */
            $argument = $factory($factoryContext);
        } else {
            $factory = $container->get($factory);

            if (!is_callable($factory)) {
                throw new InvalidArgumentFactoryException($action->argument);
            }
            /** @var object $argument */
            $argument = $factory($factoryContext);
        }

        if (is_callable($action->handler)) {
            return new ActionContext(
                $action->id,
                $container,
                $argument,
            );
        }

        return $argument;
    }

    /** @return array<string, object>  */
    private function prepareRequiredResults(CompleteAction $completeAction): array
    {
        $results = [];

        if (ResultStatus::Fail === $completeAction->result->status && null !== $completeAction->action->type) {
            $alternatesActions = $this->completeActionStorage->getAllByArray($completeAction->action->alternates);

            foreach ($alternatesActions as $alternateAction) {
                if (ResultStatus::Success === $alternateAction->result->status) {
                    if (null === $alternateAction->result->data) {
                        continue;
                    }

                    $results[$completeAction->action->id] = $alternateAction->result->data;

                    return $results;
                }
            }
        }

        if (null !== $completeAction->result->data && null !== $completeAction->action->type) {
            $results[$completeAction->action->id] = $completeAction->result->data;
        }

        return $results;
    }
}
