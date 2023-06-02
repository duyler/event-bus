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
use Duyler\EventBus\Collector\StateHandlerCollector;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Dto\Subscription;
use Throwable;

readonly class Bus
{
    public function __construct(
        private BusService            $busService,
        private DoWhile               $doWhile,
        private Rollback              $rollback,
        private StateHandlerCollector $stateHandlerCollector,
    ) {
    }

    public function addAction(Action $action): static
    {
        $this->busService->addAction($action);
        return $this;
    }

    public function addSubscription(Subscription $subscription): static
    {
        $this->busService->addSubscription($subscription);
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
        $this->busService->doAction($action);
    }

    public function doExistsAction(string $actionId): void
    {
        $this->busService->doExistsAction($actionId);
    }

    public function actionIsExists(string $actionId): bool
    {
        return $this->busService->actionIsExists($actionId);
    }

    public function getResult(string $actionId): ?Result
    {
        return $this->busService->getResult($actionId);
    }

    public function addStateMainStartHandler(StateMainStartHandlerInterface $startHandler): void
    {
        $this->stateHandlerCollector->addStateMainStartHandler($startHandler);
    }

    public function addStateMainBeforeHandler(StateMainBeforeHandlerInterface $beforeActionHandler): void
    {
        $this->stateHandlerCollector->addStateMainBeforeHandler($beforeActionHandler);
    }

    public function setStateMainSuspendHandler(StateMainSuspendHandlerInterface $suspendHandler): void
    {
        $this->stateHandlerCollector->setStateMainSuspendHandler($suspendHandler);
    }

    public function addStateMainAfterHandler(StateMainAfterHandlerInterface $afterActionHandler): void
    {
        $this->stateHandlerCollector->addStateMainAfterHandler($afterActionHandler);
    }

    public function addStateMainFinalHandler(StateMainFinalHandlerInterface $finalHandler)
    {
        $this->stateHandlerCollector->addStateMainFinalHandler($finalHandler);
    }

    public function addStateActionBeforeHandler(StateActionBeforeHandlerInterface $actionBeforeHandler): void
    {
        $this->stateHandlerCollector->addStateActionBeforeHandler($actionBeforeHandler);
    }

    public function addStateActionThrowingHandler(StateActionThrowingHandlerInterface $actionThrowingHandler): void
    {
        $this->stateHandlerCollector->addStateActionThrowingHandler($actionThrowingHandler);
    }

    public function addStateActionAfterHandler(StateActionAfterHandlerInterface $actionAfterHandler): void
    {
        $this->stateHandlerCollector->addStateActionAfterHandler($actionAfterHandler);
    }
}
