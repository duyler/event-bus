<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Control;
use Duyler\EventBus\State\Service\Trait\LogServiceTrait;
use Duyler\EventBus\State\Service\Trait\ResultServiceTrait;
use Duyler\EventBus\State\Service\Trait\RollbackServiceTrait;
use Duyler\EventBus\State\StateServiceInterface;

class StateFinalService implements StateServiceInterface
{
    use ResultServiceTrait;
    use LogServiceTrait;
    use RollbackServiceTrait;

    public function __construct(
        private readonly Control $control,
    ) {
    }
}
