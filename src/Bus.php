<?php 

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Contract\State\StateActionAfterHandlerInterface;
use Duyler\EventBus\Contract\State\StateActionBeforeHandlerInterface;
use Duyler\EventBus\Contract\State\StateActionThrowingHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainAfterHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainBeforeHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainFinalHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainStartHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainSuspendHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\ResultService;
use Duyler\EventBus\Service\SubscriptionService;
use Duyler\EventBus\State\StateHandlerStorage;
use Throwable;

readonly class Bus
{
    public function __construct(
        private DoWhile               $doWhile,
        private Rollback              $rollback,
        private StateHandlerStorage   $stateHandlerStorage,
        private ActionService         $actionService,
        private SubscriptionService   $subscriptionService,
        private ResultService         $resultService,
    ) {
    }

    public function addAction(Action $action): static
    {
        $this->actionService->addAction($action);
        return $this;
    }

    public function addSubscription(Subscription $subscription): static
    {
        $this->subscriptionService->addSubscription($subscription);
        return $this;
    }

    /**
     * @throws Throwable
     */
    public function run(): void
    {
        try {
            $this->doWhile->run();
        } catch (Throwable $exception) {
            $this->rollback->run();
            throw $exception;
        }
    }

    public function doAction(Action $action): void
    {
        $this->actionService->doAction($action);
    }

    public function doExistsAction(string $actionId): void
    {
        $this->actionService->doExistsAction($actionId);
    }

    public function actionIsExists(string $actionId): bool
    {
        return $this->actionService->actionIsExists($actionId);
    }

    public function getResult(string $actionId): ?Result
    {
        return $this->resultService->getResult($actionId);
    }

    public function addStateMainStartHandler(StateMainStartHandlerInterface $startHandler): void
    {
        $this->stateHandlerStorage->addStateMainStartHandler($startHandler);
    }

    public function addStateMainBeforeHandler(StateMainBeforeHandlerInterface $beforeActionHandler): void
    {
        $this->stateHandlerStorage->addStateMainBeforeHandler($beforeActionHandler);
    }

    public function setStateMainSuspendHandler(StateMainSuspendHandlerInterface $suspendHandler): void
    {
        $this->stateHandlerStorage->setStateMainSuspendHandler($suspendHandler);
    }

    public function addStateMainAfterHandler(StateMainAfterHandlerInterface $afterActionHandler): void
    {
        $this->stateHandlerStorage->addStateMainAfterHandler($afterActionHandler);
    }

    public function addStateMainFinalHandler(StateMainFinalHandlerInterface $finalHandler): void
    {
        $this->stateHandlerStorage->addStateMainFinalHandler($finalHandler);
    }

    public function addStateActionBeforeHandler(StateActionBeforeHandlerInterface $actionBeforeHandler): void
    {
        $this->stateHandlerStorage->addStateActionBeforeHandler($actionBeforeHandler);
    }

    public function addStateActionThrowingHandler(StateActionThrowingHandlerInterface $actionThrowingHandler): void
    {
        $this->stateHandlerStorage->addStateActionThrowingHandler($actionThrowingHandler);
    }

    public function addStateActionAfterHandler(StateActionAfterHandlerInterface $actionAfterHandler): void
    {
        $this->stateHandlerStorage->addStateActionAfterHandler($actionAfterHandler);
    }
}
