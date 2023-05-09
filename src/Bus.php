<?php 

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Contract\ValidateCacheHandlerInterface;
use Duyler\EventBus\Coroutine\CoroutineDriverProvider;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Coroutine;
use Duyler\EventBus\Dto\CoroutineDriver;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Dto\State\StateAfterHandler;
use Duyler\EventBus\Dto\State\StateBeforeHandler;
use Duyler\EventBus\Dto\State\StateFinalHandler;
use Duyler\EventBus\Dto\State\StateStartHandler;
use Duyler\EventBus\Dto\State\StateSuspendHandler;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\State\StateHandlerProvider;
use Duyler\EventBus\State\StateHandlerContainer;
use Throwable;

readonly class Bus
{
    public function __construct(
        private Control                 $control,
        private Validator               $validator,
        private DoWhile                 $doWhile,
        private Rollback                $rollback,
        private Storage                 $storage,
        private Config                  $config,
        private CoroutineDriverProvider $coroutineDriverProvider,
        private StateHandlerContainer   $stateHandlerContainer,
    ) {
    }

    public function addAction(Action $action): static
    {
        $this->storage->action()->save($action);
        return $this;
    }

    public function addCoroutine(Coroutine $coroutine): static
    {
        $this->storage->coroutine()->save($coroutine);
        return $this;
    }

    public function addCoroutineDriver(CoroutineDriver $coroutineDriver): static
    {
        $this->coroutineDriverProvider->register($coroutineDriver);
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
    public function run(): void
    {
        if ($this->config->enabledValidation) {
            $this->validator->validate();
        }

        try {
            $this->doWhile->run();
        } catch (Throwable $exception) {
            $this->rollback->run();
            throw $exception;
        }
    }

    public function doAction(Action $action): void
    {
        $this->control->doAction($action);
    }

    public function doExistsAction(string $actionId): void
    {
        $this->control->doExistsAction($actionId);
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

    public function addStateStartHandler(StateStartHandler $startHandler): void
    {
        $this->stateHandlerContainer->registerStartHandler($startHandler);
    }

    public function addStateBeforeHandler(StateBeforeHandler $beforeHandler): void
    {
        $this->stateHandlerContainer->registerBeforeHandler($beforeHandler);
    }

    public function addStateAfterHandler(StateAfterHandler $afterHandler): void
    {
        $this->stateHandlerContainer->registerAfterHandler($afterHandler);
    }

    public function addStateFinalHandler(StateFinalHandler $finalHandler): void
    {
        $this->stateHandlerContainer->registerFinalHandler($finalHandler);
    }

    public function addStateSuspendHandler(StateSuspendHandler $suspendHandler): void
    {
        $this->stateHandlerContainer->registerSuspendHandler($suspendHandler);
    }
}
