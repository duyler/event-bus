<?php 

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Contract\ValidateCacheHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Dto\StateHandler;
use Duyler\EventBus\Dto\Subscription;
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
        private StateHandlerContainer   $stateHandlerContainer,
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

    public function addStateHandler(StateHandler $stateHandler): void
    {
        $this->stateHandlerContainer->add($stateHandler);
    }
}
