<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Control;
use Duyler\EventBus\State\Service\Trait\ResultServiceTrait;
use Duyler\EventBus\State\StateServiceInterface;

class StateSuspendService implements StateServiceInterface
{
    use ResultServiceTrait;

    public function __construct(
        private readonly Control $control,
    ) {
    }
}
