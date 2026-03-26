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
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Storage\CompleteActionStorage;
use Duyler\EventBus\Storage\EventRelationStorage;
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
    ) {}

    public function build(Action $action, ActionContainer $container, string $correlationId): ?object
    {
        /** @var array<string, object> $results */
        $results = [];

        $results = $this->collectSubscriptionResults($action, $correlationId) + $results;

        $completeActions = $this->completeActionStorage->getAllByArray(
            $action->getRequired()->getArrayCopy(),
            $correlationId,
        );

        foreach ($completeActions as $completeAction) {
            $results = $this->prepareRequiredResults($completeAction) + $results;
        }

        if ($this->actionSubstitution->isSubstituteResult($action->getId())) {
            $actionResultSubstitution = $this->actionSubstitution->getSubstituteResult($action->getId());
            /** @var object $substitution */
            $substitution = $actionResultSubstitution->substitution;
            $results[$actionResultSubstitution->requiredActionId] = $substitution;
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
        );

        if (is_callable($factory)) {
            /** @var object $argument */
            $argument = $factory($factoryContext);
        } else {
            $factory = $container->get($factory);

            if (false === is_callable($factory)) {
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

    /**
     * @return array<string, object>
     */
    private function collectSubscriptionResults(Action $action, string $correlationId): array
    {
        $results = [];

        foreach ($action->getSubscriptionEvents() as $eventId) {
            $subjectId = $this->extractSubjectId($eventId);

            if ($this->eventRelationStorage->isExists($eventId, $correlationId)) {
                $eventRelation = $this->eventRelationStorage->getLast($eventId, $correlationId);
                if (null !== $eventRelation->event->data) {
                    /** @var object $eventData */
                    $eventData = $eventRelation->event->data;
                    $results[$subjectId] = $eventData;
                }
            }

            if ($this->completeActionStorage->isExists($subjectId, $correlationId)) {
                $completeAction = $this->completeActionStorage->get($subjectId, $correlationId);
                if (null !== $completeAction->result->data) {
                    /** @var object $resultData */
                    $resultData = $completeAction->result->data;
                    $results[$subjectId] = $resultData;
                }
            }
        }

        return $results;
    }

    private function extractSubjectId(string $eventId): string
    {
        $parts = explode(IdFormatter::DELIMITER, $eventId);
        return $parts[0];
    }

    /**
     * @return array<string, object>
     */
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

                    /** @var object $alternateActionResultData */
                    $alternateActionResultData = $alternateAction->result->data;
                    $results[$completeAction->action->getId()] = $alternateActionResultData;

                    return $results;
                }
            }
        }

        if (null !== $completeAction->result->data && null !== $completeAction->action->getType()) {
            /** @var object $completeActionResultData */
            $completeActionResultData = $completeAction->result->data;
            $results[$completeAction->action->getId()] = $completeActionResultData;
        }

        return $results;
    }

    /**
     * @param null|object $argument
     */
    private function createContext(
        Action $action,
        ActionContainer $container,
        ?object $argument,
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

        /** @var CustomContextInterface $customContext */
        return $customContext;
    }
}
