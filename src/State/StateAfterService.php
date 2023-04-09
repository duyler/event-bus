<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\Control;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Subscribe;
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

    public function addSubscribe(Subscribe $subscribe): void
    {
        $this->control->addSubscribe($subscribe);
    }

    public function addAction(Action $action): void
    {
        $this->control->addAction($action);
    }
}
