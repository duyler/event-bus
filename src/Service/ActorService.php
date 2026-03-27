<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Actor\ActorContainerProvider;
use Duyler\EventBus\Build\Actor as ExternalActor;
use Duyler\EventBus\Build\ActorHandlerSubstitution;
use Duyler\EventBus\Build\ActorResultSubstitution;
use Duyler\EventBus\Build\SharedService;
use Duyler\EventBus\Bus\Actor;
use Duyler\EventBus\Bus\ActorRequiredIterator;
use Duyler\EventBus\Bus\ActorRequiredMap;
use Duyler\EventBus\Bus\Bus;
use Duyler\EventBus\Bus\TaskQueue;
use Duyler\EventBus\Contract\ActorSubstitutionInterface;
use Duyler\EventBus\Enum\TaskStatus;
use Duyler\EventBus\Exception\ActorAlreadyDefinedException;
use Duyler\EventBus\Exception\ActorNotDefinedException;
use Duyler\EventBus\Exception\CannotRequirePrivateActorException;
use Duyler\EventBus\Exception\CannotSubscribeOnPrivateActorException;
use Duyler\EventBus\Exception\CannotSubscribeOnRequiredActorException;
use Duyler\EventBus\Exception\CannotSubscribeOnSilentActorException;
use Duyler\EventBus\Exception\CircularCallActorException;
use Duyler\EventBus\Exception\EventNotDefinedException;
use Duyler\EventBus\Exception\NotAllowedSealedActorException;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Internal\Event\ActorAddedEvent;
use Duyler\EventBus\Internal\Event\ActorRemovedEvent;
use Duyler\EventBus\Storage\ActorContainerStorage;
use Duyler\EventBus\Storage\ActorStorage;
use Duyler\EventBus\Storage\CompleteActorStorage;
use Duyler\EventBus\Storage\EventRelationStorage;
use Duyler\EventBus\Storage\EventStorage;
use Duyler\EventBus\Storage\TaskStorage;
use Psr\EventDispatcher\EventDispatcherInterface;

use function array_key_exists;
use function array_pop;
use function count;
use function in_array;

