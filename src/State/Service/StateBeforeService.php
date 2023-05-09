<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Control;
use Duyler\EventBus\State\Service\Trait\LogServiceTrait;
use Duyler\EventBus\State\StateServiceInterface;

class StateBeforeService implements StateServiceInterface
{
    use LogServiceTrait;

    public function __construct(
        public readonly string   $actionId,
        private readonly Control $control,
    ) {
    }
}
