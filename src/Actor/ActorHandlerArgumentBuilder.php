<?php

declare(strict_types=1);

namespace Duyler\EventBus\Actor;

use Duyler\EventBus\Actor\Context\ActorContext;
use Duyler\EventBus\Actor\Context\CustomContextInterface;
use Duyler\EventBus\Actor\Context\FactoryContext;
use Duyler\EventBus\Actor\Exception\InvalidArgumentFactoryException;
use Duyler\EventBus\Bus\Actor;
use Duyler\EventBus\Bus\ActorContainer;
use Duyler\EventBus\Bus\CompleteActor;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Storage\CompleteActorStorage;
use Duyler\EventBus\Storage\EventRelationStorage;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;

use function is_callable;

class ActorHandlerArgumentBuilder
{
    public function __construct(
        private readonly CompleteActorStorage $completeActorStorage,
        private readonly ActorSubstitution $actorSubstitution,
        private readonly EventRelationStorage $eventRelationStorage,
    ) {}

    public function build(Actor $actor, ActorContainer $container, string $scope): ?object
    {
        /** @var array<string, object> $results */
        $results = [];

        $results = $this->collectSubscriptionResults($actor, $scope) + $results;

        $completeActors = $this->completeActorStorage->getAllByArray(
            $actor->getRequired()->getArrayCopy(),
            $scope,
        );

        foreach ($completeActors as $completeActor) {
            $results = $this->prepareRequiredResults($completeActor) + $results;
        }

        if ($this->actorSubstitution->isSubstituteResult($actor->getId())) {
            $actorResultSubstitution = $this->actorSubstitution->getSubstituteResult($actor->getId());
            /** @var object $substitution */
            $substitution = $actorResultSubstitution->substitution;
            $results[$actorResultSubstitution->requiredActorId] = $substitution;
        }

        if (null === $actor->getArgument()) {
            if (is_callable($actor->getHandler())) {
                return $this->createContext(
                    $actor,
                    $container,
                    null,
                );
            }
            return null;
        }

        if (null === $actor->getArgumentFactory()) {
            foreach ($results as $definition) {
                $actorArgument = $actor->getArgument();
                if ($definition instanceof $actorArgument) {
                    if (is_callable($actor->getHandler())) {
                        return $this->createContext(
                            $actor,
                            $container,
                            $definition,
                        );
                    }
                    return $definition;
                }
            }
            throw new LogicException(
                'Argument factory is not set to unresolved argument: ' . $actor->getArgument() . ' for ' . $actor->getId(),
            );
        }

        $factory = $actor->getArgumentFactory();

        $factoryContext = new FactoryContext(
            $actor->getId(),
            $container,
            $results,
        );

        if (is_callable($factory)) {
            /** @var object $argument */
            $argument = $factory($factoryContext);
        } else {
            $factory = $container->get($factory);

            if (false === is_callable($factory)) {
                throw new InvalidArgumentFactoryException($actor->getArgument());
            }
            /** @var object $argument */
            $argument = $factory($factoryContext);
        }

        if (is_callable($actor->getHandler())) {
            return $this->createContext(
                $actor,
                $container,
                $argument,
            );
        }

        return $argument;
    }

    /**
     * @return array<string, object>
     */
    private function collectSubscriptionResults(Actor $actor, string $scope): array
    {
        $results = [];

        foreach ($actor->getSubscriptionEvents() as $eventId) {
            $subjectId = IdFormatter::fromEventId($eventId);

            if ($this->eventRelationStorage->isExists($eventId, $scope)) {
                $eventRelation = $this->eventRelationStorage->getLast($eventId, $scope);
                if (null !== $eventRelation->event->data) {
                    /** @var object $eventData */
                    $eventData = $eventRelation->event->data;
                    $results[$subjectId] = $eventData;
                }
            }

            if ($this->completeActorStorage->isExists($subjectId, $scope)) {
                $completeActor = $this->completeActorStorage->get($subjectId, $scope);
                if (null !== $completeActor->result->data) {
                    /** @var object $resultData */
                    $resultData = $completeActor->result->data;
                    $results[$subjectId] = $resultData;
                }
            }
        }

        return $results;
    }

    /**
     * @return array<string, object>
     */
    private function prepareRequiredResults(CompleteActor $completeActor): array
    {
        $results = [];

        if (ResultStatus::Fail === $completeActor->result->status && null !== $completeActor->actor->getType()) {
            $fallbackActors = $this->completeActorStorage->getAllByArray($completeActor->actor->getFallbacks());

            foreach ($fallbackActors as $fallbackActor) {
                if (ResultStatus::Success === $fallbackActor->result->status) {
                    if (null === $fallbackActor->result->data) {
                        continue;
                    }

                    /** @var object $fallbackActorResultData */
                    $fallbackActorResultData = $fallbackActor->result->data;
                    $results[$completeActor->actor->getId()] = $fallbackActorResultData;

                    return $results;
                }
            }
        }

        if (null !== $completeActor->result->data && null !== $completeActor->actor->getType()) {
            /** @var object $completeActorResultData */
            $completeActorResultData = $completeActor->result->data;
            $results[$completeActor->actor->getId()] = $completeActorResultData;
        }

        return $results;
    }

    /**
     * @param null|object $argument
     */
    private function createContext(
        Actor $actor,
        ActorContainer $container,
        ?object $argument,
    ): object {
        $context = new ActorContext(
            $actor->getId(),
            $container,
            $argument,
        );

        if (null === $actor->getContext()) {
            return $context;
        }

        $reflectionClass = new ReflectionClass($actor->getContext());
        $customContext = $reflectionClass->newInstance($context);

        if (false === $customContext instanceof CustomContextInterface) {
            throw new InvalidArgumentException('Custom context class must implement ' . CustomContextInterface::class);
        }

        /** @var CustomContextInterface $customContext */
        return $customContext;
    }
}