readonly class ActorService
{
    public function __construct(
        private ActorStorage $actorStorage,
        private ActorContainerProvider $actorContainerProvider,
        private ActorSubstitutionInterface $actorSubstitution,
        private EventStorage $eventStorage,
        private Bus $bus,
        private ActorContainerStorage $actorContainerStorage,
        private EventRelationStorage $eventRelationStorage,
        private CompleteActorStorage $completeActorStorage,
        private ActorRequiredMap $actorRequiredMap,
        private TaskStorage $taskStorage,
        private TaskQueue $taskQueue,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    private function validateActor(Actor $actor): void
    {
        if ($this->actorStorage->isExists($actor->getId())) {
            throw new ActorAlreadyDefinedException($actor->getId());
        }

        /** @var string $subject */
        foreach ($actor->getRequired() as $subject) {
            if (false === $this->actorStorage->isExists($subject)) {
                $this->throwActorNotDefined($subject);
            }

            $requiredActor = $this->actorStorage->get($subject);

            $this->checkRequiredActor($actor->getId(), $requiredActor);
        }

        foreach ($actor->getFallbacks() as $actorId) {
            if (false === $this->actorStorage->isExists($actorId)) {
                $this->throwActorNotDefined($actorId);
            }
        }

        foreach ($actor->getSubscriptionEvents() as $eventId) {
            if (false === $this->eventStorage->has($eventId)) {
                $this->throwEventNotDefined($eventId, $actor->getId());
            }
        }

        $this->checkSealedActor($actor);
    }

    public function doExistsActor(string $actorId, string $scope = 'common'): void
    {
        if (false === $this->actorStorage->isExists($actorId)) {
            $this->throwActorNotDefined($actorId);
        }

        $actor = $this->actorStorage->get($actorId);

        $this->bus->doActor($actor, $scope);
    }

    public function getById(string $actorId): Actor
    {
        if (false === $this->actorStorage->isExists($actorId)) {
            $this->throwActorNotDefined($actorId);
        }

        return $this->actorStorage->get($actorId);
    }

    /** @return array<string, Actor> */
    public function getByType(string $type): array
    {
        return $this->actorStorage->getByType($type);
    }

    public function actorIsExists(string $actorId): bool
    {
        return $this->actorStorage->isExists($actorId);
    }

    /**
     * @param array<string, Actor> $actors
     */
    public function collect(array $actors): void
    {
        foreach ($actors as $actor) {

            $requiredIterator = new ActorRequiredIterator($actor->getRequired(), $actors);

            /** @var string $subject */
            foreach ($requiredIterator as $subject) {
                if (false === array_key_exists($subject, $actors)) {
                    $this->throwActorNotDefined($subject);
                }

                $this->checkRequiredActor($actor->getId(), $actors[$subject]);
            }

            foreach ($actor->getFallbacks() as $actorId) {
                if (false === array_key_exists($actorId, $actors)) {
                    $this->throwActorNotDefined($actorId);
                }
            }

            foreach ($actor->getSealed() as $actorId) {
                if (false === array_key_exists($actorId, $actors)) {
                    $this->throwActorNotDefined($actorId);
                }
            }

            foreach ($actor->getSubscriptionEvents() as $eventId) {
                $actorId = IdFormatter::fromEventId($eventId);

                if (in_array($actorId, $actor->getRequired()->getArrayCopy())) {
                    throw new CannotSubscribeOnRequiredActorException($actor->getId(), $actorId);
                }

                if (array_key_exists($actorId, $actors)) {
                    $subscriptionActor = $actors[$actorId];
                    $this->checkSubscriptionActor($actorId, $subscriptionActor);
                }

                if (false === $this->eventStorage->has($eventId) && false === array_key_exists($actorId, $actors)) {
                    $this->throwEventNotDefined($eventId, $actor->getId());
                }
            }

            $this->actorRequiredMap->create($actor);
            $this->actorStorage->save($actor);

            $this->eventDispatcher->dispatch(new ActorAddedEvent(ExternalActor::fromInternal($actor)));
        }
    }

    private function checkRequiredActor(string $subject, Actor $requiredActor): void
    {
        if (in_array($subject, $requiredActor->getRequired()->getArrayCopy())) {
            throw new CircularCallActorException($subject, $requiredActor->getId());
        }

        if ($requiredActor->isPrivate()) {
            throw new CannotRequirePrivateActorException($subject, $requiredActor->getId());
        }

        if (count($requiredActor->getSealed()) > 0 && false === in_array($subject, $requiredActor->getSealed())) {
            throw new NotAllowedSealedActorException($subject, $requiredActor->getId());
        }
    }

    private function checkSubscriptionActor(string $subject, Actor $subscriptionActor): void
    {
        if ($subscriptionActor->isPrivate()) {
            throw new CannotSubscribeOnPrivateActorException($subject, $subscriptionActor->getId());
        }

        if ($subscriptionActor->isSilent()) {
            throw new CannotSubscribeOnSilentActorException($subject, $subscriptionActor->getId());
        }

        if (count($subscriptionActor->getSealed()) > 0
            && false === in_array($subject, $subscriptionActor->getSealed())
        ) {
            throw new NotAllowedSealedActorException($subject, $subscriptionActor->getId());
        }
    }

    private function checkSealedActor(Actor $sealedActor): void
    {
        foreach ($sealedActor->getSealed() as $actorId) {
            if (false === $this->actorStorage->isExists($actorId)) {
                $this->throwActorNotDefined($actorId);
            }
        }
    }

    private function throwActorNotDefined(string $subject): never
    {
        throw new ActorNotDefinedException($subject);
    }

    private function throwEventNotDefined(string $eventId, string $actorId): never
    {
        throw new EventNotDefinedException($eventId, $actorId);
    }

    public function addSharedService(SharedService $sharedService): void
    {
        $this->actorContainerProvider->addSharedService($sharedService);
    }

    public function addResultSubstitutions(ActorResultSubstitution $actorResultSubstitution): void
    {
        $this->actorSubstitution->addResultSubstitutions($actorResultSubstitution);
    }

    public function addHandlerSubstitution(ActorHandlerSubstitution $handlerSubstitution): void
    {
        $this->actorSubstitution->addHandlerSubstitution($handlerSubstitution);
    }

    public function addDynamicActor(Actor $actor): void
    {
        $this->validateActor($actor);

        $this->actorRequiredMap->create($actor);
        $this->actorStorage->save($actor);
        $this->actorStorage->saveDynamic($actor);

        $this->eventDispatcher->dispatch(new ActorAddedEvent(ExternalActor::fromInternal($actor)));
    }

    public function doDynamicActor(Actor $actor, string $scope = 'common'): void
    {
        $this->validateActor($actor);

        $this->actorRequiredMap->create($actor);
        $this->actorStorage->save($actor);
        $this->actorStorage->saveDynamic($actor);

        $this->eventDispatcher->dispatch(new ActorAddedEvent(ExternalActor::fromInternal($actor)));

        $this->bus->doActor($actor, $scope);
    }

    public function removeActor(string $actorId): void
    {
        $stack = [$actorId];
        $visited = [];

        while (0 < count($stack)) {

            $currentActorId = array_pop($stack);

            if (isset($visited[$currentActorId])) {
                continue;
            }

            $visited[$currentActorId] = true;

            if (false === $this->actorStorage->isExistsDynamic($currentActorId)) {
                continue;
            }

            $actor = $this->actorStorage->get($currentActorId);

            $requiredMap = $this->actorRequiredMap->get($currentActorId);

            $this->actorRequiredMap->remove($currentActorId);

            $tasks = $this->taskStorage->getAllByActorId($currentActorId);
            foreach ($tasks as $task) {
                if ($this->taskQueue->inQueue($currentActorId)) {
                    if (TaskStatus::Primary === $task->getStatus()) {
                        $task->reject();
                    }
                }
                if (TaskStatus::Held !== $task->getStatus()) {
                    $this->bus->removeHeldTask($task->getId());
                }
            }

            IdFormatter::remove($currentActorId);

            $this->actorStorage->removeDynamic($currentActorId);
            $this->actorContainerStorage->remove($currentActorId);
            $this->eventRelationStorage->removeByActorId($currentActorId);
            if ($this->completeActorStorage->isExists($currentActorId)) {
                $this->completeActorStorage->remove($currentActorId);
            }

            foreach ($requiredMap as $subject) {
                $stack[] = $subject->getId();
            }

            $this->eventDispatcher->dispatch(new ActorRemovedEvent(ExternalActor::fromInternal($actor)));
        }
    }
}
