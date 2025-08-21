<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Action\Context\ActionContext;
use Duyler\EventBus\Action\Context\CustomContextInterface;
use Duyler\EventBus\Action\Context\FactoryContext;
use Duyler\EventBus\Action\Exception\InvalidArgumentFactoryException;
use Duyler\EventBus\Bus\Action;
use Duyler\EventBus\Bus\ActionContainer;
use Duyler\EventBus\Bus\CompleteAction;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Storage\CompleteActionStorage;
use Duyler\EventBus\Storage\EventRelationStorage;
use Duyler\EventBus\Storage\EventStorage;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;

use function is_callable;

class ActionHandlerArgumentBuilder
{
    public function __construct(
        private readonly CompleteActionStorage $completeActionStorage,
        private readonly ActionSubstitution $actionSubstitution,
        private readonly EventRelationStorage $eventRelationStorage,
        private readonly EventStorage $eventStorage,
    ) {}

    public function build(Action $action, ActionContainer $container): ?object
    {
        /** @var array<string, object> $results */
        $results = [];

        if ($this->eventRelationStorage->has($action->getId())) {
            foreach ($action->getListen() as $eventId) {
                $eventDto = $this->eventRelationStorage->shift($action->getId(), $eventId)->event;
                $event = $this->eventStorage->get($eventDto->id);
                if (null !== $eventDto->data && null !== $event && null !== $event->type) {
                    $results[$event->id] = $eventDto->data;
                }
            }
        }

        $completeActionByType = $this->completeActionStorage->getAllAllowedByTypeArray(
            $action->getDependsOn(),
            $action->getId(),
        );

        foreach ($completeActionByType as $completeAction) {
            $results[$completeAction->action->getId()] = $completeAction->result->data;
        }

        $completeActions = $this->completeActionStorage->getAllByArray($action->getRequired()->getArrayCopy());

        foreach ($completeActions as $completeAction) {
            $results = $this->prepareRequiredResults($completeAction) + $results;
        }

        if ($this->actionSubstitution->isSubstituteResult($action->getId())) {
            $actionResultSubstitution = $this->actionSubstitution->getSubstituteResult($action->getId());
            $results[$actionResultSubstitution->requiredActionId] = $actionResultSubstitution->substitution;
        }

        if (null === $action->getArgument()) {
            if (is_callable($action->getHandler())) {
                return $this->createContext(
                    $action,
                    $container,
                    null,
                );
            }
            return null;
        }

        if (null === $action->getArgumentFactory()) {
            foreach ($results as $definition) {
                $actionArgument = $action->getArgument();
                if ($definition instanceof $actionArgument) {
                    if (is_callable($action->getHandler())) {
                        return $this->createContext(
                            $action,
                            $container,
                            $definition,
                        );
                    }
                    return $definition;
                }
            }
            throw new LogicException(
                'Argument factory is not set to unresolved argument: ' . $action->getArgument() . ' for ' . $action->getId(),
            );
        }

        $factory = $action->getArgumentFactory();

        $factoryContext = new FactoryContext(
            $action->getId(),
            $container,
            $results,
            $this->completeActionStorage->getAllAllowedByTypeArray(
                $action->getDependsOn(),
                $action->getId(),
            ),
        );

        if (is_callable($factory)) {
            /** @var object $argument */
            $argument = $factory($factoryContext);
        } else {
            $factory = $container->get($factory);

            if (!is_callable($factory)) {
                throw new InvalidArgumentFactoryException($action->getArgument());
            }
            /** @var object $argument */
            $argument = $factory($factoryContext);
        }

        if (is_callable($action->getHandler())) {
            return $this->createContext(
                $action,
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

        if (ResultStatus::Fail === $completeAction->result->status && null !== $completeAction->action->getType()) {
            $alternatesActions = $this->completeActionStorage->getAllByArray($completeAction->action->getAlternates());

            foreach ($alternatesActions as $alternateAction) {
                if (ResultStatus::Success === $alternateAction->result->status) {
                    if (null === $alternateAction->result->data) {
                        continue;
                    }

                    $results[$completeAction->action->getId()] = $alternateAction->result->data;

                    return $results;
                }
            }
        }

        if (null !== $completeAction->result->data && null !== $completeAction->action->getType()) {
            $results[$completeAction->action->getId()] = $completeAction->result->data;
        }

        return $results;
    }

    private function createContext(
        Action $action,
        ActionContainer $container,
        mixed $argument,
    ): object {

        $context = new ActionContext(
            $action->getId(),
            $container,
            $argument,
        );

        if (null === $action->getContext()) {
            return $context;
        }

        $reflectionClass = new ReflectionClass($action->getContext());
        $customContext = $reflectionClass->newInstance($context);

        if (false === $customContext instanceof CustomContextInterface) {
            throw new InvalidArgumentException('Custom context class must implement ' . CustomContextInterface::class);
        }

        return $customContext;
    }
}
