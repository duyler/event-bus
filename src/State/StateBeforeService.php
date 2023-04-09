<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\Control;

class StateBeforeService extends AbstractStateService
{
    public function __construct(
        public readonly string $actionId,
        Control $control
    ) {
        parent::__construct($control);
    }
}
