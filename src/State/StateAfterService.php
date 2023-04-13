<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\Control;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\Enum\ResultStatus;

class StateAfterService extends AbstractStateService
{
    public function __construct(
        public readonly ResultStatus  $resultStatus,
        public readonly object | null $resultData,
        public readonly string        $actionId,
        Control      $control
    ) {
        parent::__construct($control);
    }

    public function addSubscription(Subscription $subscription): void
    {
        $this->control->addSubscription($subscription);
    }

    public function subscriptionIsExists(Subscription $subscription): bool
    {
        return $this->control->subscriptionIsExists($subscription);
    }

    public function addAction(Action $action): void
    {
        if ($this->control->actionIsExists($action->id) === false) {
            $this->control->addAction($action);
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
}
