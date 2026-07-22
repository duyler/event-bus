<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\Bus\Actor;
use Duyler\EventBus\Contract\State\StateHandlerObservedInterface;
use Duyler\EventBus\Contract\StateActorInterface;
use Duyler\EventBus\State\Service\StateActorAfterService;
use Duyler\EventBus\State\Service\StateActorBeforeService;
use Duyler\EventBus\State\Service\StateActorThrowingService;
use Duyler\EventBus\Storage\ActorContainerStorage;
use Override;
use Throwable;

use function count;
use function in_array;

class StateActor implements StateActorInterface
{
    public function __construct(
        private readonly StateHandlerStorage $stateHandlerStorage,
        private readonly ActorContainerStorage $actorContainerStorage,
        private readonly StateContextScope $contextScope,
    ) {}

    #[Override]
    public function before(Actor $actor, ?object $argument, string $scope): void
    {
        $stateService = new StateActorBeforeService(
            $this->actorContainerStorage->get($actor->getId(), $scope),
            $actor,
            $argument,
        );

        foreach ($this->stateHandlerStorage->getActorBefore() as $handler) {
            $context = $this->contextScope->getContext($handler::class);
            if ($this->isObserved($handler, $actor, $context)) {
                $handler->handle($stateService, $context);
            }
        }
    }

    #[Override]
    public function after(Actor $actor, mixed $resultData, string $scope): void
    {
        $stateService = new StateActorAfterService(
            $this->actorContainerStorage->get($actor->getId(), $scope),
            $actor,
            $resultData,
        );

        foreach ($this->stateHandlerStorage->getActorAfter() as $handler) {
            $context = $this->contextScope->getContext($handler::class);
            if ($this->isObserved($handler, $actor, $context)) {
                $handler->handle($stateService, $context);
            }
        }
    }

    #[Override]
    public function throwing(Actor $actor, Throwable $exception, string $scope): void
    {
        $stateService = new StateActorThrowingService(
            $this->actorContainerStorage->get($actor->getId(), $scope),
            $exception,
            $actor,
        );

        foreach ($this->stateHandlerStorage->getActorThrowing() as $handler) {
            $context = $this->contextScope->getContext($handler::class);
            if ($this->isObserved($handler, $actor, $context)) {
                $handler->handle($stateService, $context);
            }
        }
    }

    private function isObserved(StateHandlerObservedInterface $handler, Actor $actor, StateContext $context): bool
    {
        $observed = $handler->observed($context);
        return count($observed) === 0 || in_array($actor->getExternalId(), $observed);
    }
}
