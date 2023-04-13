<?php 

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Contract\ValidateCacheHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Dto\StateAfterHandler;
use Duyler\EventBus\Dto\StateBeforeHandler;
use Duyler\EventBus\Dto\StateFinalHandler;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\State\StateHandlerBuilder;
use Throwable;

readonly class Bus
{
    public function __construct(
        private Dispatcher          $dispatcher,
        private Validator           $validator,
        private DoWhile             $doWhile,
        private Rollback            $rollback,
        private Storage             $storage,
        private StateHandlerBuilder $stateHandlerBuilder,
    ) {
    }

    public function addAction(Action $action): static
    {
        $this->storage->action()->save($action);
        return $this;
    }

    public function addSubscription(Subscription $subscription): static
    {
        $this->storage->subscription()->save($subscription);
        return $this;
    }

    /**
     * @throws Throwable
     */
    public function run(string $startAction): void
    {
        $this->dispatcher->dispatchStartedAction($startAction);
        $this->validator->validate();

        try {
            $this->doWhile->run();
        } catch (Throwable $exception) {
            $this->rollback->run();
            throw $exception;
        }
    }

    public function setValidateCacheHandler(ValidateCacheHandlerInterface $validateCacheHandler): static
    {
        $this->validator->setValidateCacheHandler($validateCacheHandler);
        return $this;
    }

    public function actionIsExists(string $actionId): bool
    {
        return $this->storage->action()->isExists($actionId);
    }

    public function getResult(string $actionId): ?Result
    {
        return $this->storage->task()->getResult($actionId);
    }

    public function addStateAfterHandler(StateAfterHandler $afterHandler): void
    {
        $this->stateHandlerBuilder->createAfter($afterHandler);
    }

    public function addStateBeforeHandler(StateBeforeHandler $beforeHandler): void
    {
        $this->stateHandlerBuilder->createBefore($beforeHandler);
    }

    public function addStateFinalHandler(StateFinalHandler $finalHandler): void
    {
        $this->stateHandlerBuilder->createFinal($finalHandler);
    }
}
