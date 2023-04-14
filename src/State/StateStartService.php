<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\Control;

class StateStartService implements StateServiceInterface
{
    use StateServiceTrait;

    public function __construct(
        public readonly Control $control,
    ) {
    }
}
