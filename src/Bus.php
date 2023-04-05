<?php 

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Contract\FinalStateHandlerInterface;
use Duyler\EventBus\Contract\StateHandlerInterface;
use Duyler\EventBus\Contract\ValidateCacheHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Dto\Subscribe;
use Throwable;

readonly class Bus
{
    public function __construct(
        private Dispatcher $dispatcher,
        private Validator  $validator,
        private DoWhile    $doWhile,
        private Rollback   $rollback,
        private Storage    $storage,
        private State      $state,
    ) {
    }

    public function addAction(Action $action): static
    {
        $this->storage->action()->save($action);
        return $this;
    }

    public function addSubscribe(Subscribe $subscribe): static
    {
        $this->storage->subscribe()->save($subscribe);
        return $this;
    }

    public function run(string $startAction): void
    {
        $this->dispatcher->dispatchStartedTask($startAction);
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

    public function addStateHandler(StateHandlerInterface $stateHandler): void
    {
        $this->state->addStateHandler($stateHandler);
    }

    public function addFinalStateHandler(FinalStateHandlerInterface $stateHandler): void
    {
        $this->state->addFinalStateHandler($stateHandler);
    }
}
