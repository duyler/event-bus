<?php 

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Dto\StateHandler;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\State\StateHandlerContainer;
use Throwable;

readonly class Bus
{
    public function __construct(
        private Control               $control,
        private DoWhile               $doWhile,
        private Rollback              $rollback,
        private StateHandlerContainer $stateHandlerContainer,
    ) {
    }

    public function addAction(Action $action): static
    {
        $this->control->addAction($action);
        return $this;
    }

    public function addSubscription(Subscription $subscription): static
    {
        $this->control->addSubscription($subscription);
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
        $this->control->doAction($action);
    }

    public function doExistsAction(string $actionId): void
    {
        $this->control->doExistsAction($actionId);
    }

    public function actionIsExists(string $actionId): bool
    {
        return $this->control->actionIsExists($actionId);
    }

    public function getResult(string $actionId): ?Result
    {
        return $this->control->getResult($actionId);
    }

    public function addStateHandler(StateHandler $stateHandler): void
    {
        $this->stateHandlerContainer->add($stateHandler);
    }
}
